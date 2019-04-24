<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_TjUcm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

/**
 * Class for get TjUCM
 *
 * @package     Com_TjUcm
 * @subpackage  component
 * @since       0.0.1
 */
class TjucmApiResourceType extends ApiResource
{
	/**
	 * Get UCM Type Data
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	public function get()
	{
	    $executionStartTime = microtime(true);
		$jInput = JFactory::getApplication()->input;
		$client = $jInput->get('client');
		$table = JTable::getInstance('Type', 'TjucmTable');
		$table->load(["unique_identifier" => $client]);

		$TjucmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');
		$TjucmModelType->setState("filter.client", $client);

		// Variable to store UCM Type
		$ucmType = $TjucmModelType->getItem($table->id);

		// Variable to store creator name and id
		$created_by = JFactory::getUser($ucmType->created_by);
		$created_by = array("id" => $created_by->id,"name" => $created_by->name);
		$ucmType->created_by = $created_by;

		// Variable to store modifier name and id
		$modified_by = JFactory::getUser($ucmType->modified_by);
		$modified_by = array("id" => $modified_by->id,"name" => $modified_by->name);
		$ucmType->modified_by = $modified_by;

		// Variable to store Field Groups
		$fieldgroups = array();
		$TjfieldsModelGroups = JModelLegacy::getInstance('Groups', 'TjfieldsModel');
		$fieldgroups = $TjfieldsModelGroups->getItems();

		// Getting fields of fieldgroups
		foreach ($fieldgroups as $groupKey => $groupValue)
		{
			$TjfieldsModelFields = JModelLegacy::getInstance('Fields', 'TjfieldsModel');
			$TjfieldsModelFields->setState("filter.group_id", $fieldgroups[$groupKey]->id);

			// Variable to store Fields of FieldGroup
			$fields = $TjfieldsModelFields->getItems();

			// Getting options of field
			foreach ($fields as $fieldKey => $fieldValue)
			{
				$TjfieldsModelOptions = JModelLegacy::getInstance('Options', 'TjfieldsModel');
				$TjfieldsModelOptions->setState("filter.field_id", $fields[$fieldKey]->id);

				// Variable to store Options of Field
				$options = $TjfieldsModelOptions->getItems();

				// Adding options to field if any
				$fields[$fieldKey]->options = empty($options) ? null : $options;
			}

			// Adding fields to fieldGroups
			$fieldgroups[$groupKey]->fields = $fields;
		}


		// Adding fieldGroups to UcmType
		$ucmType->fieldgroups = $fieldgroups;

		// Variable to store request response
		$return_arr = array();
		
		// If no activities found then return the error message
		if (empty($ucmType))
		{
			$return_arr['success'] = false;
			$return_arr['message'] = JText::_("COM_TJUCM_NO_TYPE");
		}
		else
		{
			$return_arr['success'] = true;
			$return_arr['message'] = "";
			$return_arr['ucmType'] = $ucmType;
			$executionEndTime = microtime(true);
			$seconds = $executionEndTime - $executionStartTime;
			$return_arr["time"]=$seconds;
		}

		$this->plugin->setResponse($return_arr);
	}

	/**
	 * Post Type Data
	 *
	 * @return  json Type details
	 *
	 * @since   0.0.1
	 */
	public function post()
	{
		die("Working");
		$this->plugin->setResponse(["null"]);
	}

	/**
	 * Delete Type Data
	 *
	 * @return  boolean
	 *
	 * @since   0.0.1
	 */
	public function delete()
	{
		die("Working");
		$this->plugin->setResponse(["null"]);
	}
}
