<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
JLoader::import('components.com_tjfields.helpers.tjucm', JPATH_SITE);

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

	protected $types;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$this->state  = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params     = $app->getParams('com_tjucm');
		$this->listcolumn = $this->get('Fields');
		$this->types = $this->get('Types');
		$this->allowedToAdd = false;

		$model = $this->getModel("Items");
		$this->ucmTypeId = $id = $model->getState('ucmType.id');
		$this->client = $model->getState('ucm.client');

		$user = JFactory::getUser();
		$canCreate = $user->authorise('core.type.createitem', 'com_tjucm.type.' . $this->ucmTypeId);
		$canView = $user->authorise('core.type.viewitem', 'com_tjucm.type.' . $this->ucmTypeId);

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
				$this->client     = 'com_tjucm.' . $this->ucm_type;
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

		// Get if user is allowed to save the content
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');
		$typeId = $tjUcmModelType->getTypeId($this->client);

		$TypeData = $tjUcmModelType->getItem($typeId);

		$allowedCount = $TypeData->allowed_count;
		$user   = JFactory::getUser();
		$userId = $user->id;

		if (empty($this->id))
		{
			if ($canCreate)
			{
				$this->allowedToAdd = $itemFormModel->allowedToAddTypeData($userId, $this->client, $allowedCount);
			}
		}

		if ($this->created_by == $userId)
		{
			$canView = true;
		}

		// Check the view access to the article (the model has already computed the values).
		if (!$canView)
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
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

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_TJUCM_DEFAULT_PAGE_TITLE'));
		}

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
