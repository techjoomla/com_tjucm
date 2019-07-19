<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

// Add Table Path
Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjucm/tables');

/**
 * Routing class from com_tjucm
 *
 * @subpackage  com_tjucm
 *
 * @since       _DEPLOY_VERSION_
 */
class TjUcmRouter extends JComponentRouterBase
{
	private $views = array('itemform', 'items', 'item');

	private $menu_views = array('itemform', 'items');

	/**
	 * Build the route for the com_tjucm component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function build(&$query)
	{
		$segments = array();

		// Get a menu item based on Itemid or currently active
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$db = JFactory::getDbo();

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;

			// If Itemid is there in the URL then we do not need client in the URL
			unset($query['client']);
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_tjucm')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		// Are we dealing with an view for which menu is already created
		if (($menuItem instanceof stdClass)
			&& isset($menuItem->query['view']) && isset($query['view']))
		{
			if ($menuItem->query['view'] == $query['view'] && in_array($query['view'], $this->menu_views))
			{
				unset($query['view']);

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}

				return $segments;
			}
		}

		// If the URL is task URL
		if (isset($query['task']))
		{
			$segments[] = str_replace(".", "-", $query['task']);
			unset($query['task']);
		}

		// Check if view is set.
		if (!isset($query['view']))
		{
			return $segments;
		}

		// Add the view only for normal views for which menu is not created
		$view = $query['view'];
		$segments[] = $view;

		unset($query['view']);

		/* Handle client in URL */
		if (isset($query['client']))
		{
			$ucmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', $db));
			$ucmTypeTable->load(array('unique_identifier' => $query['client']));

			$segments[] = $ucmTypeTable->alias;
			unset($query['client']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$item = $this->menu->getActive();
		$vars = array();
		$db = JFactory::getDbo();

		// Count route segments
		$count = count($segments);

		// If first segment is not the view then its task
		if (!in_array($segments[0], $this->views))
		{
			$vars['task'] = str_replace("-", ".", $segments[0]);
		}
		else
		{
			$vars['view'] = $segments[0];
		}

		if ($count >= 1)
		{
			$ucmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', $db));
			$ucmTypeTable->load(array('alias' => $segments[1]));

			if ($ucmTypeTable->id)
			{
				$vars['client'] = $ucmTypeTable->unique_identifier;
			}
		}

		return $vars;
	}
}
