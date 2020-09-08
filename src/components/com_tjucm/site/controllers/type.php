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
defined('_JEXEC') or die;

use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;

/**
 * Type controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class TjucmControllerType extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'types';
		parent::__construct();
	}

	/**
	 * Method to check the compatibility between ucm types
	 *
	 * @return  mixed
	 * 
	 * @since    __DEPLOY_VERSION__
	 */
	public function getCompatibleUcmTypes()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app 	= Factory::getApplication();
		$post 	= $app->input->post;
		$client = $post->get('client', '', 'STRING');

		if (empty($client))
		{
			echo new JResponseJson(null);
			$app->close();
		}

		JLoader::import('components.com_tjucm.models.types', JPATH_ADMINISTRATOR);
		$typesModel = BaseDatabaseModel::getInstance('Types', 'TjucmModel');
		$typesModel->setState('filter.state', 1);
		$ucmTypes 	= $typesModel->getItems();

		JLoader::import('components.com_tjucm.models.type', JPATH_ADMINISTRATOR);
		$typeModel = BaseDatabaseModel::getInstance('Type', 'TjucmModel');

		$validUcmType = array();
		$validUcmType[0]['value'] = "";
		$validUcmType[0]['text'] = Text::_('COM_TJUCM_COPY_ITEMS_SELECT_UCM_TYPE');

		foreach ($ucmTypes as $key => $type)
		{
			if ($type->unique_identifier != $client)
			{
				$result = $typeModel->getCompatibleUcmTypes($client, $type->unique_identifier);

				if ($result)
				{
					$validUcmType[$key + 1]['value'] = $type->unique_identifier;
					$validUcmType[$key + 1]['text']  = $type->title;
				}
			}
		}

		if (count($validUcmType) <= 1)
		{
			$validUcmType = false;
		}

		echo new JResponseJson($validUcmType);
		$app->close();
	}

	/**
	 * Method to get Cluster field
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getClusterFieldOptions()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();

		$lang = Factory::getLanguage();
		$lang->load('com_tjfields', JPATH_SITE);

		$post = $app->input->post;
		$client = $post->get('client', '', 'STRING');

		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$typeTable = Table::getInstance('type', 'TjucmTable');
		$typeTable->load(array('unique_identifier' => $client));

		if (empty($client))
		{
			echo new JResponseJson(null);
			$app->close();
		}

		// Show records belonging to users cluster if com_cluster is installed and enabled - start
		$clusterExist = ComponentHelper::getComponent('com_cluster', true)->enabled;

		if (empty($clusterExist))
		{
			echo new JResponseJson(null);
			$app->close();
		}

		JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
		$fieldTable = Table::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
		$fieldTable->load(array('client' => $client, 'type' => 'cluster'));

		if (!$fieldTable->id)
		{
			echo new JResponseJson(null);
			$app->close();
		}

		JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);
		JLoader::import("/components/com_cluster/includes/cluster", JPATH_ADMINISTRATOR);
		$clustersModel = ClusterFactory::model('Clusters', array('ignore_request' => true));
		$clusters = $clustersModel->getItems();

		// Get list of clusters with data in UCM type
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('cluster_id'));
		$query->from($db->quoteName('#__tj_ucm_data'));
		$query->where($db->quoteName('client') . '=' . $db->quote($client));
		$query->group($db->quoteName('cluster_id'));
		$db->setQuery($query);
		$clustersWithData = $db->loadColumn();

		$usersClusters = array();

		$clusterObj = new stdclass;
		$clusterObj->text = Text::_("COM_TJFIELDS_OWNERSHIP_CLUSTER");
		$clusterObj->value = "";

		$usersClusters[] = $clusterObj;

		if (!empty($clusters))
		{
			foreach ($clusters as $clusterList)
			{
				if (RBACL::check(Factory::getUser()->id, 'com_cluster', 'core.viewitem.' . $typeTable->id, $clusterList->id) || RBACL::check(Factory::getUser()->id, 'com_cluster', 'core.viewallitem.' . $typeTable->id))
				{
					if (!empty($clusterList->id))
					{
						if (in_array($clusterList->id, $clustersWithData))
						{
							$clusterObj = new stdclass;
							$clusterObj->text = $clusterList->name;
							$clusterObj->value = $clusterList->id;

							$usersClusters[] = $clusterObj;
						}
					}
				}
			}
		}

		echo new JResponseJson($usersClusters);
		$app->close();
	}
}
