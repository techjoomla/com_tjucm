<?php
/**
 * @version    SVN: <svn_id>
 * @package    TJUCM
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die( 'Restricted access');
jimport('joomla.plugin.plugin');
jimport('joomla.application.component.model');

$lang = JFactory::getLanguage();
$lang->load('com_tjucm', JPATH_ADMINISTRATOR);

/**
 * Base Class for api plugin
 *
 * @package     TJUCM
 * @subpackage  component
 * @since       1.0
 */
class PlgAPITjucm extends ApiPlugin
{
	/**
	 * TjUcm api plugin to load com_api classes
	 *
	 * @param   string  $subject  originalamount
	 * @param   array   $config   coupon_code
	 *
	 * @since   1.0
	 */
	public function __construct($subject, $config = array())
	{
		parent::__construct($subject, $config = array());
		// Load all required helpers.
		$component_path = JPATH_ROOT . '/components/com_tjucm';

		if (!file_exists($component_path))
		{
			return;
		}

		// Load component models
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_tjucm/models');
		// Load component tables
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/tables');
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_tjucm/tables');
		
		ApiResource::addIncludePath(dirname(__FILE__) . '/tjucm');
	}
}
