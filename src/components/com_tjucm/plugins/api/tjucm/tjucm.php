<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die( 'Restricted access');
jimport('joomla.plugin.plugin');
jimport('joomla.application.component.model');

$lang = JFactory::getLanguage();
$lang->load('com_tjucm', JPATH_ADMINISTRATOR);

/**
 * Base Class for api plugin
 *
 * @package     TjUcm
 * @subpackage  Plg_Api_ucm
 * @since       _DEPLOY_VERSION_
 */

class PlgAPITjucm extends ApiPlugin
{
	/**
	 * Tjucm api plugin to load com_api classes
	 *
	 * @param   string  $subject  originalamount
	 * @param   array   $config   coupon_code
	 *
	 * @since   _DEPLOY_VERSION_
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

		ApiResource::addIncludePath(dirname(__FILE__) . '/tjucm');
	}
}
