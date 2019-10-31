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
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Migration file for TJ-UCM
 *
 * @since  1.0
 */
class TjHouseKeepingUcmSubformData extends TjModelHouseKeeping
{
	public $title = "Vertical storage of UCM subform data";

	public $description = 'Creating child record of each node of UCM subform in UCM data table';

	/**
	 * Subform migration script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function migrate()
	{
		$result = array();

		try
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('ud.*, fv.id AS fieldValueId, fv.field_id, fv.client AS sub_client, fv.value, f.name');
			$query->from($db->qn('#__tjfields_fields_value', 'fv'));
			$query->join('INNER', $db->qn('#__tjfields_fields', 'f') . ' ON (' .
			$db->qn('f.id') . ' = ' . $db->qn('fv.field_id') . ' AND ' . $db->qn('f.type') . ' = "ucmsubform")');
			$query->join('INNER', $db->qn('#__tj_ucm_data', 'ud') . ' ON (' .
			$db->qn('fv.content_id') . ' = ' . $db->qn('ud.id') . ')');
			$db->setQuery($query);

			$data = $db->loadObjectlist();

			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjucm/models');

			// Get UCM type id from uniquue identifier
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
			$tjUcmModelType = BaseDatabaseModel::getInstance('Type', 'TjucmModel', array('ignore_request' => true));

			// Process all ucm subform records
			foreach ($data as $ucmSubFormData)
			{
				if (json_decode($ucmSubFormData->value) === null)
				{
					continue;
				}

				// Create Main table data
				$validData = array();
				$validData['id'] = 0;
				$validData['parent_id'] = $ucmSubFormData->id;
				$validData['cluster_id'] = $ucmSubFormData->cluster_id;
				$validData['state'] = $ucmSubFormData->state;
				$validData['created_by'] = $ucmSubFormData->created_by;
				$validData['created_date'] = $ucmSubFormData->created_date;
				$validData['modified_by'] = $ucmSubFormData->modified_by;
				$validData['modified_date'] = $ucmSubFormData->modified_date;

				$tjFieldsFieldsModel = BaseDatabaseModel::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
				$tjFieldsFieldsModel->setState('filter.client', $ucmSubFormData->client);
				$tjFieldsFieldsModel->setState('filter.type', 'ucmsubform');
				$tjFieldsFieldsModel->setState('filter.search', 'id:' . $ucmSubFormData->field_id);

				// Get ucmsubform field data
				$ucmSubFormFields = $tjFieldsFieldsModel->getItems();

				// Initialize ucmsubform data variable
				$ucmSubFormDataSet = array();
				$ucmSubFormClient = '';

				// Sort all the ucmsubform records as per client
				foreach ($ucmSubFormFields as $ucmSubFormField)
				{
					// Get decoded data object - Convert subform JSON data into an Array
					$subformRecords = new Registry($ucmSubFormData->value);

					if (!empty($subformRecords))
					{
						// Initialize subform data variables
						$subFormData = $ucmSubFormDataSet = array();

						foreach ($subformRecords as $key => $subformRecord)
						{
							// Type cast subform records
							$subformRecord = (array) $subformRecord;

							if (!empty($subformRecord))
							{
								// Add ucmSubFormFieldName in the data to pass data to JS
								$subformRecord['ucmSubformFieldName'] = $ucmSubFormData->name;
								$subFormData[] = $subformRecord;
							}
						}

						if (!empty($subFormData))
						{
							// Format subform data to save entry as a new UCM type data
							$ucmSubFormFieldParams = new Registry($ucmSubFormField->params);
							$ucmSubFormFormSource = explode('/', $ucmSubFormFieldParams->get('formsource'));
							$ucmSubFormClient = $ucmSubFormFormSource[1] . '.' . str_replace('form_extra.xml', '', $ucmSubFormFormSource[4]);
							$ucmSubFormDataSet[$ucmSubFormClient] = $subFormData;
							$ucmSubFormData->value = $ucmSubFormClient;
						}
					}
				}

				// Save ucmSubForm records
				if (!empty($ucmSubFormDataSet))
				{
					$itemFormModel = BaseDatabaseModel::getInstance('ItemForm', 'TjucmModel', array('ignore_request' => true));

					// Set ucm type id to check permission in Item form model save() for logged-in user
					$ucmTypeId = $tjUcmModelType->getTypeId($ucmSubFormClient);
					$itemFormModel->setState('ucmType.id', $ucmTypeId);

					// Call method to save ucmsubform data into new UCM data
					$subFormContentIds = $itemFormModel->saveUcmSubFormRecords($validData, $ucmSubFormDataSet);

					// To update existing ucm subform field value from JSON to subform ucm type name
					if ($subFormContentIds)
					{
						$obj = new stdClass;
						$obj->id = $ucmSubFormData->fieldValueId;
						$obj->field_id = $ucmSubFormData->field_id;
						$obj->content_id = $ucmSubFormData->id;
						$obj->value = $ucmSubFormClient;

						$db->updateObject('#__tjfields_fields_value', $obj, 'id');
					}
				}
			}

			$result['status']   = true;
			$result['message']  = "Migration successful";
		}
		catch (Exception $e)
		{
			$result['err_code'] = '';
			$result['status']   = false;
			$result['message']  = $e->getMessage();
		}

		return $result;
	}
}
