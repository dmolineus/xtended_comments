<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  David Molineus 2010
 * @author     David Molineus <www.netzmacht.de>
 * @package    Comments
 * @license    LGPL
 * @filesource
 */


/**
 * Class ModuleComments
 *
 * @copyright  Leo Feyer 2005-2010
 * @author     Leo Feyer <http://www.typolight.org>
 * @package    Controller
 */
class xc_ModuleComments extends ModuleComments
{
	/**
	 * Generate module
	 */
	protected function compile()
	{
		global $objPage;

		// create a new class just to rename Comments to cg_Comments
		$this->import('xc_Comments');
		$objConfig = new stdClass();

		$objConfig->perPage = $this->perPage;
		$objConfig->order = $this->com_order;
		$objConfig->template = $this->com_template;
		$objConfig->requireLogin = $this->com_requireLogin;
		$objConfig->disableCaptcha = $this->com_disableCaptcha;
		$objConfig->bbcode = $this->com_bbcode;
		$objConfig->moderate = $this->com_moderate;
		
		// define xtended comments specific comments class
		$objConfig->showAvatar = $this->com_showAvatar;
		$objConfig->useAvatarExtension = $this->com_useAvatarExtension;
		$objConfig->defaultAvatar = $this->com_defaultAvatar;
		$objConfig->gravatarSize = $this->com_gravatarSize;
		$objConfig->gravatarRating = $this->com_gravatarRating;
		$objConfig->highlightAuthor = $this->com_highlightAuthor;

		$this->xc_Comments->addCommentsToTemplate($this->Template, $objConfig, 'tl_page', $objPage->id, $GLOBALS['TL_ADMIN_EMAIL']);
	}
}

?>
