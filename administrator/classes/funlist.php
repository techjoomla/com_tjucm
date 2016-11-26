<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_pip
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.form');

/**
 * Extra function list.
 *
 * @package     Joomla.site
 * @subpackage  com_tjucm
 *
 * @since       1.0
 */
class TjucmFunList
{
	/**
	 * Function to get data
	 *
	 * @param   STRING  $table         Name of database table
	 * @param   STRING  $selectList    Selected value colume name
	 * @param   STRING  $where         Query where condition
	 * @param   STRING  $returnObject  Selecting data using JDatabase - link https://docs.joomla.org/Selecting_data_using_JDatabase
	 * @param   STRING  $joinType      LEFT, RIGHT etc
	 * @param   STRING  $joinTable     Name of database table
	 *
	 * @return  true
	 *
	 * @since 1.0.0
	 */
	public function getDataValues($table, $selectList = "*", $where = "", $returnObject = "", $joinType = "", $joinTable = "")
	{
		// Ref - link https://docs.joomla.org/Selecting_data_using_JDatabase

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($selectList);
		$query->from($table);

		if ($joinTable)
		{
			$query->join($joinType, $joinTable);
		}

		if ($where)
		{
			$query->where($where);
		}

		$db->setQuery($query);

		return $db->$returnObject();
	}
}
