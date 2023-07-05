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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

jimport('joomla.filesystem.file');

/**
 * Item controller class.
 *
 * @since  1.6
 */
class TjucmControllerItemForm extends FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$app = Factory::getApplication();
		$this->client  = $app->input->get('client');
		$this->created_by  = $app->input->get('created_by');

		// If client is empty then get client from jform data
		if (empty($this->client))
		{
			$data = $app->input->get('jform', array(), 'array');
			$this->client  = $data['client'];
		}

		// If client is empty then get client from menu params
		if (empty($this->client))
		{
			// Get the active item
			$menuitem   = $app->getMenu()->getActive();

			// Get the params
			$this->menuparams = $menuitem->getparams();

			if (!empty($this->menuparams))
			{
				$this->ucm_type   = $this->menuparams->get('ucm_type');

				if (!empty($this->ucm_type))
				{
					JLoader::import('components.com_tjfields.tables.type', JPATH_ADMINISTRATOR);
					$ucmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
					$ucmTypeTable->load(array('alias' => $this->ucm_type));
					$this->client = $ucmTypeTable->unique_identifier;
				}
			}
		}

		// Get UCM type id from unique identifier
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = BaseDatabaseModel::getInstance('Type', 'TjucmModel');
		$this->ucmTypeId = $tjUcmModelType->getTypeId($this->client);

		$this->appendUrl = "";

		if (!empty($this->created_by))
		{
			$this->appendUrl .= "&created_by=" . $this->created_by;
		}

		if (!empty($this->client))
		{
			$this->appendUrl .= "&client=" . $this->client;
		}

		$this->isajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? true : false;

		parent::__construct();
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function edit($key = null, $urlVar = null)
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_tjucm.edit.item.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_tjucm.edit.item.id', $editId);
		$app->setUserState('com_tjucm.edit.itemform.data.copy_id', 0);

		// Get the model.
		$model = $this->getModel('ItemForm', 'TjucmModel');

		$recordId = '';

		// Check out the item
		if ($editId)
		{
			$recordId = '&id=' . $editId;
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$tjUcmFrontendHelper = new TjucmHelpersTjucm;
		$link = 'index.php?option=com_tjucm&view=itemform&client=' . $this->client . $recordId;
		$itemId = $tjUcmFrontendHelper->getItemId($link);
		$this->setRedirect(Route::_('index.php?option=com_tjucm&view=itemform' . $recordId . '&Itemid=' . $itemId, false));
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   12.2
	 */
	public function cancel($key = null)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$table = $model->getTable();
		$context = "com_tjucm.edit.itemform.data";
		$tjUcmFrontendHelper = new TjucmHelpersTjucm;

		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		$recordId = $this->input->getInt($key);

		// Attempt to check-in the current record.
		if ($recordId)
		{
			if (property_exists($table, 'checked_out'))
			{
				if ($model->checkin($recordId) === false)
				{
					// Check-in failed, go back to the record and display a notice.
					
					Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'error');
					$link = 'index.php?option=com_tjucm&view=itemform&client=' . $this->client;
					$itemId = $tjUcmFrontendHelper->getItemId($link);
					$this->setRedirect(Route::_($link . '&Itemid=' . $itemId . $this->getRedirectToItemAppend($recordId, $key), false));

					return false;
				}
			}
		}

		// Clean the session data and redirect.
		$this->releaseEditId($context, $recordId);
		Factory::getApplication()->setUserState($context . '.data', null);

		$link = 'index.php?option=com_tjucm&view=items' . $this->appendUrl;
		$itemId = $tjUcmFrontendHelper->getItemId($link);
		$this->setRedirect(Route::_($link . '&Itemid=' . $itemId . $this->getRedirectToListAppend(), false));

		return true;
	}

	/**
	 * Method to remove data
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.6
	 */
	public function remove()
	{
		// Check for request forgeries.
		(Session::checkToken('get') or Session::checkToken()) or jexit(Text::_('JINVALID_TOKEN'));

		$app   = Factory::getApplication();
		$model = $this->getModel('ItemForm', 'TjucmModel');
		$pk    = $app->input->getInt('id');

		// Get content_id to be deleted.
		$contentId = $app->input->getInt('id');
		$tjUcmFrontendHelper = new TjucmHelpersTjucm;

		// Attempt to save the data
		try
		{
			$model->setState('ucmType.id', $this->ucmTypeId);
			$return = $model->delete($contentId);

			// Check in the profile
			$model->checkin($return);

			// Clear the profile id from the session.
			$app->setUserState('com_tjucm.edit.item.id', null);

			$menu = $app->getMenu();
			$item = $menu->getActive();
			$url = (empty($item->link) ? 'index.php?option=com_tjucm&view=items' : $item->link);

			// Redirect to the list screen
			$this->setMessage(Text::_('COM_TJUCM_ITEM_DELETED_SUCCESSFULLY'));

			$link = $url . $this->appendUrl;
			$itemId = $tjUcmFrontendHelper->getItemId($link);
			$this->setRedirect(Route::_($link . '&Itemid=' . $itemId, false));

			// Flush the data from the session.
			$app->setUserState('com_tjucm.edit.item.data', null);
		}
		catch (Exception $e)
		{
			$errorType = ($e->getCode() == '404' || '403') ? 'error' : 'warning';
			$this->setMessage($e->getMessage(), $errorType);

			$link = 'index.php?option=com_tjucm&view=items' . $this->appendUrl;
			$itemId = $tjUcmFrontendHelper->getItemId($link);
			$this->setRedirect(Route::_($link . '&Itemid=' . $itemId, false));
		}
	}

	/**
	 * Redirect user to items list view if user is not allowed to add mote items
	 *
	 * @param   INT  $typeId        Type id
	 * @param   INT  $allowedCount  Allowed Count
	 *
	 * @return boolean
	 */
	public function redirectToListView($typeId, $allowedCount)
	{
		$user = Factory::getUser();

		$tjUcmFrontendHelper = new TjucmHelpersTjucm;
		$link = "index.php?option=com_tjucm&view=items&created_by=" . $user->id . $this->appendUrl;
		$itemId = $tjUcmFrontendHelper->getItemId($link);

		$link = Route::_($link . '&Itemid=' . $itemId, false);

		Factory::getApplication()->redirect($link, sprintf(Text::_('COM_TJUCM_ALLOWED_COUNT_LIMIT'), $allowedCount), "Warning");
	}

	/**
	 * Method to check out an item for copying and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since   1.2.1
	 */
	public function prepareForCopy()
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$editId    = $app->input->getInt('id', 0);
		$clusterId = $app->input->getInt('cluster_id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_tjucm.edit.item.id', $editId);
		$app->setUserState('com_tjucm.edit.itemform.data.copy_id', $editId);

		$cluster = '';

		// Check cluster exist
		if ($clusterId)
		{
			$cluster = '&cluster_id=' . $clusterId;
		}

		$tjUcmFrontendHelper = new TjucmHelpersTjucm;
		$link = 'index.php?option=com_tjucm&view=itemform&client=' . $this->client;
		$itemId = $tjUcmFrontendHelper->getItemId($link);

		// Redirect to the edit screen.
		$this->setRedirect(Route::_($link . '&Itemid=' . $itemId . $cluster . $this->getRedirectToItemAppend(), false));
	}
}
