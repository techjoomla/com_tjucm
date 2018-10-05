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

/**
 * Item controller class.
 *
 * @since  1.6
 */
class TjucmControllerItem extends JControllerLegacy
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$app = JFactory::getApplication();

		$this->client  = JFactory::getApplication()->input->get('client');
		$this->created_by  = JFactory::getApplication()->input->get('created_by');

		$this->appendUrl = "";

		if (!empty($this->created_by))
		{
			$this->appendUrl .= "&created_by=" . $this->created_by;
		}

		if (!empty($this->client))
		{
			$this->appendUrl .= "&client=" . $this->client;
		}

		// Get UCM type id from uniquue identifier
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');
		$this->ucmTypeId = $tjUcmModelType->getTypeId($this->client);

		parent::__construct();
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_tjucm.edit.item.id');
		$editId = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_tjucm.edit.item.id', $editId);

		// Get the model.
		$model = $this->getModel('Item', 'TjucmModel');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=itemform&layout=edit&id=' . $editId . '&client=' . $this->client, false));
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 *
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Checking if the user can remove object
		$user = JFactory::getUser();
		$canEdit    = $user->authorise('core.type.edititem', 'com_tjucm.type.edititem' . $this->ucmTypeId);
		$canChange  = $user->authorise('core.type.edititemstate', 'com_tjucm.type.' . $this->ucmTypeId);

		if ($canEdit || $canChange)
		{
			$model = $this->getModel('Item', 'TjucmModel');

			// Get the user data.
			$id    = $app->input->getInt('id');
			$state = $app->input->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(JText::sprintf('COM_TJUCM_SAVE_FAILED', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_tjucm.edit.item.id', null);

			// Flush the data from the session.
			$app->setUserState('com_tjucm.edit.item.data', null);

			// Redirect to the list screen.
			$this->setMessage(JText::_('COM_TJUCM_ITEM_SAVED_SUCCESSFULLY'));

			// If there isn't any menu item active, redirect to list view
			$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=items' . $this->appendUrl, false));
		}
		else
		{
			// If there isn't any menu item active, redirect to list view
			$this->setRedirect(
			JRoute::_('index.php?option=com_tjucm&view=items' . $this->appendUrl, false), JText::_('COM_TJUCM_ITEM_SAVED_STATE_ERROR'), 'error');
		}
	}

	/**
	 * Remove data
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function remove()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Checking if the user can remove object
		$user = JFactory::getUser();
		$canDelete  = $user->authorise('core.type.deleteitem', 'com_tjucm.type.' . $this->ucmTypeId);

		if ($canDelete)
		{
			$model = $this->getModel('Item', 'TjucmModel');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(JText::sprintf("COM_TJUCM_DELETE_FAILED", $model->getError()), 'warning');
			}
			else
			{
				// Check in the profile.
				if ($return)
				{
					$model->checkin($return);
				}

				// Clear the profile id from the session.
				$app->setUserState('com_tjucm.edit.item.id', null);

				// Flush the data from the session.
				$app->setUserState('com_tjucm.edit.item.data', null);

				$this->setMessage(JText::_('COM_TJUCM_ITEM_DELETED_SUCCESSFULLY'));
			}

			// If there isn't any menu item active, redirect to list view
			$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=items' . $this->appendUrl, false));
		}
		else
		{
			// If there isn't any menu item active, redirect to list view
			$this->setRedirect(
			JRoute::_('index.php?option=com_tjucm&view=items' . $this->appendUrl, false), JText::_('COM_TJUCM_ITEM_SAVED_STATE_ERROR'), 'error');
		}
	}
}
