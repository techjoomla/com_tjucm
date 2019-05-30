<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

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
		$file = 'components.com_tjucm.models.' . strtolower($name);
		JLoader::import($file, JPATH_SITE);
		$model = BaseDatabaseModel::getInstance($name, 'TjucmModel');

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
		$db = Factory::getDbo();
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
		Text::script('COM_TJUCM_ITEMFORM_SUBMIT_ALERT', true);
		Text::script('COM_TJUCM_FIELDS_VALIDATION_ERROR_DATE', true);
		Text::script('COM_TJUCM_FIELDS_VALIDATION_ERROR_NUMBER', true);
		Text::script('COM_TJUCM_MSG_ON_SAVED_FORM', true);
	}

	/**
	 * Get Itemid for menu links
	 *
	 * @param   string  $link  URL to find itemid for
	 *
	 * @return  integer  $itemId
	 */
	public function getItemId($link)
	{
		$app = Factory::getApplication();

		static $ucmItemIds = array();

		// Check in itemids array
		if (!empty($ucmItemIds[$link]))
		{
			return $ucmItemIds[$link];
		}

		$itemId = 0;
		parse_str($link, $parsedLinked);

		if (isset($parsedLinked['view']))
		{
			// For all product menu link
			if ($parsedLinked['view'] == 'itemform' || $parsedLinked['view'] == 'items')
			{
				if (!empty($parsedLinked['client']))
				{
					$menu = $app->getMenu();
					$menuItems = $menu->getItems('link', "index.php?option=com_tjucm&view=" . $parsedLinked['view']);

					foreach ($menuItems as $menuItem)
					{
						$menuParams = $menuItem->params;

						if (!empty($menuParams))
						{
							$ucmTypeAlias = $menuParams->get('ucm_type');

							Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjucm/tables');
							$ucmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', $db));
							$ucmTypeTable->load(array('alias' => $ucmTypeAlias));

							if ($ucmTypeTable->unique_identifier == $parsedLinked['client'])
							{
								return $menuItem->id;
							}
						}
					}
				}
			}
		}

		if (!$itemId)
		{
			if ($app->issite())
			{
				$menu = $app->getMenu();
				$menuItem = $menu->getItems('link', $link, true);

				if ($menuItem)
				{
					$itemId = $menuItem->id;
				}
			}

			if (!$itemId)
			{
				$db = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->quoteName('id'));
				$query->from($db->quoteName('#__menu'));
				$query->where($db->quoteName('link') . ' LIKE ' . $db->Quote($link));
				$query->where($db->quoteName('published') . '=' . $db->Quote(1));
				$query->where($db->quoteName('type') . '=' . $db->Quote('component'));
				$db->setQuery($query);
				$itemId = $db->loadResult();
			}

			if (!$itemId)
			{
				$input = Factory::getApplication()->input;
				$itemId = $input->get('Itemid', 0);
			}
		}

		// Add Itemid and link mapping
		if (empty($ucmItemIds[$link]))
		{
			if (!empty($itemId))
			{
				$ucmItemIds[$link] = $itemId;
			}
		}

		return $itemId;
	}
}
