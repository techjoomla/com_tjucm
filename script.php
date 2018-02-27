<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

define('MODIFIED', 1);
define('NOT_MODIFIED', 2);

defined('_JEXEC') or die;

/**
 * Updates the database structure of the component
 *
 * @version  Release: 0.2b
 * @author   Component Creator <support@component-creator.com>
 * @since    0.1b
 */
class Com_TjucmInstallerScript
{
	/**
	 * Method called before install/update the component. Note: This method won't be called during uninstall process.
	 *
	 * @param   string  $type    Type of process [install | update]
	 * @param   mixed   $parent  Object who called this method
	 *
	 * @return boolean True if the process should continue, false otherwise
	 */
	public function preflight($type, $parent)
	{
		$jversion = new JVersion;

		// Installing component manifest file version
		$manifest = $parent->get("manifest");
		$release  = (string) $manifest['version'];

		// Abort if the component wasn't build for the current Joomla version
		if (!$jversion->isCompatible($release))
		{
			JFactory::getApplication()->enqueueMessage(
				JText::_('This component is not compatible with installed Joomla version'),
				'error'
			);

			return false;
		}

		return true;
	}

	/**
	 * Method to install the component
	 *
	 * @param   mixed  $parent  Object who called this method.
	 *
	 * @return void
	 *
	 * @since 0.2b
	 */
	public function install($parent)
	{
		$this->installDb($parent);
		$this->installPlugins($parent);
		$this->installModules($parent);
	}

	/**
	 * Method to update the DB of the component
	 *
	 * @param   mixed  $parent  Object who started the upgrading process
	 *
	 * @return void
	 *
	 * @since 0.2b
	 */
	private function installDb($parent)
	{
		$installation_folder = $parent->getParent()->getPath('source');

		$app = JFactory::getApplication();

		if (function_exists('simplexml_load_file') && file_exists($installation_folder . '/installer/structure.xml'))
		{
			$component_data = simplexml_load_file($installation_folder . '/installer/structure.xml');

			// Check if there are tables to import.
			foreach ($component_data->children() as $table)
			{
				$this->processTable($app, $table);
			}
		}
		else
		{
			if (!function_exists('simplexml_load_file'))
			{
				$app->enqueueMessage(JText::_('This script needs \'simplexml_load_file\' to update the component'));
			}
			else
			{
				$app->enqueueMessage(JText::_('Structure file was not found.'));
			}
		}
	}

	/**
	 * Process a table
	 *
	 * @param   JApplicationCms   $app    Application object
	 * @param   SimpleXMLElement  $table  Table to process
	 *
	 * @return void
	 *
	 * @since 0.2b
	 */
	private function processTable($app, $table)
	{
		$db = JFactory::getDbo();

		$table_added = false;

		if (isset($table['action']))
		{
			switch ($table['action'])
			{
				case 'add':

					// Check if the table exists before create the statement
					if (!$this->existsTable($table['table_name']))
					{
						$create_statement = $this->generateCreateTableStatement($table);
						$db->setQuery($create_statement);

						try
						{
							$db->execute();
							$app->enqueueMessage(
								JText::sprintf(
									'Table `%s` has been successfully created',
									(string) $table['table_name']
								)
							);
							$table_added = true;
						}
						catch (Exception $ex)
						{
							$app->enqueueMessage(
								JText::sprintf(
									'There was an error creating the table `%s`. Error: %s',
									(string) $table['table_name'],
									$ex->getMessage()
								), 'error'
							);
						}
					}
					break;
				case 'change':

					// Check if the table exists first to avoid errors.
					if ($this->existsTable($table['old_name']) && !$this->existsTable($table['new_name']))
					{
						try
						{
							$db->renameTable($table['old_name'], $table['new_name']);
							$app->enqueueMessage(
								JText::sprintf(
									'Table `%s` was successfully renamed to `%s`',
									$table['old_name'],
									$table['new_name']
								)
							);
						}
						catch (Exception $ex)
						{
							$app->enqueueMessage(
								JText::sprintf(
									'There was an error renaming the table `%s`. Error: %s',
									$table['old_name'],
									$ex->getMessage()
								), 'error'
							);
						}
					}
					else
					{
						if (!$this->existsTable($table['table_name']))
						{
							// If the table does not exists, let's create it.
							$create_statement = $this->generateCreateTableStatement($table);
							$db->setQuery($create_statement);

							try
							{
								$db->execute();
								$app->enqueueMessage(
									JText::sprintf('Table `%s` has been successfully created', $table['table_name'])
								);
								$table_added = true;
							}
							catch (Exception $ex)
							{
								$app->enqueueMessage(
									JText::sprintf(
										'There was an error creating the table `%s`. Error: %s',
										$table['table_name'],
										$ex->getMessage()
									), 'error'
								);
							}
						}
					}
					break;
				case 'remove':

					try
					{
						// We make sure that the table will be removed only if it exists specifying ifExists argument as true.
						$db->dropTable((string) $table['table_name'], true);
						$app->enqueueMessage(
							JText::sprintf('Table `%s` was successfully deleted', $table['table_name'])
						);
					}
					catch (Exception $ex)
					{
						$app->enqueueMessage(
							JText::sprintf(
								'There was an error deleting Table `%s`. Error: %s',
								$table['table_name'], $ex->getMessage()
							), 'error'
						);
					}

					break;
			}
		}

		// If the table wasn't added before, let's process the fields of the table
		if (!$table_added)
		{
			if ($this->existsTable($table['table_name']))
			{
				$this->executeFieldsUpdating($app, $table);
			}
		}
	}

	/**
	 * Checks if a certain exists on the current database
	 *
	 * @param   string  $table_name  Name of the table
	 *
	 * @return boolean True if it exists, false if it does not.
	 */
	private function existsTable($table_name)
	{
		$db = JFactory::getDbo();

		$table_name = str_replace('#__', $db->getPrefix(), (string) $table_name);

		return in_array($table_name, $db->getTableList());
	}

	/**
	 * Generates a 'CREATE TABLE' statement for the tables passed by argument.
	 *
	 * @param   SimpleXMLElement  $table  Table of the database
	 *
	 * @return string 'CREATE TABLE' statement
	 */
	private function generateCreateTableStatement($table)
	{
		$create_table_statement = '';

		if (isset($table->field))
		{
			$fields = $table->children();

			$fields_definitions = array();
			$indexes            = array();

			$db = JFactory::getDbo();

			foreach ($fields as $field)
			{
				$field_definition = $this->generateColumnDeclaration($field);

				if ($field_definition !== false)
				{
					$fields_definitions[] = $field_definition;
				}

				if ($field['index'] == 'index')
				{
					$indexes[] = $field['field_name'];
				}
			}

			foreach ($indexes as $index)
			{
				$fields_definitions[] = JText::sprintf(
					'INDEX %s (%s ASC)',
					$db->quoteName((string) $index), $index
				);
			}

			$fields_definitions[]   = 'PRIMARY KEY (`id`)';
			$create_table_statement = JText::sprintf(
				'CREATE TABLE IF NOT EXISTS %s (%s)',
				$table['table_name'],
				implode(',', $fields_definitions)
			);
		}

		return $create_table_statement;
	}

	/**
	 * Generate a column declaration
	 *
	 * @param   SimpleXMLElement  $field  Field data
	 *
	 * @return string Column declaration
	 */
	private function generateColumnDeclaration($field)
	{
		$db        = JFactory::getDbo();
		$col_name  = $db->quoteName((string) $field['field_name']);
		$data_type = $this->getFieldType($field);

		if ($data_type !== false)
		{
			$default_value = (isset($field['default'])) ? 'DEFAULT ' . $field['default'] : '';

			$other_data = '';

			if (isset($field['is_autoincrement']) && $field['is_autoincrement'] == 1)
			{
				$other_data .= ' AUTO_INCREMENT';
			}

			$comment_value = (isset($field['description'])) ? 'COMMENT ' . $db->quote((string) $field['description']) : '';

			return JText::sprintf(
				'%s %s NOT NULL %s %s %s', $col_name, $data_type,
				$default_value, $other_data, $comment_value
			);
		}

		return false;
	}

	/**
	 * Generates SQL field type of a field.
	 *
	 * @param   SimpleXMLElement  $field  Field information
	 *
	 * @return  mixed SQL string data type, false on failure.
	 */
	private function getFieldType($field)
	{
		$data_type = (string) $field['field_type'];

		if (isset($field['field_length']) && ($this->allowsLengthField($data_type) || $data_type == 'ENUM'))
		{
			$data_type .= '(' . (string) $field['field_length'] . ')';
		}

		return (!empty($data_type)) ? $data_type : false;
	}

	/**
	 * Check if a SQL type allows length values.
	 *
	 * @param   string  $field_type  SQL type
	 *
	 * @return boolean True if it allows length values, false if it does not.
	 */
	private function allowsLengthField($field_type)
	{
		$allow_length = array(
			'INT',
			'VARCHAR',
			'CHAR',
			'TINYINT',
			'SMALLINT',
			'MEDIUMINT',
			'INTEGER',
			'BIGINT',
			'FLOAT',
			'DOUBLE',
			'DECIMAL',
			'NUMERIC'
		);

		return (in_array((string) $field_type, $allow_length));
	}

	/**
	 * Updates all the fields related to a table.
	 *
	 * @param   JApplicationCms   $app    Application Object
	 * @param   SimpleXMLElement  $table  Table information.
	 *
	 * @return void
	 */
	private function executeFieldsUpdating($app, $table)
	{
		if (isset($table->field))
		{
			foreach ($table->children() as $field)
			{
				$table_name = (string) $table['table_name'];

				$this->processField($app, $table_name, $field);
			}
		}
	}

	/**
	 * Process a certain field.
	 *
	 * @param   JApplicationCms   $app         Application object
	 * @param   string            $table_name  The name of the table that contains the field.
	 * @param   SimpleXMLElement  $field       Field Information.
	 *
	 * @return void
	 */
	private function processField($app, $table_name, $field)
	{
		$db = JFactory::getDbo();

		if (isset($field['action']))
		{
			switch ($field['action'])
			{
				case 'add':
					$result = $this->addField($table_name, $field);

					if ($result === MODIFIED)
					{
						$app->enqueueMessage(
							JText::sprintf('Field `%s` has been successfully added', $field['field_name'])
						);
					}
					else
					{
						if ($result !== NOT_MODIFIED)
						{
							$app->enqueueMessage(
								JText::sprintf(
									'There was an error adding the field `%s`. Error: %s',
									$field['field_name'], $result
								), 'error'
							);
						}
					}
					break;
				case 'change':

					if (isset($field['old_name']) && isset($field['new_name']))
					{
						if ($this->existsField($table_name, $field['old_name']))
						{
							$renaming_statement = JText::sprintf(
								'ALTER TABLE %s CHANGE %s %s %s',
								$table_name, $db->quoteName($field['old_name']->__toString()),
								$db->quoteName($field['new_name']->__toString()),
								$this->getFieldType($field)
							);
							$db->setQuery($renaming_statement);

							try
							{
								$db->execute();
								$app->enqueueMessage(
									JText::sprintf('Field `%s` has been successfully modified', $field['old_name'])
								);
							}
							catch (Exception $ex)
							{
								$app->enqueueMessage(
									JText::sprintf(
										'There was an error modifying the field `%s`. Error: %s',
										$field['field_name'],
										$ex->getMessage()
									), 'error'
								);
							}
						}
						else
						{
							$result = $this->addField($table_name, $field);

							if ($result === MODIFIED)
							{
								$app->enqueueMessage(
									JText::sprintf('Field `%s` has been successfully modified', $field['field_name'])
								);
							}
							else
							{
								if ($result !== NOT_MODIFIED)
								{
									$app->enqueueMessage(
										JText::sprintf(
											'There was an error modifying the field `%s`. Error: %s',
											$field['field_name'], $result
										), 'error'
									);
								}
							}
						}
					}
					else
					{
						$result = $this->addField($table_name, $field);

						if ($result === MODIFIED)
						{
							$app->enqueueMessage(
								JText::sprintf('Field `%s` has been successfully added', $field['field_name'])
							);
						}
						else
						{
							if ($result !== NOT_MODIFIED)
							{
								$app->enqueueMessage(
									JText::sprintf(
										'There was an error adding the field `%s`. Error: %s',
										$field['field_name'], $result
									), 'error'
								);
							}
						}
					}

					break;
				case 'remove':

					// Check if the field exists first to prevent issue removing the field
					if ($this->existsField($table_name, $field['field_name']))
					{
						$drop_statement = JText::sprintf(
							'ALTER TABLE %s DROP COLUMN %s',
							$table_name, $field['field_name']
						);
						$db->setQuery($drop_statement);

						try
						{
							$db->execute();
							$app->enqueueMessage(
								JText::sprintf('Field `%s` has been successfully deleted', $field['field_name'])
							);
						}
						catch (Exception $ex)
						{
							$app->enqueueMessage(
								JText::sprintf(
									'There was an error deleting the field `%s`. Error: %s',
									$field['field_name'],
									$ex->getMessage()
								), 'error'
							);
						}
					}

					break;
			}
		}
		else
		{
			$result = $this->addField($table_name, $field);

			if ($result === MODIFIED)
			{
				$app->enqueueMessage(
					JText::sprintf('Field `%s` has been successfully added', $field['field_name'])
				);
			}
			else
			{
				if ($result !== NOT_MODIFIED)
				{
					$app->enqueueMessage(
						JText::sprintf(
							'There was an error adding the field `%s`. Error: %s',
							$field['field_name'], $result
						), 'error'
					);
				}
			}
		}
	}

	/**
	 * Add a field if it does not exists or modify it if it does.
	 *
	 * @param   string            $table_name  Table name
	 * @param   SimpleXMLElement  $field       Field Information
	 *
	 * @return mixed Constant on success(self::$MODIFIED | self::$NOT_MODIFIED), error message if an error occurred
	 */
	private function addField($table_name, $field)
	{
		$db = JFactory::getDbo();

		$query_generated = false;

		// Check if the field exists first to prevent issues adding the field
		if ($this->existsField($table_name, $field['field_name']))
		{
			if ($this->needsToUpdate($table_name, $field))
			{
				$change_statement = $this->generateChangeFieldStatement($table_name, $field);
				$db->setQuery($change_statement);
				$query_generated = true;
			}
		}
		else
		{
			$add_statement = $this->generateAddFieldStatement($table_name, $field);
			$db->setQuery($add_statement);
			$query_generated = true;
		}

		if ($query_generated)
		{
			try
			{
				$db->execute();

				return MODIFIED;
			}
			catch (Exception $ex)
			{
				return $ex->getMessage();
			}
		}

		return NOT_MODIFIED;
	}

	/**
	 * Checks if a field exists on a table
	 *
	 * @param   string  $table_name  Table name
	 * @param   string  $field_name  Field name
	 *
	 * @return boolean True if exists, false if it do
	 */
	private function existsField($table_name, $field_name)
	{
		$db = JFactory::getDbo();

		return in_array((string) $field_name, array_keys($db->getTableColumns($table_name)));
	}

	/**
	 * Check if a field needs to be updated.
	 *
	 * @param   string            $table_name  Table name
	 * @param   SimpleXMLElement  $field       Field information
	 *
	 * @return boolean True if the field has to be updated, false otherwise
	 */
	private function needsToUpdate($table_name, $field)
	{
		$db = JFactory::getDbo();

		$query = JText::sprintf(
			'SHOW FULL COLUMNS FROM `%s` WHERE Field LIKE %s', $table_name, $db->quote((string) $field['field_name'])
		);
		$db->setQuery($query);

		$field_info = $db->loadObject();

		if (strripos($field_info->Type, $this->getFieldType($field)) === false)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Generates an change column statement
	 *
	 * @param   string            $table_name  Table name
	 * @param   SimpleXMLElement  $field       Field Information
	 *
	 * @return string Change column statement
	 */
	private function generateChangeFieldStatement($table_name, $field)
	{
		$column_declaration = $this->generateColumnDeclaration($field);

		return JText::sprintf('ALTER TABLE %s MODIFY %s', $table_name, $column_declaration);
	}

	/**
	 * Generates an add column statement
	 *
	 * @param   string            $table_name  Table name
	 * @param   SimpleXMLElement  $field       Field Information
	 *
	 * @return string Add column statement
	 */
	private function generateAddFieldStatement($table_name, $field)
	{
		$column_declaration = $this->generateColumnDeclaration($field);

		return JText::sprintf('ALTER TABLE %s ADD %s', $table_name, $column_declaration);
	}

	/**
	 * Installs plugins for this component
	 *
	 * @param   mixed  $parent  Object who called the install/update method
	 *
	 * @return void
	 */
	private function installPlugins($parent)
	{
		$installation_folder = $parent->getParent()->getPath('source');
		$app                 = JFactory::getApplication();

		/* @var $plugins SimpleXMLElement */
		$plugins = $parent->get("manifest")->plugins;

		if (!empty($plugins))
		{
			if (count($plugins->children()))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				foreach ($plugins->children() as $plugin)
				{
					$pluginName  = (string) $plugin['plugin'];
					$pluginGroup = (string) $plugin['group'];
					$path        = $installation_folder . '/plugins/' . $pluginGroup . '/' . $pluginName;
					$installer   = new JInstaller;

					if (!$this->isAlreadyInstalled('plugin', $pluginName, $pluginGroup))
					{
						$result = $installer->install($path);
					}
					else
					{
						$result = $installer->update($path);
					}

					if ($result)
					{
						$app->enqueueMessage('Plugin ' . $pluginName . ' was installed successfully');
					}
					else
					{
						$app->enqueueMessage('There was an issue installing the plugin ' . $pluginName,
							'error');
					}

					$query
						->clear()
						->update('#__extensions')
						->set('enabled = 1')
						->where(
							array(
								'type LIKE ' . $db->quote('plugin'),
								'element LIKE ' . $db->quote($pluginName),
								'folder LIKE ' . $db->quote($pluginGroup)
							)
						);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	/**
	 * Check if an extension is already installed in the system
	 *
	 * @param   string  $type    Extension type
	 * @param   string  $name    Extension name
	 * @param   mixed   $folder  Extension folder(for plugins)
	 *
	 * @return boolean
	 */
	private function isAlreadyInstalled($type, $name, $folder = null)
	{
		$result = false;

		switch ($type)
		{
			case 'plugin':
				$result = file_exists(JPATH_PLUGINS . '/' . $folder . '/' . $name);
				break;
			case 'module':
				$result = file_exists(JPATH_SITE . '/modules/' . $name);
				break;
		}

		return $result;
	}

	/**
	 * Installs plugins for this component
	 *
	 * @param   mixed  $parent  Object who called the install/update method
	 *
	 * @return void
	 */
	private function installModules($parent)
	{
		$installation_folder = $parent->getParent()->getPath('source');
		$app                 = JFactory::getApplication();

		if (!empty($parent->get("manifest")->modules))
		{
			$modules = $parent->get("manifest")->modules;

			if (count($modules->children()))
			{
				foreach ($modules->children() as $module)
				{
					$moduleName = (string) $module['module'];
					$path       = $installation_folder . '/modules/' . $moduleName;
					$installer  = new JInstaller;

					if (!$this->isAlreadyInstalled('module', $moduleName))
					{
						$result = $installer->install($path);
					}
					else
					{
						$result = $installer->update($path);
					}

					if ($result)
					{
						$app->enqueueMessage('Module ' . $moduleName . ' was installed successfully');
					}
					else
					{
						$app->enqueueMessage('There was an issue installing the module ' . $moduleName,
							'error');
					}
				}
			}
		}
	}

	/**
	 * Method to update the component
	 *
	 * @param   mixed  $parent  Object who called this method.
	 *
	 * @return void
	 */
	public function update($parent)
	{
		$this->installDb($parent);
		$this->installPlugins($parent);
		$this->installModules($parent);
		$this->fixDatabaseOnUpdate();
	}

	/**
	 * Method to fix database on update
	 *
	 * @return void
	 */
	public function fixDatabaseOnUpdate()
	{
		$field_array = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query = "SHOW COLUMNS FROM `#__tj_ucm_data`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('client', $field_array))
		{
			$query = "ALTER TABLE `#__tj_ucm_data` ADD COLUMN `client` VARCHAR(255) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR . JText::_('Unable to Alter #__tj_ucm_data table. (While adding filterable client )') . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		if (!in_array('draft', $field_array))
		{
			$query = "ALTER TABLE `#__tj_ucm_data` ADD COLUMN `draft` TINYINT(1) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR . JText::_('Unable to Alter #__tj_ucm_data table. (While adding draft )') . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}
	}

	/**
	 * Method to uninstall the component
	 *
	 * @param   mixed  $parent  Object who called this method.
	 *
	 * @return void
	 */
	public function uninstall($parent)
	{
		$this->uninstallPlugins($parent);
		$this->uninstallModules($parent);
	}

	/**
	 * Uninstalls plugins
	 *
	 * @param   mixed  $parent  Object who called the uninstall method
	 *
	 * @return void
	 */
	private function uninstallPlugins($parent)
	{
		$app     = JFactory::getApplication();
		$plugins = $parent->get("manifest")->plugins;

		if (count($plugins->children()))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			foreach ($plugins->children() as $plugin)
			{
				$pluginName  = (string) $plugin['plugin'];
				$pluginGroup = (string) $plugin['group'];
				$query
					->clear()
					->select('extension_id')
					->from('#__extensions')
					->where(
						array(
							'type LIKE ' . $db->quote('plugin'),
							'element LIKE ' . $db->quote($pluginName),
							'folder LIKE ' . $db->quote($pluginGroup)
						)
					);
				$db->setQuery($query);
				$extension = $db->loadResult();

				if (!empty($extension))
				{
					$installer = new JInstaller;
					$result    = $installer->uninstall('plugin', $extension);

					if ($result)
					{
						$app->enqueueMessage('Plugin ' . $pluginName . ' was uninstalled successfully');
					}
					else
					{
						$app->enqueueMessage('There was an issue uninstalling the plugin ' . $pluginName,
							'error');
					}
				}
			}
		}
	}

	/**
	 * Uninstalls plugins
	 *
	 * @param   mixed  $parent  Object who called the uninstall method
	 *
	 * @return void
	 */
	private function uninstallModules($parent)
	{
		$app = JFactory::getApplication();

		if (!empty($parent->get("manifest")->modules))
		{
			$modules = $parent->get("manifest")->modules;

			if (count($modules->children()))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				foreach ($modules->children() as $plugin)
				{
					$moduleName = (string) $plugin['module'];
					$query
						->clear()
						->select('extension_id')
						->from('#__extensions')
						->where(
							array(
								'type LIKE ' . $db->quote('module'),
								'element LIKE ' . $db->quote($moduleName)
							)
						);
					$db->setQuery($query);
					$extension = $db->loadResult();

					if (!empty($extension))
					{
						$installer = new JInstaller;
						$result    = $installer->uninstall('module', $extension);

						if ($result)
						{
							$app->enqueueMessage('Module ' . $moduleName . ' was uninstalled successfully');
						}
						else
						{
							$app->enqueueMessage('There was an issue uninstalling the module ' . $moduleName,
								'error');
						}
					}
				}
			}
		}
	}
}
