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

jimport('joomla.filesystem.file');

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
		$app = JFactory::getApplication();
		$this->client  = $app->input->get('client');
		$this->created_by  = $app->input->get('created_by');

		if (empty($this->client))
		{
			$data = $app->input->get('jform', array(), 'array');
			$this->client  = $data['client'];
		}

		// Get UCM type id from uniquue identifier
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$this->tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');
		$this->ucmTypeId = $this->tjUcmModelType->getTypeId($this->client);

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
	 * Method to add a new record.
	 *
	 * @return  boolean  True if the record can be added, false if not.
	 *
	 * @since   12.2
	 */
	public function add()
	{
		$context = "com_tjucm.edit.itemform.data";

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=com_tjucm&view=items' . $this->appendUrl
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
				'index.php?option=com_tjucm&view=itemform&client=' . $this->client
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
		$app = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$model = $this->getModel();
		$task = $this->getTask();
		$formStatus = $app->input->get('form_status', '', 'STRING');

		// Set client value
		$model->setClient($this->client);

		$table = $model->getTable();

		// Get the user data.
		$data = $app->input->get('jform', array(), 'array');
		$all_jform_data = $data;

		// Added By KOMAL TEMP
		$files           = $app->input->files->get('jform');

		// END

		// Jform tweak - Get all posted data.
		$post = $app->input->post;

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
						'index.php?option=com_tjucm&view=itemform&client=' . $this->client
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
					'index.php?option=com_tjucm&view=items' . $this->appendUrl
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
					'index.php?option=com_tjucm&view=itemform&client=' . $this->client
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

			if (!empty($formExtra))
			{
				// Remove required attribute from fields if data is stored in draft mode
				if ($formStatus == 'draft')
				{
					$validData['draft'] = 1;
					$fieldSets = $formExtra->getFieldsets();

					foreach ($fieldSets as $fieldset)
					{
						foreach ($formExtra->getFieldset($fieldset->name) as $field)
						{
							$formExtra->setFieldAttribute($field->fieldname, 'required', false);
						}
					}
				}
				else
				{
					$validData['draft'] = 0;
				}

				// Validate the posted extra data.
				$extra_jform_data = $model->validateExtra($formExtra, $extra_jform_data);
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
		$context = "com_tjucm.edit.itemform.data";

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
							'index.php?option=com_tjucm&view=itemform&client=' . $this->client
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
				'index.php?option=com_tjucm&view=items' . $this->appendUrl
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
			$model->setState('ucmType.id', $this->ucmTypeId);
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
			$this->setRedirect(JRoute::_($url . $this->appendUrl, false));

			// Flush the data from the session.
			$app->setUserState('com_tjucm.edit.item.data', null);
		}
		catch (Exception $e)
		{
			$errorType = ($e->getCode() == '404' || '403') ? 'error' : 'warning';
			$this->setMessage($e->getMessage(), $errorType);
			$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=items' . $this->appendUrl, false));
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
		$user = JFactory::getUser();
		$createdBy = $user->id;
		$link = JRoute::_("index.php?option=com_tjucm&view=items&id=" . $typeId . $this->appendUrl, false);

		JFactory::getApplication()->redirect($link, sprintf(JText::_('COM_TJUCM_ALLOWED_COUNT_LIMIT'), $allowedCount), "Warning");
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowAdd($data = array())
	{
		$user = JFactory::getUser();

		return $user->authorise('core.type.createitem', 'com_tjucm.type.' . $this->ucmTypeId);
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$user = JFactory::getUser();
		$edit = $user->authorise('core.type.edititem', 'com_tjucm.type.' . $this->ucmTypeId);
		$editOwn = $user->authorise('core.type.editownitem', 'com_tjucm.type.' . $this->ucmTypeId);

		if ($edit || $editOwn)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$post = $app->input->post;
		$model = $this->getModel();

		$source_client = $post->get('source_client');
		$source_fields = $this->getFieldsData($source_client);

		$target_client = $post->get('target_client');
		$target_fields = $this->getFieldsData($target_client);

		$commonElement = array_diff_assoc($source_fields, $target_fields);

		// Attempt to save the data
		try
		{
			if (empty($commonElement))
			{
				$copyIds = $post->get('cid');
				$model->setClient($target_client);

				JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);
				$tjFieldsHelper = new TjfieldsHelper;

				foreach ($copyIds as $content_id)
				{
					// UCM table Data
					$ucmTable = $model->getTable();
					$ucmTable->load($content_id);

					$ucmData = array();
					$ucmData['id'] = 0;
					$ucmData['client'] = $target_client;
					$ucmData['ordering'] = $ucmTable->ordering;
					$ucmData['state'] = $ucmTable->state;
					$ucmData['created_by'] = $ucmTable->created_by;
					$ucmData['draft'] = $ucmTable->draft;

					// Tjfield values
					$data['content_id']  = $content_id;
					$data['user_id']     = JFactory::getUser()->id;
					$data['client']      = $source_client;

					$extra_fields_data = $tjFieldsHelper->FetchDatavalue($data);

					foreach ($extra_fields_data as $extraData)
					{
						$prefixSourceClient = str_replace(".", "_", $source_client);
						$field_name = explode($prefixSourceClient . "_", $extraData->name);

						$prefixTargetClient = str_replace(".", "_", $target_client);
						$targetFieldName = $prefixTargetClient . '_' . $field_name[1];

						$ucmExtraData[$targetFieldName] = new stdClass;

						if (!is_array($extraData->value))
						{
							$ucmExtraData[$targetFieldName] = $extraData->value;
						}
						else
						{
							$temp = array();

							switch ($extraData->type)
							{
								case 'multi_select':
									foreach ($extraData->value as $option)
									{
										$temp[] = $option->value;
									}

									if (!empty($temp))
									{
										$ucmExtraData[$targetFieldName] = $temp;
									}

								break;

								case 'single_select':
									foreach ($extraData->value as $option)
									{
										$ucmExtraData[$targetFieldName] = $option->value;
									}
								break;

								case 'radio':
								default:
									foreach ($extraData->value as $option)
									{
										$ucmExtraData[$targetFieldName] = $option->value;
									}
								break;
							}
						}
					}

					$recordId = $model->save($ucmData, $ucmExtraData);
					$dispatcher = JDispatcher::getInstance();
					JPluginHelper::importPlugin("system", "jlike_tjucm");
					$dispatcher->trigger('jlike_tjucmOnAfterSave', array($recordId,$ucmData));
				}

				$menu = $app->getMenu();
				$item = $menu->getActive();
				$url = (empty($item->link) ? 'index.php?option=com_tjucm&view=items' : $item->link);

				// Redirect to the list screen
				$this->setMessage(JText::_('COM_TJUCM_ITEM_COPY_SUCCESSFULLY'));
				$this->setRedirect(JRoute::_($url . $this->appendUrl, false));
			}
			else
			{
				$this->setMessage(JText::_('COM_TJUCM_ITEM_NOT_COPY_SUCCESSFULLY'), 'error');
				$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=items' . $this->appendUrl, false));
			}
		}
		catch (Exception $e)
		{
			$errorType = ($e->getCode() == '404' || '403') ? 'error' : 'warning';
			$this->setMessage($e->getMessage(), $errorType);
			$this->setRedirect(JRoute::_('index.php?option=com_tjucm&view=items' . $this->appendUrl, false));
		}
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $client  The model.
	 *
	 * @return  array  Field name and datatype array
	 *
	 * @since   1.6
	 */
	public function getFieldsData($client)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
		$fieldsModel = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$fieldsModel->setState('filter.state', 1);

		if (!empty($client))
		{
			$fieldsModel->setState('filter.client', $client);
		}

		$fields = $fieldsModel->getItems();

		$data = array();

		foreach ($fields as $field)
		{
			$prefix = str_replace(".", "_", $client);
			$field_name = explode($prefix . "_", $field->name);

			$data[$field_name[1]] = new stdClass;
			$data[$field_name[1]] = $field->type;
		}

		return $data;
	}
}
