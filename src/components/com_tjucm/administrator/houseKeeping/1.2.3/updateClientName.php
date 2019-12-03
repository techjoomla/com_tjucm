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
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;

/**
 * Migration file for TJ-UCM
 *
 * @since  1.0
 */
class TjHouseKeepingUpdateClientName extends TjModelHouseKeeping
{
	public $title = "Update Types Name";

	public $description = 'Update UCM Types name and name of fields in each Type';

	/**
	 * Subform migration script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function migrate()
	{
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjucm/tables');
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');
		JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_ADMINISTRATOR);

		$result = array();
		$ucmSubFormFieldsConfig = array();

		try
		{
			// Get all the UCM types
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->qn('#__tj_ucm_types'));
			$db->setQuery($query);
			$ucmTypes = $db->loadObjectlist();

			$session = JFactory::getSession();
			$updatedTypes = (empty($session->get('updatedTypes'))) ? array() : $session->get('updatedTypes');

			if (!empty($ucmTypes))
			{
				foreach ($ucmTypes as $ucmType)
				{
					if (in_array($ucmType->id, $updatedTypes))
					{
						continue;
					}

					$ucmTypeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', $db));
					$ucmTypeTable->load($ucmType->id);
					$updatedUniqueIdentifier = 'com_tjucm.' . preg_replace("/[^a-zA-Z0-9]/", "", str_replace('com_tjucm.', '', $ucmTypeTable->unique_identifier));
					$ucmTypeParams = new Registry($ucmType->params);

					// Variable to store the old client of the UCM Type
					$oldClientName = $ucmTypeTable->unique_identifier;
					$ucmTypeTable->unique_identifier = $updatedUniqueIdentifier;

					if ($ucmTypeTable->store())
					{
						// Get all the field groups of the UCM Type
						$query = $db->getQuery(true);
						$query->select('*');
						$query->from($db->qn('#__tjfields_groups'));
						$query->where($db->quoteName('client') . '=' . $db->quote($oldClientName));
						$db->setQuery($query);
						$fieldGroups = $db->loadObjectlist();

						foreach ($fieldGroups as $fieldGroup)
						{
							$tjfieldsGroupTable = JTable::getInstance('Group', 'TjfieldsTable', array('dbo', $db));
							$tjfieldsGroupTable->load($fieldGroup->id);
							$tjfieldsGroupTable->client = $updatedUniqueIdentifier;

							// Update the client of field group in the given UCM Type
							if ($tjfieldsGroupTable->store())
							{
								$query = $db->getQuery(true);
								$query->select('*');
								$query->from($db->qn('#__tjfields_fields'));
								$query->where($db->quoteName('client') . '=' . $db->quote($oldClientName));
								$query->where($db->quoteName('group_id') . '=' . $fieldGroup->id);
								$db->setQuery($query);
								$fields = $db->loadObjectlist();

								foreach ($fields as $field)
								{
									$tjfieldsFieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
									$tjfieldsFieldTable->load($field->id);
									$tjfieldsFieldTable->client = $updatedUniqueIdentifier;

									if ($tjfieldsFieldTable->type == 'cluster')
									{
										$tjfieldsFieldTable->name = str_replace('.', '_', $updatedUniqueIdentifier) . '_clusterclusterid';
									}
									elseif ($tjfieldsFieldTable->type == 'ownership')
									{
										$tjfieldsFieldTable->name = str_replace('.', '_', $updatedUniqueIdentifier) . '_ownershipcreatedby';
									}
									elseif ($tjfieldsFieldTable->type == 'itemcategory')
									{
										$tjfieldsFieldTable->name = str_replace('.', '_', $updatedUniqueIdentifier) . '_itemcategoryitemcategory';
									}
									else
									{
										$tjfieldsFieldTable->name = str_replace('.', '_', $updatedUniqueIdentifier) . '_' .
										strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $field->label));
									}

									$tjfieldsFieldTable->store();

									// Check if field name is unique
									$tjfieldsHelper = new TjfieldsHelper;
									$isUnique = $tjfieldsHelper->checkIfUniqueName($tjfieldsFieldTable->name);

									// If the name of the field is not unique then update the name by appending count to it
									if ($isUnique > 1)
									{
										$count = 0;

										while ($tjfieldsHelper->checkIfUniqueName($tjfieldsFieldTable->name) > 1)
										{
											$count++;
											$tjfieldsFieldTable->name = $tjfieldsFieldTable->name . $count;
										}

										$tjfieldsFieldTable->store();
									}
								}
							}
						}

						// Update client in ucm_data table
						$query = $db->getQuery(true);
						$fields = array($db->quoteName('client') . ' = ' . $db->quote($updatedUniqueIdentifier));
						$conditions = array($db->quoteName('client') . ' = ' . $db->quote($oldClientName));
						$query->update($db->quoteName('#__tj_ucm_data'))->set($fields)->where($conditions);
						$db->setQuery($query);
						$db->execute();

						// Update client in fields_value table
						$query = $db->getQuery(true);
						$query->update($db->quoteName('#__tjfields_fields_value'))->set($fields)->where($conditions);
						$db->setQuery($query);
						$db->execute();

						// Update value of ucmsubform fields in fields_value table
						$query = $db->getQuery(true);
						$fields = array($db->quoteName('value') . ' = ' . $db->quote($updatedUniqueIdentifier));
						$conditions = array($db->quoteName('value') . ' = ' . $db->quote($oldClientName));
						$query->update($db->quoteName('#__tjfields_fields_value'))->set($fields)->where($conditions);
						$db->setQuery($query);
						$db->execute();

						$oldClientParts = explode('.', $oldClientName);
						$newClientParts = explode('.', $updatedUniqueIdentifier);
						$oldFileName = $oldClientParts[1] . 'form_extra.xml';
						$newFileName = $newClientParts[1] . 'form_extra.xml';
						$oldFileNameAdmin = $oldClientParts[1] . '_extra.xml';

						// If the UCM type is of subform type then update the link in the respective field
						if ($ucmTypeParams->get('is_subform'))
						{
							$query = $db->getQuery(true);
							$query->select('*');
							$query->from($db->qn('#__tjfields_fields'));
							$query->where($db->quoteName('params') . ' LIKE ' . $db->quote('%' . $oldFileName . '%'));
							$db->setQuery($query);
							$ucmSubFormFields = $db->loadObjectlist();

							foreach ($ucmSubFormFields as $ucmSubFormField)
							{
								$tjfieldsFieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
								$tjfieldsFieldTable->load($ucmSubFormField->id);
								$tjfieldsFieldTable->params = str_replace($oldFileName, $newFileName, $tjfieldsFieldTable->params);
								$tjfieldsFieldTable->store();
							}
						}

						// If the UCM type is used as data source in related field the we need to update the UCM name in the field params
						if ($ucmTypeParams->get('is_subform'))
						{
							$query = $db->getQuery(true);
							$query->select('*');
							$query->from($db->qn('#__tjfields_fields'));
							$query->where($db->quoteName('params') . ' LIKE ' . $db->quote('%' . $oldClientName . '%'));
							$db->setQuery($query);
							$formFields = $db->loadObjectlist();

							foreach ($formFields as $formField)
							{
								$tjfieldsFieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
								$tjfieldsFieldTable->load($formField->id);
								$fieldParams = new Registry($tjfieldsFieldTable->params);

								if ($fieldParams->get('client') == $oldClientName)
								{
									$fieldParams->set('client', $updatedUniqueIdentifier);
								}

								$tjfieldsFieldTable->params = json_encode($fieldParams);
								$tjfieldsFieldTable->store();
							}
						}

						// Delete the old XML files for the UCM type
						if (File::exists(JPATH_SITE . '/components/com_tjucm/models/forms/' . $oldFileName))
						{
							File::delete(JPATH_SITE . '/components/com_tjucm/models/forms/' . $oldFileName);
						}

						if (File::exists(JPATH_SITE . '/' . 'administrator/components/com_tjucm/models/forms/' . $oldFileNameAdmin))
						{
							File::delete(JPATH_SITE . '/' . 'administrator/components/com_tjucm/models/forms/' . $oldFileNameAdmin);
						}

						// Regenerate the XML files
						$tjfieldsHelper->generateXml(array('client' => $updatedUniqueIdentifier, 'client_type' => $newClientParts[1]));
					}

					$updatedTypes[] = $ucmType->id;
					$session->set('updatedTypes', $updatedTypes);

					$result['status']   = '';
					$result['message']  = "Migration in progress";

					return $result;
				}
			}

			$session->set('updatedTypes', '');

			$result['status']   = true;
			$result['message']  = "Migration successful";
		}
		catch (Exception $e)
		{
			$result['err_code'] = '';
			$result['status']   = false;
			$result['message']  = $e->getMessage();
		}

		return $result;
	}
}
