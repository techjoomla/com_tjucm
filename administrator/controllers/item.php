<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjucm
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Item controller class.
 *
 * @since  1.6
 */
class TjucmControllerItem extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'items';

		$app = JFactory::getApplication();
		$this->client = $app->input->getSTRING('client');

		parent::__construct();
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  1.6
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('Item', 'TjucmModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');
		$all_jform_data = $data;

		// Jform tweak - Get all posted data.
		$post = JFactory::getApplication()->input->post;

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$datax = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_tjucm.edit.item.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_tjucm.edit.item.id');
			$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=itemform&layout=edit&client=' . $this->client . '&id=' . $id, false));
		}

		// Jform tweaking - get data for extra fields jform.
		$extra_jform_data = array_diff_key($all_jform_data, $datax);

		// Check if form file is present.
		jimport('joomla.filesystem.file');
		$db     = JFactory::getDbo();
		$query  = "SELECT DISTINCT id as category_id FROM #__categories where extension='" . $this->client . "'";
		$db->setQuery($query);
		$courseInfo = $db->loadObject();

		/* Explode client 1. Componet name 2.type */
		$client = explode(".", $this->client);
		/* End */

		$filePath = JPATH_ADMINISTRATOR . '/components/com_tjucm/models/forms/' . $courseInfo->category_id . $client[1] . '_extra.xml';

		if (JFile::exists($filePath))
		{
			// Validate the posted data.
			$formExtra = $model->getFormExtra();

			if (!$formExtra)
			{
				JError::raiseWarning(500, $model->getError());

				return false;
			}

			// Validate the posted extra data.
			$extra_jform_data = $model->validateExtra($formExtra, $extra_jform_data);

			// Check for errors.
			if ($extra_jform_data === false)
			{
				// Get the validation messages.
				$errors = $model->getErrors();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				// Save the data in the session.
				// Tweak.
				$app->setUserState('com_tjucm.edit.item.data', $all_jform_data);

				// Tweak *important
				$app->setUserState('com_tjucm.edit.item.data', $all_jform_data['id']);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_tjucm.edit.item.id');
				$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=itemform&layout=edit&client=' . $this->client . '&id=' . $id, false));

				return false;
			}
		}

		// Attempt to save the data.
		// $return = $model->save($data);

		$return = $model->save($data, $extra_jform_data, $post);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_tjucm.edit.item.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_tjucm.edit.item.id');
			$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=itemform&layout=edit&client=' . $this->client . '&id=' . $id, false));
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_tjucm.edit.item.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_TJUCM_ITEM_SAVED_SUCCESSFULLY'));
		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_tjucm&view=items&client=' . $this->client  : $item->link);
		$this->setRedirect(JRoute::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_tjucm.edit.item.data', null);
	}
}
