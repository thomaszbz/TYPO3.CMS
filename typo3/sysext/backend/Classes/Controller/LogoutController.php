<?php
namespace TYPO3\CMS\Backend\Controller;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;

/**
 * Script Class for logging a user out.
 * Does not display any content, just calls the logout-function for the current user and then makes a redirect.
 */
class LogoutController implements \TYPO3\CMS\Core\Http\ControllerInterface {

	/**
	 * Injects the request object for the current request or subrequest
	 * As this controller goes only through the main() method, it is rather simple for now
	 * This will be split up in an abstract controller once proper routing/dispatcher is in place.
	 *
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface $response
	 */
	public function processRequest(ServerRequestInterface $request) {
		$this->logout();

		$redirectUrl = isset($request->getParsedBody()['redirect']) ? $request->getParsedBody()['redirect'] : $request->getQueryParams()['redirect'];
		$redirectUrl = GeneralUtility::sanitizeLocalUrl($redirectUrl);
		if (empty($redirectUrl)) {
			/** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
			$uriBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
			$redirectUrl = (string)$uriBuilder->buildUriFromRoute('login', array(), $uriBuilder::ABSOLUTE_URL);
		}
		/** @var Response $response */
		$response = GeneralUtility::makeInstance(Response::class);
		$response =	$response->withHeader('Location', GeneralUtility::locationHeaderUrl($redirectUrl));
		return $response->withStatus(303);
	}

	/**
	 * Performs the logout processing
	 *
	 * @return void
	 */
	public function logout() {
		if (empty($this->getBackendUser()->user['username'])) {
			return;
		}
		// Logout written to log
		$this->getBackendUser()->writelog(255, 2, 0, 1, 'User %s logged out from TYPO3 Backend', array($this->getBackendUser()->user['username']));
		/** @var \TYPO3\CMS\Core\FormProtection\BackendFormProtection $backendFormProtection */
		$backendFormProtection = FormProtectionFactory::get();
		$backendFormProtection->removeSessionTokenFromRegistry();
		$this->getBackendUser()->logoff();
	}

	/**
	 * Returns the current BE user.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

}
