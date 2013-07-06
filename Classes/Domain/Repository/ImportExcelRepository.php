<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Andrea Schmuttermair 
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 *
 *
 * @package import_excel
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_ImportExcel_Domain_Repository_ImportExcelRepository extends Tx_Extbase_Persistence_Repository {
	
	
	/**
    * initialize: ignore pid for queries
    * @return void
    */
    public function initializeObject() {
        $querySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
        $querySettings->setRespectStoragePage(FALSE);
        $this->setDefaultQuerySettings($querySettings);
    }
	
	 /**
     * get field names
     * @param string $table tablename
     * @return array field names
     */
    public function getFieldNames($table) {
		
        $ignore_fields = array('uid','pid','tstamp','crdate','cruser_id','deleted','hidden','starttime','endtime','t3_origuid','sys_language_uid','l10n_parent','l10n_diffsource','t3ver_oid','t3ver_id','t3ver_wsid','t3ver_label','t3ver_state','t3ver_stage','t3ver_count','t3ver_tstamp','t3ver_move_id','sorting');
        
		/*$query = $this->createQuery();
		$query->getQuerySettings()->setReturnRawQueryResult(TRUE);
		$query->statement('SHOW columns from '.$table);
		//$query->statement('SELECT column_name FROM information_schema.columns WHERE table_name = ?', array($table));    
		$result = $query->execute();*/
		
		$fields = array();
		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'SHOW columns from '.$table);	
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		    if (!in_array($row['Field'], $ignore_fields)) {
		      $fields[] = $row['Field'];    
		    }
			
		}
		//var_dump($fields);
       	return $fields;              
    }
	
	
	
}

?>