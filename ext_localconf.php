<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (version_compare(TYPO3_branch, '6.0', '<')) {

	$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['ext/linkvalidator/classes/class.tx_linkvalidator_processor.php'] = t3lib_extMgm::extPath('on_linkvalidator_tv') . 'xclass/class.ux_tx_linkvalidator_processor.php';
	$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['ext/linkvalidator/modfuncreport/class.tx_linkvalidator_modfuncreport.php'] = t3lib_extMgm::extPath('on_linkvalidator_tv') . 'xclass/class.ux_tx_linkvalidator_modfuncreport.php';
	
} else {

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Linkvalidator\\LinkAnalyzer'] = array(
		'className' => 'OnlineNow\\OnLinkvalidatorTv\\Xclass\\Linkvalidator\\LinkAnalyzer'
	);
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Linkvalidator\\Report\\LinkValidatorReport'] = array(
		'className' => 'OnlineNow\\OnLinkvalidatorTv\\Xclass\\Linkvalidator\\Report\\LinkValidatorReport'
	);
}
?>