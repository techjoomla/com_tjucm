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

jimport('joomla.application.component.view');

/**
 * View class for a list of Tjucm.
 *
 * @since  1.6
 */
class TjucmViewItems extends JViewLegacy
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

	protected $canView;

	protected $canEdit;

	protected $canChange;

	protected $canEditOwn;

	protected $canDelete;

	protected $menuparams;

	protected $ucm_type;

	protected $showList;

	protected $created_by;

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
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// Check the view access to the items.
		if (!$user->id)
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
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
		$this->canCreate    = $user->authorise('core.type.createitem', 'com_tjucm.type.' . $this->ucmTypeId);
		$this->canView      = $user->authorise('core.type.viewitem', 'com_tjucm.type.' . $this->ucmTypeId);
		$this->canEdit      = $user->authorise('core.type.edititem', 'com_tjucm.type.' . $this->ucmTypeId);
		$this->canChange    = $user->authorise('core.type.edititemstate', 'com_tjucm.type.' . $this->ucmTypeId);
		$this->canEditOwn   = $user->authorise('core.type.editownitem', 'com_tjucm.type.' . $this->ucmTypeId);
		$this->canDelete    = $user->authorise('core.type.deleteitem', 'com_tjucm.type.' . $this->ucmTypeId);
		$this->canDeleteOwn = $user->authorise('core.type.deleteownitem', 'com_tjucm.type.' . $this->ucmTypeId);

		// If did not get the client from url then get if from menu param
		if (empty($this->client))
		{
			// Get the active item
			$menuItem = $app->getMenu()->getActive();

			// Get the params
			$this->menuparams = $menuItem->params;

			if (!empty($this->menuparams))
			{
				$this->ucm_type   = $this->menuparams->get('ucm_type');

				if (!empty($this->ucm_type))
				{
					JLoader::import('components.com_tjfields.tables.type', JPATH_ADMINISTRATOR);
					$ucmTypeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', JFactory::getDbo()));
					$ucmTypeTable->load(array('alias' => $this->ucm_type));
					$this->client = $ucmTypeTable->unique_identifier;
				}
			}
		}

		// If there are no fields column to show in list view then dont allow to show data
		$this->showList = $model->showListCheck($this->client);

		// Include models
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');

		/* Get model instance here */
		$itemFormModel = JModelLegacy::getInstance('itemForm', 'TjucmModel');

		$input = JFactory::getApplication()->input;
		$input->set("content_id", $id);
		$this->created_by = $input->get("created_by", '', 'INT');

		// Get ucm type data
		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$typeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', JFactory::getDbo()));
		$typeTable->load(array('unique_identifier' => $this->client));
		$typeParams = json_decode($typeTable->params);

		if (isset($typeParams->list_layout) && !empty($typeParams->list_layout))
		{
			$this->setLayout($typeParams->list_layout);
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

		if ($this->created_by == $userId)
		{
			$this->canView = true;
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
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		$menu ? $this->params->def('page_heading', $this->params->get('page_title', $menu->title))
		: $this->params->def('page_heading', JText::_('COM_TJUCM_DEFAULT_PAGE_TITLE'));

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
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
