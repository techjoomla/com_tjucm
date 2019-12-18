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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Filesystem\File;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * Items list controller class.
 *
 * @since  1.6
 */
class TjucmControllerItems extends TjucmController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function &getModel($name = 'Items', $prefix = 'TjucmModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Function to import records in specifed UCM type from CSV.
	 *
	 * @return null
	 *
	 * @since	1.2.4
	 */
	public function importCsv()
	{
		Session::checkToken() or die('Invalid Token');

		$app = Factory::getApplication();
		$importFile = $app->input->files->get('csv-file-upload');

		$client = $app->input->get("client", '', 'STRING');

		if (empty($client))
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_SOMETHING_WENT_WRONG'), 'error');
			$app->redirect(Uri::root() . 'index.php?option=com_tjucm&view=items&layout=importitems&tmpl=component');
		}

		// Check if the file is a CSV file
		if ($importFile['type'] != "text/csv")
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_ITEMS_INVALID_CSV_FILE'), 'error');
			$app->redirect(Uri::root() . 'index.php?option=com_tjucm&view=items&layout=importitems&tmpl=component&client=' . $client);
		}

		// Load required files
		JLoader::import('components.com_tjucm.models.itemform', JPATH_SITE);
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_tjfields.models.options', JPATH_ADMINISTRATOR);

		$uploadPath = Factory::getConfig()->get('tmp_path') . '/' . File::makeSafe($importFile['name']);

		// Upload the JSON file
		if (!File::upload($importFile['tmp_name'], $uploadPath))
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_ITEMS_CSV_FILE_UPLOAD_ERROR'), 'error');
			$app->redirect(Uri::root() . 'index.php?option=com_tjucm&view=items&layout=importitems&tmpl=component&client=' . $client);
		}

		// Get all fields in the given UCM type
		$tjFieldsFieldsModel = BaseDatabaseModel::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$tjFieldsFieldsModel->setState("filter.client", $client);
		$tjFieldsFieldsModel->setState("filter.state", 1);
		$tjFieldsFieldsModel->setState('list.ordering', 'a.ordering');
		$tjFieldsFieldsModel->setState('list.direction', 'asc');
		$fields = $tjFieldsFieldsModel->getItems();

		// Map the field names as per field labels in the uploaded CSV file
		$fieldsArray = array();
		$requiredFieldsName = array();
		$requiredFieldsLabel = array();
		$fieldsName = array_column($fields, 'name');
		$fieldsLabel = array_column($fields, 'label');
		$fieldHeaders = array_combine($fieldsName, $fieldsLabel);

		foreach ($fields as $field)
		{
			// Get the required fields for the UCM type
			if ($field->required == 1)
			{
				$requiredFieldsName[$field->name] = $field->name;
				$requiredFieldsLabel[] = $field->label;
			}

			// Add options data the radio and list type fields
			if (in_array($field->type, array('radio', 'single_select', 'multi_select', 'tjlist')))
			{
				$tjFieldsOptionsModel = BaseDatabaseModel::getInstance('Options', 'TjfieldsModel', array('ignore_request' => true));
				$tjFieldsOptionsModel->setState("filter.field_id", $field->id);
				$field->options = $tjFieldsOptionsModel->getItems();
			}

			$fieldsArray[$field->name] = $field;
		}

		// Read the CSV file
		$file = fopen($uploadPath, 'r');
		$headerRow = true;
		$invalidRows = 0;
		$validRows = 0;

		// Loop through the uploaded file
		while (($data = fgetcsv($file)) !== false)
		{
			if ($headerRow)
			{
				$headers = $data;
				$headerRow = false;

				// Check if all the required fields headers are present in the CSV file to be imported
				$isValid = (count(array_intersect($requiredFieldsLabel, $headers)) == count($requiredFieldsLabel));

				if (!$isValid)
				{
					$app->enqueueMessage(Text::_('COM_TJUCM_ITEMS_INVALID_CSV_FILE_REQUIRED_COLUMN_MISSING'), 'error');
					$app->redirect(Uri::root() . 'index.php?option=com_tjucm&view=items&layout=importitems&tmpl=component&client=' . $client);
				}
			}
			elseif (count($headers) == count($data))
			{
				$itemData = array();

				// Prepare item data for item creation
				foreach ($data as $key => $value)
				{
					$fieldName = array_search($headers[$key], $fieldHeaders);
					$value = trim($value);

					if ($fieldName !== false && $value != '')
					{
						if (isset($fieldsArray[$fieldName]->options) && !empty($fieldsArray[$fieldName]->options))
						{
							$fieldParams = new Registry($fieldsArray[$fieldName]->params);

							// If there are multiple values for a field then we need to send those as array
							if (strpos($value, '||') !== false && $fieldParams->get('multiple'))
							{
								$optionValue = array_map('trim', explode("||", $value));
								$multiSelectValues = array();

								foreach ($fieldsArray[$fieldName]->options as $option)
								{
									if (in_array($option->options, $optionValue))
									{
										$multiSelectValues[] = $option->value;
									}
								}

								$itemData[$fieldName] = $multiSelectValues;
							}
							else
							{
								foreach ($fieldsArray[$fieldName]->options as $option)
								{
									if ($option->options == $value)
									{
										$itemData[$fieldName] = $option->value;

										break;
									}
								}
							}
						}
						else
						{
							$itemData[$fieldName] = trim($value);
						}
					}
				}

				// Check if all the required values are present in the row
				$isValid = (count(array_intersect_key($itemData, $requiredFieldsName)) == count($requiredFieldsName));

				if (!$isValid || empty($itemData))
				{
					$invalidRows++;
				}
				else
				{
					// Save the record in UCM
					$tjucmItemFormModel = BaseDatabaseModel::getInstance('ItemForm', 'TjucmModel');

					if ($tjucmItemFormModel->save(array('client' => $client)))
					{
						$contentId = (int) $tjucmItemFormModel->getState($tjucmItemFormModel->getName() . '.id');

						$fieldsData = array();
						$fieldsData['client']      = $client;
						$fieldsData['content_id']  = $contentId;
						$fieldsData['fieldsvalue'] = $itemData;

						if ($tjucmItemFormModel->saveFieldsData($fieldsData))
						{
							$validRows++;

							continue;
						}
					}
					else
					{
						// Return the error messages if any
						if (!empty($tjucmItemFormModel->getErrors()))
						{
							foreach ($tjucmItemFormModel->getErrors() as $error)
							{
								$app->enqueueMessage($error, 'error');
							}
						}
					}
				}
			}
			else
			{
				$invalidRows++;
			}
		}

		if ($validRows)
		{
			$app->enqueueMessage(Text::sprintf('COM_TJUCM_ITEMS_IMPORTED_SCUUESSFULLY', $validRows), 'success');
		}

		if ($invalidRows)
		{
			$app->enqueueMessage(Text::sprintf('COM_TJUCM_ITEMS_IMPORT_REJECTED_RECORDS', $invalidRows), 'warning');
		}

		if (empty($validRows) && empty($invalidRows))
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_ITEMS_NO_RECORDS_TO_IMPORT'), 'error');
		}

		$app->redirect(Uri::root() . 'index.php?option=com_tjucm&view=items&layout=importitems&tmpl=component&client=' . $client);
	}

	/**
	 * Function to generate schema of CSV file for importing the records in specifed UCM type.
	 *
	 * @return null
	 *
	 * @since	1.2.4
	 */
	public function getCsvImportFormat()
	{
		Session::checkToken('get') or die('Invalid Token');

		$app = Factory::getApplication();
		$client = $app->input->get("client", '', 'STRING');

		if (empty($client))
		{
			$app->enqueueMessage(Text::_('COM_TJUCM_SOMETHING_WENT_WRONG'), 'error');
			$app->redirect(Uri::root() . 'index.php?option=com_tjucm&view=items&layout=importitems&tmpl=component');
		}

		// Get UCM Type data
		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$ucmTypeTable = Table::getInstance('Type', 'TjucmTable');
		$ucmTypeTable->load(array("unique_identifier" => $client));

		// Get fields in the given UCM type
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		$tjFieldsFieldsModel = BaseDatabaseModel::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$tjFieldsFieldsModel->setState("filter.client", $client);
		$tjFieldsFieldsModel->setState("filter.state", 1);
		$tjFieldsFieldsModel->setState('list.ordering', 'a.ordering');
		$tjFieldsFieldsModel->setState('list.direction', 'asc');
		$fields = $tjFieldsFieldsModel->getItems();
		$fieldsLabel = array_column($fields, 'label');

		// Generate schema CSV file with CSV headers as label of the fields for given UCM type and save it in temp folder
		$fileName = preg_replace('/[^A-Za-z0-9\-]/', '', $ucmTypeTable->title) . '.csv';
		$csvFileTmpPath = Factory::getConfig()->get('tmp_path') . '/' . $fileName;
		$output = fopen($csvFileTmpPath, 'w');
		fputcsv($output, $fieldsLabel);
		fclose($output);

		// Download the CSV file
		header("Content-type: text/csv");
		header("Content-disposition: attachment; filename = " . $fileName);
		readfile($csvFileTmpPath);

		jexit();
	}
}
