<?php
/**
 * @package     TjUcm
 * @subpackage  Plg_Api_ucm
 * 
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

/**
 * Class for get TjUCM item
 *
 * @package     TjUcm
 * @subpackage  Plg_Api_ucm
 * @since       _DEPLOY_VERSION_
 */
class TjucmApiResourceItem extends ApiResource
{
	/**
	 * Get UCM Item Data
	 *
	 * @return  void
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function get()
	{
		$jInput = JFactory::getApplication()->input;
		$id = $jInput->get('id');
		$tjUcmModelItem = JModelLegacy::getInstance('Item', 'TjucmModel');

		// Setting Client ID
		$item = $tjUcmModelItem->getItem($id);

		$this->plugin->setResponse($item);
	}

	/**
	 * Post Item Data
	 *
	 * @return  void
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function post()
	{
		$jInput = JFactory::getApplication()->input;
		$client = $jInput->get('client');
		$id = $jInput->get('id');

		// Getting the request Body Data
		$jinput = JFactory::getApplication()->input->json;

		// Setting Item details
		$data = array();
		$data["id"] = $id;
		$data["client"] = $client;
		$data["draft"] = $jinput->get('draft');
		$data["categoryId"] = $jinput->get('category_id');
		$data["state"] = $jinput->get('state');
		$fields = $jinput->get('fields', array(), 'array');
		$extra_jform_data = array();

		// Addding Extra item field values
		$tjFieldsModelFields = JModelLegacy::getInstance('Fields', 'TjfieldsModel');
		$tjFieldsModelFields->setState("filter.client", $client);

		// Variable to store Fields of FieldGroup
		$tjFields = $tjFieldsModelFields->getItems();

		// Array to store field id=>name
		$fieldsAssoc = array();

		foreach ($tjFields as $k => $v)
		{
			$fieldsAssoc[$v->id] = $v->name;
		}

		unset($tjFields);

		// Mapping field name with its value 
		foreach ($fields as $k => $field)
		{
			$extra_jform_data[$fieldsAssoc[(int) $field["id"]]] = $field["value"];
		}

		$tjUcmModelItemForm = JModelLegacy::getInstance('ItemForm', 'TjucmModel');

		// Setting Client ID
		$tjUcmModelItemForm->setClient($client);
		$itemId = $tjUcmModelItemForm->save($data, $extra_jform_data);

		// Response Array
		$return_arr = array();

		if ($itemId)
		{
			$return_arr['success'] = true;
			$return_arr['message'] = JText::_("COM_TJUCM_ITEM_ADDED");
			$return_arr['id'] = $itemId;
		}
		else
		{
			$return_arr['success'] = false;
			$return_arr['message'] = JText::_("COM_TJUCM_ITEM_NOT_ADDED");
		}

		$this->plugin->setResponse($return_arr);
	}
}
