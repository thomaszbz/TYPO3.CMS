<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {

	// Register file_navframe
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModulePath(
		'file_navframe',
		'EXT:backend/Modules/FileSystemNavigationFrame/'
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'web',
		'layout',
		'top',
		'EXT:backend/Modules/Layout/',
		array(
			'script' => '_DISPATCH',
			'access' => 'user,group',
			'name' => 'web_layout',
			'labels' => array(
				'tabs_images' => array(
					'tab' => 'EXT:backend/Resources/Public/Icons/module-page.svg',
				),
				'll_ref' => 'LLL:EXT:backend/Resources/Private/Language/locallang_mod.xlf',
			),
		)
	);

	// Register BackendLayoutDataProvider for PageTs
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider']['pagets'] = \TYPO3\CMS\Backend\Provider\PageTsBackendLayoutDataProvider::class;
}
