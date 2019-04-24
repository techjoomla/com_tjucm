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
class TjucmApiResourceItem extends ApiResource
{
	/**
	 * Get UCM Item Data
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	public function get()
	{
		$jInput = JFactory::getApplication()->input;
		$client = $jInput->get('client');
		$id = $jInput->get('id');
		$TjucmModelItem = JModelLegacy::getInstance('Item', 'TjucmModel');

		// Setting Client ID
		$item = $TjucmModelItem->getItem($id);
		$this->plugin->setResponse($item);
	}

	/**
	 * Post Item Data
	 *
	 * @return  Json Item details
	 *
	 * @since   0.0.1
	 */
	public function post()
	{
		$jInput = JFactory::getApplication()->input;
		$client = $jInput->get('client');
		// Getting the request Body Data
		$input = JFactory::getApplication()->input;
		$file  = $input->files->get('image'); 
		var_dump($file);
		$jinput = JFactory::getApplication()->input->json;
		var_dump($jInput->get('name'));
		die;
		// Setting Item details
		$data = array();
		$data["id"] = $jinput->get('id');
		
		$data["client"] = $jinput->get('client');;
		$data["draft"] = $jinput->get('draft');
		$data["categoryId"] = $jinput->get('category_id');
		$data["state"] = $jinput->get('state');;
		$fields = $jinput->get('fields', array(), 'array');
		$extra_jform_data = array();
		// Addding Extra item field values
		$TjfieldsModelFields = JModelLegacy::getInstance('Fields', 'TjfieldsModel');
		$TjfieldsModelFields->setState("filter.client", $client);
		
		// Variable to store Fields of FieldGroup
		$tjFields = $TjfieldsModelFields->getItems();
		
		$temp=array();
		foreach ($tjFields as $k=>$v)
		{
		    $temp[$v->id]=$v->name;
		}
		unset($tjFields);
		print_r($temp);
		foreach ($fields as $k => $field)
		{
			$extra_jform_data[$temp[(int)$field["id"]]] = $field["value"];
		}
		$TjucmModelItemForm = JModelLegacy::getInstance('ItemForm', 'TjucmModel');
		// Setting Client ID
		$TjucmModelItemForm->setClient($client);
		$itemId = $TjucmModelItemForm->save($data, $extra_jform_data);

		
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

	/**
	 * Delete Item Data
	 *
	 * @return  boolean
	 *
	 * @since   0.0.1
	 */
	public function delete()
	{
		die("Working");
		$this->plugin->setResponse($result_arr);
	}
}
