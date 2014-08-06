<?php
namespace OnlineNow\OnLinkvalidatorTv\XClass\Linkvalidator;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Online Now! GmbH (typo3@online-now.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
 
 /**
 * This class provides link analyzing for templavoila data
 *
 * @author Online Now! GmbH <typo3@online-now.de>
 */

class LinkAnalyzer extends \TYPO3\CMS\Linkvalidator\LinkAnalyzer {

	const TV_PREFIX 		= 'TV:';
	const SECTION_DIVIDER  	= ':';
	
	/**
	 * Array of TemplaVoila DataObjects
	 *
	 * @var array
	 */
	protected $tvDataObjects = array();
	
	/**
	 * Find all supported broken links for a specific record. Extends the original class to check TemplaVoila data
	 *
	 * @param array $results Array of broken links
	 * @param string $table Table name of the record
	 * @param array $fields Array of fields to analyze
	 * @param array $record Record to analyse
	 * @return void
	 */	
	
	public function analyzeRecord(array &$results, $table, array $fields, array $record) {
		
		// only check content elements with tv data
		if($table === 'tt_content' && isset($record['tx_templavoila_flex']) && !is_null($record['tx_templavoila_flex']) && isset($record['tx_templavoila_ds']) && (int)$record['tx_templavoila_ds'] > 0) {
		
				// Put together content of all relevant fields
			$haystack = '';
				/** @var t3lib_parsehtml $htmlParser */
			$htmlParser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Html\\HtmlParser');

			$idRecord 	= $record['uid'];
			$dsID 		= (int)$record['tx_templavoila_ds'];

			// get tv data object for soft references and field config
			if(!isset($this->tvDataObjects[$dsID])) {
				
				$where = "deleted = 0 AND uid='".(int)$dsID."'";
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, dataprot', 'tx_templavoila_datastructure', $where);
				if($res) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					if(is_array($row) && !empty($row)) {
						
						$xmlArray = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($row['dataprot']);	
						$this->tvDataObjects[$dsID] = array(
							'config' 		=> $xmlArray['ROOT']['el'],
							'checkFields' 	=> null
						);
					}
				}				
			}			

			if(isset($this->tvDataObjects[$dsID])) {
				
				$dataArray 	= \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($record['tx_templavoila_flex']);	
				$xmlArray 	= $this->tvDataObjects[$dsID]['config'];
				
				// extract fields from datastructure, do it once for each ds
				if(is_null($this->tvDataObjects[$dsID]['checkFields'])) {
					
					$checkFields = array();					
					foreach($xmlArray AS $key => $value) {
						if(is_array($value) && !empty($value)) {
							// walk recursive trough ds structure
							$this->extractCheckFields($key, $value, $checkFields);
						}
					}
					$this->tvDataObjects[$dsID]['checkFields'] = $checkFields;	
				}
				unset($xmlArray);
				
				// now check the actual content for the fields
				$checkContent = array();
				if(is_array($dataArray)) {
					foreach($dataArray['data']['sDEF']['lDEF'] AS $k => $v) {
					
						if(isset($this->tvDataObjects[$dsID]['checkFields'][$k])) {
							
							$item = array(
								'field' 	=> $k,
								'values'	=> array()
							);
							foreach($v AS $k2 => $v2) {
								$value 	= trim($v2);
								if($k2[0] === 'v' && strlen($k2) < 9 && strlen($value) > 0) {
									$item['values'][$k2] = $value;
								}						
							}
							if(!empty($item['values'])) {
								$checkContent[] = $item;
							}
							unset($dataArray['data']['sDEF']['lDEF'][$k]);
							
						} else {
							// maybe content from a section or nested fce
							if(!isset($v['vDEF'])) {							
								
								$this->extractContent($k, $v, $checkContent, $dsID);
								
							} else {
								unset($dataArray['data']['sDEF']['lDEF'][$k]);
							}
						}
					}
					unset($dataArray);

					// check the actual content
					$elementCounter = array();
					foreach($checkContent AS $k => $v) {
										
						$conf 	= $this->tvDataObjects[$dsID]['checkFields'][$v['field']];
						
						// create an inernal counter, needed for fields in sections
						if(!isset($elementCounter[$conf['field']])) {
							$elementCounter[$conf['field']] = 0;
						}
						$elementCounter[$conf['field']]++;						
						
						if(isset($conf['softref'])) {

								// Explode the list of soft references/parameters
							$softRefs = \TYPO3\CMS\Backend\Utility\BackendUtility::explodeSoftRefParserList($conf['softref']);	
							
								// Traverse soft references
							foreach ($softRefs as $spKey => $spParams) {
									/** @var t3lib_softrefproc $softRefObj Create or get the soft reference object */
								$softRefObj = &\TYPO3\CMS\Backend\Utility\BackendUtility::softRefParserObj($spKey);
									// If there is an object returned...
								if (is_object($softRefObj)) {
									
									// walk trough all available values including translations
									foreach($v['values'] AS $k2 => $v2) {
										
										$value 		= trim($v2);
										$language 	= ltrim($k2, 'v');
										if($language === 'DEF') 
											$language = '';
										else {
											$language = ' ' .$language ;
										}

										$fieldName 	= self::TV_PREFIX . $conf['title'] . $language.' (' . $conf['field'] . ':' . $elementCounter[$conf['field']].')';
										
										if ((bool)$conf['linkField']) {
											// maybe we have a link set with target and title etc., so explode the string and only check the first part
											$valueParts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(' ', $value, true);
											if(is_array($valueParts) && isset($valueParts[0])) {
												$value = $valueParts[0];
												unset($valueParts);
											}
										}
										if ((bool)$conf['linkField'] && !(bool)\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($value) && !parse_url($value, PHP_URL_SCHEME)) {

											// check if a local file was set
											$filePrefixCheck = false;
											$filePrefix = array(
												$softRefObj->fileAdminDir . '/',
												'media:',
												'file:',
												'uploads/',
												'typo3temp/'
											);
											foreach($filePrefix AS $k3 => $v3) {
												$filePrefixCheck = \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($value, $v3);
												if((bool)$filePrefixCheck) {
													break;
												}
											}	
											if (!(bool)$filePrefixCheck) {
												// probably an external link without scheme, so add it
												$value = 'http://' . $value;
											}
										}
											// Do processing
										$resultArray = $softRefObj->findRef($table, $fieldName, $idRecord, $value, $spKey, $spParams);
										if (!empty($resultArray['elements'])) {

											if ($spKey == 'typolink_tag') {
												$this->analyseTypoLinks($resultArray, $results, $htmlParser, $record, $fieldName, $table);
											} else {
												$this->analyseLinks($resultArray, $results, $record, $fieldName, $table);
											}
										}											
									}									
								}
							}
						}
					}
					unset($checkContent);
				}
			}		

		} else {
			
			// run parent function for everything else
			parent::analyzeRecord($results, $table, $fields, $record);
		}
	}
	
	/**
	 * This function parses a templavoila flex field for content to analyze. 
	 * It runs recursively to add content from sections and containers.
	 *
	 * @param string $key The name of the field
	 * @param array $dataArray The dataobject array
	 * @param array $checkContent Array of content to analyze
	 * @param int $dsID The ID of the templavoila dataobject
	 * @return void
	 */		
	
	protected function extractContent($key = null, $dataArray = null, array &$checkContent, $dsID = 0) {
		
		if(isset($dataArray['el']) && is_array($dataArray['el']) && !empty($dataArray['el'])) {

			$this->extractContent($key, $dataArray['el'], $checkContent, $dsID);
		
		} else {
		
			if(is_array($dataArray) && !empty($dataArray)) {

				foreach($dataArray AS $k => $v) {
					
					if(!isset($v['vDEF'])) {
					
						$newKey = $key.self::SECTION_DIVIDER.$k;
						if((bool)\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($k)) {
							$newKey = $key;
						}
						$this->extractContent($newKey, $v, $checkContent, $dsID);
						
					} else {

						if(isset($this->tvDataObjects[$dsID]['checkFields'][$key.self::SECTION_DIVIDER.$k])) {

							$item = array(
								'field' 	=> $key.self::SECTION_DIVIDER.$k,
								'values'	=> array()
							);
							foreach($v AS $k2 => $v2) {
								$value 	= trim($v2);
								if($k2[0] === 'v' && strlen($k2) < 9 && strlen($value) > 0) {
									$item['values'][$k2] = $value;
								}						
							}
							if(!empty($item['values'])) {
								$checkContent[] = $item;
							}
						}
					}
				}			
			}		
		}	
	}
	
	/**
	 * This function parses a templavoila dataobject array for fields to check. 
	 * It runs recursively to add fields in sections and containers.
	 *
	 * @param string $key The name of the field
	 * @param array $dataArray The dataobject array
	 * @param array $checkFields Array of fields to analyze
	 * @return void
	 */		

	protected function extractCheckFields($key = null, $dataArray = null, array &$checkFields) {
		
		// we found a section, so run again with the children
		if(isset($dataArray['type']) && $dataArray['type'] === 'array' && isset($dataArray['section']) && (bool)$dataArray['section']) {
		
			$this->extractCheckFields($key, $dataArray['el'], $checkFields);		
		
		} else {
			
			if(is_array($dataArray) && !empty($dataArray)) {
					
				// we found a field	
				if(isset($dataArray['TCEforms'])) {
				
					$this->addCheckField($key, $dataArray, $checkFields);
				
				} else {
					
					foreach($dataArray AS $k => $v) {
						// we found a container so run again with the children
						if(isset($v['type']) && $v['type'] === 'array' && isset($v['el'])) {
							$this->extractCheckFields($key.self::SECTION_DIVIDER.$k, $v['el'], $checkFields);
						}
						// normal element so add to check field if softref is set
						$this->addCheckField($key.self::SECTION_DIVIDER.$k, $v, $checkFields);
					}
				}			
			}
		}
	}
	
	/**
	 * This function checks for softparser settings of a templavoila field.
	 *
	 * @param string $key The name of the field
	 * @param array $dataArray The config array for the field
	 * @param array $checkFields Array of fields to analyze
	 * @return void
	 */		
	
	protected function addCheckField($key = null, $dataArray = null, array &$checkFields) {

		if(isset($dataArray['TCEforms']) && isset($dataArray['TCEforms']['config'])) {

			$conf = $dataArray['TCEforms']['config'];
			$conf['linkField'] = false;
			// field is set as link field
			if($dataArray['tx_templavoila']['eType'] === 'link') {
				$conf['linkField'] = true;
			}
			// link fields
			if(!isset($conf['softref']) && (bool)$conf['linkField']) {
				$conf['softref'] = 'typolink';
			}	
			if(isset($conf['softref'])) {
			
				// for sections and containers, only get the field name for display in results
				$fieldNames = explode(self::SECTION_DIVIDER, $key);
				
				$checkFields[$key] = array(
					'field'		=> end($fieldNames),
					'title' 	=> $dataArray['tx_templavoila']['title'],
					'softref' 	=> $conf['softref'],
					'linkField'	=> $conf['linkField']
				);
			}
		}
	}	
}