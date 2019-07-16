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
defined('_JEXEC') or die;

/**
 * Tjucm Listhelper.
 *
 * @since  1.6
 */
abstract class JHtmlListhelper
{
	/**
	 * Function to toggle
	 *
	 * @param   string  $value  value
	 * @param   string  $view   view
	 * @param   string  $field  field
	 * @param   string  $i      counter
	 *
	 * @return html
	 *
	 * @since 1.0
	 * */
	public static function toggle($value = 0, $view = '', $field = '', $i = '')
	{
		$states = array(
			0 => array('icon-remove', JText::_('Toggle'), 'inactive btn-danger'),
			1 => array('icon-checkmark', JText::_('Toggle'), 'active btn-success'),
		);

		$state  = \Joomla\Utilities\ArrayHelper::getValue($states, (int) $value, $states[0]);
		$text   = '<span aria-hidden="true" class="' . $state[0] . '"></span>';
		$html   = '<a href="#" class="btn btn-micro ' . $state[2] . '"';
		$html  .= 'onclick="return toggleField(\'cb' . $i . '\',\''
		. $view . '.toggle\',\'' . $field . '\')" title="' . JText::_($state[1]) . '">' . $text . '</a>';

		return $html;
	}
}
