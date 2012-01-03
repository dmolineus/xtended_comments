<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * @copyright  David Molineus 2010
 * @author     David Molineus <mail@netzmacht.de>
 * @package    News
 * @license    LGPL
 * @filesource
 */


/**
 * Class xc_ModuleNewsReader
 *
 * Front end module "news reader".
 * @copyright  David Molineus
 * @author     David Molineus <mail@netzmacht.de>
 * @package    News
 */
class xc_ModuleNewsReader extends ModuleNewsReader
{
	/**
	 * just made some litte changes of the original ModuleNewesReader:compile() method
	 *  + load different Comments class
	 *  + define config values for xtended comments (gravatar stuff)
	 */
	protected function compile()
	{
		global $objPage;

		$this->Template->articles = '';
		$this->Template->referer = 'javascript:history.go(-1)';
		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

		$time = time();

		// Get news item
		$objArticle = $this->Database->prepare("SELECT *, author AS authorId, (SELECT title FROM tl_news_archive WHERE tl_news_archive.id=tl_news.pid) AS archive, (SELECT jumpTo FROM tl_news_archive WHERE tl_news_archive.id=tl_news.pid) AS parentJumpTo, (SELECT name FROM tl_user WHERE id=author) AS author FROM tl_news WHERE pid IN(" . implode(',', array_map('intval', $this->news_archives)) . ") AND (id=? OR alias=?)" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<?) AND (stop='' OR stop>?) AND published=1" : ""))
									 ->limit(1)
									 ->execute((is_numeric($this->Input->get('items')) ? $this->Input->get('items') : 0), $this->Input->get('items'), $time, $time);

		if ($objArticle->numRows < 1)
		{
			$this->Template->articles = '<p class="error">' . sprintf($GLOBALS['TL_LANG']['MSC']['invalidPage'], $this->Input->get('items')) . '</p>';

			// Do not index the page
			$objPage->noSearch = 1;
			$objPage->cache = 0;

			// Send 404 header
			header('HTTP/1.1 404 Not Found');
			return;
		}

		$arrArticle = $this->parseArticles($objArticle);
		$this->Template->articles = $arrArticle[0];

		// Overwrite page title
		if (strlen($objArticle->headline))
		{
			$objPage->pageTitle = $objArticle->headline;
		}
		
		// Overwrite the page description
		if ($objArticle->teaser != '')
		{
			$objPage->description = $this->prepareMetaDescription($objArticle->teaser);
		}

		// HOOK: comments extension required
		if ($objArticle->noComments || !in_array('comments', $this->Config->getActiveModules()))
		{
			$this->Template->allowComments = false;
			return;
		}

		// Check whether comments are allowed
		$objArchive = $this->Database->prepare("SELECT * FROM tl_news_archive WHERE id=?")
									 ->limit(1)
									 ->execute($objArticle->pid);

		if ($objArchive->numRows < 1 || !$objArchive->allowComments)
		{
			$this->Template->allowComments = false;
			return;
		}

		$this->Template->allowComments = true;

		// load xtended comments specific Comments class
		$this->import('xc_Comments');
		$arrNotifies = array();

		// Adjust the comments headline level
		$intHl = min(intval(str_replace('h', '', $this->hl)), 5);
		$this->Template->hlc = 'h' . ($intHl + 1);

		$this->import('Comments');
		$arrNotifies = array();

		// Notify system administrator
		if ($objArchive->notify != 'notify_author')
		{
			$arrNotifies[] = $GLOBALS['TL_ADMIN_EMAIL'];
		}

		// Notify author
		if ($objArchive->notify != 'notify_admin')
		{
			$objAuthor = $this->Database->prepare("SELECT email FROM tl_user WHERE id=?")
										->limit(1)
										->execute($objArticle->authorId);

			if ($objAuthor->numRows)
			{
				$arrNotifies[] = $objAuthor->email;
			}
		}

		$objConfig = new stdClass();

		$objConfig->perPage = $objArchive->perPage;
		$objConfig->order = $objArchive->sortOrder;
		$objConfig->template = $this->com_template;
		$objConfig->requireLogin = $objArchive->requireLogin;
		$objConfig->disableCaptcha = $objArchive->disableCaptcha;
		$objConfig->bbcode = $objArchive->bbcode;
		$objConfig->moderate = $objArchive->moderate;
		
		// define xtended comments specific comments class
		$objConfig->showAvatar = $objArchive->xc_showAvatar;
		$objConfig->useAvatarExtension = $objArchive->xc_useAvatarExtension;
		$objConfig->defaultAvatar = $objArchive->xc_defaultAvatar;
		$objConfig->gravatarSize = $objArchive->xc_gravatarSize;
		$objConfig->gravatarRating = $objArchive->xc_gravatarRating;
		$objConfig->highlightAuthor = $objArchive->xc_highlightAuthor;

		// use xc_Comments
		$this->xc_Comments->addCommentsToTemplate($this->Template, $objConfig, 'tl_news', $objArticle->id, $arrNotifies);
	}
}

?>
