<?php
namespace TYPO3\CMS\Rtehtmlarea\Extension;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Rtehtmlarea\RteHtmlAreaApi;

/**
 * User Elements extension for htmlArea RTE
 */
class UserElements extends RteHtmlAreaApi {

	/**
	 * The name of the plugin registered by the extension
	 *
	 * @var string
	 */
	protected $pluginName = 'UserElements';

	/**
	 * The comma-separated list of button names that the registered plugin is adding to the htmlArea RTE toolbar
	 *
	 * @var string
	 */
	protected $pluginButtons = 'user';

	/**
	 * The name-converting array, converting the button names used in the RTE PageTSConfing to the button id's used by the JS scripts
	 *
	 * @var array
	 */
	protected $convertToolbarForHtmlAreaArray = array(
		'user' => 'UserElements'
	);

	/**
	 * Return JS configuration of the htmlArea plugins registered by the extension
	 *
	 * @return string JS configuration for registered plugins, in this case, JS configuration of block elements
	 */
	public function buildJavascriptConfiguration() {
		$jsArray = array();
		$button = 'user';
		if (in_array($button, $this->toolbar)) {
			if (!is_array($this->configuration['thisConfig']['buttons.']) || !is_array($this->configuration['thisConfig']['buttons.'][($button . '.')])) {
				$jsArray[] = 'RTEarea[editornumber].buttons.' . $button . ' = new Object();';
			}
			$jsArray[] = 'RTEarea[editornumber].buttons.' . $button . '.pathUserModule = ' . GeneralUtility::quoteJSvalue(BackendUtility::getModuleUrl('rtehtmlarea_wizard_user_elements')) . ';';
		}
		return implode(LF, $jsArray);
	}

}
