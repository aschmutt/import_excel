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
     * pageRenderer
     *
     * @var t3lib_PageRenderer
     */
    protected $pageRenderer;

    /**
     * pageId
     *
     * @var integer
     */
    protected $pageId;
    
     /**
     * settings
     *
     * @var Array
     */
    protected $settings;

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
        
                $this->pageRenderer->addInlineLanguageLabelFile('EXT:import_excel/Resources/Private/Language/locallang.xml');
       
               //load TS Settings
                $configurationManager = t3lib_div::makeInstance('Tx_Extbase_Configuration_BackendConfigurationManager');
                $tsConfig = $configurationManager->getConfiguration(
                    $this->request->getControllerExtensionName(),
                    $this->request->getPluginName()
                );  
                $this->settings = $tsConfig['settings'];
    }

    
    /**
     * Default action for this controller.
     *
     * @return string The rendered view
     */
    public function indexAction() {
        t3lib_div::devLog('indexAction called','import_excel',0,array());
        //Load Main ExtJS 
    }
    
    /**
     * test ajax
     *
     * @return string json
     */
    public function testAjaxAction() {
        t3lib_div::devLog('testAjaxAction called','import_excel',0,array());
         $test = array('test'=>1, 'test2'=>'Hello from Extbase');
         $test = array('records'=>array(
                array( name => "TYPO3 Record 0", column1 => "0", column2 => "0" ),
                array( name => "TYPO3 Record 1", column1 => "1", column2 => "1" ),
                array( name => "TYPO3 Record 2", column1 => "2", column2 => "2" ),
                array( name => "TYPO3 Record 3", column1 => "3", column2 => "3" ),
                array( name => "TYPO3 Record 4", column1 => "4", column2 => "4" ),
            )
         );
         $result = json_encode($test);
         return '!#!#!'.$result.'!#!#!';
    }
    
   
    
   
    /**
     * action select: select Excel file and table for import
     *
     * @return void
     */
    public function selectAction() {
            
        if (!$this->settings){
            $this->flashMessageContainer->add(Tx_Extbase_Utility_Localization::translate('tx_importexcel.error.settings', $this->extKey),'', t3lib_Flashmessage::ERROR);
            return;
        }
        
        $objReader = $this->getPHPExcelReader();
        if (!$objReader) {
            $this->flashMessageContainer->add('No PHP Excel installation found!','', t3lib_Flashmessage::ERROR);
            return;
        }
        
        if ($this->pageId <= 0) {
            $this->flashMessageContainer->add('No Upload Folder selected in Page Tree!','', t3lib_Flashmessage::ERROR);
            return;
        }
        
        //load tables
        $table = $this->getTable();
        
        //load file 
        $uploadfile = t3lib_div::_GP("uploadfile");
        $uploadfilename = $_FILES ? $_FILES['filename']['name'] : t3lib_div::_GP("file");
        if (strlen($uploadfilename) > 0) {
            $uploadfile = t3lib_div::upload_to_tempfile($_FILES['filename']['tmp_name']);
            //if file upload failes, usually filesize is too big. Check Configuration: [maxFileSize] in TYPO3 Install Tool, post_max_size and upload_max_filesize in php.ini','', t3lib_Flashmessage::ERROR);
        }
        $this->view->assign('uploadfile', $uploadfile);
        
        if (strlen($uploadfile) > 0) {
                
            //check Table
            if (!$table) {
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
     * assign excel fields to table fields
     *
     * @param string $table
     * @param string $uploadfile
     * @validate $uploadfile NotEmpty
     * @return void
     */
    public function assignFieldsAction($table, $uploadfile) {
        
        $table = $this->getTable($table);    
                
        $this->view->assign('table', $table);
        $this->view->assign('uploadfile', $uploadfile);
        
        $fields = $this->importExcelRepository->getFieldNames($table, $this->settings['tables'][$table]);
        
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
            $this->redirect('select','ImportExcel',Null,array());
        }
        unset($objReader);
        unset($objPHPExcel);
        
        $assignment = t3lib_div::_GP("assignment");
        if ( ($assignment != NULL) && (count($assignment)>0) ) {
            // next step
            $this->redirect('importPreview','ImportExcel',Null,array(
                    'table' => $table,
                    'uploadfile' => $uploadfile,
                    'assignment' => $assignment));
        }
        
        $assignlist = $this->getAssignlist($fields, $data[1],$assignment,$this->settings['tables'][$table]['config']);
        unset($data);
        
        $this->view->assign('fields', $fields);
        $this->view->assign('assignlist', $assignlist);
    }

    /**
     * assign excel fields to table fields
     *
     * @param string $table
     * @param string $uploadfile
     * @param array $assignment
     * @validate $uploadfile NotEmpty
     * @return void
     */
    public function importPreviewAction($table, $uploadfile, $assignment) {
                    
                //load tablename
                $table = $this->getTable($table);    
                $this->view->assign('table', $table);
                
                //todo: validate $uploadfile,$assignment
                $this->view->assign('uploadfile', $uploadfile);
                $this->view->assign('assignment', $assignment);
                
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
                    $this->redirect('select','ImportExcel',Null,array());
                }    
                unset($objReader);
                unset($objPHPExcel);
                
                //load all configuration and assignment
                $fields = $this->importExcelRepository->getFieldNames($table, $this->settings['tables'][$table]);
                
                $outputlists = $this->getInsertUpdateArrays($table,$data,$assignment,$fields);
                $this->view->assign('insertlist', array_slice($outputlists['insert'],0,500,true));
                $this->view->assign('updatelist', array_slice($outputlists['update'],0,500,true));
                $this->view->assign('existinglist', array_slice($outputlists['existing'],0,500,true));
                
                $this->view->assign('insertNum', count($outputlists['insert']));
                $this->view->assign('updateNum', count($outputlists['update']));
    }

    /**
     * assign excel fields to table fields
     *
     * @param string $table
     * @param string $uploadfile
     * @param array $assignment
     * @param int $overwrite if >0, overwrite existing data
     * @validate $uploadfile NotEmpty
     * @return void
     */
    public function doImportAction($table, $uploadfile, $assignment,  $overwrite=0) {
       
        //load tablename
        $table = $this->getTable($table);    
        
        //todo: validate $uploadfile,$assignment 
        
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
            $this->redirect('select','ImportExcel',Null,array());
        }        
        $data = $this->trimData($data);
        
        //load all configuration and assignment
        $fieldconfig = $this->settings['tables'][$table]['config'];
        $fields = $this->importExcelRepository->getFieldNames($table, $this->settings['tables'][$table]);
        
        //create insert, update lists
        $identifier = $this->settings['tables'][$table]['identifier']['field'];
        $outputlists = $this->getInsertUpdateArrays($table,$data,$assignment,$fields);
       
        $this->importExcelRepository->insertData($table, $outputlists['insert']);
        if ($overwrite>0) {
            $this->importExcelRepository->updateData($table, $outputlists['update'],$identifier);    
        }

    }

    
    /**
     * load data from database and split in insert, update and overwrite
     * 
     * @param string $table tablename
     * @param array $data
     * @param array $assignment
     * @param array $fields
     * */
    protected function getInsertUpdateArrays($table,$data,$assignment,$fields) {
        
        $fieldconfig = $this->settings['tables'][$table]['config'];
        $identifier = $this->settings['tables'][$table]['identifier']['field'];
        
        //load overwrite Info from database
        $existinglist = $this->importExcelRepository->getOverwrites($table,$identifier,$data,$assignment,$fields);
        
        $insertlist = array();
        $updatelist = array();
        
        foreach($data as $key=>$row) {
            if ($key>1) {  
                $entry = array();
                foreach ($fields as $fieldname) {
                    if ($assignment[$fieldname] >= 0) {
                        if ($fieldconfig[$fieldname]) {
                            $entry[$fieldname] = $this->getValueFromFieldconfig($fieldconfig[$fieldname]);
                        }
                        else {
                            $entry[$fieldname] = $row[$assignment[$fieldname]];
                        }    
                    }
                }
                
                $identifierExisiting = $row[$assignment[$identifier]];
                if ($existinglist[$identifierExisiting]) {
                    $updatelist[] = $entry;
                } else {
                    $insertlist[] = $entry;
                }
            }
        }
        
        $output=array(
            'insert'    => $insertlist,
            'update'    => $updatelist,
            'existing'  => $existinglist,
        );
        return $output;  
    }

    /**
     * Get currently selected table
     *
     * @param string $table tablename - if empty try to get from GET Parameter
     * @return string tablename or NULL
     */
    protected function getTable($table = "") {
                    
        //list of allowed tables            
        $tables = $this->settings['tables']['allowed'];
        $tablelist = explode(',', $tables);
        $this->view->assign('tablelist', $tablelist);
        
        //get selected table
        if (strlen($table)==0) {
            $table = t3lib_div::_GP("table");
        }
        
        //validate selected table
        if ((strlen($table)>0) && (in_array($table,$tablelist))) {
            $this->view->assign('table', $table);
            return $table;
        } 
        return NULL;
    }

    /**
     * get Assignment Array from TS Config and User input
     *
     * @param array $fields
     * @param array $titlerow (first line in Excel = Title row)
     * @param array $assignment field assignment from user
     * @param array $fieldconfig 
     * @return
     */
    protected function getAssignlist($fields, $titlerow, $assignment, $fieldconfig=array()) {
                $assignlist = array();

                foreach ($fields as $fieldname) {
                    $entry = array();
                    $entry['fieldname'] = $fieldname;
                    
                    //default: ignore value
                    $entry['selectfield'] = array(
                        0 => array(
                            'title'=>'<ignore>',
                            'selected'=>0,
                            'value' =>'-1'),
                    );
                    
                    //TS configuration
                    if ($fieldconfig[$fieldname]) {
                        $entry['selectfield'][] = array(
                            'title'=>$this->getValueFromFieldconfig($fieldconfig[$fieldname]),
                            'selected'=>1,
                            'value'=>'',
                        );
                    }
                    else {
                        //compare selection and fieldname
                        foreach($titlerow as $key=>$exceltitle) {
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
                    }
                       
                    $assignlist[] = $entry;
                }
                return $assignlist;
    }

    /**
     * getValueFromFieldconfig
     * @return object value 
     */
    protected function getValueFromFieldconfig($fieldconfig=array()) {
        switch ($fieldconfig['default']) {
            case 'timestamp':
                return time();
            case 'pid':
                return $this->pageId;
            default:
                break;
        }
        
        if (strlen($fieldconfig['value']) > 0) return $fieldconfig['value'];
            
        return "";
    }
    
    /**
     * @param array $data
     * @return array $data
     */
    protected function trimData($data) {
        $start=time();
        foreach ($data as $index=>$row) {
            foreach ($row as $key=>$value) {
                if (strlen($value)>0) {
                    $data[$index][$key]=trim($value);
                }
            }
        }
        $duration = time()-$start;
        //print_r("duration trim: $duration");
        return $data;
    }

    /**
     * includes PHPExcel Files and returns a PHPExcel Reader Object
     *
     * @return object $objReader or null if error
     */
    protected function getPHPExcelReader() {
                
                $phpexcel = $this->settings['phpexcel'];
                
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
                    $phpexcel_path = $this->settings['phpexcel_path'];
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