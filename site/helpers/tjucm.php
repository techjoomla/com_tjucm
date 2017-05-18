<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

/**
 * Class TjucmFrontendHelper
 *
 * @since  1.6
 */
class TjucmHelpersTjucm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$app = JFactory::getApplication();


		$this->client  = JFactory::getApplication()->input->get('client');

		// Get UCM type id from uniquue identifier
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');
		$this->ucmTypeId = $tjUcmModelType->getTypeId($this->client);

		parent::__construct();
	}

	/**
	 * Get an instance of the named model
	 *
	 * @param   string  $name  Model name
	 *
	 * @return null|object
	 */
	public static function getModel($name)
	{
		$model = null;

		// If the file exists, let's
		if (file_exists(JPATH_SITE . '/components/com_tjucm/models/' . strtolower($name) . '.php'))
		{
			require_once JPATH_SITE . '/components/com_tjucm/models/' . strtolower($name) . '.php';
			$model = JModelLegacy::getInstance($name, 'TjucmModel');
		}

		return $model;
	}

	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	/**
	 * Gets the edit permission for an user
	 *
	 * @param   mixed  $item  The item
	 *
	 * @return  bool
	 */
	public static function canUserEdit($item)
	{
		$permission = false;
		$user       = JFactory::getUser();

		if ($user->authorise('core.type.edititem', 'com_tjucm.type.' . $this->ucmTypeId))
		{
			$permission = true;
		}
		else
		{
			if (isset($item->created_by))
			{
				if ($user->authorise('core.type.editownitem', 'com_tjucm.type.' . $this->ucmTypeId) && $item->created_by == $user->id)
				{
					$permission = true;
				}
			}
			else
			{
				$permission = true;
			}
		}

		return $permission;
	}
}
