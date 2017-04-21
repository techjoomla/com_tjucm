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

require_once JPATH_SITE . "/components/com_tjfields/filterFields.php";

/**
 * Item controller class.
 *
 * @since  1.6
 */
class TjucmControllerItemForm extends JControllerForm
{
	// Use imported Trait in model
	use TjfieldsFilterField;

	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'items';

		$this->client  = JFactory::getApplication()->input->get('client');

		if (empty($this->client))
		{
			$this->client  = JFactory::getApplication()->input->get('jform', array(), 'array')['client'];
		}

		$this->isajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? true : false;

		parent::__construct();
	}

	/**
	 * Method to add a new record.
	 *
	 * @return  boolean  True if the record can be added, false if not.
	 *
	 * @since   12.2
	 */
	public function add()
	{
		$context = "$this->option.edit.$this->context";

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list . '&client=' . $this->client
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		// Clear the record edit information from the session.
		JFactory::getApplication()->setUserState($context . '.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item . '&client=' . $this->client
				. $this->getRedirectToItemAppend(), false
			)
		);

		return true;
	}

	/**
	 * Function to apply field data changes
	 *
	 * @return  void
	 */
	public function apply()
	{
		$this->save();
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
		$app = JFactory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_tjucm.edit.item.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_tjucm.edit.item.id', $editId);

		// Get the model.
		$model = $this->getModel('ItemForm', 'TjucmModel');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId)
		{
			$model->checkin($previousId);
		}

		// Get the active item
		$menuitem   = $app->getMenu()->getActive();

		// Get the params
		$this->menuparams = $menuitem->params;
		$this->ucm_type   = $this->menuparams->get('ucm_type');
		$this->client     = 'com_tjucm.' . $this->ucm_type;

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=itemform&client=' . $this->client . '&id=' . $editId, false));
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$model = $this->getModel();
		$task = $this->getTask();

		// Set client value
		$model->setClient($this->client);

		$table = $model->getTable();

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');
		$all_jform_data = $data;

		// Added By KOMAL TEMP
		$files           = $app->input->files->get('jform');

		// END

		// Jform tweak - Get all posted data.
		$post = JFactory::getApplication()->input->post;

		// Populate the row id from the session.

		// $data[$key] = $recordId;

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			if ($checkin && $model->checkin($data[$key]) === false)
			{
				// Check-in failed. Go back to the item and display a notice.
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item . '&client=' . $this->client
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}

			// Reset the ID, the multilingual associations and then treat the request as for Apply.
			$data[$key] = 0;
			$data['associations'] = array();
			$task = 'apply';
		}

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
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
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item . '&client=' . $this->client
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		// Jform tweaking - get data for extra fields jform.
		$extra_jform_data = array_diff_key($all_jform_data, $validData);

		// Check if form file is present.
		jimport('joomla.filesystem.file');
		/* Explode client 1. Componet name 2.type */
		$client = explode(".", $this->client);
		/* End */

		$filePath = JPATH_ADMINISTRATOR . '/components/com_tjucm/models/forms/' . $client[1] . '_extra.xml';

		if (JFile::exists($filePath))
		{
			// Validate the posted data.
			$formExtra = $model->getFormExtra(
						array(
							"category" => isset($data['category_id']) ? $data['category_id'] : '',
							"clientComponent" => 'com_tjucm',
							"client" => $this->client,
							"view" => $client[1],
							"layout" => 'edit')
							);

			if (!$formExtra)
			{
				JError::raiseWarning(500, $model->getError());

				return false;
			}

			$formExtra = array_filter($formExtra);

			if (!empty($formExtra))
			{
				if (!empty($formExtra[0]))
				{
					// Validate the posted extra data.
					// $extra_jform_data = $model->validateExtra($formExtra[0], $extra_jform_data);
				}
				else
				{
					// Validate the posted extra data.
					// $extra_jform_data = $model->validateExtra($formExtra[1], $extra_jform_data);
				}
			}

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

		if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}

		try
		{
			$status_title = JFactory::getApplication()->input->get('form_status');
			$validData['status'] = $status_title;

			// Added By KOMAL TEMP
			if (!empty($files))
			{
				$extra_jform_data['tjFieldFileField'] = $files;
			}

			$recordId = $model->save($validData, $extra_jform_data, $post);
			$dispatcher        = JDispatcher::getInstance();
			JPluginHelper::importPlugin("system", "jlike_tjucm");
			$dispatcher->trigger('jlike_tjucmOnAfterSave', array($recordId,$validData));
			$response = $recordId;
			$redirect_url = '';
			$redirect_msg = '';
		}

		catch (Exception $e)
		{
			$response = $e;
			$redirect_url = '';
			$redirect_msg = $e->getMsg();
		}

		if ($this->isajax)
		{
			echo new JResponseJson($response);
			jexit();
		}
		else
		{
			$app->redirect($redirect_url, $redirect_msg);
		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);

		return true;
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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$table = $model->getTable();
		$context = "$this->option.edit.$this->context";

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
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
					$this->setMessage($this->getError(), 'error');

					$this->setRedirect(
						JRoute::_(
							'index.php?option=' . $this->option . '&view=' . $this->view_item . '&client=' . $this->client
							. $this->getRedirectToItemAppend($recordId, $key), false
						)
					);

					return false;
				}
			}
		}

		// Clean the session data and redirect.
		$this->releaseEditId($context, $recordId);
		JFactory::getApplication()->setUserState($context . '.data', null);

		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list . '&client=' . $this->client
				. $this->getRedirectToListAppend(), false
			)
		);

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
		$app   = JFactory::getApplication();
		$model = $this->getModel('ItemForm', 'TjucmModel');
		$pk    = $app->input->getInt('id');

		// Get the user data.
		$data       = array();
		$data['id'] = $app->input->getInt('id');
		$data['client'] = $this->client;

		// Attempt to save the data
		try
		{
			$return = $model->delete($data);

			// Check in the profile
			$model->checkin($return);

			// Clear the profile id from the session.
			$app->setUserState('com_tjucm.edit.item.id', null);

			$menu = $app->getMenu();
			$item = $menu->getActive();
			$url = (empty($item->link) ? 'index.php?option=com_tjucm&view=items' : $item->link);

			// Redirect to the list screen
			$this->setMessage(JText::_('COM_TJUCM_ITEM_DELETED_SUCCESSFULLY'));
			$this->setRedirect(JRoute::_($url, false));

			// Flush the data from the session.
			$app->setUserState('com_tjucm.edit.item.data', null);
		}
		catch (Exception $e)
		{
			$errorType = ($e->getCode() == '404') ? 'error' : 'warning';
			$this->setMessage($e->getMessage(), $errorType);
			$this->setRedirect('index.php?option=com_tjucm&view=items');
		}
	}

	/**
	 * Method to delete uploaded document.
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   1.6
	 */
	public function delete_doc()
	{
		if (JFactory::getUser()->id)
		{
			$objx     = new stdClass;
			$jinput   = JFactory::getApplication()->input;
			$field_name  = $jinput->post->get('field_id', '', 'string');
			$nameOfField = explode('jform_', $field_name);
			$fileName = $jinput->post->get('fileName', '', 'string');

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$conditions = array($db->quoteName('id') . ' IN (' . $fieldValueEntryId . ') ');

			$query->select("*")
			->from("#__tjfields_fields")
			->where("name = '" . $nameOfField[1] . "'");
			$db->setQuery($query);

			$field_rs = $db->loadobject();

			if ($field_rs->id)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Delete all custom keys for user 1001.
				$conditions = array(
					$db->quoteName('field_id') . ' = ' . $db->quote($field_rs->id),
					$db->quoteName('user_id') . ' = ' . $db->quote(JFactory::getUser()->id)
				);

				$query->delete($db->quoteName('#__tjfields_fields_value'));
				$query->where($conditions);

				$db->setQuery($query);

				if ($db->execute())
				{
					if ($fileName)
					{
						$full_client = explode('.', $field_rs->client);
						$client = $full_client[0];
						$client_type = $full_client[1];

						// Get correct file path
						$return = 0;
						$filePath = 'media/' . $client . '/' . $client_type . DIRECTORY_SEPARATOR . $fileName;

						// Remove file if it exists
						if (file_exists($filePath) )
						{
							unlink($filePath);

							$return = 1;

							// H header('Location:index.php');
						}
						else
						{
							$return = 0;
						}
					}

					$objx->success = 1;
					$objx->data    = '';

					// JText::_("COM_PIP_FISN_REPORT_DOCUMENT_DELETE_SUCCESSFULLY");
					$objx->message = '';
					$objx->error   = 0;
				}
				else
				{
					$objx->success = 0;
					$objx->data    = '';

					// JText::_("COM_PIP_FISN_REPORT_DOCUMENT_NOT_DELETE");
					$objx->message = '';
					$objx->error   = 1;
				}
			}
			else
			{
				$objx->success = 0;
				$objx->data    = "";

				// JText::_("COM_PIP_FISN_REPORT_NOT_DELETE");
				$objx->message = '';
				$objx->error   = 1;
			}
		}
		else
		{
			$objx->success = 0;
			$objx->data    = '';
			$objx->message = JText::_("Time Out");
			$objx->error   = 1;
		}

		print_r(json_encode($objx));

		jexit();
	}
}
