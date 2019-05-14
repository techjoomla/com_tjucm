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
jimport('joomla.application.component.modellist');

/**
 * Class for get TjUCM types
 *
 * @package     TjUcm
 * @subpackage  Plg_Api_ucm
 * @since       _DEPLOY_VERSION_
 */
class TjucmApiResourceTypes extends ApiResource
{
	/**
	 * Get UCM Types Data
	 *
	 * @return  void
	 *
	 * @since  _DEPLOY_VERSION_
	 */
	public function get()
	{
		$jInput = JFactory::getApplication()->input;
		$client = $jInput->get('client');
		$state = $jInput->get('state');
		$search = $jInput->get('search');
		$created_by = $jInput->get('created_by');
		$limit = $jInput->get('limit', 20, INT);
		$offset = $jInput->get('offset', 0, INT);
		$modified_by = $jInput->get('modified_by');

		$TjucmModelTypes = JModelLegacy::getInstance('Types', 'TjucmModel', array('ignore_request' => true));
		$TjucmModelTypes->setState("filter.state", $state);
		$TjucmModelTypes->setState("filter.search", $search);
		$TjucmModelTypes->setState("filter.created_by", $created_by);
		$TjucmModelTypes->setState("filter.modified_by", $modified_by);
		$TjucmModelTypes->setState("filter.limit", $limit);
		$TjucmModelTypes->setState("filter.offset", $offset);

		// Variable to store UCM Types
		$result = $TjucmModelTypes->getItems();

		// Variable to store total UCM Types count matching filter
		$total = $TjucmModelTypes->getTotal();

		foreach ($result as $key => $value)
		{
			$creating_user = JFactory::getUser($result[$key]->created_by);
			$created_by = array("id" => $result[$key]->created_by, "name" => $creating_user->name);
			$result[$key]->created_by = $created_by;
			unset($result[$key]->created_by_name);
			unset($result[$key]->uEditor);

			$modify_user = JFactory::getUser($result[$key]->modified_by);
			$modified_by = array("id" => $result[$key]->modified_by, "name" => $modify_user->name);
			$result[$key]->modified_by = $modified_by;
			unset($result[$key]->modified_by_name);
		}

		// Response array
		$return_arr = array();

		// If no activities found then return the error message
		if (empty($result))
		{
			$return_arr['success'] = false;
			$return_arr['message'] = JText::_("COM_TJUCM_NO_TYPE");
		}
		else
		{
			$return_arr['success'] = true;
			$return_arr['message'] = "";
			$return_arr['total'] = $total;
			$return_arr['results'] = $result;
		}

		$this->plugin->setResponse($return_arr);
	}
}
