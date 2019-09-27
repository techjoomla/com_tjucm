<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_TjUcm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

/**
 * Class for get TjUCM
 *
 * @package     Com_TjUcm
 * @subpackage  component
 * @since       __DEPLOY_VERSION__
 */
class TjucmApiResourceItems extends ApiResource
{
	/**
	 * Get UCM Type Data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get()
	{
		$app    = Factory::getApplication();
		$input  = $app->input;
		$user   = Factory::getUser();

		// Get Parameter
		$client          = $input->get('client');
		$typeId          = $input->get('typeId');
		$filterProcess   = $input->get('filter_process', 'myprocess', "String");
		$filterClusterId = $input->get('filter_cluster_id', 'all', "String");
		$limitstart      = $input->get('limitstart', 0, "INT");
		$limit           = $input->get('limit', 20, "INT");
		$selectedFieldId = $input->get('field_id', 0, 'INT');
		$field_value     = $input->get('field_value', '', 'String');

		// Store result
		$result = array();
		$return = array();

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjucm/models');
		$tjucmModelItems = BaseDatabaseModel::getInstance('Items', 'TjucmModel', array('ignore_request' => true));
		$tjucmModelItems->setState("ucm.client", $client);
		$tjucmModelItems->setState("ucmType.id", $typeId);
		$tjucmModelItems->setState("created_by", (int) $user->id);
		$tjucmModelItems->setState("filter.cluster_id", $filterClusterId);
		$tjucmModelItems->setState("filter.process", $filterProcess);
		$tjucmModelItems->setState("list.ordering", 'a.id');
		$tjucmModelItems->setState("list.direction", 'DESC');
		$tjucmModelItems->setState("list.start", $limitstart);
		$tjucmModelItems->setState("list.limit", $limit);

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
		$tjfieldsModel = BaseDatabaseModel::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$tjfieldsModel->setState("filter.client", $client);
		$tjfieldsModel->setState("filter.filterable", 1);
		$fields = $tjfieldsModel->getItems();
		$itemid = $typeId . ':' . $app->input->getInt('Itemid', 0);

		if (!empty($selectedFieldId))
		{
			foreach ($fields as $field)
			{
				if ((int) $field->id == $selectedFieldId)
				{
					$tjucmModelItems->setState('filter.field.' . $field->name, $field_value);
				}
			}
		}

		$result            = $tjucmModelItems->getItems();
		$return['success'] = (empty($result)) ? false : true;
		$return['message'] = (empty($result)) ? Text::_("PLG_API_TJUCM_NO_RECORDS"): "";
		$return['results'] = (!empty($result)) ? $result : '';

		$this->plugin->setResponse($return);
	}
}
