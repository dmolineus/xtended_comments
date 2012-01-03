<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Leo Feyer 2005-2010
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Comments
 * @license    LGPL
 * @filesource
 */


/**
 * Class Comments
 *
 * @copyright  Leo Feyer 2005-2010
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Controller
 */
class xc_Comments extends Comments
{

	/**
	 * Add comments to a template
	 * @param object
	 * @param object
	 * @param string
	 * @param integer
	 * @param array
	 */
	public function addCommentsToTemplate($objTemplate, $objConfig, $strSource, $intParent, $arrNotifies)
	{
		$limit = null;
		$arrComments = array();

		// Pagination
		if ($objConfig->perPage > 0)
		{
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
			$limit = $objConfig->perPage;
			$offset = ($page - 1) * $objConfig->perPage;
 
			// Get total number of comments
			$objTotal = $this->Database->prepare("SELECT COUNT(*) AS count FROM tl_comments WHERE source=? AND parent=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
									   ->execute($strSource, $intParent);

			// Add pagination menu
			$objPagination = new Pagination($objTotal->count, $objConfig->perPage);
			$objTemplate->pagination = $objPagination->generate("\n  ");
		}

		$this->loadDataContainer('tl_member');
		$strAvatarField = '';
		$strAvatarJoin = '';	
		
		// add support for avatar extension
		// make sure that avatar field exists
		if(isset($GLOBALS['TL_DCA']['tl_member']['fields']['avatar'])) {
			$strAvatarField = ', m.avatar AS member_avatar';
			$strAvatarJoin = 'LEFT OUTER JOIN tl_member m ON m.id = c.member_id ';
		}
		
		// check if gravatar module is used
		// field is not defined with dca, so check module name
		if($GLOBALS['FE_MOD']['user']['avatar'] == 'GravatarModule') {
			$strAvatarField .= ', m.gravatar AS member_gravatar';
		}

		// Get all published comments
		// geht author mail address to highlight comments of the author
		// doesn't work with ModuleComments
		if(($strSource == 'tl_news') && ($objConfig->highlightAuthor != 'notify_admin'))
		{
			$strSql = "SELECT c . * , u.email AS author_email" . $strAvatarField  . " FROM tl_comments c LEFT OUTER JOIN " . $strSource;
			$strSql .= " n ON n.id = c.parent LEFT OUTER JOIN tl_user u ON u.id = n.author ";
			$strSql .= $strAvatarJoin . "WHERE c.source=? AND c.parent=? " ;
			$strSql .= (!BE_USER_LOGGED_IN ? " AND c.published=1" : "") . " ORDER BY c.date" . (($objConfig->order == 'descending') ? " DESC" : "");
		}
		elseif($strSource == 'tl_content' && ($objConfig->highlightAuthor != 'notify_admin')) 
		{			
			$strSql = "SELECT c . * , u.email AS author_email" . $strAvatarField  . " FROM tl_comments c LEFT OUTER JOIN tl_content d ON d.id= c.parent ";
			$strSql .= "LEFT OUTER JOIN tl_article n ON n.id = d.pid ";
			$strSql .= "LEFT OUTER JOIN tl_user u ON u.id = n.author ";
			$strSql .= $strAvatarJoin . " WHERE c.source=? AND c.parent=? ";
			$strSql .= (!BE_USER_LOGGED_IN ? " AND c.published=1" : "") . " ORDER BY c.date" . (($objConfig->order == 'descending') ? " DESC" : "");
		}
		// otherwise use default query
		else 
		{
			$strSql = "SELECT c.*" . $strAvatarField  . " FROM tl_comments c ";
			$strSql .= $strAvatarJoin;
			$strSql .= "WHERE source=? AND parent=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : "") . " ORDER BY date";
			$strSql .= (($objConfig->order == 'descending') ? " DESC" : "");
		}
		
		$objCommentsStmt = $this->Database->prepare($strSql);

		if ($limit)
		{
			$objCommentsStmt->limit($limit, $offset);
		}

		$objComments = $objCommentsStmt->execute($strSource, $intParent);
		$total = $objComments->numRows;

		if ($total > 0)
		{
			$count = 0;

			if ($objConfig->template == '')
			{
				$objConfig->template = 'com_default';
			}

			$objPartial = new FrontendTemplate($objConfig->template);

			while ($objComments->next())
			{
				$objPartial->setData($objComments->row());

				$objPartial->comment = trim($objComments->comment);
				$objPartial->datim = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objComments->date);
				$objPartial->date = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objComments->date);
				$objPartial->class = (($count < 1) ? ' first' : '') . (($count >= ($total - 1)) ? ' last' : '') . (($count % 2 == 0) ? ' even' : ' odd');
				$objPartial->by = $GLOBALS['TL_LANG']['MSC']['comment_by'];
				$objPartial->id = 'c' . $objComments->id;
				$objPartial->timestamp = $objComments->date;
				
				// add author class if needed
				// check if highlighting is needed
				$highlight = false;
				
				switch($objConfig->highlightAuthor) {
					case 'notify_author':
						$highlight = ($objComments->author_email == $objComments->email) ? true : false;
					break;
					
					case 'notify_admin':
						$highlight = ($objConfig->highlightAuthor == 'notify_admin' && $objComments->email == $GLOBALS['TL_CONFIG']['adminEmail']) ? true : false;
					break;
					case 'notify_both':
						$highlight = ($objComments->email == $GLOBALS['TL_CONFIG']['adminEmail']);
						$highlight = ($highlight || ($objComments->author_email == $objComments->email)); 
					break;
				}
				
				if($highlight) {
					$objPartial->class .= ' author';
				}
				
				$fields = &$GLOBALS['TL_DCA']['tl_member']['fields'];

				// give gravatar specific variables to comment template
				if($objConfig->showAvatar) {					
					// check if avatar extension is installed and used
					// check if gravatar extension is installed and gravatar is prefered by user
					if(($objConfig->useAvatarExtension == 1) && (isset($fields['avatar'])) && ($objComments->member_avatar != '') && ($objComments->member_gravatar == null)) {
						$objPartial->avatarUrl = Avatar::filename($objComments->member_avatar);
					}
					else {
						$url = 'http://www.gravatar.com/avatar/' . md5($objComments->email) . '?d=';
						$url .= ($objConfig->defaultAvatar != '') ? $this->Environment->base . $this->urlEncode($objConfig->defaultAvatar) : '';
						$url .= ($objConfig->gravatarSize ? '&amp;s=' . $objConfig->gravatarSize : '');
						$url .= ($objConfig->gravatarRating ? '&amp;r=' . $objConfig->gravatarRating : '') ;
						$objPartial->avatarUrl = $url;
					}
				}
				
				$objPartial->showAvatar = $objConfig->showAvatar;					
				$objPartial->avatarSize = $objConfig->gravatarSize;

				$arrComments[] = $objPartial->parse();
				++$count;
			}
		}

		$objTemplate->comments = $arrComments;
		$objTemplate->addComment = $GLOBALS['TL_LANG']['MSC']['addComment'];
		$objTemplate->name = $GLOBALS['TL_LANG']['MSC']['com_name'];
		$objTemplate->email = $GLOBALS['TL_LANG']['MSC']['com_email'];
		$objTemplate->website = $GLOBALS['TL_LANG']['MSC']['com_website'];

		// Get the front end user object
		$this->import('FrontendUser', 'User');

		// Access control
		if ($objConfig->requireLogin && !BE_USER_LOGGED_IN && !FE_USER_LOGGED_IN)
		{
			$objTemplate->requireLogin = true;
			return;
		}

		// Form fields
		$arrFields = array
		(
			'name' => array
			(
				'name' => 'name',
				'label' => $GLOBALS['TL_LANG']['MSC']['com_name'],
				'value' => trim($this->User->firstname . ' ' . $this->User->lastname),
				'inputType' => 'text',
				'eval' => array('mandatory'=>true, 'maxlength'=>64)
			),
			'email' => array
			(
				'name' => 'email',
				'label' => $GLOBALS['TL_LANG']['MSC']['com_email'],
				'value' => $this->User->email,
				'inputType' => 'text',
				'eval' => array('rgxp'=>'email', 'mandatory'=>true, 'maxlength'=>128, 'decodeEntities'=>true)
			),
			'website' => array
			(
				'name' => 'website',
				'label' => $GLOBALS['TL_LANG']['MSC']['com_website'],
				'inputType' => 'text',
				'eval' => array('rgxp'=>'url', 'maxlength'=>128, 'decodeEntities'=>true)
			)
		);

		// Captcha
		if (!$objConfig->disableCaptcha)
		{
			$arrFields['captcha'] = array
			(
				'name' => 'captcha',
				'inputType' => 'captcha',
				'eval' => array('mandatory'=>true)
			);
		}

		// Comment field
		$arrFields['comment'] = array
		(
			'name' => 'comment',
			'label' => $GLOBALS['TL_LANG']['MSC']['com_comment'],
			'inputType' => 'textarea',
			'eval' => array('mandatory'=>true, 'rows'=>4, 'cols'=>40, 'preserveTags'=>true)
		);

		$doNotSubmit = false;
		$arrWidgets = array();
		$strFormId = 'com_'. $strSource .'_'. $intParent;

		// Initialize widgets
		foreach ($arrFields as $arrField)
		{
			$strClass = $GLOBALS['TL_FFL'][$arrField['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				continue;
			}

			$arrField['eval']['required'] = $arrField['eval']['mandatory'];
			$objWidget = new $strClass($this->prepareForWidget($arrField, $arrField['name'], $arrField['value']));

			// Validate the widget
			if ($this->Input->post('FORM_SUBMIT') == $strFormId)
			{
				$objWidget->validate();

				if ($objWidget->hasErrors())
				{
					$doNotSubmit = true;
				}
			}

			$arrWidgets[$arrField['name']] = $objWidget;
		}

		$objTemplate->fields = $arrWidgets;
		$objTemplate->submit = $GLOBALS['TL_LANG']['MSC']['com_submit'];
		$objTemplate->action = ampersand($this->Environment->request);
		$objTemplate->messages = $this->getMessages();
		$objTemplate->formId = $strFormId;
		$objTemplate->hasError = $doNotSubmit;

		// Confirmation message
		if ($_SESSION['TL_COMMENT_ADDED'])
		{
			global $objPage;

			// Do not index the page
			$objPage->noSearch = 1;
			$objPage->cache = 0;

			$objTemplate->confirm = $GLOBALS['TL_LANG']['MSC']['com_confirm'];
			$_SESSION['TL_COMMENT_ADDED'] = false;
		}

		// Add the comment
		if ($this->Input->post('FORM_SUBMIT') == $strFormId && !$doNotSubmit)
		{
			$this->import('String');
			$strWebsite = $arrWidgets['website']->value;

			// Add http:// to the website
			if (strlen($strWebsite) && !preg_match('@^(https?://|ftp://|mailto:|#)@i', $strWebsite))
			{
				$strWebsite = 'http://' . $strWebsite;
			}

			// Do not parse any tags in the comment
			$strComment = htmlspecialchars(trim($arrWidgets['comment']->value));
			$strComment = str_replace(array('&amp;', '&lt;', '&gt;'), array('[&]', '[lt]', '[gt]'), $strComment);

			// Remove multiple line feeds
			$strComment = preg_replace('@\n\n+@', "\n\n", $strComment);

			// Parse BBCode
			if ($objConfig->bbcode)
			{
				$strComment = $this->parseBbCode($strComment);
			}

			// Prevent cross-site request forgeries
			$strComment = preg_replace('/(href|src|on[a-z]+)="[^"]*(contao\/main\.php|typolight\/main\.php|javascript|vbscri?pt|script|alert|document|cookie|window)[^"]*"+/i', '$1="#"', $strComment);

			$time = time();

			// Prepare the record
			$arrSet = array
			(
				'source' => $strSource,
				'parent' => $intParent,
				'tstamp' => $time,
				'name' => $arrWidgets['name']->value,
				'email' => $arrWidgets['email']->value,
				'website' => $strWebsite,
				'comment' => $this->convertLineFeeds($strComment),
				'ip' => $this->Environment->ip,
				'date' => $time,
				'published' => ($objConfig->moderate ? '' : 1),
				'member_id' => (FE_USER_LOGGED_IN ? $this->User->id : 0) /* important for avatar usage */
			);

			$insertId = $this->Database->prepare("INSERT INTO tl_comments %s")->set($arrSet)->execute()->insertId;

			// HOOK: add custom logic
			if (isset($GLOBALS['TL_HOOKS']['addComment']) && is_array($GLOBALS['TL_HOOKS']['addComment']))
			{
				foreach ($GLOBALS['TL_HOOKS']['addComment'] as $callback)
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($insertId, $arrSet);
				}
			}

			// Notification
			$objEmail = new Email();

			$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
			$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
			$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['com_subject'], $this->Environment->host);

			// Convert the comment to plain text
			$strComment = strip_tags($strComment);
			$strComment = $this->String->decodeEntities($strComment);
			$strComment = str_replace(array('[&]', '[lt]', '[gt]'), array('&', '<', '>'), $strComment);

			// Add comment details
			$objEmail->text = sprintf($GLOBALS['TL_LANG']['MSC']['com_message'],
									  $arrSet['name'] . ' (' . $arrSet['email'] . ')',
									  $strComment,
									  $this->Environment->base . $this->Environment->request,
									  $this->Environment->base . 'contao/main.php?do=comments&act=edit&id=' . $insertId);

			$objEmail->sendTo($arrNotifies);

			// Pending for approval
			if ($objConfig->moderate)
			{
				$_SESSION['TL_COMMENT_ADDED'] = true;
			}

			$this->reload();
		}
	}

}

?>
