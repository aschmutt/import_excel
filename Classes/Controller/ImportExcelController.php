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
class Tx_ImportExcel_Controller_ImportExcelController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * importExcelRepository
	 *
	 * @var Tx_ImportExcel_Domain_Repository_ImportExcelRepository
	 */
	protected $importExcelRepository;
	
    /**
     * extension key
     *
     * @var string
     */
    public $extKey = 'import_excel';

    /**
     * @var t3lib_PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var integer
     */
    protected $pageId;
	
	/**
	 * @var array List of tables for import
	 * */
	public $tablelist;
	
    /**
     * @var array List of fields for table
     * */
    /*public $fieldlist = array(
		'tx_importexcel_testtable' => array ('title','description'),
        'tx_noeecdb_domain_model_errorcode' => array('code','console','message','description','resolution','english','french','spanish','german','italian','dutch','russian','portuguese','comment','result')
	);*/

	/**
	 * injectImportExcelRepository
	 *
	 * @param Tx_ImportExcel_Domain_Repository_ImportExcelRepository $importExcelRepository
	 * @return void
	 */
	public function injectImportExcelRepository(Tx_ImportExcel_Domain_Repository_ImportExcelRepository $importExcelRepository) {
		$this->importExcelRepository = $importExcelRepository;
	}
	
	

    /**
     * Initializes the controller before invoking an action method.
     *
     * @return void
     */
    protected function initializeAction() {
        $this->pageId = intval(t3lib_div::_GP('id'));

        $this->pageRenderer->addInlineLanguageLabelFile('EXT:workspaces/Resources/Private/Language/locallang.xml');
		
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$tables = $this->extConf['tables'];
		$this->tablelist = explode(',', $tables);
    }
       

    /**
     * Processes a general request. The result can be returned by altering the given response.
     *
     * @param Tx_Extbase_MVC_RequestInterface $request The request object
     * @param Tx_Extbase_MVC_ResponseInterface $response The response, modified by this handler
     * @throws Tx_Extbase_MVC_Exception_UnsupportedRequestType if the controller doesn't support the current request type
     * @return void
     */
    public function processRequest(Tx_Extbase_MVC_RequestInterface $request, Tx_Extbase_MVC_ResponseInterface $response) {
        $this->template = t3lib_div::makeInstance('template');
        $this->pageRenderer = $this->template->getPageRenderer();

        $GLOBALS['SOBE'] = new stdClass();
        $GLOBALS['SOBE']->doc = $this->template;

        parent::processRequest($request, $response);

        $pageHeader = $this->template->startpage(
            $GLOBALS['LANG']->sL('LLL:EXT:workspaces/Resources/Private/Language/locallang.xml:module.title')
        );
        $pageEnd = $this->template->endPage();

        $response->setContent($pageHeader . $response->getContent() . $pageEnd);
    }   

	/**
	 * action select: select Excel file and table for import
	 * @return void
	 */
	public function selectAction() {
		
		$objReader = $this->getPHPExcelReader();
		if (!$objReader) {
			return;
		}
		
		//load file	
		$uploadfile = t3lib_div::_GP("uploadfile");
		$uploadfilename = $_FILES ? $_FILES['filename']['name'] : t3lib_div::_GP("file");
		if (strlen($uploadfilename) > 0) {
			$uploadfile = t3lib_div::upload_to_tempfile($_FILES['filename']['tmp_name']);
		}
		$this->view->assign('uploadfile', $uploadfile);
		
		$table = t3lib_div::_GP("table");
		$this->view->assign('table', $table);
        //var_dump($table);
        
		if (strlen($uploadfile) > 0) {
            //check PID
            if ($this->pageId <= 0) {
                $this->flashMessageContainer->add('Please select a Folder in Page Tree!','', t3lib_Flashmessage::ERROR);
            }
            //check Table
            else if (!in_array($table, $this->tablelist)) {
                $this->flashMessageContainer->add('Please select a table!','', t3lib_Flashmessage::ERROR);
            }
            //check Excel
            else {
                /**  Identify the type of $inputFileName  **/
                $inputFileType = PHPExcel_IOFactory::identify($uploadfile);
                if ($inputFileType != 'Excel5') {
                    $this->flashMessageContainer->add('This file type is not supported. Please upload a .xls file!','', t3lib_Flashmessage::ERROR);
                }
                else {
                    //go to next step
                    $this->redirect('assignFields','ImportExcel',Null,array(
                        'table' => $table,
                        'uploadfile' => $uploadfile));
                }	
            
            }
		}
		
		$this->view->assign('tablelist', $this->tablelist);
	}
	
	/**
	 * assign excel fields to table fields
	 * 
	 * @param string $table
	 * @param string $uploadfile
	 *
	 * @return void
	 */
	public function assignFieldsAction($table, $uploadfile) {
		
		//todo: validate input
		$this->view->assign('table', $table);
		$this->view->assign('uploadfile', $uploadfile);
		
		$fields = $this->importExcelRepository->getFieldNames($table);
        
		//Read Excel File
        if (!($objReader = $this->getPHPExcelReader())) {
        	return;
        }
        $objPHPExcel = $objReader->load($uploadfile);
		$data = array();
		if ($objPHPExcel) {
			$data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		}
        if (!$objPHPExcel || !$data || (count($data)<1)) {
        	$this->flashMessageContainer->add('Could not read Excel File','', t3lib_Flashmessage::ERROR);
        	$this->redirect('list','ImportExcel',Null,array());
        }
		
        $assignment = t3lib_div::_GP("assignment");
		//var_dump($assignment);
		if ( ($assignment != NULL) && (count($assignment)>0) ) {
			// next step
			$this->redirect('importPreview','ImportExcel',Null,array(
					'table' => $table,
					'uploadfile' => $uploadfile,
					'assignment' => $assignment));
		}
        
		$assignlist = array();
		foreach ($fields as $fieldname) {
			$entry = array();
			$entry['fieldname'] = $fieldname;
			
			$entry['selectfield'] = array(
				0 => array(
					'title'=>'ignore',
					'selected'=>0,
					'value' => 0),
			);
			
			foreach($data[1] as $key=>$exceltitle) {
				$option=array();
				$option['title'] = $exceltitle;
				$option['value'] = $key;
				$option['selected'] = 0;
				if (strlen($assignment[$fieldname]) > 0) {
					if (strcasecmp($assignment[$fieldname],$key)==0) {
						$option['selected'] = 1;
					}
				}
				else if (strcasecmp($exceltitle,$fieldname) == 0) {
					$option['selected'] = 1;
				}
				$entry['selectfield'][] = $option;
			}	
			$assignlist[] = $entry;
		}
		
		$this->view->assign('fields', $fields);
		$this->view->assign('assignlist', $assignlist);
		
	}

	/**
	 * assign excel fields to table fields
	 * 
	 * @param string $table
	 * @param string $uploadfile
	 * @param array $assignment
	 *
	 * @return void
	 */
	public function importPreviewAction($table, $uploadfile, $assignment) {
		//todo: validate input
		$this->view->assign('table', $table);
		$this->view->assign('uploadfile', $uploadfile);
		$this->view->assign('assignment', $assignment);
		
			
		$fields = $this->importExcelRepository->getFieldNames($table);
        
		//Read Excel File
        if (!($objReader = $objReader = $this->getPHPExcelReader())) {
        	return;
        }
        $objPHPExcel = $objReader->load($uploadfile);
		$data = array();
		if ($objPHPExcel) {
			$data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		}
        if (!$objPHPExcel || !$data || (count($data)<1)) {
        	$this->flashMessageContainer->add('Could not read Excel File','', t3lib_Flashmessage::ERROR);
        	$this->redirect('list','ImportExcel',Null,array());
        }        
        
		$assignlist = array();
		foreach ($fields as $fieldname) {
			$entry = array();
			$entry['fieldname'] = $fieldname;
			
			$entry['selectfield'] = array(
				0 => array(
					'title'=>'ignore',
					'selected'=>0,
					'value' => 0),
			);
			
			foreach($data[1] as $key=>$exceltitle) {
				$option=array();
				$option['title'] = $exceltitle;
				$option['value'] = $key;
				$option['selected'] = 0;
				if (strlen($assignment[$fieldname]) > 0) {
					if (strcasecmp($assignment[$fieldname],$key)==0) {
						$option['selected'] = 1;
					}
				}
				else if (strcasecmp($exceltitle,$fieldname) == 0) {
					$option['selected'] = 1;
				}
				$entry['selectfield'][] = $option;
			}	
			$assignlist[] = $entry;
		}
		
		$this->view->assign('fields', $fields);
		$this->view->assign('assignlist', $assignlist);

		$insertlist = array();
		foreach($data as $key=>$row) {
			if ($key>1) {
				$insert = array();
				$insert['crdate'] = time();
				$insert['tstamp'] = time();
				$insert['pid'] = $this->pageId;
				foreach ($fields as $fieldname) {
					if (strlen($assignment[$fieldname]) > 0) {
						$insert[$fieldname] = $row[$assignment[$fieldname]];
					}
				}
				
				$insertlist[] = $insert;
			}
		}
		$this->view->assign('insertlist', $insertlist);

		
	}

	/**
	 * assign excel fields to table fields
	 * 
	 * @param string $table
	 * @param string $uploadfile
	 * @param array $assignment
	 *
	 * @return void
	 */
	public function doImportAction($table, $uploadfile, $assignment) {
		//todo: validate input
		
		$fields = $this->importExcelRepository->getFieldNames($table);
        
		//Read Excel File
        if (!($objReader = $objReader = $this->getPHPExcelReader())) {
        	return;
        }
        $objPHPExcel = $objReader->load($uploadfile);
		$data = array();
		if ($objPHPExcel) {
			$data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		}
        if (!$objPHPExcel || !$data || (count($data)<1)) {
        	$this->flashMessageContainer->add('Could not read Excel File','', t3lib_Flashmessage::ERROR);
        	$this->redirect('list','ImportExcel',Null,array());
        }        
        
		$assignlist = array();
		foreach ($fields as $fieldname) {
			$entry = array();
			$entry['fieldname'] = $fieldname;
			
			$entry['selectfield'] = array(
				0 => array(
					'title'=>'ignore',
					'selected'=>0,
					'value' => 0),
			);
			
			foreach($data[1] as $key=>$exceltitle) {
				$option=array();
				$option['title'] = $exceltitle;
				$option['value'] = $key;
				$option['selected'] = 0;
				if (strlen($assignment[$fieldname]) > 0) {
					if (strcasecmp($assignment[$fieldname],$key)==0) {
						$option['selected'] = 1;
					}
				}
				else if (strcasecmp($exceltitle,$fieldname) == 0) {
					$option['selected'] = 1;
				}
				$entry['selectfield'][] = $option;
			}	
			$assignlist[] = $entry;
		}
		
		$this->view->assign('fields', $fields);
		$this->view->assign('assignlist', $assignlist);

		$insertlist = array();
		foreach($data as $key=>$row) {
			if ($key>1) {
				$insert = array();
				$insert['crdate'] = time();
				$insert['tstamp'] = time();
				$insert['pid'] = $this->pageId;
				foreach ($fields as $fieldname) {
					if (strlen($assignment[$fieldname]) > 0) {
						$insert[$fieldname] = $row[$assignment[$fieldname]];
					}
				}
				
				$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insert);
				$insert['database result'] = $res;
			}
				
			$insertlist[] = $insert;
		}
		$this->flashMessageContainer->add('Import completed successfully','', t3lib_Flashmessage::OK);
		
		//todo: delete import file
		
		$this->view->assign('insertlist', $insertlist);

		
	}
	
    
    /**
     * includes PHPExcel Files and returns a PHPExcel Reader Object
     * @return object $objReader or null if error
     */
    protected function getPHPExcelReader(){
    	
		$phpexcel = $this->extConf['phpexcel'];
		
		if ($phpexcel == "phpexcel_service") {
			try {
				$phpExcelService = t3lib_div::makeInstanceService('phpexcel');	
				if (!$phpExcelService) {
					$this->flashMessageContainer->add('Extension phpexcel_service could not be found. Please check Configuration in Extension Manager!','', t3lib_Flashmessage::ERROR);
					return null;	
				}
			}
			catch (Exception $e) {
				$this->flashMessageContainer->add('Extension phpexcel_service could not be found. Please check Configuration in Extension Manager!','', t3lib_Flashmessage::ERROR);
				return null;
			}
		}
		else if ($phpexcel == "phpexcel_library") {
			try {
				require_once t3lib_extMgm::extPath("phpexcel_library")."sv1/class.tx_phpexcellibrary_sv1.php";
		        tx_phpexcellibrary_sv1::includeAllFiles();	
			}
			catch (Exception $e) {
				$this->flashMessageContainer->add('Extension phpexcel_library could not be found. Please check Configuration in Extension Manager!','', t3lib_Flashmessage::ERROR);
				return null;
			}
		} 
		else if ($phpexcel == "ownpath") {
			$phpexcel_path = $this->extConf['phpexcel_path'];
			$phpexcel_path = rtrim($phpexcel_path,"/ ");
			if (!file_exists($phpexcel_path.'/PHPExcel.php')) {
				$this->flashMessageContainer->add('PHPExcel.php could not be found in path "'.$phpexcel_path.'". Please check Configuration in Extension Manager!','', t3lib_Flashmessage::ERROR);
				return null;
			}
			require_once $phpexcel_path.'/PHPExcel.php';
			if (!file_exists($phpexcel_path.'/PHPExcel/IOFactory.php')) {
				$this->flashMessageContainer->add('/PHPExcel/IOFactory.php could not be found in path "'.$phpexcel_path.'". Please check Configuration in Extension Manager!','', t3lib_Flashmessage::ERROR);
				return null;
			}	
			require_once $phpexcel_path.'/PHPExcel/IOFactory.php';
		}
		else {
			return null;
		}
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
	    return $objReader;	
    }

}
?>