<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
		'web',	           // Make module a submodule of 'web'
		'importexcel',	   // Submodule key
		'',				   // Position
		array(
			'ImportExcel' => 'select,assignFields,doImport,importPreview,testAjax',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_importexcel.xml',
		)
	);

}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Import Excel files to database');

t3lib_extMgm::addLLrefForTCAdescr('tx_importexcel_domain_model_importexcel', 'EXT:import_excel/Resources/Private/Language/locallang_csh_tx_importexcel_domain_model_importexcel.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_importexcel_domain_model_importexcel');
$TCA['tx_importexcel_domain_model_importexcel'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:import_excel/Resources/Private/Language/locallang_db.xml:tx_importexcel_domain_model_importexcel',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'title,description,field_int,',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/ImportExcel.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_importexcel_domain_model_importexcel.gif'
	),
);

## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder
?>