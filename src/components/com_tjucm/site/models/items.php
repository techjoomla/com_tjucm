<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

use Joomla\CMS\Component\ComponentHelper;

/**
 * Methods supporting a list of Tjucm records.
 *
 * @since  1.6
 */
class TjucmModelItems extends JModelList
{
	private $client;

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
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'state',
				'type_id',
				'created_by',
				'created_date',
				'modified_by',
				'modified_date',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = "a.id", $direction = "DESC")
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'STRING');
		$this->setState('filter.search', $search);

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');

		$typeId = $app->input->get('id', "", "INT");

		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjucm/tables');
		$typeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', $db));
		$typeTable->load(array('id' => $typeId));
		$ucmType = $typeTable->unique_identifier;

		// Set state for field filters
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		$fieldsModel = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$fieldsModel->setState('filter.client', $this->client);
		$fieldsModel->setState('filter.filterable', 1);
		$fields = $fieldsModel->getItems();

		foreach ($fields as $field)
		{
			$filterValue = $app->getUserStateFromRequest($this->context . '.' . $field->name, $field->name, '', 'STRING');
			$this->setState('filter.field.' . $field->name, $filterValue);
		}

		if (empty($ucmType))
		{
			// Get the active item
			$menuitem   = $app->getMenu()->getActive();

			// Get the params
			$this->menuparams = $menuitem->params;

			if (!empty($this->menuparams))
			{
				$this->ucm_type   = $this->menuparams->get('ucm_type');

				if (!empty($this->ucm_type))
				{
					$ucmType     = 'com_tjucm.' . $this->ucm_type;
				}
			}
		}

		if (empty($ucmType))
		{
			// Get UCM type id from uniquue identifier
			$ucmType = $app->input->get('client', '', 'STRING');
		}

		if (empty($typeId))
		{
			$typeId = $tjUcmModelType->getTypeId($ucmType);
		}

		$clusterId = $app->getUserStateFromRequest($this->context . '.cluster', 'cluster');

		if ($clusterId)
		{
			$this->setState('filter.cluster_id', $clusterId);
		}

		$this->setState('ucm.client', $ucmType);
		$this->setState("ucmType.id", $typeId);

		$createdBy = $app->input->get('created_by', "", "INT");
		$canView = $user->authorise('core.type.viewitem', 'com_tjucm.type.' . $typeId);

		if (!$canView)
		{
			$createdBy = $user->id;
		}

		$this->setState("created_by", $createdBy);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$this->fields = $this->getFields();

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT ' . $db->quoteName('a.id') . ', '
				. $db->quoteName('a.state') . ', '
				. $db->quoteName('a.cluster_id') . ', '
				. $db->quoteName('a.created_by')
			)
		);

		$query->from($db->quoteName('#__tj_ucm_data', 'a'));

		// Join over the users for the checked out user
		$query->select($db->quoteName('uc.name', 'uEditor'));
		$query->join("LEFT", $db->quoteName('#__users', 'uc') . ' ON (' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out') . ')');

		$this->client = $this->getState('ucm.client');

		if (!empty($this->client))
		{
			$query->where($db->quoteName('a.client') . ' = ' . $db->quote($db->escape($this->client)));
		}

		$ucmType = $this->getState('ucmType.id', '', 'INT');

		if (!empty($ucmType))
		{
			$query->where($db->quoteName('a.type_id') . ' = ' . (INT) $ucmType);
		}

		$createdBy = $this->getState('created_by', '', 'INT');

		if (!empty($createdBy))
		{
			$query->where($db->quoteName('a.created_by') . ' = ' . (INT) $createdBy);
		}

		// Show records belonging to users cluster if com_cluster is installed and enabled - start
		$clusterExist = ComponentHelper::getComponent('com_cluster', true)->enabled;

		if ($clusterExist)
		{
			JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
			$fieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
			$fieldTable->load(array('client' => $this->client, 'type' => 'cluster'));

			if ($fieldTable->id)
			{
				JFormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models/fields/');
				$cluster = JFormHelper::loadFieldType('cluster', false);
				$clusterList = $cluster->getOptionsExternally();

				if (!empty($clusterList))
				{
					$usersClusters = array();

					foreach ($clusterList as $clusterList)
					{
						if (!empty($clusterList->value))
						{
							$usersClusters[] = $clusterList->value;
						}
					}
				}

				$query->where($db->quoteName('a.cluster_id') . ' IN (' . implode(",", $usersClusters) . ')');
			}
		}

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where($db->quoteName('a.state') . ' = ' . (INT) $published);
		}
		elseif ($published === '')
		{
			$query->where(($db->quoteName('(a.state) ') . ' IN (0, 1)'));
		}

		// Search by content id
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->escape(trim($search), true);

			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.id') . ' = ' . (int) str_replace('id:', '', $search));
			}
		}

		// Search on fields data
		$filteredItemIds = $this->filterContent();

		if (is_array($filteredItemIds))
		{
			if (!empty($filteredItemIds))
			{
				$filteredItemIds = implode(',', $filteredItemIds);
				$query->where($db->quoteName('a.id') . ' IN (' . $filteredItemIds . ')');
			}
			else
			{
				// If no search results found then do not return any record
				$query->where($db->quoteName('a.id') . '=0');
			}
		}

		// Filter by cluster
		$clusterId = (int) $this->getState('filter.cluster_id');

		if ($clusterId)
		{
			$query->where($db->quoteName('a.cluster_id') . ' = ' . $clusterId);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Function to filter content as per field values
	 *
	 * @return   Array  Content Ids
	 *
	 * @since    1.2.1
	 */
	private function filterContent()
	{
		// Flag to mark if field specific search is done from the search box
		$filterFieldFound = 0;

		// Flag to mark if any filter is applied or not
		$filterApplied = 0;

		// Variable to store count of the self joins on the fields_value table
		$filterFieldsCount = 0;

		// Apply search filter
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('fv1.content_id');
		$query->from($db->quoteName('#__tjfields_fields_value', 'fv1'));
		$query->join('INNER', $db->qn('#__tjfields_fields', 'f') . ' ON (' . $db->qn('fv1.field_id') . ' = ' . $db->qn('f.id') . ')');
		$query->where($db->quoteName('f.state') . ' =1');
		$query->where($db->quoteName('f.client') . ' = ' . $db->quote($this->client));

		// Filter by field value
		$search = $this->getState('filter.search');

		if (!empty($this->fields) && (stripos($search, 'id:') !== 0))
		{
			foreach ($this->fields as $fieldId => $field)
			{
				// For field specific search
				if (stripos($search, $field . ':') === 0)
				{
					$filterFieldsCount++;

					if ($filterFieldsCount > 1)
					{
						$query->join('LEFT', $db->qn('#__tjfields_fields_value', 'fv' . $filterFieldsCount) . ' ON (' . $db->qn('fv' . ($filterFieldsCount-1).'.content_id') . ' = ' . $db->qn('fv'.$filterFieldsCount.'.content_id') . ')');
					}

					$search = trim(str_replace($field . ':', '', $search));
					$query->where($db->qn('fv'.$filterFieldsCount.'.field_id') . ' = ' . $fieldId);
					$query->where($db->qn('fv'.$filterFieldsCount.'.value') . ' LIKE ' . $db->q('%' . $search . '%'));
					$filterFieldFound = 1;
					$filterApplied = 1;

					break;
				}
			}
		}

		// For generic search
		if ($filterFieldFound == 0 && !empty($search)  && (stripos($search, 'id:') !== 0))
		{
			$filterFieldsCount++;

			if ($filterFieldsCount > 1)
			{
				$query->join('LEFT', $db->qn('#__tjfields_fields_value', 'fv' . $filterFieldsCount) . ' ON (' . $db->qn('fv' . ($filterFieldsCount-1).'.content_id') . ' = ' . $db->qn('fv'.$filterFieldsCount.'.content_id') . ')');
			}

			$query->where($db->quoteName('fv'.$filterFieldsCount.'.value') . ' LIKE ' . $db->q('%' . $search . '%'));
			$filterApplied = 1;
		}

		// For filterable fields
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		$fieldsModel = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$fieldsModel->setState('filter.client', $this->client);
		$fieldsModel->setState('filter.filterable', 1);
		$fields = $fieldsModel->getItems();

		foreach ($fields as $field)
		{
			$filterValue = $this->getState('filter.field.' . $field->name);

			if ($filterValue != '')
			{
				$filterFieldsCount++;

				if ($filterFieldsCount > 1)
				{
					$query->join('LEFT', $db->qn('#__tjfields_fields_value', 'fv' . $filterFieldsCount) . ' ON (' . $db->qn('fv' . ($filterFieldsCount-1).'.content_id') . ' = ' . $db->qn('fv'.$filterFieldsCount.'.content_id') . ')');
				}

				$query->where($db->qn('fv'.$filterFieldsCount.'.field_id') . ' = ' . $field->id);
				$query->where($db->qn('fv'.$filterFieldsCount.'.value') . ' = ' . $db->q($filterValue));
				$filterApplied = 1;
			}
		}

		$query->setLimit($this->getState('list.limit'));

		// If there is any filter applied then only execute the query
		if ($filterApplied)
		{
			$db->setQuery($query);

			return $db->loadColumn();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getFields()
	{
		// Load fields model
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		$fieldsModel = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$fieldsModel->setState('filter.showonlist', 1);
		$fieldsModel->setState('filter.state', 1);
		$this->client = $this->getState('ucm.client');

		if (!empty($this->client))
		{
			$fieldsModel->setState('filter.client', $this->client);
		}

		$items = $fieldsModel->getItems();

		$data = array();

		foreach ($items as $item)
		{
			$data[$item->id] = $item->label;
		}

		return $data;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$typeId = $this->getState('ucmType.id');
		$createdBy = $this->getState('created_by', '');

		JLoader::import('components.com_tjucm.models.item', JPATH_SITE);
		$itemModel = new TjucmModelItem;
		$canView = $itemModel->canView($typeId);
		$user = JFactory::getUser();

		// If user is not allowed to view the records and if the created_by is not the logged in user then do not show the records
		if (!$canView)
		{
			if (!empty($createdBy) && $createdBy == $user->id)
			{
				$canView = true;
			}
		}

		if (!$canView)
		{
			return false;
		}

		$items = parent::getItems();
		$itemsArray = (array) $items;
		$contentIds = array_column($itemsArray, 'id');
		$fieldValues = $this->getFieldsData($contentIds);

		foreach ($items as &$item)
		{
			$item->field_values = array();

			foreach ($fieldValues as $key => &$fieldValue)
			{
				if ($item->id == $fieldValue->content_id)
				{
					$item->field_values[$fieldValue->field_id] = $fieldValue->value;
					unset($fieldValues[$key]);
				}
			}
		}

		foreach ($items as &$item)
		{
			$fieldValues = array();

			foreach ($this->fields as $fieldId => $fieldValue)
			{
				if (!array_key_exists($fieldId, $item->field_values))
				{
					$fieldValues[$fieldId] = "";
				}
				else
				{
					$fieldValues[$fieldId] = $item->field_values[$fieldId];
				}
			}

			$item->field_values = $fieldValues;
		}

		return $items;
	}

	/**
	 * Method to fields data for given content Ids
	 *
	 * @param   array  $contentIds  An array of record ids.
	 *
	 * @return  ARRAY  Fields data if successful, false if an error occurs.
	 *
	 * @since   1.2.1
	 */
	private function getFieldsData($contentIds)
	{
		$contentIds = implode(',', $contentIds);

		if (empty($contentIds))
		{
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__tjfields_fields_value', 'fv'));
		$query->join('INNER', $db->qn('#__tjfields_fields', 'f') . ' ON (' . $db->qn('f.id') . ' = ' . $db->qn('fv.field_id') . ')');
		$query->where($db->qn('f.state') . '=1');
		$query->where($db->qn('fv.content_id') . ' IN (' . $contentIds . ')');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 *
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = JFactory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null)
			{
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat)
		{
			$app->enqueueMessage(JText::_("COM_TJUCM_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 *
	 * @param   string  $date  Date to be checked
	 *
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);

		return (date_create($date)) ? JFactory::getDate($date)->format("Y-m-d") : null;
	}

	/**
	 * Method to getAliasFieldNameByView
	 *
	 * @param   array  $view  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function getAliasFieldNameByView($view)
	{
		switch ($view)
		{
			case 'items':
				return 'alias';
			break;
		}
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
		$db = JFactory::getDbo();
		$table = JTable::getInstance('type', 'TjucmTable', array('dbo', $db));

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Check if there are fields to show in list view
	 *
	 * @param   string  $client  Client
	 *
	 * @return boolean
	 */
	public function showListCheck($client)
	{
		if (!empty($client))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select("count(" . $db->quoteName('id') . ")");
			$query->from($db->quoteName('#__tjfields_fields'));
			$query->where($db->quoteName('client') . '=' . $db->quote($client));
			$query->where($db->quoteName('showonlist') . '=1');
			$db->setQuery($query);

			$result = $db->loadResult();

			return $result;
		}
		else
		{
			return false;
		}
	}
}
