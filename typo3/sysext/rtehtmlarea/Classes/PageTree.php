<?php
namespace TYPO3\CMS\Rtehtmlarea;

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
 * Displays the page/file tree for browsing database records or files.
 * Used from TCEFORMS an other elements
 * In other words: This is the ELEMENT BROWSER!
 *
 * Adapted for htmlArea RTE by Stanislas Rolland
 */

use TYPO3\CMS\Backend\Tree\View\ElementBrowserPageTreeView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Rtehtmlarea\Controller\BrowseLinksController;

/**
 * Class which generates the page tree
 */
class PageTree extends ElementBrowserPageTreeView {

	/**
	 * Create the page navigation tree in HTML
	 *
	 * @param array|string $treeArr Tree array
	 * @return string HTML output.
	 */
	public function printTree($treeArr = '') {
		$titleLen = (int)$GLOBALS['BE_USER']->uc['titleLen'];
		if (!is_array($treeArr)) {
			$treeArr = $this->tree;
		}
		$out = '';
		$closeDepth = array();
		foreach ($treeArr as $treeItem) {

			$classAttr = $treeItem['row']['_CSSCLASS'];
			if ($treeItem['isFirst']) {
				$out .= '<ul class="list-tree">';
			}

			// Add CSS classes to the list item
			if ($treeItem['hasSub']) {
				$classAttr .= ' list-tree-control-open';
			}

			$selected = '';
			/** @var BrowseLinksController $controller */
			$controller = $GLOBALS['SOBE'];
			if ($controller->browser->curUrlInfo['act'] === 'page' && $controller->browser->curUrlInfo['pageid'] == $treeItem['row']['uid'] && $controller->browser->curUrlInfo['pageid']) {
				$selected = ' bg-success';
			}
			$aOnClick = 'return jumpToUrl(' . GeneralUtility::quoteJSvalue($this->getThisScript()
					. 'act=' . $controller->browser->act . '&editorNo=' . $controller->browser->editorNo
					. '&contentTypo3Language=' . $controller->browser->contentTypo3Language
					. '&mode=' . $controller->browser->mode . '&expandPage=' . $treeItem['row']['uid']) . ');';
			$cEbullet = $this->ext_isLinkable($treeItem['row']['doktype'], $treeItem['row']['uid'])
				? '<a href="#" class="pull-right" onclick="' . htmlspecialchars($aOnClick) . '"><i class="fa fa-caret-square-o-right"></i></a>'
				: '';
			$out .= '
				<li' . ($classAttr ? ' class="' . trim($classAttr) . '"' : '') . '>
					<span class="list-tree-group' . $selected . '">
						' . $cEbullet . '
						<span class="list-tree-icon">' . $treeItem['HTML'] . '</span>
						' . $this->wrapTitle($this->getTitleStr($treeItem['row'], $titleLen), $treeItem['row'], $this->ext_pArrPages) . '
					</span>
				';

			if (!$treeItem['hasSub']) {
				$out .= '</li>';
			}

			// We have to remember if this is the last one
			// on level X so the last child on level X+1 closes the <ul>-tag
			if ($treeItem['isLast']) {
				$closeDepth[$treeItem['invertedDepth']] = 1;
			}
			// If this is the last one and does not have subitems, we need to close
			// the tree as long as the upper levels have last items too
			if ($treeItem['isLast'] && !$treeItem['hasSub']) {
				for ($i = $treeItem['invertedDepth']; $closeDepth[$i] == 1; $i++) {
					$closeDepth[$i] = 0;
					$out .= '</ul></li>';
				}
			}
		}
		return '<ul class="list-tree list-tree-root">' . $out . '</ul>';
	}

}
