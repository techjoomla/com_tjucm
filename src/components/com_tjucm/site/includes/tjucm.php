<?php
/**
 * @package     Tjucm
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;

require_once JPATH_SITE . '/components/com_tjucm/includes/defines.php';

/**
 * Tjucm factory class.
 *
 * This class perform the helpful operation required to Tjucm package
 *
 * @since  __DEPLOY_VERSION__
 */
class TJUCM
{
	/**
	 * Holds the record of the loaded Tjucm classes
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private static $loadedClass = array();

	/**
	 * Holds the record of the component config
	 *
	 * @var    Joomla\Registry\Registry
	 * @since  __DEPLOY_VERSION__
	 */
	private static $config = null;

	/**
	 * Retrieves a table from the table folder
	 *
	 * @param   string  $name    The table file name
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table|boolean object or false on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 **/
	public static function table($name, $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');
		$table = Table::getInstance($name, 'TjucmTable', $config);

		return $table;
	}

	/**
	 * Retrieves a model from the model folder
	 *
	 * @param   string  $name    The model name
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  BaseDatabaseModel|boolean object or false on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 **/
	public static function model($name, $config = array())
	{
		JLoader::import('components.com_tjucm.models.type', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_tjucm.models.types', JPATH_ADMINISTRATOR);
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_tjucm/models', 'TjucmModel');
		$model = BaseDatabaseModel::getInstance($name, 'TjucmModel', $config);

		return $model;
	}

	/**
	 * Magic method to create instance of Tjucm library
	 *
	 * @param   string  $name       The name of the class
	 * @param   mixed   $arguments  Arguments of class
	 *
	 * @return  mixed   return the Object of the respective class if exist OW return false
	 *
	 * @since   __DEPLOY_VERSION__
	 **/
	public static function __callStatic($name, $arguments)
	{
		self::loadClass($name);

		$className = 'Tjucm' . StringHelper::ucfirst($name);

		if (class_exists($className))
		{
			if (method_exists($className, 'getInstance'))
			{
				return call_user_func_array(array($className, 'getInstance'), $arguments);
			}

			return new $className;
		}

		return false;
	}

	/**
	 * Load the class library if not loaded
	 *
	 * @param   string  $className  The name of the class which required to load
	 *
	 * @return  boolean True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 **/
	public static function loadClass($className)
	{
		if (! isset(self::$loadedClass[$className]))
		{
			$className = (string) StringHelper::strtolower($className);

			$path = JPATH_SITE . '/components/com_tjucm/includes/' . $className . '.php';

			include_once $path;

			self::$loadedClass[$className] = true;
		}

		return self::$loadedClass[$className];
	}

	/**
	 * Load the component configuration
	 *
	 * @return  Joomla\Registry\Registry  A Registry object.
	 */
	public static function config()
	{
		if (empty(self::$config))
		{
			self::$config = ComponentHelper::getParams('com_tjucm');
		}

		return self::$config;
	}

	/**
	 * Initializes the css, js and necessary dependencies
	 *
	 * @param   string  $location  The location where the assets needs to load
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function init($location = 'site')
	{
		static $loaded = null;
		$docType = Factory::getDocument()->getType();
		$versionClass = self::Version();

		if (! $loaded[$location] && ($docType == 'html'))
		{
			if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
			{
				JLoader::register('TjStrapper', JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php');
				TjStrapper::loadTjAssets();
			}

			$version = '2.0';
			$input   = Factory::getApplication()->input;
			$view    = $input->get('view', '', 'STRING');

			try
			{
				$version = $versionClass->getMediaVersion();
			}
			catch (Exception $e)
			{
				// Silence is peace :)
			}

			$options = array("version" => $version);

			if ($view == 'itemform')
			{
				HTMLHelper::script('media/com_tjucm/js/com_tjucm.min.js', $options);
				HTMLHelper::script('media/com_tjucm/js/core/class.min.js', $options);
				HTMLHelper::script('media/com_tjucm/js/core/base.min.js', $options);
				HTMLHelper::script('media/com_tjucm/js/services/item.min.js', $options);
				HTMLHelper::script('media/com_tjucm/js/vendor/jquery/jquery.form.js', $options);
				HTMLHelper::script('media/com_tjucm/js/ui/itemform.min.js', $options);
				HTMLHelper::script('media/com_tjucm/js/services/items.min.js', $options);

				HTMLHelper::StyleSheet('media/com_tjucm/css/tjucm.css', $options);
			}

			if ($view == 'item')
			{
				HTMLHelper::script('media/com_tjucm/js/ui/item.js');
			}

			// Load css file on every page
			HTMLHelper::StyleSheet('media/com_tjucm/css/tjucm.css', $options);
			$loaded[$location] = true;
		}
	}
}
