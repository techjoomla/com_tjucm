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
	 * This define the lanugage contant which you have use in js file.
	 *
	 * @since   1.0
	 * @return   null
	 */
	public static function getLanguageConstantForJs()
	{
		JText::script('COM_TJUCM_ITEMFORM_ALERT', true);
		JText::script('COM_TJUCM_DATES_FIELDS_VALIDATION_ERROR', true);
		JText::script('COM_TJUCM_NUMBER_FIELDS_VALIDATION_ERROR', true);
	}
}
