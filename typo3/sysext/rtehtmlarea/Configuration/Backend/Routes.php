<?php

/**
 * Definitions of routes
 */
return [
	// Register RTE browse links wizard
	'rtehtmlarea_wizard_browse_links' => [
		'path' => '/rte/wizard/link',
		'controller' => \TYPO3\CMS\Rtehtmlarea\Controller\BrowseLinksController::class
	],
	// Register RTE select image wizard
	'rtehtmlarea_wizard_select_image' => [
		'path' => '/rte/wizard/image',
		'controller' => \TYPO3\CMS\Rtehtmlarea\Controller\SelectImageController::class
	],
	// Register RTE user elements wizard
	'rtehtmlarea_wizard_user_elements' => [
		'path' => '/rte/wizard/userelements',
		'controller' => \TYPO3\CMS\Rtehtmlarea\Controller\UserElementsController::class
	],
	// Register RTE parse html wizard
	'rtehtmlarea_wizard_parse_html' => [
		'path' => '/rte/wizard/parsehtml',
		'controller' => \TYPO3\CMS\Rtehtmlarea\Controller\ParseHtmlController::class
	],
];
