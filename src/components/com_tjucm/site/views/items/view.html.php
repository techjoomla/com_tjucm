<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

jimport('joomla.application.component.view');

/**
 * View class for a list of Tjucm.
 *
 * @since  1.6
 */
class TjucmViewItems extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	protected $listcolumn;

	protected $allowedToAdd;

	protected $ucmTypeId;

	protected $client;

	protected $canCreate;

	protected $canImport;

	protected $menuparams;

	protected $ucm_type;

	protected $showList;

	protected $created_by;

	protected $ucmTypeParams;

	protected $title;

	protected $draft;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return boolean|void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if (!$user->id)
		{
			$msg = Text::_('COM_TJUCM_LOGIN_MSG');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url = base64_encode($current);
			Factory::getApplication()->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
		}

		// Check the view access to the items.
		if (!$user->id)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		$this->state        = $this->get('State');
		$this->items        = $this->get('Items');
		$this->pagination   = $this->get('Pagination');
		$this->params       = $app->getParams('com_tjucm');
		$this->listcolumn   = $this->get('Fields');
		$this->allowedToAdd = false;
		$model              = $this->getModel("Items");
		$this->ucmTypeId    = $id = $model->getState('ucmType.id');
		$this->client       = $model->getState('ucm.client');
		$this->canCreate    = TjucmAccess::canCreate($this->ucmTypeId);
		$this->canImport    = TjucmAccess::canImport($this->ucmTypeId);
		$this->draft        = array("" => Text::_('COM_TJUCM_DATA_STATUS_SELECT_OPTION'),
			"0" => Text::_("COM_TJUCM_DATA_STATUS_SAVE"), "1" => Text::_('COM_TJUCM_DATA_STATUS_DRAFT'));
		$this->canCopyItem = $user->authorise('core.type.copyitem', 'com_tjucm.type.' . $this->ucmTypeId);
		$this->canCopyToSameUcmType = $model->canCopyToSameUcmType($this->client);
		$this->sortableFields = array('text', 'number', 'checkbox', 'textarea', 'textareacounter', 'calendar', 'email', 'radio', 'single_select', 'itemcategory', 'cluster', 'ownership');

		// If did not get the client from url then get if from menu param
		if (empty($this->client))
		{
			// Get the active item
			$menuItem = $app->getMenu()->getActive();

			// Get the params
			$this->menuparams = $menuItem->getparams();

			if (!empty($this->menuparams))
			{
				$this->ucm_type   = $this->menuparams->get('ucm_type');

				if (!empty($this->ucm_type))
				{
					JLoader::import('components.com_tjfields.tables.type', JPATH_ADMINISTRATOR);
					$ucmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
					$ucmTypeTable->load(array('alias' => $this->ucm_type));
					$this->client = $ucmTypeTable->unique_identifier;
					$this->title = $ucmTypeTable->title;
				}
			}
		}

		// To get title of list as per the ucm type
		if (!isset($this->title))
		{
			JLoader::import('components.com_tjfields.tables.type', JPATH_ADMINISTRATOR);
			$ucmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
			$ucmTypeTable->load(array('unique_identifier' => $this->client));
			$this->title = $ucmTypeTable->title;
		}

		// If there are no fields column to show in list view then dont allow to show data
		$this->showList = $model->showListCheck($this->client);

		// Include models
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');

		/* Get model instance here */
		$itemFormModel = BaseDatabaseModel::getInstance('itemForm', 'TjucmModel');

		$input = Factory::getApplication()->input;
		$input->set("content_id", $id);
		$this->created_by = $input->get("created_by", '', 'INT');

		// Get ucm type data
		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$typeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
		$typeTable->load(array('unique_identifier' => $this->client));
		$this->ucmTypeParams = json_decode($typeTable->params);

		if (isset($this->ucmTypeParams->list_layout) && !empty($this->ucmTypeParams->list_layout))
		{
			$this->setLayout($this->ucmTypeParams->list_layout);
		}

		$allowedCount = (!empty($typeTable->allowed_count))?$typeTable->allowed_count:'0';
		$userId = $user->id;

		if (empty($this->id))
		{
			if ($this->canCreate)
			{
				$this->allowedToAdd = $itemFormModel->allowedToAddTypeData($userId, $this->client, $allowedCount);
			}
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		$menu ? $this->params->def('page_heading', $this->params->get('page_title', $menu->title))
		: $this->params->def('page_heading', Text::_('COM_TJUCM_DEFAULT_PAGE_TITLE'));

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
