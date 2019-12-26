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
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.database.table');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View to edit
 *
 * @since  1.6
 */
class TjucmViewItemform extends JViewLegacy
{
	/**
	 * The JForm object
	 *
	 * @var  Form
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * The model state
	 *
	 * @var  object|array
	 */
	protected $params;

	/**
	 * @var  boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $canSave;

	/**
	 * The Record Id
	 *
	 * @var  Int
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $id;

	/**
	 * The Copy Record Id
	 *
	 * @var  Int
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $copyRecId;

	/**
	 * The Title of view
	 *
	 * @var  String
	 *
	 * @since 1.2.4
	 */
	protected $title;

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
		$app  = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();
		$this->state   = $this->get('State');
		$this->id = $input->getInt('id', $input->getInt('content_id', 0));

		// Include models
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');

		/* Get model instance here */
		$model = $this->getModel();
		$model->setState('item.id', $this->id);

		$this->item    = $this->get('Data');
		$this->params  = $app->getParams('com_tjucm');
		$this->canSave = $this->get('CanSave');
		$this->form = $this->get('Form');
		$this->client = $input->get('client');

		$clusterId = $input->getInt('cluster_id', 0);

		// Set cluster_id in request parameters
		if ($this->id && !$clusterId)
		{
			$input->set('cluster_id', $this->item->cluster_id);
			$clusterId = $this->item->cluster_id;
		}

		// Get com_cluster component status
		if (ComponentHelper::getComponent('com_cluster', true)->enabled)
		{
			// Get com_subusers component status
			$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

			// Check user have permission to edit record of assigned cluster
			if ($subUserExist && !empty($clusterId) && !$user->authorise('core.manageall', 'com_cluster'))
			{
				JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);

				// Check user has permission for mentioned cluster
				if (!RBACL::authorise($user->id, 'com_cluster', 'core.manage', $clusterId))
				{
					$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
					$app->setHeader('status', 403, true);

					return;
				}
			}
		}

		// Get a copy record id
		$this->copyRecId = (int) $app->getUserState('com_tjucm.edit.itemform.data.copy_id', 0);

		// Check copy id set and empty request id record
		if ($this->copyRecId && !$this->id)
		{
			$this->id = $this->copyRecId;
		}

		// Code check cluster Id of URL with saved cluster_id both are equal in edit mode
		if (!$this->copyRecId && $this->id)
		{
			$clusterId = $input->getInt("cluster_id", 0);

			if ($clusterId != $this->item->cluster_id)
			{
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
				$app->setHeader('status', 403, true);

				return;
			}
		}

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

		if (empty($this->client))
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_ITEM_DOESNT_EXIST'), 'error');
			$app->setHeader('status', 404, true);

			return;
		}

		// Check the view access to the itemform (the model has already computed the values).
		if ($this->item->params->get('access-view') == false)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return;
		}

		// Get ucm type data
		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$typeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', JFactory::getDbo()));
		$typeTable->load(array('unique_identifier' => $this->client));
		$typeParams = json_decode($typeTable->params);

		// Check if the UCM type is unpublished
		if ($typeTable->state == "0")
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_ITEM_DOESNT_EXIST'), 'error');
			$app->setHeader('status', 404, true);

			return;
		}

		// Set Layout to type view
		$layout = isset($typeParams->layout) ? $typeParams->layout : '';

		if (isset($typeParams->layout) && !empty($typeParams->layout))
		{
			$this->setLayout($typeParams->layout);
		}

		$allowedCount = $typeParams->allowed_count;
		$userId = $user->id;

		if (empty($this->id))
		{
			$this->allowedToAdd = $model->allowedToAddTypeData($userId, $this->client, $allowedCount);

			if (!$this->allowedToAdd)
			{
				JLoader::import('controllers.itemform', JPATH_SITE . '/components/com_tjucm');
				$itemFormController = new TjucmControllerItemForm;
				$itemFormController->redirectToListView($typeId, $allowedCount);
			}
		}

		$view = explode('.', $this->client);

		// Call to extra fields
		$this->form_extra = $model->getFormExtra(
		array(
			"clientComponent" => 'com_tjucm',
			"client" => $this->client,
			"view" => $view[1],
			"layout" => 'edit',
			"content_id" => $this->id, )
			);

		// Check if draft save is enabled for the form
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');
		$tjUcmTypeTable = JTable::getInstance('Type', 'TjucmTable');
		$tjUcmTypeTable->load(array('unique_identifier' => $this->client));
		$typeParams = json_decode($tjUcmTypeTable->params);

		$this->allow_auto_save = (isset($typeParams->allow_auto_save) && empty($typeParams->allow_auto_save)) ? 0 : 1;
		$this->allow_draft_save = (isset($typeParams->allow_draft_save) && !empty($typeParams->allow_draft_save)) ? 1 : 0;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(Text::_("COM_TJUCM_SOMETHING_WENT_WRONG"), 'error');

			return false;
		}

		// Ucm triggger before item form display
		JPluginHelper::importPlugin('tjucm');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('tjucmOnBeforeItemFormDisplay', array(&$this->item, &$this->form_extra));

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
		$this->title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_TJUCM_DEFAULT_PAGE_TITLE'));
		}

		$this->title = $this->params->get('page_title', '');

		if (empty($this->title))
		{
			$this->title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$this->title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $this->title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$this->title = Text::sprintf('JPAGETITLE', $this->title, $app->get('sitename'));
		}

		$this->document->setTitle($this->title);

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
