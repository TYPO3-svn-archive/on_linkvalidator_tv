<?php
namespace OnlineNow\OnLinkvalidatorTv\XClass\Linkvalidator\Report;
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

class LinkValidatorReport extends \TYPO3\CMS\Linkvalidator\Report\LinkValidatorReport {

	protected function renderTableRow($table, array $row, $brokenLinksItemTemplate) {
		
		// replace default icon with tv icon
		if(\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($row['field'], \OnlineNow\OnLinkvalidatorTv\XClass\Linkvalidator\LinkAnalyzer::TV_PREFIX)) {
		
			$row['CType'] = 'templavoila_pi1';
			// remove TV: prefix from field name
			$row['field'] = ltrim($row['field'], \OnlineNow\OnLinkvalidatorTv\XClass\Linkvalidator\LinkAnalyzer::TV_PREFIX);
		}
		return parent::renderTableRow($table, $row, $brokenLinksItemTemplate);
	}
}
?>