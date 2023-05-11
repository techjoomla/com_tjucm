<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
jimport('joomla.plugin.plugin');

/**
 * Class for get TjUCM type
 *
 * @package     TjUcm
 * @subpackage  Plg_Api_ucm
 * @since       _DEPLOY_VERSION_
 */
class TjucmApiResourceType extends ApiResource
{
	/**
	 * Get UCM Type Data
	 *
	 * @return  void
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function get()
	{
		$jInput = Factory::getApplication()->input;
		$client = $jInput->get('client');
		$table = Table::getInstance('Type', 'TjucmTable');
		$table->load(["unique_identifier" => $client]);

		$tjUcmModelType = BaseDatabaseModel::getInstance('Type', 'TjucmModel');
		$tjUcmModelType->setState("filter.client", $client);

		// Variable to store UCM Type
		$ucmType = $tjUcmModelType->getItem($table->id);

		// Variable to store creator name and id
		$created_by = Factory::getUser($ucmType->created_by);
		$ucmType->created_by = array("id" => $created_by->id, "name" => $created_by->name);

		// Variable to store modifier name and id
		$modified_by = Factory::getUser($ucmType->modified_by);
		$ucmType->modified_by = array("id" => $modified_by->id, "name" => $modified_by->name);

		$tjFieldsModelGroups = BaseDatabaseModel::getInstance('Groups', 'TjfieldsModel', array('ignore_request' => true));
		$tjFieldsModelGroups->setState('list.ordering', 'a.ordering');
		$tjFieldsModelGroups->setState('list.direction', 'asc');

		// Variable to store Field Groups
		$fieldgroups = $tjFieldsModelGroups->getItems();

		// Getting fields of fieldgroups
		foreach ($fieldgroups as $groupKey => $groupValue)
		{
			$tjFieldsModelFields = BaseDatabaseModel::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
			$tjFieldsModelFields->setState("filter.group_id", $fieldgroups[$groupKey]->id);
			$tjFieldsModelFields->setState('list.ordering', 'a.ordering');
			$tjFieldsModelFields->setState('list.direction', 'asc');

			// Variable to store Fields of FieldGroup
			$fields = $tjFieldsModelFields->getItems();

			// Getting options of field
			foreach ($fields as $fieldKey => $fieldValue)
			{
				$tjFieldsModelOptions = BaseDatabaseModel::getInstance('Options', 'TjfieldsModel');
				$tjFieldsModelOptions->setState("filter.field_id", $fields[$fieldKey]->id);

				// Variable to store Options of Field
				$options = $tjFieldsModelOptions->getItems();

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
			$return_arr['message'] = Text::_("COM_TJUCM_NO_TYPE");
		}
		else
		{
			$return_arr['success'] = true;
			$return_arr['message'] = "";
			$return_arr['ucmType'] = $ucmType;
		}

		$this->plugin->setResponse($return_arr);
	}
}
