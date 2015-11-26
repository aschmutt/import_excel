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
	 *
	 * @return void
	 */
	public function initializeObject() {
		        $querySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
		        $querySettings->setRespectStoragePage(FALSE);
		        $this->setDefaultQuerySettings($querySettings);
	}

	/**
	 * get field names
	 *
	 * @param string $table tablename
     * @param string $tableSettings TS Settings for table
	 * @return array field names
	 */
	public function getFieldNames($table,$tableSettings="") {
        
        $ignoreFields = explode(",",$tableSettings['ignore_fields']);
		
		$query = $this->createQuery();
		$query->getQuerySettings()->setReturnRawQueryResult(TRUE);
		$query->statement('SHOW columns from '.$table);
		$results = $query->execute();
        
        $fields = array();
        foreach ($results as $fieldrow) {
            if (!in_array($fieldrow['Field'], $ignoreFields)) {
                $fields[] = $fieldrow['Field'];
            }
        }
		
		//var_dump($fields);
       	return $fields;
	}
    
    /**
     * get field names
     *
     * @param string $table tablename
     * @param string $identifier 
     * @param array $data from Excel
     * @param array $assignment
     * @return array field names
     */
    public function getOverwrites($table, $identifier,$data,$assignment,$fields) {
        
        $codesList = "'";
        $count=0;
        foreach($data as $row) {
            //skip first row as title row    
            if ($count++>0){
                $value = $row[$assignment[$identifier]];
                if ( ((int)$value>0) || (strlen($value)>0) ){
                    $codesList .=  $value. "','";    
                }
            }
        }
        $codesList = substr($codesList,0,strlen($codesList)-2);
        
        $query = $this->createQuery();
        $query->getQuerySettings()->setReturnRawQueryResult(TRUE);
        $sqlStatement = 'SELECT * FROM '.$table.' WHERE ' . $identifier . ' in ('.$codesList.')';
        //var_dump($sqlStatement);
        $query->statement($sqlStatement);
        $results = $query->execute();
        
        $output = array();
        
        foreach ($results as $row) {
            $outputRow = array();
            foreach ($row as $key=>$value) {
                if ((array_key_exists($key, $assignment)) && ($assignment[$key] >= 0)) {
                    $outputRow["$key"]=$value;
                }
            }
            
            $ident = $row[$identifier];
            $output[$ident]=$outputRow;    
        }
                    
        return $output;
    }
    
    /**
     * @param string $table tablename
     * @param array $data data for insert statement
     * @return void
     * */
    public function insertData($table, $data){
        
        foreach($data as $row) {
            //print_r($GLOBALS['TYPO3_DB']->INSERTquery($table, $row));
            $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $row);
        }    
    }
    
    /**
     * @param string $table tablename
     * @param array $data data for insert statement
     * @param string $identifer something like "uid"
     * @return void
     * */
    public function updateData($table, $data, $identifier){
        
        foreach($data as $row) {
            $where = ' '. $identifier . ' like "'.$row[$identifier].'" ';
            //print_r($GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $row));
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $row);
        }    
    }

}

?>