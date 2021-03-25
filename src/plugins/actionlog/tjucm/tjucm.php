<?php
/**
 * @package     TJUCM
 * @subpackage  PlgActionlogTjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;

/**
 * UCM Actions Logging Plugin.
 *
 * @since  __DEPLOY__VERSION__
 */
class PlgActionlogTjUcm extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY__VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY__VERSION__
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  __DEPLOY__VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Proxy for ActionlogsModelUserlog addLog method
	 *
	 * This method adds a record to #__action_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array   $messages            The contents of the messages to be logged
	 * @param   string  $messageLanguageKey  The language key of the message
	 * @param   string  $context             The context of the content passed to the plugin
	 * @param   int     $userId              ID of user perform the action, usually ID of current logged in user
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY__VERSION__
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		JLoader::register('ActionlogsModelActionlog', JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php');

		$model = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog($messages, $messageLanguageKey, $context, $userId);
	}

	/**
	 * On saving UCM type data - logging method
	 *
	 * Method is called when ucm type is to be stored in the database.
	 * This method logs who created/edited any data of UCM type
	 *
	 * @param   Array    $type   Holds the ucm type data
	 * @param   Boolean  $isNew  True if a new type is stored.
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjUcmOnAfterTypeSave($type, $isNew)
	{
		if ($isNew)
		{
			if (!$this->params->get('logActionForTypeSave', 1))
			{
				return;
			}

			$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_TYPE_ADDED';
			$action             = 'add';
		}
		else
		{
			if (!$this->params->get('logActionForTypeUpdate', 1))
			{
				return;
			}

			$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_TYPE_UPDATED';
			$action             = 'update';
		}

		$context = Factory::getApplication()->input->get('option');
		$user = Factory::getUser();

		$message = array(
			'action'      => $action,
			'id'          => $type['typeId'],
			'title'       => $type['title'],
			'userid'      => $user->id,
			'username'    => ucfirst($user->username),
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			'typelink'    => 'index.php?option=com_tjucm&view=type&layout=edit&id=' . $type['typeId'],
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On deleting UCM type data - logging method
	 *
	 * Method is called after ucm type is deleted in the database.
	 *
	 * @param   String  $context  com_tjucm
	 * @param   Object  $table    Holds the coupon data.
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjUcmOnAfterTypeDelete($context, $table)
	{
		if (!$this->params->get('logActionForTypeDelete', 1))
		{
			return;
		}

		$context = Factory::getApplication()->input->get('option');
		$user    = Factory::getUser();

		$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_TYPE_DELETED';
		$message = array(
				'action'      => 'delete',
				'id'          => $table->id,
				'title'       => $table->title,
				'identifier'  => $table->unique_identifier,
				'userid'      => $user->id,
				'username'    => ucfirst($user->username),
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On changing state of UCM Type - logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who changed state of UCM type
	 *
	 * @param   String  $context  com_tjucm
	 * @param   Array   $pks      Holds array of primary key.
	 * @param   Int     $value    Switch case value.
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjUcmOnAfterTypeChangeState($context, $pks, $value)
	{
		if (!$this->params->get('logActionForTypeStateChange', 1))
		{
			return;
		}

		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$tjucmTableType = Table::getInstance('type', 'TjucmTable', array());

		$context  = Factory::getApplication()->input->get('option');
		$jUser    = Factory::getUser();
		$userId   = $jUser->id;
		$userName = ucfirst($jUser->username);

		switch ($value)
		{
			case 0:
				$messageLanguageKey = 'PLG_ACTIONLOGS_TJUCM_TYPE_UNPUBLISHED';
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = 'PLG_ACTIONLOGS_TJUCM_TYPE_PUBLISHED';
				$action             = 'publish';
				break;
			case 2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_TJUCM_TYPE_ARCHIVED';
				$action             = 'archive';
				break;
			case -2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_TJUCM_TYPE_TRASHED';
				$action             = 'trash';
				break;
			default:
				$messageLanguageKey = '';
				$action             = '';
				break;
		}

		foreach ($pks as $pk)
		{
			$tjucmTableType->load(array('id' => $pk));

			$message = array(
					'action'      => $action,
					'id'          => $tjucmTableType->id,
					'title'       => $tjucmTableType->title,
					'identifier'  => $tjucmTableType->unique_identifier,
					'itemlink'    => 'index.php?option=com_tjucm&view=type&layout=edit&id=' . $tjucmTableType->id,
					'userid'      => $userId,
					'username'    => $userName,
					'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
				);

			$this->addLog(array($message), $messageLanguageKey, $context, $userId);
		}
	}

	/**
	 * On saving UCM item data - logging method
	 *
	 * Method is called when ucm item is to be stored in the database.
	 * This method logs who created/edited any data of UCM item
	 *
	 * @param   Integer  $item   Holds the ucm item id
	 * 
	 * @param   Integer  $isNew  Flag to mark new records
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjucmOnAfterSaveItem($item, $isNew)
	{
		if (!$this->params->get('tjucmOnAfterSaveItem', 1))
		{
			return;
		}

		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$tjucmTableType = Table::getInstance('type', 'TjucmTable', array());
		$tjucmTableType->load(array('unique_identifier' => $item['client']));

		$context  = Factory::getApplication()->input->get('option');
		$user     = Factory::getUser();

		$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_ITEM_ADDED';
		$message = array(
				'action'      => 'add',
				'id'          => $item['id'],
				'title'       => $tjucmTableType->title,
				'userid'      => $user->id,
				'username'    => ucfirst($user->username),
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On saving UCM item data - logging method
	 *
	 * Method is called when ucm item is to be stored in the database.
	 * This method logs who created/edited any data of UCM item
	 *
	 * @param   Integer  $recordId  Holds the ucm item id
	 * 
	 * @param   Integer  $client    UCM client
	 * 
	 * @param   Integer  $data      fields value
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjucmOnBeforeSaveItemData($recordId, $client, $data)
	{
		if (!$this->params->get('tjucmOnAfterSaveItemData', 1) || empty($recordId))
		{
			return;
		}

		$context  = Factory::getApplication()->input->get('option');
		$user     = Factory::getUser();

		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$tjucmTableType = Table::getInstance('type', 'TjucmTable', array());
		$tjucmTableType->load(array('unique_identifier' => $client));

		JLoader::import('components.com_tjfields.tables.fieldsvalue', JPATH_ADMINISTRATOR);
		$fieldValue = Table::getInstance('FieldsValue', 'TjfieldsTable', array());
		$fieldValue->load(array('content_id' => $recordId, 'client' => $client));

		$clusterId = "";
		$clusterTitle = "";
		$ownerClusterId = "";
		$ownerClusterTitle = "";

		if (ComponentHelper::getComponent('com_cluster', true)->enabled)
		{
			$clusterField = str_replace(".", "_", $client) . '_clusterclusterid';

			JLoader::import('components.com_cluster.models.clusteruser', JPATH_ADMINISTRATOR);
			$clusterUserModel = JModelLegacy::getInstance('ClusterUser', 'ClusterModel');
			$usersClusters = $clusterUserModel->getUsersClusters($user->id);

			if ($data[$clusterField])
			{
				$editingRecordOfOtherCluster = true;

				// Check if user belongs to the cluster who has created the record or not
				foreach ($usersClusters as $usersCluster)
				{
					if ($usersCluster->cluster_id == $data[$clusterField])
					{
						// If user is not part of cluster who owns the record then he is editing record on behalf or other cluster
						$editingRecordOfOtherCluster = false;

						break;
					}
				}

				JLoader::import('components.com_cluster.tables.clusters', JPATH_ADMINISTRATOR);
				$clusterTable = Table::getInstance('Clusters', 'ClusterTable', array());

				if ($editingRecordOfOtherCluster)
				{
					$clusterTable->load($usersClusters[0]->cluster_id);
					$clusterId = $usersClusters[0]->cluster_id;
					$clusterTitle = $clusterTable->name;

					$clusterTable->load($data[$clusterField]);
					$ownerClusterId = $data[$clusterField];
					$ownerClusterTitle = $clusterTable->name;

					$messageLanguageKey = ($fieldValue->id) ? 'PLG_ACTIONLOG_TJUCM_OTHER_CLUSTER_ITEM_DATA_EDIT' : 'PLG_ACTIONLOG_TJUCM_OTHER_CLUSTER_ITEM_DATA_ADDED';
				}
				else
				{
					$clusterTable->load($data[$clusterField]);
					$clusterId = $tjucmTableItem->cluster_id;
					$clusterTitle = $clusterTable->name;

					$messageLanguageKey = ($fieldValue->id) ? 'PLG_ACTIONLOG_TJUCM_CLUSTER_ITEM_DATA_EDIT' : 'PLG_ACTIONLOG_TJUCM_CLUSTER_ITEM_DATA_ADDED';
				}
			}
		}
		else
		{
			$messageLanguageKey = ($fieldValue->id) ? 'PLG_ACTIONLOG_TJUCM_ITEM_DATA_EDIT' : 'PLG_ACTIONLOG_TJUCM_ITEM_DATA_ADDED';
		}

		JLoader::import('components.com_tjucm.helpers.tjucm', JPATH_SITE);
		$tjUcmFrontendHelper = new TjucmHelpersTjucm;
		$link = 'index.php?option=com_tjucm&view=item&client=' . $client . '&id=' . $recordId;
		$itemId = $tjUcmFrontendHelper->getItemId($link);
		$link = JRoute::_($link . '&Itemid=' . $itemId, false);

		$message = array(
				'action'              => 'add',
				'id'                  => $recordId,
				'title'               => $tjucmTableType->title,
				'cluster_id'          => $clusterId,
				'cluster_title'       => $clusterTitle,
				'owner_cluster_id'    => $ownerClusterId,
				'owner_cluster_title' => $ownerClusterTitle,
				'userid'              => $user->id,
				'username'            => ucfirst($user->name),
				'accountlink'         => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
				'item_link'           => $link,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On deleting UCM item data - logging method
	 *
	 * Method is called after ucm item is deleted in the database.
	 *
	 * @param   Object  $item    Holds the item obj.
	 * 
	 * @param   Object  $client  UCM type
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjUcmOnBeforeDeleteItem($item, $client)
	{
		if (!$this->params->get('TjUcmOnAfterItemDelete', 1))
		{
			return;
		}

		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$tjucmTableType = Table::getInstance('type', 'TjucmTable', array());
		$tjucmTableType->load(array('unique_identifier' => $client));

		$context = Factory::getApplication()->input->get('option');
		$user    = Factory::getUser();

		$clusterId = "";
		$clusterTitle = "";
		$ownerClusterId = "";
		$ownerClusterTitle = "";

		if (ComponentHelper::getComponent('com_cluster', true)->enabled)
		{
			JLoader::import('components.com_tjucm.tables.item', JPATH_ADMINISTRATOR);
			$tjucmTableItem = Table::getInstance('Item', 'TjucmTable', array());
			$tjucmTableItem->load($item);

			JLoader::import('components.com_cluster.models.clusteruser', JPATH_ADMINISTRATOR);
			$clusterUserModel = JModelLegacy::getInstance('ClusterUser', 'ClusterModel');
			$usersClusters = $clusterUserModel->getUsersClusters($user->id);

			$deletingRecordOfOtherCluster = true;

			// Check if user belongs to the cluster who has created the record or not
			foreach ($usersClusters as $usersCluster)
			{
				if ($usersCluster->cluster_id == $tjucmTableItem->cluster_id)
				{
					// If user is not part of cluster who owns the record then he is editing record on behalf or other cluster
					$deletingRecordOfOtherCluster = false;

					break;
				}
			}

			if ($tjucmTableItem->cluster_id)
			{
				JLoader::import('components.com_cluster.tables.clusters', JPATH_ADMINISTRATOR);
				$clusterTable = Table::getInstance('Clusters', 'ClusterTable', array());

				if ($deletingRecordOfOtherCluster)
				{
					$clusterTable->load($usersClusters[0]->cluster_id);
					$clusterId = $usersClusters[0]->cluster_id;
					$clusterTitle = $clusterTable->name;

					$clusterTable->load($tjucmTableItem->cluster_id);
					$ownerClusterId = $tjucmTableItem->cluster_id;
					$ownerClusterTitle = $clusterTable->name;

					$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_OTHER_CLUSTER_ITEM_DELETED';
				}
				else
				{
					$clusterTable->load($tjucmTableItem->cluster_id);
					$clusterId = $tjucmTableItem->cluster_id;
					$clusterTitle = $clusterTable->name;

					$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_CLUSTER_ITEM_DELETED';
				}
			}
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_ITEM_DELETED';
		}

		$message = array(
				'action'              => 'delete',
				'id'                  => $item,
				'title'               => $tjucmTableType->title,
				'cluster_id'          => $clusterId,
				'cluster_title'       => $clusterTitle,
				'owner_cluster_id'    => $ownerClusterId,
				'owner_cluster_title' => $ownerClusterTitle,
				'userid'              => $user->id,
				'username'            => ucfirst($user->name),
				'accountlink'         => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}
}
