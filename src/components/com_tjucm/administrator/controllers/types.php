<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filesystem\File;

/**
 * Types list controller class.
 *
 * @since  1.6
 */
class TjucmControllerTypes extends JControllerAdmin
{
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
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
		$app = Factory::getApplication();
		$input = $app->input;
		$cids = $input->get('cid', array(), 'ARRAY');

		$exportData = array();

		foreach ($cids as $cid)
		{
			$ucmTypeTable = Table::getInstance('Type', 'TjucmTable');
			$ucmTypeTable->load(array("id" => $cid));
			$ucmTypeData = (object) $ucmTypeTable->getProperties();

			$tjFieldsGroupsModel = JModelLegacy::getInstance('Groups', 'TjfieldsModel', array('ignore_request' => true));
			$tjFieldsGroupsModel->setState('list.ordering', 'a.ordering');
			$tjFieldsGroupsModel->setState('list.direction', 'asc');
			$tjFieldsGroupsModel->setState("filter.client", $ucmTypeData->unique_identifier);

			// Variable to store Field Groups
			$fieldGroups = $tjFieldsGroupsModel->getItems();

			// Getting fields of fieldGroups
			foreach ($fieldGroups as $groupKey => $groupValue)
			{
				$tjFieldsFieldsModel = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
				$tjFieldsFieldsModel->setState("filter.group_id", $fieldGroups[$groupKey]->id);
				$tjFieldsFieldsModel->setState('list.ordering', 'a.ordering');
				$tjFieldsFieldsModel->setState('list.direction', 'asc');

				// Variable to store Fields of FieldGroup
				$fields = $tjFieldsFieldsModel->getItems();

				// Getting options of field
				foreach ($fields as $fieldKey => $fieldValue)
				{
					$tjFieldsOptionsModel = JModelLegacy::getInstance('Options', 'TjfieldsModel', array('ignore_request' => true));
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
			header("Content-disposition: json" . JHtml::date('now', 'Y-M-D-H-i-s', true) . ".json");
			header("Content-disposition: filename=" . 'ucmTypeData' . JHtml::date('now', 'Y-M-D-H-i-s', true) . ".json");

			ob_clean();
			echo $jsonExportData;
			jexit();
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_SOMETHING_WENT_WRONG'), 'error');
			$link = JUri::base() . substr(JRoute::_('index.php?option=com_tjucm&view=types&layout=default', false), strlen(JUri::base(true)) + 1);
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
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');

		$app = Factory::getApplication();
		$input = $app->input;
		$user = Factory::getUser();
		$input->set('task', 'apply');
		$importFile = $input->files->get('ucm-types-upload');

		// Check if the file is a JSON file
		if ($importFile['type'] != "application/json")
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_TYPE_IMPORT_INVALID_FILE_UPLOAD_ERROR'), 'error');
			$app->redirect(JUri::root() . 'administrator/index.php?option=com_tjucm&view=types&layout=import&tmpl=component');
		}

		$uploadPath = $safefilename = Factory::getConfig()->get('tmp_path') . '/' . File::makeSafe($importFile['name']);

		// Upload the JSON file
		if (!File::upload($importFile['tmp_name'], $uploadPath))
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_TYPE_IMPORT_FILE_UPLOAD_ERROR'), 'error');
			$app->redirect(JUri::root() . 'administrator/index.php?option=com_tjucm&view=types&layout=import&tmpl=component');
		}

		// Read the file
		$ucmTypesData = File::read($uploadPath);
		$ucmTypesData = json_decode($ucmTypesData);

		if ($ucmTypesData === null)
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_TYPE_IMPORT_INVALID_FILE_CONTENT_ERROR'), 'error');
			$app->redirect(JUri::root() . 'administrator/index.php?option=com_tjucm&view=types&layout=import&tmpl=component');
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
			$ucmTypeData['alias'] = '';
			$ucmTypeData['asset_id'] = '';
			$ucmTypeData['checked_out'] = '';
			$ucmTypeData['checked_out_time'] = '';
			$ucmTypeData['modified_by'] = '';
			$ucmTypeData['modified_date'] = '';

			// Assign importer as created_by for the UCM Type being imported
			$ucmTypeData['created_by'] = $user->id;
			$ucmTypeData['created_date'] = Factory::getDate()->toSql(true);

			// Add record in ucm type table
			$tjUcmTypeModel = JModelLegacy::getInstance('Type', 'TjucmModel', array('ignore_request' => true));
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

					$tjFieldsGroupModel = JModelLegacy::getInstance('Group', 'TjfieldsModel', array('ignore_request' => true));
					$tjFieldsGroupModel->save($fieldGroupData);
					$fieldGroupId = (int) $tjFieldsGroupModel->getState($tjFieldsGroupModel->getName() . '.id');

					if (!empty($fieldGroupId) && !empty($fields))
					{
						foreach ($fields as $field)
						{
							$input->post->set('tjfields', '');
							$options = $field->options;

							// Format options data
							if (!empty($options))
							{
								foreach ($options as &$option)
								{
									$option = (array) $option;
									$option['optionname'] = $option['options'];
									$option['optionvalue'] = $option['value'];
								}

								$input->post->set('tjfields', $options);
							}

							unset($field->options);
							$field = (array) $field;

							// Cleaning Field data
							$field['id'] = '';
							$field['core'] = 0;
							$field['ordering'] = '';
							$field['asset_id'] = '';
							$field['created_by'] = $user->id;
							$field['group_id'] = $fieldGroupId;
							$field['saveOption'] = empty($options) ? 0 : 1 ;
							$field['params'] = (array) json_decode($field['params']);

							$tjFieldsFieldModel = JModelLegacy::getInstance('Field', 'TjfieldsModel', array('ignore_request' => true));
							$tjFieldsFieldModel->save($field);
							$fieldId = (int) $tjFieldsFieldModel->getState($tjFieldsFieldModel->getName() . '.id');

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
		$app->redirect(JUri::root() . 'administrator/index.php?option=com_tjucm&view=types&layout=import&tmpl=component');
	}
}
