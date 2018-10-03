<?php
/**
 * @version    SVN: <svn_id>
 * @package    TJUCM
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.controller');

if (!defined('DS'))
{
	define('DS', '/');
}

/**
 * Tjlms Installer
 *
 * @since  1.0.0
 */
class Pkg_UcmInstallerScript
{
	/** @var array The list of extra modules and plugins to install */

	private $installation_queue = array(
	'libraries' => array('techjoomla' => 1));

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
		if ($type == 'update')
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('manifest_cache')))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('element') . ' = ' . $db->quote('pkg_ucm'))
				->where($db->quoteName('type') . ' = ' . $db->quote('package'));

			$db->setQuery($query);
			$result = $db->loadObject();
			$decode = json_decode($result->manifest_cache);
			$this->oldversion = $decode->version;
		}
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
		// $parent is the class calling this method
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
		// Install subextensions
		$status = $this->_installSubextensions($parent);

		// Install Techjoomla Straper
		$straperStatus = $this->_installStraper($parent);

		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root() . '/media/techjoomla_strapper/css/bootstrap.min.css');

		// Show the post-installation page
		$this->_renderPostInstallation($status, $straperStatus, $parent);
	}

	/**
	 * Install strappers
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  void
	 */
	private function _installStraper($parent)
	{
		$src = $parent->getParent()->getPath('source');

		$source = $src . '/strapper';
		$target = JPATH_ROOT . '/media/techjoomla_strapper';

		$haveToInstallStraper = false;

		if (!JFolder::exists($target))
		{
			$haveToInstallStraper = true;
		}
		else
		{
			$straperVersion = array();

			if (JFile::exists($target . '/version.txt'))
			{
				$rawData = JFile::read($target . '/version.txt');
				$info = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			}
			else
			{
				$straperVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}

			$rawData = JFile::read($source . '/version.txt');
			$info = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);

			$haveToInstallStraper = $straperVersion['package']['date']->toUNIX() > $straperVersion['installed']['date']->toUNIX();
		}

		$installedStraper = false;

		if ($haveToInstallStraper)
		{
			$versionSource = 'package';
			$installer = new JInstaller;
			$installedStraper = $installer->install($source);
		}
		else
		{
			$versionSource = 'installed';
		}

		if (!isset($straperVersion))
		{
			$straperVersion = array();

			if (JFile::exists($target . '/version.txt'))
			{
				$rawData = JFile::read($target . '/version.txt');
				$info = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			}
			else
			{
				$straperVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}

			$rawData = JFile::read($source . '/version.txt');
			$info = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);

			$versionSource = 'installed';
		}

		if (!($straperVersion[$versionSource]['date'] instanceof JDate))
		{
			$straperVersion[$versionSource]['date'] = new JDate;
		}

		return array(
			'required'	=> $haveToInstallStraper,
			'installed'	=> $installedStraper,
			'version'	=> $straperVersion[$versionSource]['version'],
			'date'		=> $straperVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}

	/**
	 * Renders the post-installation message
	 *
	 * @param   JInstaller  $status         parent
	 * @param   JInstaller  $straperStatus  parent
	 * @param   JInstaller  $parent         parent
	 *
	 * @return  void
	 */
	private function _renderPostInstallation($status, $straperStatus, $parent)
	{
		$document = JFactory::getDocument();
		JFactory::getLanguage()->load('com_tjucm', JPATH_ADMINISTRATOR, null, true);
?>
	   <?php
		$rows = 1;
?>
	   <link rel="stylesheet" type="text/css" href="<?php
		echo JURI::root() . 'media/techjoomla_strapper/css/bootstrap.min.css';
?>"/>
		<div class="techjoomla-bootstrap" >
		<table class="table-condensed table" width="100%">
			<thead>
				<tr class="row1">
					<th class="title" colspan="2">Extension</th>
					<th>Status</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>
			<tbody>
				<tr class="row2">
					<td class="key" colspan="2"><strong>UCM component</strong></td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>

				<tr class="row2">
					<td class="key" colspan="2">
						<strong>TechJoomla Strapper</strong> [<?php
		echo $straperStatus['date'];
?>]
					</td>
					<td>
						<strong>
							<span style="color: <?php
		echo $straperStatus['required'] ? ($straperStatus['installed'] ? 'green' : 'red') : '#660';
?>; font-weight: bold;">
								<?php
		echo $straperStatus['required'] ? ($straperStatus['installed'] ? 'Installed' : 'Not Installed') : 'Already up-to-date';
?>
							</span>
						</strong>
					</td>
				</tr>
				<!-- LIB INSTALL-->
				<?php
		if (count($status->libraries))
		{
?>
			   <tr class="row1">
					<th>Library</th>
					<th></th>
					<th></th>
					</tr>
				<?php
			foreach ($status->libraries as $libraries)
			{
?>
			   <tr class="row2">
					<td class="key"><?php
				echo ucfirst($libraries['name']);
?></td>
					<td class="key"></td>
					<td><strong style="color: <?php
				echo $libraries['result'] ? "green" : "red";
?>"><?php
				echo $libraries['result'] ? 'Installed' : 'Not installed';
?></strong></td>
				</tr>
				<?php
			}
		}
		?>

			</tbody>
		</table>
		</div>
		<?php
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param   JInstaller  $parent  parent
	 *
	 * @return  JObject The subextension installation status
	 */
	private function _installSubextensions($parent)
	{
		$src = $parent->getParent()->getPath('source');
		$db  = JFactory::getDbo();

		$status          = new JObject;

		// Library installation
		if (count($this->installation_queue['libraries']))
		{
			foreach ($this->installation_queue['libraries'] as $folder => $status1)
			{
				$path = "$src/libraries/$folder";

				$query = $db->getQuery(true)->select('COUNT(*)')
				->from($db->qn('#__extensions'))
				->where('( ' . ($db->qn('name') . ' = ' . $db->q($folder)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($folder)) . ' )')
				->where($db->qn('folder') . ' = ' . $db->q($folder));
				$db->setQuery($query);
				$count = $db->loadResult();

				$installer = new JInstaller;
				$result    = $installer->install($path);

				$status->libraries[] = array(
					'name' => $folder,
					'group' => $folder,
					'result' => $result,
					'status' => $status1
				);

				if ($published && !$count)
				{
					$query = $db->getQuery(true)
					->update($db->qn('#__extensions'))
					->set($db->qn('enabled') . ' = ' . $db->q('1'))
					->where('( ' . ($db->qn('name') . ' = ' . $db->q($folder)) . ' OR ' . ($db->qn('element') . ' = ' . $db->q($folder)) . ' )')
					->where($db->qn('folder') . ' = ' . $db->q($folder));
					$db->setQuery($query);
					$db->query();
				}
			}
		}

		return $status;
	}

	/**
	 * _renderPostUninstallation
	 *
	 * @param   STRING  $status  status of installed extensions
	 * @param   ARRAY   $parent  parent item
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	private function _renderPostUninstallation($status, $parent)
	{
?>
	   <?php
		$rows = 0;
?>
	   <h2><?php
		echo JText::_('TJUCM Uninstallation Status');
?></h2>
		<table class="adminlist">
			<thead>
				<tr>
					<th class="title" colspan="2"><?php
		echo JText::_('Extension');
?></th>
					<th width="30%"><?php
		echo JText::_('Status');
?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>
			<tbody>
				<tr class="row0">
					<td class="key" colspan="2"><?php
		echo 'TJUCM ' . JText::_('Component');
?></td>
					<td><strong style="color: green"><?php
		echo JText::_('Removed');
?></strong></td>
				</tr>
		   </tbody>
		</table>
		<?php
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param   JInstaller  $parent  Parent
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function uninstall($parent)
	{
		// Show the post-uninstallation page
		$this->_renderPostUninstallation($status, $parent);
	}
}
