<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('JPATH_BASE') or die;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldTimeupdated extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'timeupdated';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 *
	 * @since    1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		$old_time_updated = $this->value;
		$hidden           = (boolean) $this->element['hidden'];

		if ($hidden == null || !$hidden)
		{
			if (!strtotime($old_time_updated))
			{
				$html[] = '-';
			}
			else
			{
				$jdate       = new Date($old_time_updated);
				$pretty_date = $jdate->format(Text::_('DATE_FORMAT_LC2'));
				$html[]      = "<div>" . $pretty_date . "</div>";
			}
		}

		$time_updated = Factory::getDate()->toSql();
		$html[]       = '<input type="hidden" name="' . $this->name . '" value="' . $time_updated . '" />';

		return implode($html);
	}
}
