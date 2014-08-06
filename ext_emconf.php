<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "on_linkvalidator_tv".
 *
 * Auto generated 08-04-2014 11:58
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Linkvalidator for TemplaVoila!',
	'description' => 'This extensions provides the ability to use the TYPO3 linkvalidator with TemplaVoila! content elements.',
	'category' => 'module',
	'author' => 'Online Now! GmbH',
	'author_email' => 'typo3@online-now.de',
	'shy' => '',
	'dependencies' => 'linkvalidator,templavoila',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'doNotLoadInFE' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '1.0.1',
	'constraints' => array(
		'depends' => array(
			'linkvalidator' => '',
			'templavoila' => '',
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:11:{s:9:"ChangeLog";s:4:"2b5d";s:12:"ext_icon.gif";s:4:"2694";s:17:"ext_localconf.php";s:4:"fc2b";s:10:"README.txt";s:4:"7573";s:45:"Classes/Xclass/Linkvalidator/LinkAnalyzer.php";s:4:"efa8";s:59:"Classes/Xclass/Linkvalidator/Report/LinkValidatorReport.php";s:4:"ab69";s:14:"doc/manual.sxw";s:4:"9382";s:19:"doc/wizard_form.dat";s:4:"d8a5";s:20:"doc/wizard_form.html";s:4:"3974";s:50:"xclass/class.ux_tx_linkvalidator_modfuncreport.php";s:4:"f604";s:46:"xclass/class.ux_tx_linkvalidator_processor.php";s:4:"3d48";}',
	'suggests' => array(
	),
);

?>