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
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

JFormHelper::loadFieldClass('list');

/**
 * This Class supports checkout process.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class JFormFieldUcmTypes extends FormFieldList
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
		$db	= Factory::getDbo();
		$query	= $db->getQuery(true);
		$query->select($db->quoteName(array("title", "alias")));
		$query->from($db->quoteName('#__tj_ucm_types'));
		$query->where($db->quoteName('state') . '=1');

		// Get the options.
		$db->setQuery($query);

		$ucmTypes = $db->loadObjectList();

		$options = array();

		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_TJUCM_SELECT_UCM_TYPE_DESC'));

		foreach ($ucmTypes as $ucmType)
		{
			$options[] = HTMLHelper::_('select.option', $ucmType->alias, $ucmType->title);
		}

		return $options;
	}
}
