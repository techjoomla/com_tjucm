<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('list');

/**
 * This Class supports checkout process.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class JFormFieldUcmTypes extends JFormFieldList
{
	public $type = 'ucmtypes';

	/**
	 * Function to get ucm type list
	 *
	 * @return  null
	 *
	 * @since	1.0
	 */
	public function getOptions()
	{
		// Initialize variables.
		$options = array();
		$db	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select($db->quoteName(array("title", "alias")));
		$query->from($db->quoteName('#__tj_ucm_types'));
		$query->where($db->quoteName('state') . '=1');

		// Get the options.
		$db->setQuery($query);

		$ucmTypes = $db->loadObjectList();

		$options = array();

		$options[] = JHtml::_('select.option', '', JText::_('COM_TJUCM_SELECT_UCM_TYPE_DESC'));

		foreach ($ucmTypes as $ucmType)
		{
			$options[] = JHtml::_('select.option', $ucmType->alias, $ucmType->title);
		}

		return $options;
	}
}
