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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Event\Dispatcher as EventDispatcher;

jimport('joomla.filesystem.file');

require_once JPATH_SITE . "/components/com_tjfields/filterFields.php";

/**
 * Item controller class.
 *
 * @since  1.6
 */
class TjucmControllerItemForm extends FormController
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
			$tjUcmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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

			$isNew = (empty($data['id'])) ? 1 : 0;

			// Plugin trigger on before item save
			PluginHelper::importPlugin('actionlog');
			$dispatcher = new EventDispatcher();
			$dispatcher->triggerEvent('tjUcmOnBeforeSaveItem', array($data, $isNew));

			if ($model->save($data))
			{
				$result['id'] = $model->getState($model->getName() . '.id');

				// Plugin trigger on after item save
				PluginHelper::importPlugin('actionlog');
				$dispatcher = new EventDispatcher();
				$dispatcher->triggerEvent('tjUcmOnafterSaveItem', array($data, $isNew));

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
			echo new JsonResponse($e);
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
			echo new JsonResponse(null);
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

				echo new JsonResponse(null);
				$app->close();
			}

			$table = $model->getTable();
			$table->load($recordId);

			$fieldData = array();
			$fieldData['content_id'] = $recordId;
			$fieldData['fieldsvalue'] = $data;
			$fieldData['client'] = $client;
			$fieldData['created_by'] = $table->created_by;

			// Plugin trigger on before item date save
			PluginHelper::importPlugin('actionlog');
			$dispatcher = new EventDispatcher();
			$dispatcher->triggerEvent('tjUcmOnBeforeSaveItemData', array($recordId, $client, $data));

			// If data is valid then save the data into DB
			$response = $model->saveFieldsData($fieldData);

			// Plugin trigger on after item data save
			PluginHelper::importPlugin('actionlog');
			$dispatcher = new EventDispatcher();
			$dispatcher->triggerEvent('tjUcmOnAfterSaveItemData', array($recordId, $client, $data));

			echo new JsonResponse($response);
			$app->close();
		}
		catch (Exception $e)
		{
			echo new JsonResponse($e);
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app          = Factory::getApplication();
		$post         = $app->input->post;
		$recordId     = $post->get('recordid', 0, 'INT');
		$client       = $post->get('client', '', 'STRING');
		$formData     = $post->get('jform', array(), 'ARRAY');
		$filesData    = $app->input->files->get('jform', array(), 'ARRAY');
		$formData     = array_merge_recursive($formData, $filesData);
		$section      = $post->get('tjUcmFormSection', '', 'STRING');
		$showDraftMsg = $post->get('showDraftMessage', 1, 'INT');
		$draft        = $post->get('draft', 0, 'INT');

		if (empty($formData) || empty($client))
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_FORM_VALIDATATION_FAILED'), 'error');
			echo new JsonResponse(null);
			$app->close();
		}

		try
		{
			// Create JForm object for the field
			$model = $this->getModel('itemform');
			$formData['client'] = $client;

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

			// Validate UCM subform data - start
			$fieldSets = $form->getFieldsets();

			foreach ($fieldSets as $fieldset)
			{
				foreach ($form->getFieldset($fieldset->name) as $field)
				{
					if ($field->type == 'Ucmsubform')
					{
						$subForm = $field->loadSubForm();
						$subFormFieldName = str_replace('jform[', '', $field->name);
						$subFormFieldName = str_replace(']', '', $subFormFieldName);

						if (!empty($formData[$subFormFieldName]))
						{
							foreach ($formData[$subFormFieldName] as $ucmSubFormData)
							{
								$ucmSubFormData = $model->validate($subForm, $ucmSubFormData);

								if ($ucmSubFormData === false)
								{
									$data = false;
								}
							}
						}
					}
				}
			}

			// Validate UCM subform data - end

			if ($data === false)
			{
				$errors = $model->getErrors();
				$this->processErrors($errors);

				echo new JsonResponse(null);
				$app->close();
			}

			$table = $model->getTable();
			$table->load($recordId);

			$formData = array();
			$formData['content_id'] = $recordId;
			$formData['fieldsvalue'] = $data;
			$formData['client'] = $client;
			$formData['created_by'] = $table->created_by;

			// Plugin trigger on before item date save
			PluginHelper::importPlugin('actionlog');
			$dispatcher = new EventDispatcher();
			$dispatcher->triggerEvent('tjUcmOnBeforeSaveItemData', array($recordId, $client, $data));

			// If data is valid then save the data into DB
			$response = $model->saveFieldsData($formData);

			// Plugin trigger on before item date save
			PluginHelper::importPlugin('actionlog');
			$dispatcher = new EventDispatcher();
			$dispatcher->triggerEvent('tjUcmOnAfterSaveItemData', array($recordId, $client, $data));

			$msg = null;

			if ($response && empty($section))
			{
				if ($draft)
				{
					if ($showDraftMsg)
					{
						$msg = ($response) ? Text::_("COM_TJUCM_ITEM_DRAFT_SAVED_SUCCESSFULLY") : Text::_("COM_TJUCM_FORM_SAVE_FAILED");
					}
				}
				else
				{
					$msg = ($response) ? Text::_("COM_TJUCM_ITEM_SAVED_SUCCESSFULLY") : Text::_("COM_TJUCM_FORM_SAVE_FAILED");
				}

				// Disable the draft mode of the item if full form is submitted
				$table->load($recordId);
				$table->draft = $draft;
				$table->modified_date = Factory::getDate()->toSql();
				$table->store();

				// Perform actions (redirection or trigger call) after final submit
				if (!$draft)
				{
					// TJ-ucm plugin trigger after save
					$dispatcher = JEventDispatcher::getInstance();
					PluginHelper::importPlugin("content");
					$dispatcher->triggerEvent('onUcmItemAfterSave', array($table->getProperties(), $data));
				}
			}
			else
			{
				$msg = Text::_("COM_TJUCM_FORM_SAVE_FAILED_AUTHORIZATION_ERROR");
			}

			echo new JsonResponse($response, $msg);
			$app->close();
		}
		catch (Exception $e)
		{
			echo new JsonResponse($e);
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
			echo new JsonResponse($e);
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
			for ($i = 0; $i < count($errors); $i++)
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

			$app->enqueueMessage(implode("<br>", $msg), 'error');
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$post = $app->input->post;
		$model = $this->getModel('itemform');

		$client = $post->get('client', '', 'STRING');
		$contentId = $post->get('content_id', 0, 'INT');

		if (empty($client) || empty($contentId))
		{
			echo new JsonResponse(null);
			$app->close();
		}

		$app->input->set('id', $contentId);
		$updatedOptionsForRelatedField = $model->getUdatedRelatedFieldOptions($client, $contentId);

		echo new JsonResponse($updatedOptionsForRelatedField);
		$app->close();
	}

	/**
	 * Method to copy item
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function copyItem()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$post = $app->input->post;

		$sourceClient = $app->input->get('client', '', 'string');
		$filter = $app->input->get('filter', '', 'ARRAY');
		$targetClient = $filter['target_ucm'];

		if (!$targetClient)
		{
			$targetClient = $sourceClient;
		}

		$clusterId = $filter['cluster_list'];

		JLoader::import('components.com_tjucm.models.type', JPATH_ADMINISTRATOR);
		$typeModel = BaseDatabaseModel::getInstance('Type', 'TjucmModel');

		if ($sourceClient != $targetClient)
		{
			// Server side Validation for source and UCM Type
			$result = $typeModel->getCompatibleUcmTypes($sourceClient, $targetClient);
		}
		else
		{
			$result = true;
		}

		if ($result)
		{
			$copyIds = $app->input->get('cid');
			JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);
			$tjFieldsHelper = new TjfieldsHelper;

			if (count($copyIds))
			{
				$model = $this->getModel('itemform');
				$ucmConfigs = ComponentHelper::getParams('com_tjucm');
				$useTjQueue = $ucmConfigs->get('tjqueue_copy_items');

				if ($useTjQueue)
				{
					foreach ($copyIds as $cid)
					{
						$response = $model->queueItemCopy($cid, $sourceClient, $targetClient, Factory::getuser()->id, $clusterId);

						$msg = ($response) ? Text::_("COM_TJUCM_ITEM_COPY_TO_QUEUE_SUCCESSFULLY") : Text::_("COM_TJUCM_FORM_SAVE_FAILED");
					}
				}
				else
				{
					$model->setClient($targetClient);

					foreach ($copyIds as $cid)
					{
						$ucmOldData = array();
						$ucmOldData['clientComponent'] = 'com_tjucm';
						$ucmOldData['content_id'] = $cid;
						$ucmOldData['layout'] = 'edit';
						$ucmOldData['client']     = $sourceClient;
						$fileFieldArray = array();

						// Get the field values
						$extraFieldsData = $model->loadFormDataExtra($ucmOldData);

						// Code to replace source field name with destination field name
						foreach ($extraFieldsData as $fieldKey => $fieldValue)
						{
							$prefixSourceClient = str_replace(".", "_", $sourceClient);
							$fieldName = explode($prefixSourceClient . "_", $fieldKey);
							$prefixTargetClient = str_replace(".", "_", $targetClient);
							$targetFieldName = $prefixTargetClient . '_' . $fieldName[1];
							$tjFieldsTable = $tjFieldsHelper->getFieldData($targetFieldName);
							$fieldId = $tjFieldsTable->id;
							$fieldType = $tjFieldsTable->type;
							$fielParams = json_decode($tjFieldsTable->params);
							$sourceTjFieldsTable = $tjFieldsHelper->getFieldData($fieldKey);
							$sourceFieldParams = json_decode($sourceTjFieldsTable->params);
							$subFormData = array();

							if ($tjFieldsTable->type == 'ucmsubform' || $tjFieldsTable->type == 'subform')
							{
								$params = json_decode($tjFieldsTable->params)->formsource;
								$subFormClient = explode('components/com_tjucm/models/forms/', $params);
								$subFormClient = explode('form_extra.xml', $subFormClient[1]);
								$subFormClient = 'com_tjucm.' . $subFormClient[0];

								$params = $sourceFieldParams->formsource;
								$subFormSourceClient = explode('components/com_tjucm/models/forms/', $params);
								$subFormSourceClient = explode('form_extra.xml', $subFormSourceClient[1]);
								$subFormSourceClient = 'com_tjucm.' . $subFormSourceClient[0];

								$subFormData = (array) json_decode($fieldValue);
							}

							if ($subFormData)
							{
								foreach ($subFormData as $keyData => $data)
								{
									$prefixSourceClient = str_replace(".", "_", $sourceClient);
									$fieldName = explode($prefixSourceClient . "_", $keyData);
									$prefixTargetClient = str_replace(".", "_", $targetClient);
									$subTargetFieldName = $prefixTargetClient . '_' . $fieldName[1];
									$data = (array) $data;

									foreach ((array) $data as $key => $d)
									{
										$prefixSourceClient = str_replace(".", "_", $subFormSourceClient);
										$fieldName = explode($prefixSourceClient . "_", $key);
										$prefixTargetClient = str_replace(".", "_", $subFormClient);
										$subFieldName = $prefixTargetClient . '_' . $fieldName[1];

										Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/tables');
										$fieldTable = Table::getInstance('field', 'TjfieldsTable');

										$fieldTable->load(array('name' => $key));

										if ($fieldName[1] == 'contentid')
										{
											$d = '';
										}

										$temp = array();
										unset($data[$key]);

										if (is_array($d))
										{
											// TODO Temprary used switch case need to modify code
											switch ($fieldTable->type)
											{
												case 'multi_select':
													foreach ($d as $option)
													{
														$temp[] = $option->value;
													}

													if (!empty($temp))
													{
														$data[$subFieldName] = $temp;
													}
												break;

												case 'tjlist':
												case 'related':
													foreach ($d as $option)
													{
														$data[$subFieldName][] = $option;
													}
												break;

												default:
													foreach ($d as $option)
													{
														$data[$subFieldName] = $option->value;
													}
												break;
											}
										}
										elseif($fieldTable->type == 'file' || $fieldTable->type == 'image')
										{
											Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/tables');
											$subDestionationFieldTable = Table::getInstance('field', 'TjfieldsTable');

											$subDestionationFieldTable->load(array('name' => $subFieldName));

											$subformFileData = array();
											$subformFileData['value'] = $d;
											$subformFileData['copy'] = true;
											$subformFileData['type'] = $fieldTable->type;
											$subformFileData['sourceClient'] = $subFormSourceClient;
											$subformFileData['sourceFieldUploadPath'] = json_decode($fieldTable->params)->uploadpath;
											$subformFileData['destFieldUploadPath'] = json_decode($subDestionationFieldTable->params)->uploadpath;
											$subformFileData['user_id'] = Factory::getUser()->id;
											$data[$subFieldName] = $subformFileData;
										}
										elseif ($fieldTable->type == 'cluster')
										{
											$data[$subFieldName] = $clusterId;
										}
										else
										{
											$data[$subFieldName] = $d;
										}
									}

									unset($subFormData[$keyData]);
									$subFormData[$subTargetFieldName] = $data;
								}

								unset($extraFieldsData[$fieldKey]);
								$extraFieldsData[$targetFieldName] = $subFormData;
							}
							else
							{
								unset($extraFieldsData[$fieldKey]);

								if ($fieldType == 'file' || $fieldType == 'image')
								{
									$fileData = array();
									$fileData['value'] = $fieldValue;
									$fileData['copy'] = true;
									$fileData['type'] = $fieldType;
									$fileData['sourceClient'] = $sourceClient;
									$fileData['sourceFieldUploadPath'] = $sourceFieldParams->uploadpath;
									$fileData['destFieldUploadPath'] = $fielParams->uploadpath;
									$fileData['user_id'] = Factory::getUser()->id;
									$extraFieldsData[$targetFieldName] = $fileData;
								}
								elseif($fieldType == 'cluster')
								{
									$extraFieldsData[$targetFieldName] = $clusterId;
								}
								else
								{
									$extraFieldsData[$targetFieldName] = $fieldValue;
								}
							}
						}

						$ucmData = array();
						$ucmData['id'] 			= 0;
						$ucmData['client'] 		= $targetClient;
						$ucmData['parent_id'] 	= 0;
						$ucmData['state']		= 0;
						$ucmData['draft']	 	= 1;

						if ($clusterId)
						{
							$ucmData['cluster_id']	 	= $clusterId;
						}

						// Save data into UCM data table
						$result = $model->save($ucmData);
						$recordId = $model->getState($model->getName() . '.id');

						if ($recordId)
						{
							$formData = array();
							$formData['content_id'] = $recordId;
							$formData['fieldsvalue'] = $extraFieldsData;
							$formData['client'] = $targetClient;

							// If data is valid then save the data into DB
							$response = $model->saveExtraFields($formData);

							$msg = ($response) ? Text::_("COM_TJUCM_ITEM_COPY_SUCCESSFULLY") : Text::_("COM_TJUCM_FORM_SAVE_FAILED");
						}
					}
				}

				echo new JsonResponse($response, $msg);
				$app->close();
			}
		}
	}

	/**
	 * Method to get Related Field Options for the field.
	 *
	 * @return   null
	 *
	 * @since    1.0.0
	 */
	public function getUpdatedRelatedFieldOptions()
	{
		$app       = Factory::getApplication();
		$fieldId   = $app->input->get('fieldId', '', 'STRING');
		$clusterId = $app->input->get('clusterId', 0, 'STRING');

		// Set Cluster ID
		if ($clusterId)
		{
			$app->input->set('cluster_id', $clusterId);
		}

		// Check for request forgeries.
		if (!Session::checkToken())
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
			$app->close();
		}

		// Get object of TJ-Fields field model
		JLoader::import('components.com_tjfields.models.field', JPATH_ADMINISTRATOR);
		$tjFieldsModelField = BaseDatabaseModel::getInstance('Field', 'TjfieldsModel');
		$options = $tjFieldsModelField->getRelatedFieldOptions($fieldId);

		$relatedFieldOptions = array();

		foreach ($options as $option)
		{
			$relatedFieldOptions[] = HTMLHelper::_('select.option', trim($option['value']), trim($option['text']));
		}

		echo new JsonResponse($relatedFieldOptions);
		$app->close();
	}
}
