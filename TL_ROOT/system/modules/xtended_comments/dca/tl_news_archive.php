<?php 

/**
 * Comment Gravatars Module
 * Copyright (C) 2010 David Molineus
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
 * @author     David Molineus <mail@netzmacht.de>
 * @package    comment_gravatars
 * @license    LGPL
 * @filesource
 */


/**
 * Table tl_news_archive
 */
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][] = 'xc_showAvatar';

$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['allowComments'] .= ',xc_highlightAuthor,xc_showAvatar';
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['xc_showAvatar'] = 'xc_defaultAvatar,xc_gravatarRating,xc_gravatarSize,xc_useAvatarExtension';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['xc_showAvatar'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_news_archive']['xc_showAvatar'],
	'exclude'		=> false,
	'inputType'		=> 'checkbox',
	'eval'			=> array('tl_class'=>'clr', 'submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['xc_defaultAvatar'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_news_archive']['xc_defaultAvatar'],
	'exclude'		=> true,
	'inputType'		=> 'fileTree',
	'eval'			=> array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions' => 'jpg,png,gif')
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['xc_gravatarRating'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_news_archive']['xc_gravatarRating']['label'],
	'inputType'		=> 'select',
	'options'		=> array('g', 'pg'),
	'reference'		=> &$GLOBALS['TL_LANG']['tl_news_archive']['xc_gravatarRating'],
	'exclude'		=> false,
	'eval'			=> array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['xc_gravatarSize'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_news_archive']['xc_gravatarSize'],
	'inputType'		=> 'text',
	'exclude'		=> false,
	'eval'			=> array('rgxp' => 'digit', 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['xc_useAvatarExtension'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_news_archive']['xc_useAvatarExtension'],
	'exclude'		=> false,
	'inputType'		=> 'checkbox',
	'eval'			=> array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['xc_highlightAuthor'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_news_archive']['xc_highlightAuthor'],
	'inputType'		=> 'select',
	'options'		=> array('notify_author', 'notify_admin', 'notify_both'),
	'reference'		=> &$GLOBALS['TL_LANG']['tl_news_archive'],
	'exclude'		=> false,
	'eval'			=> array('includeBlankOption' => true, 'tl_class' => 'w50')
);

?>
