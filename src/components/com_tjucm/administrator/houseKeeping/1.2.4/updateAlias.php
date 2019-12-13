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
defined('_JEXEC') or die('Restricted access');
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;

/**
 * Migration file for TJ-UCM
 *
 * @since  1.0
 */
class TjHouseKeepingUpdateAlias extends TjModelHouseKeeping
{
	public $title = "Update Types Alias";

	public $description = 'Update UCM Types alias';

	/**
	 * Subform migration script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function migrate()
	{
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjucm/tables');
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_menus/tables');

		JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_ADMINISTRATOR);

		// TJ-Fields helper object
		$tjfieldsHelper = new TjfieldsHelper;

		$result = array();
		$ucmSubFormFieldsConfig = array();

		try
		{
			// Get all the UCM types
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->qn('#__tj_ucm_types'));
			$db->setQuery($query);
			$ucmTypes = $db->loadObjectlist();

			$session = JFactory::getSession();
			$updatedTypes = (empty($session->get('updatedTypes'))) ? array() : $session->get('updatedTypes');

			if (!empty($ucmTypes))
			{
				foreach ($ucmTypes as $ucmType)
				{
					if (in_array($ucmType->id, $updatedTypes))
					{
						continue;
					}

					$ucmTypeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', $db));
					$ucmTypeTable->load($ucmType->id);

					$updatedAlias = JFilterOutput::stringURLSafe($ucmTypeTable->alias);
					$oldAlias = $ucmTypeTable->alias;
					$ucmTypeTable->alias = $updatedAlias;
					$ucmTypeTable->store();

					$updatedTypes[] = $ucmType->id;
					$session->set('updatedTypes', $updatedTypes);

					$result['status']   = '';
					$result['message']  = "Migration in progress";
				}
			}

			// Menus
			$query->from($db->quoteName('#__menu'));
			$query->where("link" . "=" . "'index.php?option=com_tjucm&view=itemform'" . "||" . "link" . "=" . "'index.php?option=com_tjucm&view=items'");
			$db->setQuery($query);
			$menuItems = $db->loadObjectlist();

			if (!empty($menuItems))
			{
				foreach ($menuItems as $menuItem)
				{
					$menuItemTable = JTable::getInstance('Menu', 'MenusTable', array('dbo', $db));
					$menuItemTable->load($menuItem->id);
					$oldparams = json_decode($menuItemTable->params);
					$oldparams->ucm_type  = JFilterOutput::stringURLSafe($oldparams->ucm_type);
					$menuItemTable->params = json_encode($oldparams);
					$menuItemTable->store();
					$updatedTypes[] = $ucmType->id;
					$session->set('updatedTypes', $updatedTypes);
					$result['status']   = '';
					$result['message']  = "Migration in progress";
				}
			}

			$session->set('updatedTypes', '');
			$result['status']   = true;
			$result['message']  = "Migration successful";
		}
		catch (Exception $e)
		{
			$result['err_code'] = '';
			$result['status']   = false;
			$result['message']  = $e->getMessage();
		}
		return $result;
	}
}
