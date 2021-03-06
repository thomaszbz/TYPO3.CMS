<?php
namespace TYPO3\CMS\Rsaauth\Form\Element;

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

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Generation of form element of the type rsaInput
 */
class RsaInputElement extends AbstractFormElement {

	/**
	 * This will render a single-line input form field, possibly with various control/validation features
	 *
	 * @return array As defined in initializeResultArray() of AbstractNode
	 */
	public function render() {
		$languageService = $this->getLanguageService();

		$table = $this->globalOptions['table'];
		$fieldName = $this->globalOptions['fieldName'];
		$row = $this->globalOptions['databaseRow'];
		$parameterArray = $this->globalOptions['parameterArray'];
		$resultArray = $this->initializeResultArray();
		$resultArray['requireJsModules'] = array('TYPO3/CMS/Rsaauth/RsaEncryptionModule');

		$config = $parameterArray['fieldConf']['config'];
		$specConf = BackendUtility::getSpecConfParts($parameterArray['fieldConf']['defaultExtras']);
		$size = MathUtility::forceIntegerInRange($config['size'] ?: $this->defaultInputWidth, $this->minimumInputWidth, $this->maxInputWidth);
		$evalList = GeneralUtility::trimExplode(',', $config['eval'], TRUE);
		$classes = array();
		$attributes = array(
			'type' => 'text',
			'data-rsa-encryption' => $parameterArray['itemFormElID'] . '_hidden',
			'value' => '',
		);

		// readonly
		if ($this->isGlobalReadonly() || $config['readOnly']) {
			$itemFormElValue = $parameterArray['itemFormElValue'];
			$options = $this->globalOptions;
			$options['parameterArray'] = array(
				'fieldConf' => array(
					'config' => $config,
				),
				'itemFormElValue' => $itemFormElValue,
			);
			$options['renderType'] = 'none';
			/** @var NodeFactory $nodeFactory */
			$nodeFactory = $this->globalOptions['nodeFactory'];
			return $nodeFactory->create($options)->render();
		}

		// @todo: The whole eval handling is a mess and needs refactoring
		foreach ($evalList as $func) {
			switch ($func) {
				case 'required':
					$attributes['data-formengine-validation-rules'] = $this->getValidationDataAsJsonString(array('required' => TRUE));
					break;
				case 'password':
					$attributes['type'] = 'password';
					$attributes['value'] = '********';
					break;
				default:
					// @todo: This is ugly: The code should find out on it's own whether a eval definition is a
					// @todo: keyword like "date", or a class reference. The global registration could be dropped then
					// Pair hook to the one in \TYPO3\CMS\Core\DataHandling\DataHandler::checkValue_input_Eval()
					if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][$func])) {
						if (class_exists($func)) {
							$evalObj = GeneralUtility::makeInstance($func);
							if (method_exists($evalObj, 'deevaluateFieldValue')) {
								$_params = array(
									'value' => $parameterArray['itemFormElValue']
								);
								$parameterArray['itemFormElValue'] = $evalObj->deevaluateFieldValue($_params);
							}
						}
					}
			}
		}
		$evalList = array_filter($evalList, function($value) {
			return $value !== 'password';
		});

		$paramsList = array(
			'field' => $parameterArray['itemFormElName'],
			'evalList' => implode(',', $evalList),
			'is_in' => trim($config['is_in']),
		);
		// set classes
		$classes[] = 'form-control';
		$classes[] = 't3js-clearable';
		$classes[] = 'hasDefaultValue';

		// calculate attributes
		$attributes['data-formengine-validation-rules'] = $this->getValidationDataAsJsonString($config);
		$attributes['data-formengine-input-params'] = json_encode($paramsList);
		$attributes['id'] = str_replace('.', '', uniqid('formengine-input-', TRUE));
		$attributes['name'] = $parameterArray['itemFormElName'] . '_hr';
		if (isset($config['max']) && (int)$config['max'] > 0) {
			$attributes['maxlength'] = (int)$config['max'];
		}
		if (!empty($classes)) {
			$attributes['class'] = implode(' ', $classes);
		}
		if (isset($config['max']) && (int)$config['max'] > 0) {
			$attributes['maxlength'] = (int)$config['max'];
		}

		// This is the EDITABLE form field.
		$placeholderValue = $this->getPlaceholderValue($table, $config, $row);
		if (!empty($placeholderValue)) {
			$attributes['placeholder'] = trim($languageService->sL($placeholderValue));
		}

		// Build the attribute string
		$attributeString = '';
		foreach ($attributes as $attributeName => $attributeValue) {
			$attributeString .= ' ' . $attributeName . '="' . htmlspecialchars($attributeValue) . '"';
		}

		$html = '
			<input'
			. $attributeString
			. $parameterArray['onFocus'] . ' />';

		// This is the ACTUAL form field - values from the EDITABLE field must be transferred to this field which is the one that is written to the database.
		$html .= '<input type="hidden" id="' . $parameterArray['itemFormElID'] . '_hidden" name="' . $parameterArray['itemFormElName'] . '" value="' . htmlspecialchars($parameterArray['itemFormElValue']) . '" />';

		// Going through all custom evaluations configured for this field
		// @todo: Similar to above code!
		foreach ($evalList as $evalData) {
			if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][$evalData])) {
				if (class_exists($evalData)) {
					$evalObj = GeneralUtility::makeInstance($evalData);
					if (method_exists($evalObj, 'returnFieldJS')) {
						$resultArray['extJSCODE'] .= LF . 'TBE_EDITOR.customEvalFunctions[' . GeneralUtility::quoteJSvalue($evalData) . '] = function(value) {' . $evalObj->returnFieldJS() . '}';
					}
				}
			}
		}

		// Wrap a wizard around the item?
		$html = $this->renderWizards(
			array($html),
			$config['wizards'],
			$table,
			$row,
			$fieldName,
			$parameterArray,
			$parameterArray['itemFormElName'] . '_hr', $specConf
		);

		// Add a wrapper to remain maximum width
		$width = (int)$this->formMaxWidth($size);
		$html = '<div class="form-control-wrap"' . ($width ? ' style="max-width: ' . $width . 'px"' : '') . '>' . $html . '</div>';
		$resultArray['html'] = $html;
		return $resultArray;
	}

}
