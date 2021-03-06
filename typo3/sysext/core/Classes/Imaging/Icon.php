<?php
namespace TYPO3\CMS\Core\Imaging;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Icon object, holds all information for one icon, identified by the "identifier" property.
 * Is available to render itself as string.
 */
class Icon {

	/**
	 * @var string the small size
	 */
	const SIZE_SMALL = 'small'; // 16

	/**
	 * @var string the default size
	 */
	const SIZE_DEFAULT = 'default'; // 32

	/**
	 * @var string the large size
	 */
	const SIZE_LARGE = 'large'; // 48

	/**
	 * @internal
	 * @var string the overlay size, which depends on icon size
	 */
	const SIZE_OVERLAY = 'overlay';

	/**
	 * The identifier which the PHP code that calls the IconFactory hands over
	 * @var string
	 */
	protected $identifier;

	/**
	 * The identifier for a possible overlay icon
	 * @var Icon
	 */
	protected $overlayIcon = NULL;

	/**
	 * Contains the size string ("large", "small" or "default")
	 * @var string
	 */
	protected $size = '';

	/**
	 * @var Dimension
	 */
	protected $dimension;

	/**
	 * @var string
	 */
	protected $markup;

	/**
	 * @internal this method is used for internal processing, to get the prepared and final markup use render()
	 * @return string
	 */
	public function getMarkup() {
		return $this->markup;
	}

	/**
	 * @param string $markup
	 */
	public function setMarkup($markup) {
		$this->markup = $markup;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param string $identifier
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * @return Icon
	 */
	public function getOverlayIcon() {
		return $this->overlayIcon;
	}

	/**
	 * @param Icon $overlayIcon
	 */
	public function setOverlayIcon($overlayIcon) {
		$this->overlayIcon = $overlayIcon;
	}

	/**
	 * @return string
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * Sets the size and creates the new dimension
	 * @param string $size
	 */
	public function setSize($size) {
		$this->size = $size;
		$this->dimension = GeneralUtility::makeInstance(Dimension::class, $size);
	}

	/**
	 * @return Dimension
	 */
	public function getDimension() {
		return $this->dimension;
	}

	/**
	 * Render the icon as HTML code
	 *
	 * @return string
	 */
	public function render() {
		return $this->__toString();
	}

	/**
	 * Render the icon as HTML code
	 *
	 * @return string
	 */
	public function __toString() {
		$overlayIconMarkup = '';
		if ($this->overlayIcon !== NULL) {
			$overlayIconMarkup = '<span class="icon-overlay icon-' . htmlspecialchars($this->overlayIcon->getIdentifier()) . '">' . $this->overlayIcon->getMarkup() . '</span>';
		}
		return str_replace('{overlayMarkup}', $overlayIconMarkup, $this->wrappedIcon());
	}

	/**
	 * Wrap icon markup in unified HTML code
	 *
	 * @return string
	 */
	protected function wrappedIcon() {
		$markup = array();
		$markup[] = '<span class="icon icon-size-' . $this->size . ' icon-' . htmlspecialchars($this->getIdentifier()) . '">';
		$markup[] = '	<span class="icon-markup">';
		$markup[] = $this->getMarkup();
		$markup[] = '	</span>';
		$markup[] = '	{overlayMarkup}';
		$markup[] = '</span>';

		return implode(LF, $markup);
	}
}
