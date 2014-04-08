<?php
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

 class ux_tx_linkvalidator_ModFuncReport extends tx_linkvalidator_ModFuncReport {

	/**
	 * Displays one line of the broken links table.
	 *
	 * @param string $table Name of database table
	 * @param array $row Record row to be processed
	 * @param array $brokenLinksItemTemplate Markup of the template to be used
	 * @return string HTML of the rendered row
	 */
	protected function renderTableRow($table, array $row, $brokenLinksItemTemplate) {
		
		// replace default icon with tv icon
		if(t3lib_div::isFirstPartOfStr($row['field'], ux_tx_linkvalidator_Processor::TV_PREFIX)) {
		
			$row['CType'] = 'templavoila_pi1';
			// remove TV: prefix from field name
			$row['field'] = ltrim($row['field'], ux_tx_linkvalidator_Processor::TV_PREFIX);
		}
		return parent::renderTableRow($table, $row, $brokenLinksItemTemplate);
	}
}
?>