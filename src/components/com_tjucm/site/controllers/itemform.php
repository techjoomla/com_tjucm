<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

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
			$this->menuparams = $menuitem->params;

			if (!empty($this->menuparams))
			{
				$this->ucm_type   = $this->menuparams->get('ucm_type');

				if (!empty($this->ucm_type))
				{
					$this->client     = 'com_tjucm.' . $this->ucm_type;
				}
			}
		}

		// Get UCM type id from unique identifier
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');
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
	 * Method to add a new record.
	 *
	 * @return  boolean  True if the record can be added, false if not.
	 *
	 * @since   12.2
	 */
	public function add()
	{
		$app = Factory::getApplication();
		$context = "$this->option.edit.$this->context";

		$tjUcmFrontendHelper = new TjucmHelpersTjucm;

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$link = 'index.php?option=com_tjucm&view=items' . $this->appendUrl;
			$itemId = $tjUcmFrontendHelper->getItemId($link);

			$this->setRedirect(Route::_($link . '&Itemid=' . $itemId . $this->getRedirectToListAppend(), false));

			return false;
		}

		// Clear the record edit information from the session.
		$app->setUserState($context . '.data', null);

		$clusterId     = $app->input->getInt('cluster_id', 0);

		// Check cluster exist
		if ($clusterId)
		{
			$this->appendUrl .= '&cluster_id=' . $clusterId;
		}

		// Redirect to the edit screen.
		$link = 'index.php?option=com_tjucm&view=itemform&client=' . $this->appendUrl;
		$itemId = $tjUcmFrontendHelper->getItemId($link);

		$this->setRedirect(Route::_($link . '&Itemid=' . $itemId . $this->getRedirectToItemAppend(), false));

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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app   = Factory::getApplication();
		$lang  = Factory::getLanguage();
		$model = $this->getModel();
		$task  = $this->getTask();
		$formStatus = $app->input->get('form_status', '', 'STRING');

		// Set client value
		$model->setClient($this->client);

		$table = $model->getTable();

		// Get the user data.
		$data = $app->input->get('jform', array(), 'array');
		$data['id'] = empty($data['id']) ? 0 : (int) $data['id'];
		$all_jform_data = $data;

		// Get file information
		$files = $app->input->files->get('jform');

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
				$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				echo new JResponseJson(null);
				jexit();
			}

			// Reset the ID, the multilingual associations and then treat the request as for Apply.
			$data[$key] = 0;
			$data['associations'] = array();
			$task = 'apply';
		}

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			echo new JResponseJson(null);
			jexit();
		}

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			echo new JResponseJson(null);
			jexit();
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
					$app->enqueueMessage($errors[$i]->getMessage(), 'error');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'error');
				}
			}

			echo new JResponseJson(null);
			jexit();
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
							$formExtra->setFieldAttribute($field->fieldname, 'validate', '');
						}
					}
				}
				else
				{
					$validData['draft'] = 0;
				}

				// Remove the fields having empty value from both the array before merge
				if (is_array($data))
				{
					$data = array_filter($data);
				}

				if (is_array($files))
				{
					$files = array_filter($files);
				}

				/* If file field is required then in the validation method return false
				 * * so that we will mearge $data and $ files array using array_merge function
				 * * and pass to the validation funcation.*/

				if (!empty($files))
				{
					$extra_jform_data = array_merge_recursive($data, $files);
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
						$app->enqueueMessage($errors[$i]->getMessage(), 'error');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'error');
					}
				}

				echo new JResponseJson(null);
				jexit();
			}
		}

		if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}

		$response = '';

		try
		{
			$status_title = Factory::getApplication()->input->get('form_status');
			$validData['status'] = $status_title;

			if (!empty($files))
			{
				$extra_jform_data['tjFieldFileField'] = $files;
			}

			// If no data send then dont add any entry in item form table - start
			$allow = 0;

			foreach ($extra_jform_data as $extra_data)
			{
				if ($extra_data != '')
				{
					$allow = 1;

					break;
				}
			}

			if (empty($allow))
			{
				$app->enqueueMessage(Text::_("COM_TJUCM_NO_FORM_DATA"), 'error');

				echo new JResponseJson(null);
				jexit();
			}

			// Set cluster values to store in core UCM table values
			$model->setClusterData($validData, $data);

			// Get sorted dataset of submitted ucmsubform records as per their client
			$ucmSubFormDataSet = $model->getFormattedUcmSubFormRecords($validData, $extra_jform_data);

			$isNew = empty($validData['id']) ? 1 : 0;

			// Save parent form record
			$recordId = $model->save($validData, $extra_jform_data);
			$validData['parent_id'] = $recordId;

			// Save ucmSubForm records
			if (!empty($ucmSubFormDataSet))
			{
				$subFormContentIds = $model->saveUcmSubFormRecords($validData, $ucmSubFormDataSet);
			}

			if ($recordId === false)
			{
				echo new JResponseJson(null);
				jexit();
			}

			if ($recordId)
			{
				$validData['id'] = $recordId;

				$dispatcher = JEventDispatcher::getInstance();
				JPluginHelper::importPlugin("system", "jlike_tjucm");
				$dispatcher->trigger('jlike_tjucmOnAfterSave', array($recordId, $validData));

				// TJ-ucm plugin trigger after save
				$dispatcher = JEventDispatcher::getInstance();
				JPluginHelper::importPlugin("content");
				$dispatcher->trigger('onUcmItemAfterSave', array($validData, $extra_jform_data, $isNew));

				$response = $recordId;
				$redirect_url = '';
				$redirect_msg = '';
			}
		}
		catch (Exception $e)
		{
			$response = $e;
			$redirect_url = '';
			$redirect_msg = $e->getMessage();
		}

		if ($this->isajax)
		{
			if (!empty($response))
			{
				$response = array('id' => $response);

				if (isset($subFormContentIds) && !empty($subFormContentIds))
				{
					$response['childContentIds'] = $subFormContentIds;
				}
			}

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
					$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
					$this->setMessage($this->getError(), 'error');

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
		$user = Factory::getUser();

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
		$user = Factory::getUser();
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
	 * Method to check out an item for copying and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function prepareForCopy()
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$editId    = $app->input->getInt('id', 0);
		$clusterId = $app->input->getInt('cluster_id', 0);
		$itemId    = $app->input->get('Itemid', '', 'INT');

		if (empty($itemId))
		{
			$menu = $app->getMenu();
			$menuItemObj = $menu->getItems('link', 'index.php?option=com_tjucm&view=itemform', true);
			$itemId      = $menuItemObj->id;
		}

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_tjucm.edit.item.id', $editId);

		$app->setUserState('com_tjucm.edit.itemform.data.copy_id', $editId);

		// Get the model.
		$model = $this->getModel('ItemForm', 'TjucmModel');

		$cluster = '';

		// Check cluster exist
		if ($clusterId)
		{
			$cluster = '&cluster_id=' . $clusterId;
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_tjucm&view=itemform&client=' . $this->client . $cluster . '&Itemid=' . $itemId, false));
	}
}
