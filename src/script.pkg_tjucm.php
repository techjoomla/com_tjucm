<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');
jimport('joomla.filesystem.file');

$tjInstallerPath = JPATH_ROOT . '/administrator/manifests/packages/tjucm/tjinstaller.php';

if (JFile::exists(__DIR__ . '/tjinstaller.php'))
{
	include_once __DIR__ . '/tjinstaller.php';
}
elseif (JFile::exists($tjInstallerPath))
{
	include_once $tjInstallerPath;
}

/**
 * TJUCM Installer
 *
 * @since  1.0.0
 */
class Pkg_UcmInstallerScript extends TJInstaller
{
	protected $extensionName = 'TJ-UCM';

	/** @var array The list of extra modules and plugins to install */
	private $oldversion = "";

	/** @var  array  The list of extra modules and plugins to install */
	protected $installationQueue = array (
		'postflight' => array(
			),

			'files' => array(
				'tj_strapper' => 1
			),

			/*plugins => { (folder) => { (element) => (published) }}*/
			'plugins' => array (
			),

			'libraries' => array (
				'techjoomla' => 1
			)
		);

	/** @var  array  The list of extra modules and plugins to uninstall */
	protected $uninstallQueue = array (
		/*plugins => { (folder) => { (element) => (published) }}*/
		'plugins' => array ()
	);

	/** @var array The list of obsolete extra modules and plugins to uninstall when upgrading the component */
	protected $obsoleteExtensionsUninstallationQueue = array (
		// @modules => { (folder) => { (module) }* }*
		'modules' => array (
			'admin' => array (
			),
			'site' => array (
			)
		),
		// @plugins => { (folder) => { (element) }* }*
		'plugins' => array (
		)
	);

	/**
	 * A list of extensions (modules, plugins) to enable after installation. Each item has four values, in this order:
	 * type (plugin, module, ...), name (of the extension), status (0 - unpublish, 1 - publish),
	 * client (0=site, 1=admin), group (for plugins), position (for modules).
	 *
	 * @var array
	 */
	protected $extensionsToEnable = array ();

	/** @var array Obsolete files and folders to remove*/
	private $removeFilesAndFolders = array(
		'files'	=> array(
		),
		'folders' => array(
		)
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @param   JInstaller  $type    type
	 * @param   JInstaller  $parent  parent
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
	}

	/**
	 * method to install the component
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	public function install($parent)
	{
	}

	/**
	 * Runs after update
	 *
	 * @param   JInstaller  $parent  The class calling this method
	 *
	 * @return  void
	 */
	public function update($parent)
	{
	}

	/**
	 * Method to uninstall the component
	 *
	 * @param   JInstaller  $parent  Class calling this method
	 *
	 * @return  void
	 */
	public function uninstall($parent)
	{
	}

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string      $type    install, update or discover_update
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	public function postflight($type, $parent)
	{
	}
}
