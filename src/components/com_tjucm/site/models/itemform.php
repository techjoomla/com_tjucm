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
require_once JPATH_ADMINISTRATOR . '/components/com_tjucm/classes/funlist.php';

use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

/**
 * Tjucm model.
 *
 * @since  1.6
 */
class TjucmModelItemForm extends JModelForm
{
	private $item = null;

	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
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
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		JLoader::import('components.com_tjucm.classes.funlist', JPATH_ADMINISTRATOR);
		$this->common  = new TjucmFunList;

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since  1.6
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
					$ucmType     = 'com_tjucm.' . $ucm_type;
				}
			}
		}

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');
		$ucmId = $tjUcmModelType->getTypeId($ucmType);

		$this->setState('ucmType.id', $ucmId);

		// Check published state
		if ((!$user->authorise('core.type.edititem', 'com_tjucm.type.' . $ucmId))
			&& (!$user->authorise('core.type.editownitem', 'com_tjucm.type.' . $ucmId))
			&& (!$user->authorise('core.type.edititemstate', 'com_tjucm.type.' . $ucmId)))
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
		$canEdit = $user->authorise('core.type.edititem', 'com_tjucm.type.' . $ucmTypeId);
		$canEditOwn = $user->authorise('core.type.editownitem', 'com_tjucm.type.' . $ucmTypeId);
		$canCreate = $user->authorise('core.type.createitem', 'com_tjucm.type.' . $ucmTypeId);

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
	 * @since    1.6
	 */
	public function getTable($type = 'Item', $prefix = 'TjucmTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get an item by alias
	 *
	 * @param   string  $alias  Alias string
	 *
	 * @return int Element id
	 */
	public function getItemIdByAlias($alias)
	{
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('item.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('item.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Get an array of data items
	 *
	 * @param   string  $client  client value
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function setClient($client)
	{
		$this->client = $client;
	}

	/**
	 * Get an client value
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.6
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
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
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
	 * @param   array  $data              The form data.
	 * @param   array  $extra_jform_data  Exra field data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function save($data, $extra_jform_data = '')
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$status_title = $app->input->get('form_status');
		$ucmTypeId = $this->getState('ucmType.id');
		$typeItemId = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('item.id');
		$authorised = false;

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');

		if (empty($ucmTypeId))
		{
			// Get UCM type id from uniquue identifier
			$ucmTypeId = $tjUcmModelType->getTypeId($data['client']);
		}

		if ($ucmTypeId)
		{
			// Check if user is allowed to save the content
			$typeData = $tjUcmModelType->getItem($ucmTypeId);
			$allowedCount = $typeData->allowed_count;

			// 0 : add unlimited records against this UCM type
			$allowedCount = empty($allowedCount) ? 0 : $allowedCount;
			$userId = $user->id;
			$allowedToAdd = $this->allowedToAddTypeData($userId, $data['client'], $allowedCount);

			if (!$allowedToAdd && $typeItemId == 0)
			{
				$message = JText::sprintf('COM_TJUCM_ALLOWED_COUNT_LIMIT', $allowedCount);
				$app->enqueueMessage($message, 'warning');

				return false;
			}

			if ($typeItemId)
			{
				// Check the user can edit this item
				$canEdit = $user->authorise('core.type.edititem', 'com_tjucm.type.' . $ucmTypeId);
				$canEditOwn = $user->authorise('core.type.editownitem', 'com_tjucm.type.' . $ucmTypeId);

				// Get the UCM item details
				Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');
				$itemDetails = Table::getInstance('Item', 'TjucmTable');
				$itemDetails->load(array('id' => $typeItemId));

				$data['created_by'] = $itemDetails->created_by;

				if ($canEdit)
				{
					$authorised = true;
				}
				elseif (($canEditOwn) && ($itemDetails->created_by == $user->id))
				{
					if (!empty($data['created_by']) && $itemDetails->created_by == $data['created_by'])
					{
						$authorised = true;
					}
				}
			}
			else
			{
				// Check the user can create new items in this section
				$authorised = $user->authorise('core.type.createitem', 'com_tjucm.type.' . $ucmTypeId);
			}
		}

		if ($authorised !== true)
		{
			throw new Exception(JText::_('COM_TJUCM_ERROR_MESSAGE_NOT_AUTHORISED'), 403);

			return false;
		}

		$ucmTypeData = $this->common->getDataValues('#__tj_ucm_types', 'id AS type_id, params', 'unique_identifier = "'
		. $data['client'] . '"', 'loadAssoc');

		$data['type_id'] = empty($data['type_id']) ? $ucmTypeData['type_id'] : $data['type_id'];

		$ucmTypeParams = json_decode($ucmTypeData['params']);

		$table = $this->getTable();

		if (isset($ucmTypeParams->publish_items) && $ucmTypeParams->publish_items == 0)
		{
			$data['state'] = 0;
		}
		else
		{
			$data['state'] = 1;
		}

		// To store fields value in TJ-Fields
		$data_extra = array();

		if (!empty($extra_jform_data))
		{
			$data_extra['client'] = $data['client'];
			$data_extra['fieldsvalue'] = $extra_jform_data;
		}

		$isNew = empty($typeItemId) ? 1 : 0;

		// OnBefore UCM record save trigger.
		JPluginHelper::importPlugin('tjucm');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('tjucmOnBeforeSaveItem', array(&$data, &$data_extra, $isNew));

		// Load TJ-Fields tables
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/tables');

		// If item category field is added in the type then save item category agains the item record
		foreach ($extra_jform_data as $fieldName => $fieldData)
		{
			$fieldTable = Table::getInstance('Field', 'TjfieldsTable');
			$fieldTable->load(array('name' => $fieldName));

			if ($fieldTable->type == 'itemcategory')
			{
				$data['category_id'] = $fieldData;

				break;
			}
		}

		if ($table->save($data) === true)
		{
			if (!empty($extra_jform_data))
			{
				$data_extra['content_id'] = $table->id;

				// Save extra fields data.
				$this->saveExtraFields($data_extra);
			}

			$data['id'] = $table->id;

			// OnAfter UCM record save trigger.
			$dispatcher->trigger('tjucmOnAfterSaveItem', array($data, $data_extra));

			return $table->id;
		}
		else
		{
			throw new Exception($table->getError());
		}
	}

	/**
	 * Method to duplicate an Item
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = JFactory::getUser();
		$ucmTypeId = $this->getState('ucmType.id');

		// Access checks.
		if (!$user->authorise('core.type.createitem', 'com_tjucm.type.' . $ucmTypeId))
		{
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));

			return false;
		}

		$dispatcher = JEventDispatcher::getInstance();
		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		JPluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($table->load($pk, true))
			{
				// Reset the id to create a new record.
				$table->id = 0;

				if (!$table->check())
				{
					throw new Exception($table->getError());

					return false;
				}

				if (!empty($table->type_id))
				{
					if (is_array($table->type_id))
					{
						$table->type_id = implode(',', $table->type_id);
					}
				}
				else
				{
					$table->type_id = '';
				}

				// Trigger the before save event.
				$result = $dispatcher->trigger($this->event_before_save, array($context, &$table, true));

				if (in_array(false, $result, true) || !$table->store())
				{
					throw new Exception($table->getError());
				}

				// Trigger the after save event.
				$dispatcher->trigger($this->event_after_save, array($context, &$table, true));
			}
			else
			{
				throw new Exception($table->getError());

				return false;
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to delete data
	 *
	 * @param   array  $contentId  Data to be deleted
	 *
	 * @return bool|int If success returns the id of the deleted item, if not false
	 *
	 * @throws Exception
	 */
	public function delete($contentId)
	{
		$ucmTypeId = $this->getState('ucmType.id');
		$user = JFactory::getUser();
		$table = $this->getTable();
		$table->load($contentId);
		$canDelete = $user->authorise('core.type.deleteitem', 'com_tjucm.type.' . $table->type_id);
		$canDeleteown = $user->authorise('core.type.deleteownitem', 'com_tjucm.type.' . $table->type_id);

		$deleteOwn = false;
		if ($canDeleteown)
		{
			$deleteOwn = (JFactory::getUser()->id == $table->created_by ? true : false);
		}

		if ($canDelete || $deleteOwn)
		{
			$id = (!empty($contentId)) ? $contentId : (int) $this->getState('item.id');
			$table = $this->getTable();

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

					if ($table->delete($subFormContentId) === true)
					{
						$this->deleteExtraFieldsData($subFormContentId, $table->client);
					}
				}
			}

			// Delete parent record
			$table->load($id);

			if ($table->delete($id) === true)
			{
				$this->deleteExtraFieldsData($id, $table->client);

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
	 * Check if data can be saved
	 *
	 * @return bool
	 */
	public function getCanSave()
	{
		$table = $this->getTable();

		return $table !== false;
	}

	/**
	 * Method to getAliasFieldNameByView
	 *
	 * @param   array  $view  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   1.0
	 */
	public function getAliasFieldNameByView($view)
	{
		switch ($view)
		{
			case 'type':
			case 'typeform':
				return 'alias';
			break;
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
	 * Method to set cluster data in posted data.
	 *
	 * @param   array  &$validData  The validated data.
	 *
	 * @param   array  $data        UCM form data.
	 *
	 * @return null
	 *
	 * @since   1.6
	 */
	public function setClusterData(&$validData, $data)
	{
		$clusterField = $createdByField = '';

		// To get type of UCM
		if (!empty($this->client))
		{
			$client = explode(".", $this->client);
			$clusterField = $client[0] . '_' . $client[1] . '_clusterclusterid';
			$createdByField = $client[0] . '_' . $client[1] . '_ownershipcreatedby';
		}

		// Save created_by field by ownership user field (To save form on behalf of someone)
		if (!empty($data[$createdByField]) && empty($data[$clusterField]))
		{
			$validData['created_by'] = $data[$createdByField];
		}

		// Cluster Id store in UCM data
		$clusterExist = ComponentHelper::getComponent('com_cluster', true)->enabled;

		if (!empty($data[$clusterField]) && $clusterExist)
		{
			$user  = Factory::getUser();
			$isSuperUser = $user->authorise('core.admin');

			JLoader::import("/components/com_cluster/includes/cluster", JPATH_ADMINISTRATOR);
			$ClusterModel = ClusterFactory::model('ClusterUsers', array('ignore_request' => true));
			$ClusterModel->setState('list.group_by_user_id', 1);
			$ClusterModel->setState('filter.published', 1);
			$ClusterModel->setState('filter.cluster_id', (int) $data[$clusterField]);

			if (!$isSuperUser && !$user->authorise('core.manageall.cluster', 'com_cluster'))
			{
				$ClusterModel->setState('filter.user_id', $user->id);
			}

			// Get all assigned cluster entries
			$clusters = $ClusterModel->getItems();

			if (!empty($clusters))
			{
				$validData['cluster_id'] = $data[$clusterField];

				if (!empty($data[$createdByField]))
				{
					$clusterUsers = array();

					foreach ($clusters as $cluster)
					{
						$clusterUsers[] = $cluster->user_id;
					}

					if (in_array($data[$createdByField], $clusterUsers))
					{
						$validData['created_by'] = $data[$createdByField];
					}
				}
			}
		}
	}

	/**
	 * Function to get formatted data to be added of ucmsubform records
	 *
	 * @param   ARRAY  $validData          Parent record data
	 * @param   ARRAY  &$extra_jform_data  form data
	 *
	 * @return ARRAY
	 */
	public function getFormattedUcmSubFormRecords($validData, &$extra_jform_data)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
		$tjFieldsFieldsModel = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$tjFieldsFieldsModel->setState('filter.client', $validData['client']);
		$tjFieldsFieldsModel->setState('filter.type', 'ucmsubform');

		// Get list of ucmsubform fields in the parent form
		$ucmSubFormFields = $tjFieldsFieldsModel->getItems();

		// Variable to store ucmsubform records posted in the form
		$ucmSubFormDataSet = array();

		// Sort all the ucmsubform records as per client
		foreach ($ucmSubFormFields as $ucmSubFormField)
		{
			if (!isset($extra_jform_data[$ucmSubFormField->name]))
			{
				continue;
			}

			$subformRecords = $extra_jform_data[$ucmSubFormField->name];

			if (!empty($subformRecords))
			{
				$ucmSubFormData = array();

				foreach ($subformRecords as $key => $subformRecord)
				{
					// Append file data to the ucmSubForm data
					if (array_key_exists('tjFieldFileField', $extra_jform_data))
					{
						if (isset($extra_jform_data['tjFieldFileField'][$ucmSubFormField->name][$key]))
						{
							$subformRecord['tjFieldFileField'] = $extra_jform_data['tjFieldFileField'][$ucmSubFormField->name][$key];
						}
					}

					$subformRecord = array_filter($subformRecord);

					if (!empty($subformRecord))
					{
						// Add ucmSubFormFieldName in the data to pass data to JS
						$subformRecord['ucmSubformFieldName'] = $ucmSubFormField->name;

						$ucmSubFormData[] = $subformRecord;
					}
				}

				if (!empty($ucmSubFormData))
				{
					$ucmSubFormFieldParams = json_decode($ucmSubFormField->params);
					$ucmSubFormFormSource = explode('/', $ucmSubFormFieldParams->formsource);
					$ucmSubFormClient = $ucmSubFormFormSource[1] . '.' . str_replace('form_extra.xml', '', $ucmSubFormFormSource[4]);
					$ucmSubFormDataSet[$ucmSubFormClient] = $ucmSubFormData;
					$extra_jform_data[$ucmSubFormField->name] = $ucmSubFormClient;
				}
			}
		}

		// Remove empty records
		$ucmSubFormDataSet = array_filter($ucmSubFormDataSet);

		return $ucmSubFormDataSet;
	}

	/**
	 * Function to save ucmSubForm records
	 *
	 * @param   ARRAY  &$validData         Parent record data
	 * @param   ARRAY  $ucmSubFormDataSet  ucmSubForm records data
	 *
	 * @return ARRAY
	 */
	public function saveUcmSubFormRecords(&$validData, $ucmSubFormDataSet)
	{
		$db = JFactory::getDbo();
		$subFormContentIds = array();

		$isNew = empty($validData['id']) ? 1 : 0;

		// Delete removed subform details
		if (!$isNew)
		{
			$query = $db->getQuery(true);
			$query->select('id');
			$query->from($db->quoteName('#__tj_ucm_data'));
			$query->where($db->quoteName('parent_id') . '=' . $validData['id']);
			$db->setQuery($query);
			$oldSubFormContentIds = $db->loadColumn();
		}

		JLoader::import('components.com_tjfields.tables.fieldsvalue', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');

		if (!empty($ucmSubFormDataSet))
		{
			foreach ($ucmSubFormDataSet as $client => $ucmSubFormTypeData)
			{
				$validData['client'] = $client;
				$validData['type_id'] = $tjUcmModelType->getTypeId($client);

				$clientDetail = explode('.', $client);

				// This is an extra field which is used to render the reference of the ucmsubform field on the form (used in case of edit)
				$ucmSubformContentIdFieldName = $clientDetail[0] . '_' . $clientDetail[1] . '_' . 'contentid';

				$count = 0;

				foreach ($ucmSubFormTypeData as $ucmSubFormData)
				{
					$validData['id'] = isset($ucmSubFormData[$ucmSubformContentIdFieldName]) ? (int) $ucmSubFormData[$ucmSubformContentIdFieldName] : 0;

					// Unset extra data
					$sfFieldName = $ucmSubFormData['ucmSubformFieldName'];
					unset($ucmSubFormData['ucmSubformFieldName']);

					$ucmSubformContentFieldElementId = 'jform[' . $sfFieldName . '][' . $sfFieldName . $count . '][' . $ucmSubformContentIdFieldName . ']';
					$count++;

					if ($insertedId = $this->save($validData, $ucmSubFormData))
					{
						$validData['id'] = $insertedId;
						$subFormContentIds[] = array('elementName' => $ucmSubformContentFieldElementId, 'content_id' => $insertedId);

						$ucmSubFormData[$ucmSubformContentIdFieldName] = $insertedId;

						// Get field id of contentid field
						$fieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
						$fieldTable->load(array('name' => $ucmSubformContentIdFieldName));

						// Add-Update the value of content id field in the fields value table - start
						$fieldsValueTable = JTable::getInstance('Fieldsvalue', 'TjfieldsTable', array('dbo', $db));
						$fieldsValueTable->load(array('field_id' => $fieldTable->id, 'content_id' => $insertedId, 'client' => $validData['client']));

						if (empty($fieldsValueTable->id))
						{
							$fieldsValueTable->field_id = $fieldTable->id;
							$fieldsValueTable->value = $fieldsValueTable->content_id = $insertedId;
							$fieldsValueTable->client = $validData['client'];
						}

						$fieldsValueTable->user_id = JFactory::getUser()->id;
						$fieldsValueTable->store();

						// Add-Update the value of content id field in the fields value table - end
					}
				}
			}
		}

		// Delete removed ucmSubForm record from the form
		if (!empty($oldSubFormContentIds))
		{
			foreach ($oldSubFormContentIds as $oldSubFormContentId)
			{
				if (array_search($oldSubFormContentId, array_column($subFormContentIds, 'content_id')) === false)
				{
					$this->delete($oldSubFormContentId);
				}
			}
		}

		return $subFormContentIds;
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
				$subFormData->$ucmSubFormFieldName = $ucmSubFormFieldValue->value;
			}

			$client = explode('.', $recordData['client']);
			$ucmSubformContentIdFieldName = $client[0] . '_' . $client[1] . '_' . 'contentid';
			$subFormData->$ucmSubformContentIdFieldName = $contentId;

			$concat = $efd->name . $key;
			$ucmSubFormFieldData->$concat = $subFormData;
		}

		return json_encode($ucmSubFormFieldData);
	}

	/**
	 * Function to updated related field options
	 *
	 * @param   INT  $contentId  parent content id
	 *
	 * @return ARRAY
	 */
	public function getUdatedRelatedFieldOptions($contentId)
	{
		$db = JFactory::getDbo();

		// Get UCM details from the content id
		JLoader::import('components.com_tjucm.tables.item', JPATH_ADMINISTRATOR);
		$ucmItemTable = JTable::getInstance('Item', 'TjucmTable', array('dbo', $db));
		$ucmItemTable->load(array('id' => $contentId));

		// Get all the fields of the UCM type
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		$tjFieldsModelFields = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$tjFieldsModelFields->setState("filter.client", $ucmItemTable->client);
		$tjFieldsModelFields->setState("filter.state", 1);
		$fields = $tjFieldsModelFields->getItems();

		// Get data of the UCM form for given content id and ucm client
		JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);
		$tjFieldsHelper = new TjfieldsHelper;
		$ucmData = $tjFieldsHelper->FetchDatavalue(array('client' => $ucmItemTable->client, 'content_id' => $contentId));

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
				if (isset($ucmData[$field->id]) && !empty($ucmData[$field->id]->value))
				{
					// This is to get the options of related fields in the subforms of the parent UCM
					$sfFieldName = $field->name;

					// Get fields of the subform of the parent form
					$tjFieldsModelFields = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
					$tjFieldsModelFields->setState("filter.client", $ucmData[$field->id]->value);
					$tjFieldsModelFields->setState("filter.state", 1);
					$ucmSubFormfields = $tjFieldsModelFields->getItems();

					// Get the content_id of the subform records of the parent record
					$query = $db->getQuery(true);
					$query->select('id');
					$query->from($db->quoteName('#__tj_ucm_data'));
					$query->where($db->quoteName('parent_id') . '=' . $contentId);
					$query->where($db->quoteName('client') . '=' . $db->quote($ucmData[$field->id]->value));
					$db->setQuery($query);
					$subFormContentIds = $db->loadColumn();

					if (!empty($subFormContentIds))
					{
						$count = 0;

						// Loop through the subform data to get the updated options of the subform related fields
						foreach ($subFormContentIds as $subFormContentId)
						{
							$ucmSubFormData = $tjFieldsHelper->FetchDatavalue(array('client' => $ucmData[$field->id]->value, 'content_id' => $subFormContentId));

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

		return $returnData;
	}
}
