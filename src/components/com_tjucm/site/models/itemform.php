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
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

require_once JPATH_SITE . "/components/com_tjfields/filterFields.php";

use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use TJQueue\Admin\TJQueueProduce;

if (ComponentHelper::getComponent('com_tjqueue', true)->enabled)
{
	jimport('tjqueueproduce', JPATH_SITE . '/administrator/components/com_tjqueue/libraries');
}

JLoader::register('TjucmAccess', JPATH_SITE . '/components/com_tjucm/includes/access.php');

/**
 * Tjucm model.
 *
 * @since  1.0
 */
class TjucmModelItemForm extends JModelAdmin
{
	private $item = null;

	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.0
	 */
	protected $text_prefix = 'COM_TJUCM';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_tjucm.item';

	private $client = '';

	// Use imported Trait in model
	use TjfieldsFilterField;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('com_tjucm');
		$user = JFactory::getUser();

		// Load state from the request.
		$id = $app->input->getInt('id');

		if (!empty($id))
		{
			$this->setState('item.id', $id);
		}

		// Get UCM type id from uniquue identifier
		$ucmType = $app->input->get('client', '');

		if (empty($ucmType))
		{
			// Get the active item
			$menuitem   = $app->getMenu()->getActive();

			// Get the params
			$menuparams = $menuitem->params;

			if (!empty($menuparams))
			{
				$ucm_type   = $menuparams->get('ucm_type');

				if (!empty($ucm_type))
				{
					JLoader::import('components.com_tjfields.tables.type', JPATH_ADMINISTRATOR);
					$ucmTypeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', JFactory::getDbo()));
					$ucmTypeTable->load(array('alias' => $ucm_type));
					$ucmType = $ucmTypeTable->unique_identifier;
				}
			}
		}

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');
		$ucmId = $tjUcmModelType->getTypeId($ucmType);

		$this->setState('ucmType.id', $ucmId);

		// Check published state
		if ((!TjucmAccess::canEdit($ucmId, $id)) && (!TjucmAccess::canEditOwn($ucmId, $id)) && (!TjucmAccess::canEditState($ucmId, $id)))
		{
			$this->setState('filter.published', 1);
			$this->setState('fileter.archived', 2);
		}

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']) && !empty($params_array['item_id']))
		{
			$this->setState('item.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function &getData($id = null)
	{
		$user = JFactory::getUser();

		$this->item = false;

		if (empty($id))
		{
			$id = $this->getState('item.id');
		}

		// Get UCM type id (Get if user is autorised to edit the items for this UCM type)
		$ucmTypeId = $this->getState('ucmType.id');
		$canEdit = TjucmAccess::canEdit($ucmTypeId, $id);
		$canEditOwn = TjucmAccess::canEditOwn($ucmTypeId, $id);
		$canCreate = TjucmAccess::canCreate($ucmTypeId);

		// Get a level row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		if ($table !== false && $table->load($id))
		{
			// Check published state.
			$published = $this->getState('filter.published');
			$archived = $this->getState('filter.archived');

			if (is_numeric($published))
			{
				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($table->state != $published) && ($table->state != $archived)))
				{
					return JError::raiseError(404, JText::_('COM_TJUCM_ITEM_DOESNT_EXIST'));
				}
			}

			// Convert the JTable to a clean JObject.
			$properties  = $table->getProperties(1);

			$properties['params'] = clone $this->getState('params');

			$this->item = ArrayHelper::toObject($properties, 'JObject');

			$this->item->params->set('access-view', false);

			if (empty($this->item->id))
			{
				if ($canCreate)
				{
					$this->item->params->set('access-view', true);
				}
			}
			else
			{
				if ($this->item->created_by == JFactory::getUser()->id)
				{
					if ($canEditOwn || $canEdit)
					{
						$this->item->params->set('access-view', true);
					}
				}
				else
				{
					if ($canEdit)
					{
						$this->item->params->set('access-view', true);
					}
				}
			}
		}
		else
		{
			return JError::raiseError(404, JText::_('COM_TJUCM_ITEM_DOESNT_EXIST'));
		}

		return $this->item;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.0
	 */
	public function getTable($type = 'Item', $prefix = 'TjucmTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get an array of data items
	 *
	 * @param   string  $client  client
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function setClient($client)
	{
		$this->client = $client;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_tjucm.itemform', 'itemform',
			array('control' => 'jform',
				'load_data' => $loadData,
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the field form object.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.2.2
	 */
	public function getFieldForm($data = array(), $loadData = true)
	{
		// Path of empty form XML to create form object dynamically
		$formPath = JPATH_SITE . '/components/com_tjucm/models/forms/fielddata.xml';

		// Get the form.
		$form = $this->loadForm(
			array_key_first($data), $formPath,
			array('control' => 'jform',
				'load_data' => $loadData,
			)
		);

		if (empty($form))
		{
			return false;
		}
		else
		{
			$form->addFieldPath('administrator/components/com_tjfields/models/fields');
			$form->addRulePath('administrator/components/com_tjfields/models/rules');

			$fieldName = array_key_first($data);
			$fieldNamePart = explode('_', str_replace('com_tjucm_', '', $fieldName));
			unset($fieldNamePart[array_key_last($fieldNamePart)]);
			$parentFormPath = JPATH_SITE . "/administrator/components/com_tjucm/models/forms/" . implode("_", $fieldNamePart) . "_extra.xml";

			// Get parent form.
			$parentForm = $this->loadForm(
				'com_tjucm.itemform', $parentFormPath,
				array('control' => 'jform',
					'load_data' => false,
				)
			);

			// Get the field XML from parent form
			$fieldXml = $parentForm->getFieldXml($fieldName);

			// Set the field XML to the field form
			$form->setField($fieldXml);
			$form->setvalue($fieldName, '', $data[$fieldName]);
		}

		return $form;
	}

	/**
	 * Method to get the type form object.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.2.2
	 */
	public function getTypeForm($data = array(), $loadData = true)
	{
		$draft = isset($data['draft']) ? $data['draft'] : 0;
		$client = $data['client'];
		$contentId = empty($data['id']) ? '' : $data['id'];
		$clientPart = explode(".", $client);

		$data = array();
		$data['clientComponent'] = $clientPart[0];
		$data['view'] = $clientPart[1];
		$data['client'] = $client;
		$data['content_id'] = $contentId;
		$data['layout'] = 'edit';

		$form = $this->getFormObject($data);

		// If data is being saved in draft mode then dont check if the fields are required
		if ($draft)
		{
			$fieldSets = $form->getFieldsets();

			foreach ($fieldSets as $fieldset)
			{
				foreach ($form->getFieldset($fieldset->name) as $field)
				{
					// Remove required attribute from the subform fields in case of draft save
					if ($field->type == 'Subform' || $field->type == 'Ucmsubform')
					{
						$subForm = $field->loadSubForm();
						$subFormFieldSets = $subForm->getFieldsets();

						foreach ($subFormFieldSets as $subFormFieldSet)
						{
							foreach ($subForm->getFieldset($subFormFieldSet->name) as $subFormField)
							{
								$subForm->setFieldAttribute($subFormField->fieldname, 'required', false);
							}
						}
					}

					$form->setFieldAttribute($field->fieldname, 'required', false);
				}
			}
		}

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the type section form object.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.2.2
	 */
	public function getSectionForm($data = array(), $loadData = true)
	{
		if (empty($data['client']) || empty($data['section']))
		{
			return false;
		}

		$section = $data['section'];
		$client  = $data['client'];
		$clientPart = explode(".", $client);

		$data = array();
		$data['clientComponent'] = $clientPart[0];
		$data['view'] = $clientPart[1];
		$data['client'] = $client;
		$data['layout'] = 'edit';

		$parentForm = $this->getFormObject($data);

		// Create xml with the fieldset of provided section
		$newXML = new SimpleXMLElement('<form></form>');
		$newXmlFilePath = JPATH_SITE . '/components/com_tjucm/models/forms/tempfieldsetform.xml';

		// Get the fieldset XML from parent form
		$formXml = $parentForm->getXml();
		$fieldsetXml = $formXml->xpath('//fieldset[@name="' . $section . '" and not(ancestor::field/form/*)]');

		if ($fieldsetXml[0] instanceof \SimpleXMLElement)
		{
			$newFieldsetXml = $newXML->addChild('fieldset');

			foreach ($fieldsetXml[0]->children() as $child)
			{
				$fieldXml = $newFieldsetXml->addChild('field');

				foreach ($child->attributes() as $attributeName => $attributeValue)
				{
					$fieldXml->addAttribute($attributeName, $attributeValue);
				}
			}
		}

		$newXML->asXML($newXmlFilePath);

		// Get parent form.
		$sectionForm = $this->loadForm(
			'com_tjucm.itemform.section', $newXmlFilePath,
			array('control' => 'jform',
				'load_data' => false,
			)
		);

		// Delete temp xml once its object is created
		if (JFile::exists($newXmlFilePath))
		{
			JFile::delete($newXmlFilePath);
		}

		if (empty($sectionForm))
		{
			return false;
		}

		return $sectionForm;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_tjucm.edit.item.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	public function save($data)
	{
		$user = empty($data['created_by']) ? Factory::getUser() : Factory::getUser($data['created_by']);

		// Guest users are not allowed to add the records
		if (empty($user->id))
		{
			$this->setError(JText::_('COM_TJUCM_FORM_SAVE_FAILED_AUTHORIZATION_ERROR'));

			return false;
		}

		if (empty($data['id']))
		{
			// Set the state of record as per UCM type config
			$typeTable = $this->getTable('type');
			$typeTable->load(array('unique_identifier' => $data['client']));
			$typeParams = new Registry($typeTable->params);
			$data['state'] = $typeParams->get('publish_items', 0);
		}

		// Get instance of UCM type table
		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$tjUcmTypeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));

		// Check and assign valid client and type_id to the record
		if (!empty($data['type_id']) || !empty($data['client']))
		{
			if ($data['client'] != '')
			{
				$tjUcmTypeTable->load(array('unique_identifier' => $data['client']));
				$data['type_id'] = $tjUcmTypeTable->id;
			}
			else
			{
				$tjUcmTypeTable->load(array('id' => $data['type_id']));
				$data['client'] = $tjUcmTypeTable->unique_identifier;
			}
		}
		else
		{
			$this->setError(JText::_('COM_TJUCM_FORM_SAVE_FAILED_CLIENT_REQUIRED'));

			return false;
		}

		$ucmTypeParams = new Registry($tjUcmTypeTable->params);

		// Check if UCM type is subform
		$isSubform     = $ucmTypeParams->get('is_subform');

		if ($isSubform)
		{
			if ($data['parent_id'])
			{
				$tableParentData = $this->getTable();
				$tableParentData->load(array('id' => $data['parent_id']));

				if (!property_exists($tableParentData->id) && (!$tableParentData->id))
				{
					$this->setError(Text::_('COM_TJUCM_INVALID_PARENT_ID'));

					return false;
				}
			}

			if (!$data['parent_id'])
			{
				$this->setError(Text::_('COM_TJUCM_SUBFORM_NOT_ALLOWED_WITH_OUT_PARENT_ID'));

				return false;
			}
		}

		// Check if user is allowed to add/edit the record
		if (empty($data['id']))
		{
			$allowedCount = $ucmTypeParams->get('allowed_count', 0, 'INT');

			// Check if the user is allowed to add record for given UCM type
			$canAdd = TjucmAccess::canCreate($data['type_id'], $data['created_by']);

			if (!$canAdd)
			{
				$this->setError(JText::_('COM_TJUCM_FORM_SAVE_FAILED_AUTHORIZATION_ERROR'));

				return false;
			}

			// Check allowed limit if its set for given UCM type
			if (!empty($allowedCount))
			{
				$canAdd = $this->allowedToAddTypeData($user->id, $data['client'], $allowedCount);

				if (!$canAdd)
				{
					$this->setError(JText::sprintf('COM_TJUCM_ALLOWED_COUNT_LIMIT', $allowedCount));

					return false;
				}
			}
		}
		else
		{
			// Check if the user can edit this record
			$canEdit = TjucmAccess::canEdit($data['type_id'], $data['id']);
			$canEditOwn = TjucmAccess::canEditOwn($data['type_id'], $data['id']);

			$itemTable = $this->getTable();
			$itemTable->load(array('id' => $data['id']));

			if ($canEdit)
			{
				$authorised = true;
			}
			elseif (($canEditOwn) && ($itemTable->created_by == $user->id))
			{
				$authorised = true;
			}

			if (!$authorised)
			{
				$this->setError(JText::_('COM_TJUCM_FORM_SAVE_FAILED_AUTHORIZATION_ERROR'));

				return false;
			}
		}

		return parent::save($data);
	}

	/**
	 * Method to save the form fields data.
	 *
	 * @param   array  $fieldData  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.2.1
	 */
	public function saveFieldsData($fieldData)
	{
		// If the data contain data related to cluster field or ownership field then update the ucm_data table accordingly
		if (!empty($fieldData['fieldsvalue']) && !empty($fieldData['content_id']))
		{
			$clusterFieldName = str_replace('.', '_', $fieldData['client']) . '_clusterclusterid';
			$ownerShipFieldName = str_replace('.', '_', $fieldData['client']) . '_ownershipcreatedby';
			$itemCategoryFieldName = str_replace('.', '_', $fieldData['client']) . '_itemcategoryitemcategory';

			if (array_key_exists($clusterFieldName, $fieldData['fieldsvalue'])
				|| array_key_exists($ownerShipFieldName, $fieldData['fieldsvalue'])
				|| array_key_exists($itemCategoryFieldName, $fieldData['fieldsvalue']))
			{
				JLoader::import('components.com_tjucm.tables.item', JPATH_ADMINISTRATOR);
				$ucmItemTable = JTable::getInstance('Item', 'TjucmTable', array('dbo', JFactory::getDbo()));
				$ucmItemTable->load(array('id' => $fieldData['content_id']));

				if (!empty($fieldData['fieldsvalue'][$clusterFieldName]))
				{
					$ucmItemTable->cluster_id = $fieldData['fieldsvalue'][$clusterFieldName];
				}

				if (!empty($fieldData['fieldsvalue'][$ownerShipFieldName]))
				{
					JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
					$fieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', JFactory::getDbo()));
					$fieldTable->load(array('name' => $ownerShipFieldName));
					$fieldParams = new Registry($fieldTable->params);

					// If enabled then the selected user will be set as creator of the UCM type item
					if ($fieldParams->get('ucmItemOwner'))
					{
						$ucmItemTable->created_by = $fieldData['fieldsvalue'][$ownerShipFieldName];
					}
				}

				if (!empty($fieldData['fieldsvalue'][$itemCategoryFieldName]))
				{
					$ucmItemTable->category_id = $fieldData['fieldsvalue'][$itemCategoryFieldName];
				}

				$ucmItemTable->store();
			}
		}

		return $this->saveExtraFields($fieldData);
	}

	/**
	 * Method to delete data
	 *
	 * @param   array  &$contentId  Data to be deleted
	 *
	 * @return bool|int If success returns the id of the deleted item, if not false
	 *
	 * @throws Exception
	 */
	public function delete(&$contentId)
	{
		$ucmTypeId = $this->getState('ucmType.id');
		$user = JFactory::getUser();
		$table = $this->getTable();
		$table->load($contentId);
		$canDelete = TjucmAccess::canDelete($table->type_id, $contentId);
		$canDeleteown = TjucmAccess::canDeleteOwn($table->type_id, $contentId);

		$deleteOwn = false;

		if ($canDeleteown)
		{
			$deleteOwn = (JFactory::getUser()->id == $table->created_by ? true : false);
		}

		if ($canDelete || $deleteOwn)
		{
			$id = (!empty($contentId)) ? $contentId : (int) $this->getState('item.id');
			$table = $this->getTable();

			// Do not allow to delete if id is passes as empty
			if (empty($id))
			{
				return false;
			}

			// If there are child records then delete child records first
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id');
			$query->from($db->quoteName('#__tj_ucm_data'));
			$query->where($db->quoteName('parent_id') . '=' . $id);
			$db->setQuery($query);
			$subFormContentIds = $db->loadColumn();

			if (!empty($subFormContentIds))
			{
				foreach ($subFormContentIds as $subFormContentId)
				{
					$table->load($subFormContentId);

					// Plugin trigger on before item delete
					JPluginHelper::importPlugin('actionlog');
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger('tjUcmOnBeforeDeleteItem', array($subFormContentId, $table->client));

					if ($table->delete($subFormContentId) === true)
					{
						$this->deleteExtraFieldsData($subFormContentId, $table->client);

						// Plugin trigger on after item delete
						JPluginHelper::importPlugin('actionlog');
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger('tjUcmOnAfterDeleteItem', array($subFormContentId, $table->client));
					}
				}
			}

			// Delete parent record
			$table->load($id);

			// Plugin trigger on before item delete
			JPluginHelper::importPlugin('actionlog');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('tjUcmOnBeforeDeleteItem', array($id, $table->client));

			if ($table->delete($id) === true)
			{
				$this->deleteExtraFieldsData($id, $table->client);

				// Plugin trigger on after item delete
				JPluginHelper::importPlugin('actionlog');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('tjUcmOnAfterDeleteItem', array($id, $table->client));

				return $id;
			}
			else
			{
				return false;
			}
		}
		else
		{
			throw new Exception(JText::_('COM_TJUCM_ITEM_SAVED_STATE_ERROR'), 403);

			return false;
		}
	}

	/**
	 * Check if user is submit new type data or not
	 *
	 * @param   INT     $userId        User Id
	 * @param   string  $client        Client
	 * @param   INT     $allowedCount  Allowed Count
	 *
	 * @return boolean
	 */
	public function allowedToAddTypeData($userId, $client, $allowedCount)
	{
		if (!empty($userId) && !empty($client))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select("count(" . $db->quoteName('id') . ")");
			$query->from($db->quoteName('#__tj_ucm_data'));
			$query->where($db->quoteName('created_by') . '=' . (int) $userId);
			$query->where($db->quoteName('client') . '=' . $db->quote($client));
			$db->setQuery($query);
			$result = $db->loadResult();

			// If Zero Allowed count means unlimited
			if ($allowedCount == '0')
			{
				return true;
			}

			if ($result < $allowedCount)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to save ucmSubForm records
	 *
	 * @param   INT     $parentRecordId  parent content id
	 * @param   OBJECT  $efd             Field object
	 *
	 * @return STRING
	 */
	public function getUcmSubFormFieldDataJson($parentRecordId, $efd)
	{
		if (is_array($efd->value))
		{
			$efd->value = $efd->value[0];
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__tj_ucm_data'));
		$query->where($db->quoteName('parent_id') . '=' . $parentRecordId);
		$query->where($db->quoteName('client') . '=' . $db->quote($efd->value));
		$db->setQuery($query);
		$contentIds = $db->loadColumn();

		$ucmSubFormFieldData = new stdClass;

		JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);
		$tjFieldsHelper = new TjfieldsHelper;

		foreach ($contentIds as $key => $contentId)
		{
			$recordData = array();
			$recordData['content_id'] = $contentId;
			$recordData['client'] = $efd->value;
			$ucmSubFormFieldValues = $tjFieldsHelper->FetchDatavalue($recordData);

			$subFormData = new stdClass;

			foreach ($ucmSubFormFieldValues as $ucmSubFormFieldValue)
			{
				$ucmSubFormFieldName = $ucmSubFormFieldValue->name;

				$value = '';
				$temp = array();

				switch ($ucmSubFormFieldValue->type)
				{
					case 'radio':
						if (is_array($ucmSubFormFieldValue->value) || is_object($ucmSubFormFieldValue->value))
						{
							if (isset($ucmSubFormFieldValue->value[0]))
							{
								$value = $ucmSubFormFieldValue->value[0]->value;
							}
						}
						else
						{
							$value = $ucmSubFormFieldValue->value;
						}
						break;
					case 'tjlist':
					case 'related':
					case 'multi_select':

						if (is_array($ucmSubFormFieldValue->value) || is_object($ucmSubFormFieldValue->value))
						{
							foreach ($ucmSubFormFieldValue->value as $option)
							{
								$temp[] = $option->value;
							}

							if (!empty($temp))
							{
								$value = $temp;
							}
						}
						else
						{
							$value = $ucmSubFormFieldValue->value;
						}

						break;

					default:
						$value = $ucmSubFormFieldValue->value;
				}

				$subFormData->$ucmSubFormFieldName = $value;
			}

			$client = explode('.', $recordData['client']);
			$ucmSubformContentIdFieldName = $client[0] . '_' . $client[1] . '_' . 'contentid';
			$subFormData->$ucmSubformContentIdFieldName = $contentId;

			$concat = $efd->name . $key;

			// Check if any field has value for the subform entry and if there is no value in subform then dont show it
			$subFormDataArray = (array) $subFormData;
			unset($subFormDataArray[$ucmSubformContentIdFieldName]);

			if (empty($subFormDataArray))
			{
				continue;
			}

			$ucmSubFormFieldData->$concat = $subFormData;
		}

		return json_encode($ucmSubFormFieldData);
	}

	/**
	 * Function to updated related field options
	 *
	 * @param   INT  $client     client
	 *
	 * @param   INT  $contentId  Content id
	 *
	 * @return ARRAY
	 */
	public function getUdatedRelatedFieldOptions($client, $contentId)
	{
		if (empty($client) || empty($contentId))
		{
			return false;
		}

		$db = JFactory::getDbo();

		// Get all the fields of the UCM type
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		$tjFieldsModelFields = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$tjFieldsModelFields->setState("filter.client", $client);
		$tjFieldsModelFields->setState("filter.state", 1);
		$fields = $tjFieldsModelFields->getItems();

		// Get data of the UCM form for given content id and ucm client
		JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);
		$tjFieldsHelper = new TjfieldsHelper;
		$ucmData = $tjFieldsHelper->FetchDatavalue(array('client' => $client, 'content_id' => $contentId));

		// Get object of TJ-Fields field model
		JLoader::import('components.com_tjfields.models.field', JPATH_ADMINISTRATOR);
		$tjFieldsModelField = JModelLegacy::getInstance('Field', 'TjfieldsModel');

		$returnData = array();

		// Loop through the UCM fields to get related fields in the UCM and in its subform UCMs
		foreach ($fields as $field)
		{
			$fieldParams = new Registry($field->params);

			if ($field->type == 'related' && !empty($fieldParams->get('showParentRecordsOnly', '')))
			{
				$options = $tjFieldsModelField->getRelatedFieldOptions($field->id);
				$selectedValues = $ucmData[$field->id]->value;

				// Mark previously selected options as selected
				if (is_array($selectedValues))
				{
					foreach ($options as &$option)
					{
						foreach ($selectedValues as $selectedValue)
						{
							if ($option['value'] == $selectedValue->value)
							{
								$option['selected'] = 1;
							}
						}
					}
				}
				else
				{
					foreach ($options as &$option)
					{
						if ($option['value'] == $selectedValues)
						{
							$option['selected'] = 1;
						}
					}
				}

				// This is required to replace the options of related field in the DOM
				$relatedFieldElementId = 'jform_' . $field->name;

				$returnData[] = array('elementId' => $relatedFieldElementId, 'options' => $options);
			}
			elseif ($field->type == 'ucmsubform')
			{
				if (!isset($ucmData[$field->id]) || empty($ucmData[$field->id]->value))
				{
					$ucmSubFormFormSource = $fieldParams->get('formsource');
					$ucmSubFormClient = str_replace('components/com_tjucm/models/forms/', '', $ucmSubFormFormSource);
					$ucmSubFormClient = 'com_tjucm.' . str_replace('form_extra.xml', '', $ucmSubFormClient);
				}
				else
				{
					$ucmSubFormClient = $ucmData[$field->id]->value;
				}

				if (!empty($ucmSubFormClient))
				{
					// This is to get the options of related fields in the subforms of the parent UCM
					$sfFieldName = $field->name;

					// Get fields of the subform of the parent form
					$tjFieldsModelFields = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
					$tjFieldsModelFields->setState("filter.client", $ucmSubFormClient);
					$tjFieldsModelFields->setState("filter.state", 1);
					$ucmSubFormfields = $tjFieldsModelFields->getItems();

					// Get the content_id of the subform records of the parent record
					$query = $db->getQuery(true);
					$query->select('id');
					$query->from($db->quoteName('#__tj_ucm_data'));
					$query->where($db->quoteName('parent_id') . '=' . $contentId);
					$query->where($db->quoteName('client') . '=' . $db->quote($ucmSubFormClient));
					$db->setQuery($query);
					$subFormContentIds = $db->loadColumn();

					if (!empty($subFormContentIds))
					{
						$count = 0;

						// Loop through the subform data to get the updated options of the subform related fields
						foreach ($subFormContentIds as $subFormContentId)
						{
							$ucmSubFormData = $tjFieldsHelper->FetchDatavalue(array('client' => $ucmSubFormClient, 'content_id' => $subFormContentId));

							foreach ($ucmSubFormfields as $ucmSubFormfield)
							{
								$fieldParams = new Registry($ucmSubFormfield->params);

								if ($ucmSubFormfield->type == 'related' && !empty($fieldParams->get('showParentRecordsOnly', '')))
								{
									$options = $tjFieldsModelField->getRelatedFieldOptions($ucmSubFormfield->id);
									$selectedValues = $ucmSubFormData[$ucmSubFormfield->id]->value;

									// Mark previously selected options as selected
									if (is_array($selectedValues))
									{
										foreach ($options as &$option)
										{
											foreach ($selectedValues as $selectedValue)
											{
												if ($option['value'] == $selectedValue->value)
												{
													$option['selected'] = 1;
												}
											}
										}
									}
									else
									{
										foreach ($options as &$option)
										{
											if ($option['value'] == $selectedValues)
											{
												$option['selected'] = 1;
											}
										}
									}

									// This is required to replace the options of related field of subform in the DOM
									$ucmSubFormFieldElementId = 'jform_' . $sfFieldName . '__' . $sfFieldName . $count . '__' . $ucmSubFormfield->name;
									$ucmSubFormFieldElementId = str_replace('-', '_', $ucmSubFormFieldElementId);
									$ucmSubFormFieldTemplateElementId = 'jform_' . $sfFieldName . '__' . $sfFieldName . 'XXX_XXX__' . $ucmSubFormfield->name;
									$ucmSubFormFieldTemplateElementId = str_replace('-', '_', $ucmSubFormFieldTemplateElementId);
									$returnData[] = array('templateId' => $ucmSubFormFieldTemplateElementId, 'elementId' => $ucmSubFormFieldElementId, 'options' => $options);
								}
							}

							$count++;
						}
					}
					else
					{
						$parentUcmFieldParams = new Registry($field->params);

						$minimumUcmRecords = $parentUcmFieldParams->get('min', 0, 'INT');

						for ($i = 0; $i <= $minimumUcmRecords; $i++)
						{
							foreach ($ucmSubFormfields as $ucmSubFormfield)
							{
								if ($ucmSubFormfield->type == 'related')
								{
									$options = $tjFieldsModelField->getRelatedFieldOptions($ucmSubFormfield->id);

									// This is required to replace the options of related field of subform in the DOM
									$ucmSubFormFieldElementId = 'jform_' . $sfFieldName . '__' . $sfFieldName . $i . '__' . $ucmSubFormfield->name;
									$ucmSubFormFieldElementId = str_replace('-', '_', $ucmSubFormFieldElementId);
									$ucmSubFormFieldTemplateElementId = 'jform_' . $sfFieldName . '__' . $sfFieldName . 'XXX_XXX__' . $ucmSubFormfield->name;
									$ucmSubFormFieldTemplateElementId = str_replace('-', '_', $ucmSubFormFieldTemplateElementId);
									$returnData[] = array('templateId' => $ucmSubFormFieldTemplateElementId, 'elementId' => $ucmSubFormFieldElementId, 'options' => $options);
								}
							}
						}
					}
				}
			}
		}

		return $returnData;
	}

	/**
	 * Method to push data in queue.
	 *
	 * @param   string  $ucmId         Ucm id
	 * @param   string  $sourceClient  Source client
	 * @param   array   $targetClient  Target client
	 * @param   Object  $userId        User id who wants to copy item
	 * @param   Object  $clusterId     Cluster id
	 *
	 * @return  boolean value.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function queueItemCopy($ucmId, $sourceClient, $targetClient, $userId, $clusterId=0)
	{
		$return = [];

		$messageBody = new stdClass;
		$messageBody->ucmId = $ucmId;
		$messageBody->sourceClient = $sourceClient;
		$messageBody->targetClient = $targetClient;
		$messageBody->userId = $userId;

		if ($clusterId)
		{
			$messageBody->clusterId = $clusterId;
		}

		try
		{
			$TJQueueProduce = new TJQueueProduce;

			// Set message body
			$TJQueueProduce->message->setBody(json_encode($messageBody));

			// @Params client, value
			$TJQueueProduce->message->setProperty('client', 'core.copyitem');
			$TJQueueProduce->produce();
		}
		catch (Exception $e)
		{
			$return['success'] = 0;
			$return['message'] = $e->getMessage();

			return $return;
		}

		$return['success'] = 1;
		$return['message'] = '';

		return $return;
	}
}
