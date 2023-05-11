<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filesystem\File;

JLoader::register('TjControllerHouseKeeping', JPATH_SITE . "/libraries/techjoomla/controller/houseKeeping.php");

/**
 * Types list controller class.
 *
 * @since  1.6
 */
class TjucmControllerTypes extends AdminController
{
	use TjControllerHouseKeeping;

	/**
	 * Method to clone existing Types
	 *
	 * @return void
	 */
	public function duplicate()
	{
		// Check for request forgeries
		Jsession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new Exception(Text::_('COM_TJUCM_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Jtext::_('COM_TJUCM_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_tjucm&view=types');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function getModel($name = 'type', $prefix = 'TjucmModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = Factory::getApplication()->input;
		$pks   = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}

	/**
	 * Method to export selected UCM type data
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function export()
	{
		// Check for request forgeries
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
		$app = Factory::getApplication();
		$input = $app->input;
		$cids = $input->get('cid', array(), 'ARRAY');

		$exportData = array();

		foreach ($cids as $cid)
		{
			$ucmTypeTable = Table::getInstance('Type', 'TjucmTable');
			$ucmTypeTable->load(array("id" => $cid));
			$ucmTypeData = (object) $ucmTypeTable->getProperties();

			$tjFieldsGroupsModel = BaseDatabaseModel::getInstance('Groups', 'TjfieldsModel', array('ignore_request' => true));
			$tjFieldsGroupsModel->setState('list.ordering', 'a.ordering');
			$tjFieldsGroupsModel->setState('list.direction', 'asc');
			$tjFieldsGroupsModel->setState("filter.client", $ucmTypeData->unique_identifier);

			// Variable to store Field Groups
			$fieldGroups = $tjFieldsGroupsModel->getItems();

			// Getting fields of fieldGroups
			foreach ($fieldGroups as $groupKey => $groupValue)
			{
				$tjFieldsFieldsModel = BaseDatabaseModel::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
				$tjFieldsFieldsModel->setState("filter.group_id", $fieldGroups[$groupKey]->id);
				$tjFieldsFieldsModel->setState('list.ordering', 'a.ordering');
				$tjFieldsFieldsModel->setState('list.direction', 'asc');

				// Variable to store Fields of FieldGroup
				$fields = $tjFieldsFieldsModel->getItems();

				// Getting options of field
				foreach ($fields as $fieldKey => $fieldValue)
				{
					$tjFieldsOptionsModel = BaseDatabaseModel::getInstance('Options', 'TjfieldsModel', array('ignore_request' => true));
					$tjFieldsOptionsModel->setState("filter.field_id", $fields[$fieldKey]->id);

					// Variable to store Options of Field
					$options = $tjFieldsOptionsModel->getItems();

					// Adding options to field if any
					$fields[$fieldKey]->options = empty($options) ? null : $options;
				}

				// Adding fields to fieldGroups
				$fieldGroups[$groupKey]->fields = $fields;
			}

			// Adding fieldGroups to UcmType
			$ucmTypeData->fieldGroups = $fieldGroups;

			$exportData[] = $ucmTypeData;
		}

		if (!empty($exportData))
		{
			$jsonExportData = json_encode($exportData);

			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: json" . HTMLHelper::date('now', 'Y-M-D-H-i-s', true) . ".json");
			header("Content-disposition: filename=" . 'ucmTypeData' . HTMLHelper::date('now', 'Y-M-D-H-i-s', true) . ".json");

			ob_clean();
			echo $jsonExportData;
			jexit();
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_SOMETHING_WENT_WRONG'), 'error');
			$link = Uri::base() . substr(Route::_('index.php?option=com_tjucm&view=types&layout=default', false), strlen(Uri::base(true)) + 1);
			$this->setRedirect($link);
		}
	}

	/**
	 * Method to import UCM type data
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function import()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');

		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();
		$input->set('task', 'apply');
		$importFile = $input->files->get('ucm-types-upload');

		// Check if the file is a JSON file
		if ($importFile['type'] != "application/json")
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_TYPE_IMPORT_INVALID_FILE_UPLOAD_ERROR'), 'error');
			$app->redirect(Uri::root() . 'administrator/index.php?option=com_tjucm&view=types&layout=import&tmpl=component');
		}

		$uploadPath = $safefilename = Factory::getConfig()->get('tmp_path') . '/' . File::makeSafe($importFile['name']);

		// Upload the JSON file
		if (!File::upload($importFile['tmp_name'], $uploadPath))
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_TYPE_IMPORT_FILE_UPLOAD_ERROR'), 'error');
			$app->redirect(Uri::root() . 'administrator/index.php?option=com_tjucm&view=types&layout=import&tmpl=component');
		}

		// Read the file
		$ucmTypesData = File::read($uploadPath);
		$ucmTypesData = json_decode($ucmTypesData);

		if ($ucmTypesData === null)
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_TYPE_IMPORT_INVALID_FILE_CONTENT_ERROR'), 'error');
			$app->redirect(Uri::root() . 'administrator/index.php?option=com_tjucm&view=types&layout=import&tmpl=component');
		}

		foreach ($ucmTypesData as $ucmTypeData)
		{
			$ucmTypeTable = Table::getInstance('Type', 'TjucmTable');
			$ucmTypeTable->load(array("unique_identifier" => $ucmTypeData->unique_identifier));

			if (!empty($ucmTypeTable->id))
			{
				$app->enqueueMessage(Text::_('COM_TJUCM_DUPLICATE_TYPE_ERROR'), 'error');

				continue;
			}

			$fieldGroupsData = $ucmTypeData->fieldGroups;
			unset($ucmTypeData->fieldGroups);

			$ucmTypeData = (array) $ucmTypeData;
			$params = (array) json_decode($ucmTypeData['params']);

			$ucmTypeData = array_merge($ucmTypeData, $params);

			// Cleaning UCM Type data
			$ucmTypeData['id'] = '';
			$ucmTypeData['asset_id'] = '';
			$ucmTypeData['checked_out'] = '';
			$ucmTypeData['checked_out_time'] = '';
			$ucmTypeData['modified_by'] = '';
			$ucmTypeData['modified_date'] = '';

			// Assign importer as created_by for the UCM Type being imported
			$ucmTypeData['created_by'] = $user->id;
			$ucmTypeData['created_date'] = Factory::getDate()->toSql(true);

			// Add record in ucm type table
			$tjUcmTypeModel = BaseDatabaseModel::getInstance('Type', 'TjucmModel', array('ignore_request' => true));
			$tjUcmTypeModel->save($ucmTypeData);
			$ucmTypeId = (int) $tjUcmTypeModel->getState($tjUcmTypeModel->getName() . '.id');

			if (!empty($ucmTypeId) && !empty($ucmTypeId))
			{
				foreach ($fieldGroupsData as $fieldGroupData)
				{
					$fields = $fieldGroupData->fields;
					unset($fieldGroupData->fields);
					$fieldGroupData = (array) $fieldGroupData;

					// Cleaning Field Group data
					$fieldGroupData['id'] = '';
					$fieldGroupData['ordering'] = '';
					$fieldGroupData['asset_id'] = '';
					$fieldGroupData['created_by'] = $user->id;

					$tjFieldsGroupModel = BaseDatabaseModel::getInstance('Group', 'TjfieldsModel', array('ignore_request' => true));
					$tjFieldsGroupModel->save($fieldGroupData);
					$fieldGroupId = (int) $tjFieldsGroupModel->getState($tjFieldsGroupModel->getName() . '.id');

					if (!empty($fieldGroupId) && !empty($fields))
					{
						foreach ($fields as $field)
						{
							$fieldOptions = array();
							$options = $field->options;

							// Format options data
							if (!empty($options))
							{
								$optionCount = 0;

								foreach ($options as &$option)
								{
									$fieldOptions['fieldoption' . $optionCount] = array("name" => $option->options, "value" => $option->value);
									$optionCount++;
								}
							}

							$field = (array) $field;

							// Cleaning Field data
							$field['id'] = '';
							$field['core'] = 0;
							$field['ordering'] = '';
							$field['asset_id'] = '';
							$field['created_by'] = $user->id;
							$field['group_id'] = $fieldGroupId;
							$field['saveOption'] = empty($options) ? 0 : 1;
							$field['params'] = (array) json_decode($field['params']);
							$tmpName = str_replace('.', '_', $ucmTypeData['unique_identifier']) . '_';
							$field['name'] = str_replace($tmpName, '', $field['name']);
							$field['fieldoption'] = $fieldOptions;

							// Special case - Do not insert field with name 'contentid' as this will be added when ucm type for ucmsubform is created
							if ($field['name'] == 'contentid')
							{
								continue;
							}

							$input->post->set('client_type', end(explode(".", $ucmTypeData['unique_identifier'])));

							$tjFieldsFieldModel = BaseDatabaseModel::getInstance('Field', 'TjfieldsModel', array('ignore_request' => true));
							$tjFieldsFieldModel->save($field);
							$fieldId = (int) $tjFieldsFieldModel->getState($tjFieldsFieldModel->getName() . '.id');
							$input->post->set('client_type', '');

							if (!empty($fieldId) && !empty($options))
							{
								$db = Factory::getDbo();

								foreach ($options as $option)
								{
									// Cleaning Field option data
									$option->id = '';
									$option->field_id = $fieldId;

									$db->insertObject('#__tjfields_options', $option);
								}
							}
						}
					}
				}
			}
		}

		$app->enqueueMessage(Text::_('COM_TJUCM_TYPE_IMPORT_SUCCESS_MSG'), 'success');
		$app->redirect(Uri::root() . 'administrator/index.php?option=com_tjucm&view=types&layout=import&tmpl=component');
	}
}
