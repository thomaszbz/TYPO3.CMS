<?php
namespace TYPO3\CMS\Recordlist\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Recordlist\Browser\ElementBrowser;

/**
 * Script class for the Element Browser window.
 */
class ElementBrowserController implements \TYPO3\CMS\Core\Http\ControllerInterface {

	/**
	 * The mode determines the main kind of output of the element browser.
	 * There are these options for values:
	 *  - "rte" will show the link selector for the Rich Text Editor (see main_rte())
	 *  - "wizard" will allow you to browse for links (like "rte") which are passed back to FormEngine (see main_rte(TRUE))
	 *  - "db" will allow you to browse for pages or records in the page tree for FormEngine select fields (see main_db())
	 *  - "file"/"filedrag" will allow you to browse for files in the folder mounts for FormEngine file selections (main_file())
	 *  - "folder" will allow you to browse for folders in the folder mounts for FormEngine folder selecitons (see main_folder())
	 *
	 * @var string
	 */
	public $mode;

	/**
	 * Holds an instance of ElementBrowser class or a subclass
	 *
	 * This is needed fo intercommunication between tree classes that
	 * need access to variables via $GLOBALS['SOBE'].
	 * Not the most nice solution but introduced since we don't have
	 * another general way to return class-instances or registry for now
	 *
	 * @var ElementBrowser
	 */
	public $browser;

	/**
	 * Document template object
	 *
	 * @var DocumentTemplate
	 */
	public $doc;

	/**
	 * @var string
	 */
	public $content = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		$GLOBALS['SOBE'] = $this;

		// Creating backend template object:
		// this might not be needed but some classes refer to $GLOBALS['SOBE']->doc, so ...
		$this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);
		// Apply the same styles as those of the base script
		$this->doc->bodyTagId = 'typo3-browse-links-php';

		$this->init();
	}

	protected function init() {
		$this->getLanguageService()->includeLLFile('EXT:lang/locallang_browse_links.xlf');

		$this->mode = GeneralUtility::_GP('mode');
		if (!$this->mode) {
			$this->mode = 'rte';
		}
	}

	/**
	 * Injects the request object for the current request or subrequest
	 * As this controller goes only through the main() method, it is rather simple for now
	 *
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface $response
	 */
	public function processRequest(ServerRequestInterface $request) {
		$this->main();

		/** @var Response $response */
		$response = GeneralUtility::makeInstance(Response::class);
		$response->getBody()->write($this->content);
		return $response;
	}

	/**
	 * Main function, detecting the current mode of the element browser and branching out to internal methods.
	 *
	 * @return void
	 */
	public function main() {
		$this->setTemporaryDbMounts();

		$this->content = '';

		// Render type by user func
		$browserRendered = FALSE;
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/browse_links.php']['browserRendering'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/browse_links.php']['browserRendering'] as $classRef) {
				$browserRenderObj = GeneralUtility::getUserObj($classRef);
				if (is_object($browserRenderObj) && method_exists($browserRenderObj, 'isValid') && method_exists($browserRenderObj, 'render')) {
					if ($browserRenderObj->isValid($this->mode, $this)) {
						$this->content .= $browserRenderObj->render($this->mode, $this);
						$browserRendered = TRUE;
						break;
					}
				}
			}
		}
		// if type was not rendered use default rendering functions
		if (!$browserRendered) {
			$this->browser = $this->getElementBrowserInstance();
			$this->browser->init();
			$backendUser = $this->getBackendUser();
			$modData = $backendUser->getModuleData('browse_links.php', 'ses');
			list($modData) = $this->browser->processSessionData($modData);
			$backendUser->pushModuleData('browse_links.php', $modData);

			// Output the correct content according to $this->mode
			switch ((string)$this->mode) {
				case 'rte':
					$this->content = $this->browser->main_rte();
					break;
				case 'db':
					$this->content = $this->browser->main_db();
					break;
				case 'file':
				case 'filedrag':
					$this->content = $this->browser->main_file();
					break;
				case 'folder':
					$this->content = $this->browser->main_folder();
					break;
				case 'wizard':
					$this->content = $this->browser->main_rte(TRUE);
					break;
			}
		}
	}

	/**
	 * @return void
	 */
	protected function setTemporaryDbMounts() {
		$backendUser = $this->getBackendUser();

		// Clear temporary DB mounts
		$tmpMount = GeneralUtility::_GET('setTempDBmount');
		if (isset($tmpMount)) {
			$backendUser->setAndSaveSessionData('pageTree_temporaryMountPoint', (int)$tmpMount);
		}
		// Set temporary DB mounts
		$alternativeWebmountPoint = (int)$backendUser->getSessionData('pageTree_temporaryMountPoint');
		if ($alternativeWebmountPoint) {
			$alternativeWebmountPoint = GeneralUtility::intExplode(',', $alternativeWebmountPoint);
			$backendUser->setWebmounts($alternativeWebmountPoint);
		} else {
			switch ((string)$this->mode) {
				case 'rte':
				case 'db':
				case 'wizard':
					// Setting alternative browsing mounts (ONLY local to browse_links.php this script so they stay "read-only")
					$alternativeWebmountPoints = trim($backendUser->getTSConfigVal('options.pageTree.altElementBrowserMountPoints'));
					$appendAlternativeWebmountPoints = $backendUser->getTSConfigVal('options.pageTree.altElementBrowserMountPoints.append');
					if ($alternativeWebmountPoints) {
						$alternativeWebmountPoints = GeneralUtility::intExplode(',', $alternativeWebmountPoints);
						$this->getBackendUser()->setWebmounts($alternativeWebmountPoints, $appendAlternativeWebmountPoints);
					}
			}
		}
	}

	/**
	 * Get instance of ElementBrowser
	 *
	 * This method shall be overwritten in subclasses
	 *
	 * @return ElementBrowser
	 */
	protected function getElementBrowserInstance() {
		return GeneralUtility::makeInstance(ElementBrowser::class);
	}

	/**
	 * Print module content
	 *
	 * @return void
	 * @deprecated since TYPO3 CMS 7, will be removed in TYPO3 CMS 8, use processRequest() instead
	 */
	public function printContent() {
		GeneralUtility::logDeprecatedFunction();
		echo $this->content;
	}

	/**
	 * @return LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}

	/**
	 * @return BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

}
