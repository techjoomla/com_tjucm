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
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Language\Text;
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
		$this->client  = $app->input->get('client', '', 'STRING');

		// If client is empty then get client from post data
		if (empty($this->client))
		{
			$this->client = $app->input->post->get('client', '', 'STRING');
		}

		// Get UCM type id for the client
		if (!empty($this->client))
		{
			JLoader::import('components.tables.type', JPATH_ADMINISTRATOR);
			$tjUcmTypeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
			$tjUcmTypeTable->load(array('unique_identifier' => $this->client));

			if (!empty($tjUcmTypeTable->id))
			{
				$this->typeId = $tjUcmTypeTable->unique_identifier;
			}
		}

		parent::__construct();
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
	 * Function to save ucm data item
	 *
	 * @param   int  $key     admin approval 1 or 0
	 * @param   int  $urlVar  id of user who has enrolle the user
	 *
	 * @return  boolean  true or false
	 *
	 * @since 1.0.0
	 */
	public function save($key = null, $urlVar = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post = Factory::getApplication()->input->post;
		$model = $this->getModel('itemform');

		$data = array();
		$data['id'] = $post->get('id', 0, 'INT');

		if (empty($data['id']))
		{
			$client = $post->get('client', '', 'STRING');

			// For new record if there is no client specified or invalid client is given then do not process the request
			if ($client == '' || empty($this->typeId))
			{
				echo new JResponseJson('', JText::_('COM_TJUCM_FORM_SAVE_FAILED_CLIENT_REQUIRED'), true);
			}

			$data['client'] = $client;
		}

		$data['state'] = $post->get('state', 0, 'INT');
		$data['draft'] = $post->get('draft', 0, 'INT');
		$data['parent_id'] = $post->get('parent_id', 0, 'INT');

		try
		{
			$form = $model->getForm();
			$data = $model->validate($form, $data);

			if ($data == false)
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);

				echo new JResponseJson('', JText::_('COM_TJUCM_FORM_VALIDATATION_FAILED'), true);
			}

			if ($model->save($data))
			{
				$result['id'] = $model->getState($model->getName() . '.id');


				echo new JResponseJson($result, JText::_('COM_TJUCM_ITEM_SAVED_SUCCESSFULLY'));
			}
			else
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);
				echo new JResponseJson('', JText::_('COM_TJUCM_FORM_SAVE_FAILED'), true);
			}
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	public function saveFieldData()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app       = Factory::getApplication();
		$post      = $app->input->post;
		$recordId  = $post->get('recordid', 0, 'INT');
		$client    = $post->get('client', '', 'STRING');
		$fieldData = $post->get('jform', array(), 'ARRAY');

		$model     = $this->getModel('itemform');

		if (empty($fieldData))
		{
			$fieldData = $app->input->files->get('jform');
		}

		if (empty($fieldData))
		{
			$app->enqueueMessage(JText::_('COM_TJUCM_FORM_VALIDATATION_FAILED'), 'error');
			echo new JResponseJson(null);
		}

		try
		{
			// Create JForm object for the field
			$form = $model->getFieldForm($fieldData);

			// Validate field data
			$data = $model->validate($form, $fieldData);

			if ($data == false)
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);

				echo new JResponseJson(null);
			}

			$table = $model->getTable();
			$table->load($recordId);

			$fieldData = array();
			$fieldData['content_id'] = $recordId;
			$fieldData['fieldsvalue'] = $data;
			$fieldData['client'] = $client;
			$fieldData['created_by'] = $table->created_by;

			// If data is valid then save the data into DB
			$response = $model->saveExtraFields($fieldData);

			echo new JResponseJson($response);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	public function saveFormData()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app       = Factory::getApplication();
		$post      = $app->input->post;
		$recordId  = $post->get('recordid', 0, 'INT');
		$client    = $post->get('client', '', 'STRING');
		$formData  = $post->get('jform', array(), 'ARRAY');
		$filesData = $app->input->files->get('jform', array(), 'ARRAY');
		$formData  = array_merge_recursive($formData, $filesData);
		$section   = $post->get('tjUcmFormSection', '', 'STRING');

		if (empty($formData) || empty($client))
		{
			$app->enqueueMessage(JText::_('COM_TJUCM_FORM_VALIDATATION_FAILED'), 'error');
			echo new JResponseJson(null);
		}

		try
		{
			// Create JForm object for the field
			$model = $this->getModel('itemform');
			$formData['client'] = $client;
			$form  = $model->getTypeForm($formData);

			if (!empty($section))
			{
				$formData['section'] = $section;
				$form  = $model->getSectionForm($formData);
			}
			else
			{
				$form  = $model->getTypeForm($formData);
			}

			// Validate field data
			$data = $model->validate($form, $formData);

			if ($data == false)
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);

				echo new JResponseJson(null);
			}

			$table = $model->getTable();
			$table->load($recordId);

			$formData = array();
			$formData['content_id'] = $recordId;
			$formData['fieldsvalue'] = $data;
			$formData['client'] = $client;
			$formData['created_by'] = $table->created_by;

			// If data is valid then save the data into DB
			$response = $model->saveExtraFields($formData);

			echo new JResponseJson($response);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	/**
	 * Function to save ucm item field data
	 *
	 * @param   int  $key     admin approval 1 or 0
	 * @param   int  $urlVar  id of user who has enrolle the user
	 *
	 * @return  boolean  true or false
	 *
	 * @since 1.0.0
	 */
	public function saveItemFieldData($key = null, $urlVar = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post = Factory::getApplication()->input->post;
		$model = $this->getModel('itemform');

		$data = array();
		$data['id'] = $post->get('id', 0, 'INT');

		if (empty($data['id']))
		{
			$client = $post->get('client', '', 'STRING');

			// For new record if there is no client specified then do not process the request
			if ($client == '')
			{
				echo new JResponseJson('', JText::_('COM_TJUCM_FORM_SAVE_FAILED'), true);
			}

			$data['created_by'] = Factory::getUser()->id;
			$data['created_date'] = Factory::getDate()->toSql();
			$data['client'] = $client;
		}
		else
		{
			$data['modified_by'] = Factory::getUser()->id;
			$data['modified_date'] = Factory::getDate()->toSql();
		}

		$data['state'] = $post->get('state', 0, 'INT');
		$data['draft'] = $post->get('draft', 0, 'INT');

		try
		{
			$form = $model->getForm();
			$data = $model->validate($form, $data);

			if ($data == false)
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);

				echo new JResponseJson('', JText::_('COM_TJUCM_FORM_VALIDATATION_FAILED'), true);
			}

			if ($model->save($data))
			{
				$result['id'] = $model->getState($model->getName() . '.id');

				echo new JResponseJson($result, JText::_('COM_TJUCM_ITEM_SAVED_SUCCESSFULLY'));
			}
			else
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);
				echo new JResponseJson('', JText::_('COM_TJUCM_FORM_SAVE_FAILED'), true);
			}
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	/**
	 * Method to procees errors
	 *
	 * @param   ARRAY  $errors  ERRORS
	 *
	 * @return  void
	 *
	 * @since 1.0
	 */
	private function processErrors($errors)
	{
		$app = Factory::getApplication();

		if (!empty($errors))
		{
			$code = 500;
			$msg  = array();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$code  = $errors[$i]->getCode();
					$msg[] = $errors[$i]->getMessage();
				}
				else
				{
					$msg[] = $errors[$i];
				}
			}

			$app->enqueueMessage(implode("\n", $msg), 'error');
		}
	}

	public function getRelatedFieldOptions()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$post = $app->input->post;
		$model = $this->getModel('itemform');

		$client = $post->get('client', '', 'STRING');
		$contentId = $post->get('content_id', 0, 'INT');

		if (empty($client) || empty($contentId))
		{
			echo new JResponseJson(null);
		}

		$app->input->set('id', $contentId);
		$updatedOptionsForRelatedField = $model->getUdatedRelatedFieldOptions($client, $contentId);

		echo new JResponseJson($updatedOptionsForRelatedField);
	}
}
