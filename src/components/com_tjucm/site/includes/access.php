<?php
/**
 * @package     Tjucm
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;

require_once JPATH_SITE . '/components/com_tjucm/includes/defines.php';

/**
 * Tjucm factory class.
 *
 * This class perform the helpful operation required to Tjucm package
 *
 * @since  __DEPLOY_VERSION__
 */
class TjucmAccess
{
	public static function canCreate($ucmTypeId)
	{
		if (TjucmAccess::hasCluster($ucmTypeId))
		{
			// Get com_subusers component status
			$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

			// Check user have permission to edit record of assigned cluster
			if ($subUserExist)
			{
				JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);

				return RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.createitem');
			}
		}
		else
		{
			return JFactory::getUser()->authorise('core.type.createitem', 'com_tjucm.type.' . $ucmTypeId);
		}
	}

	public static function canImport($ucmTypeId)
	{
		if (TjucmAccess::hasCluster($ucmTypeId))
		{
			// Get com_subusers component status
			$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

			// Check user have permission to edit record of assigned cluster
			if ($subUserExist)
			{
				JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);

				return RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.importitem');
			}
		}
		else
		{
			return JFactory::getUser()->authorise('core.type.importitem', 'com_tjucm.type.' . $ucmTypeId);
		}
	}

	public static function canView($ucmTypeId, $contentId)
	{
		JLoader::import('components.com_tjucm.tables.item', JPATH_ADMINISTRATOR);
		$itemTable = JTable::getInstance('Item', 'TjucmTable', array('dbo', JFactory::getDbo()));
		$itemTable->load($contentId);

		if (JFactory::getUser()->id == $itemTable->created_by)
		{
			return true;
		}

		JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);

		if (TjucmAccess::hasCluster($ucmTypeId))
		{
			if (RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.viewallitem'))
			{
				return true;
			}

			// Get com_subusers component status
			$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

			// Check user have permission to edit record of assigned cluster
			if ($subUserExist)
			{
				return RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.viewitem', $itemTable->cluster_id);
			}
		}
		else
		{
			return JFactory::getUser()->authorise('core.type.viewitem', 'com_tjucm.type.' . $ucmTypeId);
		}
	}

	public static function canEdit($ucmTypeId, $contentId)
	{
		if (TjucmAccess::hasCluster($ucmTypeId))
		{
			if (RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.editallitem'))
			{
				return true;
			}

			// Get com_subusers component status
			$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

			// Check user have permission to edit record of assigned cluster
			if ($subUserExist)
			{
				JLoader::import("components.com_subusers.includes.rbacl", JPATH_ADMINISTRATOR);
				JLoader::import('components.com_tjucm.tables.item', JPATH_ADMINISTRATOR);
				$itemTable = JTable::getInstance('Item', 'TjucmTable', array('dbo', JFactory::getDbo()));
				$itemTable->load($contentId);

				return RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.edititem', $itemTable->cluster_id);
			}
		}
		else
		{
			return JFactory::getUser()->authorise('core.type.edititem', 'com_tjucm.type.' . $ucmTypeId);
		}
	}

	public static function canEditState($ucmTypeId, $contentId)
	{
		if (TjucmAccess::hasCluster($ucmTypeId))
		{
			if (RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.editallitemstate'))
			{
				return true;
			}

			// Get com_subusers component status
			$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

			// Check user have permission to edit record of assigned cluster
			if ($subUserExist)
			{
				JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);
				JLoader::import('components.com_tjucm.tables.item', JPATH_ADMINISTRATOR);
				$itemTable = JTable::getInstance('Item', 'TjucmTable', array('dbo', JFactory::getDbo()));
				$itemTable->load($contentId);

				return RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.edititemstate', $itemTable->cluster_id);
			}
		}
		else
		{
			return JFactory::getUser()->authorise('core.type.edititemstate', 'com_tjucm.type.' . $ucmTypeId);
		}
	}

	public static function canEditOwn($ucmTypeId, $contentId)
	{
		if (TjucmAccess::hasCluster($ucmTypeId))
		{
			// Get com_subusers component status
			$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

			// Check user have permission to edit record of assigned cluster
			if ($subUserExist)
			{
				JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);
				JLoader::import('components.com_tjucm.tables.item', JPATH_ADMINISTRATOR);
				$itemTable = JTable::getInstance('Item', 'TjucmTable', array('dbo', JFactory::getDbo()));
				$itemTable->load($contentId);

				return RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.editownitem', $itemTable->cluster_id);
			}
		}
		else
		{
			return JFactory::getUser()->authorise('core.type.editownitem', 'com_tjucm.type.' . $ucmTypeId);
		}
	}

	public static function canDelete($ucmTypeId, $contentId)
	{
		if (TjucmAccess::hasCluster($ucmTypeId))
		{
			if (RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.deleteallitem'))
			{
				return true;
			}

			// Get com_subusers component status
			$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

			// Check user have permission to edit record of assigned cluster
			if ($subUserExist)
			{
				JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);
				JLoader::import('components.com_tjucm.tables.item', JPATH_ADMINISTRATOR);
				$itemTable = JTable::getInstance('Item', 'TjucmTable', array('dbo', JFactory::getDbo()));
				$itemTable->load($contentId);

				return RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.deleteitem', $itemTable->cluster_id);
			}
		}
		else
		{
			return JFactory::getUser()->authorise('core.type.deleteitem', 'com_tjucm.type.' . $ucmTypeId);
		}
	}

	public static function canDeleteOwn($ucmTypeId, $contentId)
	{
		if (TjucmAccess::hasCluster($ucmTypeId))
		{
			// Get com_subusers component status
			$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

			// Check user have permission to edit record of assigned cluster
			if ($subUserExist)
			{
				JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);
				JLoader::import('components.com_tjucm.tables.item', JPATH_ADMINISTRATOR);
				$itemTable = JTable::getInstance('Item', 'TjucmTable', array('dbo', JFactory::getDbo()));
				$itemTable->load($contentId);

				return RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.deleteownitem', $itemTable->cluster_id);
			}
		}
		else
		{
			return JFactory::getUser()->authorise('core.type.deleteownitem', 'com_tjucm.type.' . $ucmTypeId);
		}
	}

	public static function hasCluster($ucmTypeId, $contentId)
	{
		if (ComponentHelper::getComponent('com_cluster', true)->enabled)
		{
			JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
			$typeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', JFactory::getDbo()));
			$typeTable->load($ucmTypeId);

			JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
			$fieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', JFactory::getDbo()));
			$fieldTable->load(array('client' => $typeTable->unique_identifier, 'type' => 'cluster', 'state' => 1));

			if ($fieldTable->id)
			{
				return true;
			}
		}

		return false;
	}
}
