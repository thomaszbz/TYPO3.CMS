<?php
namespace TYPO3\CMS\Form\View\Mail\Html\Element;

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

/**
 * View object for the fieldset element
 */
class FieldsetElementView extends \TYPO3\CMS\Form\View\Mail\Html\Element\ContainerElementView {

	/**
	 * Default layout of this object
	 *
	 * @var string
	 */
	protected $layout = '
		<td colspan="2">
			<table cellspacing="0" style="padding-left: 20px; margin-bottom: 20px;">
				<legend />
				<containerWrap />
			</table>
		</td>
	';
}
