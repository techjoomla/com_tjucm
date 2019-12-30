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
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;

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
		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app   = Factory::getApplication();
		$post  = $app->input->post;
		$model = $this->getModel('itemform');

		$data = array();
		$data['id'] = $post->get('id', 0, 'INT');

		if (empty($data['id']))
		{
			$client = $post->get('client', '', 'STRING');

			// For new record if there is no client specified or invalid client is given then do not process the request
			if ($client == '' || empty($this->typeId))
			{
				echo new JResponseJson('', Text::_('COM_TJUCM_FORM_SAVE_FAILED_CLIENT_REQUIRED'), true);
				$app->close();
			}

			// Set the state of record as per UCM type config
			$typeTable = $model->getTable('type');
			$typeTable->load(array('unique_identifier' => $client));
			$typeParams = new Registry($typeTable->params);
			$data['state'] = $typeParams->get('publish_items', 0);

			$data['client'] = $client;
		}

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

				echo new JResponseJson('', Text::_('COM_TJUCM_FORM_VALIDATATION_FAILED'), true);
				$app->close();
			}

			if ($model->save($data))
			{
				$result['id'] = $model->getState($model->getName() . '.id');

				echo new JResponseJson($result, Text::_('COM_TJUCM_ITEM_SAVED_SUCCESSFULLY'));
				$app->close();
			}
			else
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);
				echo new JResponseJson('', Text::_('COM_TJUCM_FORM_SAVE_FAILED'), true);
				$app->close();
			}
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
			$app->close();
		}
	}

	/**
	 * Method to save single field data.
	 *
	 * @return  void
	 *
	 * @since   1.2.1
	 */
	public function saveFieldData()
	{
		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
			$app->enqueueMessage(Text::_('COM_TJUCM_FORM_VALIDATATION_FAILED'), 'error');
			echo new JResponseJson(null);
			$app->close();
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
				$app->close();
			}

			$table = $model->getTable();
			$table->load($recordId);

			$fieldData = array();
			$fieldData['content_id'] = $recordId;
			$fieldData['fieldsvalue'] = $data;
			$fieldData['client'] = $client;
			$fieldData['created_by'] = $table->created_by;

			// If data is valid then save the data into DB
			$response = $model->saveFieldsData($fieldData);

			echo new JResponseJson($response);
			$app->close();
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
			$app->close();
		}
	}

	/**
	 * Method to save form data.
	 *
	 * @return  void
	 *
	 * @since   1.2.1
	 */
	public function saveFormData()
	{
		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app       = Factory::getApplication();
		$post      = $app->input->post;
		$recordId  = $post->get('recordid', 0, 'INT');
		$client    = $post->get('client', '', 'STRING');
		$formData  = $post->get('jform', array(), 'ARRAY');
		$filesData = $app->input->files->get('jform', array(), 'ARRAY');
		$formData  = array_merge_recursive($formData, $filesData);
		$section   = $post->get('tjUcmFormSection', '', 'STRING');
		$draft     = $post->get('draft', 0, 'INT');
		$state     = $formData['state'];

		if (empty($formData) || empty($client))
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_FORM_VALIDATATION_FAILED'), 'error');
			echo new JResponseJson(null);
			$app->close();
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
				$formData['draft'] = $draft;
				$form  = $model->getTypeForm($formData);
			}

			// Validate field data
			$data = $model->validate($form, $formData);

			if ($data == false)
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);

				echo new JResponseJson(null);
				$app->close();
			}

			$table = $model->getTable();
			$table->load($recordId);

			$formData = array();
			$formData['content_id'] = $recordId;
			$formData['fieldsvalue'] = $data;
			$formData['client'] = $client;
			$formData['created_by'] = $table->created_by;

			// If data is valid then save the data into DB
			$response = $model->saveFieldsData($formData);

			$msg = null;

			if ($response && empty($section))
			{
				if ($draft)
				{
					$msg = ($response) ? Text::_("COM_TJUCM_ITEM_DRAFT_SAVED_SUCCESSFULLY") : Text::_("COM_TJUCM_FORM_SAVE_FAILED");
				}
				else
				{
					$msg = ($response) ? Text::_("COM_TJUCM_ITEM_SAVED_SUCCESSFULLY") : Text::_("COM_TJUCM_FORM_SAVE_FAILED");
				}

				// Disable the draft mode of the item if full form is submitted
				$table->load($recordId);
				$table->draft = $draft;

				if ($state == null)
				{
					// For the first time saving get the record from table which already stored
					$state = $table->state;
				}

				$table->state = $state;
				$table->modified_date = Factory::getDate()->toSql();
				$table->store();

				// Perform actions (redirection or trigger call) after final submit
				if (!$draft)
				{
					// TJ-ucm plugin trigger after save
					$dispatcher = JEventDispatcher::getInstance();
					PluginHelper::importPlugin("content");
					$dispatcher->trigger('onUcmItemAfterSave', array($table->getProperties(), $data));
				}
			}

			echo new JResponseJson($response, $msg);
			$app->close();
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
			$app->close();
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
	 * @since 1.2.1
	 */
	public function saveItemFieldData($key = null, $urlVar = null)
	{
		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
				echo new JResponseJson('', Text::_('COM_TJUCM_FORM_SAVE_FAILED'), true);
				$app->close();
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

				echo new JResponseJson('', Text::_('COM_TJUCM_FORM_VALIDATATION_FAILED'), true);
				$app->close();
			}

			if ($model->save($data))
			{
				$result['id'] = $model->getState($model->getName() . '.id');

				echo new JResponseJson($result, Text::_('COM_TJUCM_ITEM_SAVED_SUCCESSFULLY'));
				$app->close();
			}
			else
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);
				echo new JResponseJson('', Text::_('COM_TJUCM_FORM_SAVE_FAILED'), true);
				$app->close();
			}
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
			$app->close();
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

	/**
	 * Method to get updated list of options for related field
	 *
	 * @return  void
	 *
	 * @since 1.2.1
	 */
	public function getRelatedFieldOptions()
	{
		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$post = $app->input->post;
		$model = $this->getModel('itemform');

		$client = $post->get('client', '', 'STRING');
		$contentId = $post->get('content_id', 0, 'INT');

		if (empty($client) || empty($contentId))
		{
			echo new JResponseJson(null);
			$app->close();
		}

		$app->input->set('id', $contentId);
		$updatedOptionsForRelatedField = $model->getUdatedRelatedFieldOptions($client, $contentId);

		echo new JResponseJson($updatedOptionsForRelatedField);
		$app->close();
	}
}
