<?php

########################################################################
# Extension Manager/Repository config file for ext "import_excel".
#
# Auto generated 26-04-2013 18:09
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Import Excel files to database',
	'description' => 'Import Excel files to database, based on PHPExcel Library',
	'category' => 'module',
	'author' => 'Andrea Schmuttermair',
	'author_email' => 'spam@schmutt.de',
	'author_company' => 'Schmuttermair Software',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.0.1',
	'constraints' => array(
		'depends' => array(
			'extbase' => '1.3.0-6.2.99',
			'fluid' => '1.3.0-6.2.99',
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:31:{s:21:"ExtensionBuilder.json";s:4:"547c";s:21:"ext_conf_template.txt";s:4:"e2d9";s:12:"ext_icon.gif";s:4:"9c8d";s:14:"ext_tables.php";s:4:"8732";s:14:"ext_tables.sql";s:4:"e372";s:13:"localconf.php";s:4:"2cd0";s:15:"versioninfo.txt";s:4:"7efa";s:44:"Classes/Controller/ImportExcelController.php";s:4:"bf1a";s:51:"Classes/Domain/Repository/ImportExcelRepository.php";s:4:"9004";s:44:"Configuration/ExtensionBuilder/settings.yaml";s:4:"18d3";s:33:"Configuration/TCA/ImportExcel.php";s:4:"6667";s:38:"Configuration/TypoScript/constants.txt";s:4:"f596";s:34:"Configuration/TypoScript/setup.txt";s:4:"7b7d";s:40:"Resources/Private/Language/locallang.xml";s:4:"a815";s:84:"Resources/Private/Language/locallang_csh_tx_importexcel_domain_model_importexcel.xml";s:4:"9ad5";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"e5ed";s:52:"Resources/Private/Language/locallang_excelimport.xml";s:4:"4c1d";s:52:"Resources/Private/Language/locallang_importexcel.xml";s:4:"e2ae";s:38:"Resources/Private/Layouts/Default.html";s:4:"1fa2";s:49:"Resources/Private/Templates/ImportExcel/List.html";s:4:"6cf1";s:57:"Resources/Private/Templates/ImportExcel/assignFields.html";s:4:"b08c";s:53:"Resources/Private/Templates/ImportExcel/doImport.html";s:4:"91d5";s:58:"Resources/Private/Templates/ImportExcel/importPreview.html";s:4:"73da";s:51:"Resources/Private/Templates/ImportExcel/select.html";s:4:"6cf1";s:30:"Resources/Public/Css/style.css";s:4:"e466";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:66:"Resources/Public/Icons/tx_importexcel_domain_model_importexcel.gif";s:4:"905a";s:27:"Resources/Public/Js/main.js";s:4:"e54e";s:51:"Tests/Unit/Controller/ImportExcelControllerTest.php";s:4:"85f9";s:43:"Tests/Unit/Domain/Model/ImportExcelTest.php";s:4:"7cd3";s:14:"doc/manual.sxw";s:4:"8d2d";}',
);

?>