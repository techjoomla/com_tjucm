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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;

/**
 * Methods supporting a list of Tjucm records.
 *
 * @since  1.6
 */
class TjucmModelItems extends JModelList
{
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

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = JModelLegacy::getInstance('Type', 'TjucmModel');

		$typeId  = $app->input->get('id', 0, "INT");
		$ucmType = $app->input->get('client', '', "STRING");

		if (empty($typeId) || empty($ucmType))
		{
			JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjucm/tables');
			$typeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', $db));

			if ($typeId && empty($ucmType))
			{
				$typeTable->load(array('id' => $typeId));
				$ucmType = $typeTable->unique_identifier;
			}

			if ($ucmType && empty($typeId))
			{
				$typeTable->load(array('unique_identifier' => $ucmType));
				$typeId = $typeTable->id;
			}
		}

		if (empty($ucmType) && empty($typeId))
		{
			// Get the active item
			$menuitem   = $app->getMenu()->getActive();

			// Get the params
			$this->menuparams = $menuitem->params;

			if (!empty($this->menuparams))
			{
				$ucmTypeAlias = $this->menuparams->get('ucm_type');

				if (!empty($ucmTypeAlias))
				{
					JLoader::import('components.com_tjfields.tables.type', JPATH_ADMINISTRATOR);
					$ucmTypeTable = JTable::getInstance('Type', 'TjucmTable', array('dbo', JFactory::getDbo()));
					$ucmTypeTable->load(array('alias' => $ucmTypeAlias));
					$ucmType = $ucmTypeTable->unique_identifier;
					$typeId  = $ucmTypeTable->id;
				}
			}
		}

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.' . $ucmType . '.filter.search', 'filter_search', '', 'STRING');
		$this->setState($ucmType . '.filter.search', $search);

		// Set state for field filters
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		$fieldsModel = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$fieldsModel->setState('filter.client', $ucmType);
		$fieldsModel->setState('filter.filterable', 1);
		$fields = $fieldsModel->getItems();

		foreach ($fields as $field)
		{
			$filterValue = $app->getUserStateFromRequest($this->context . '.' . $field->name, $field->name, '', 'STRING');
			$this->setState('filter.field.' . $field->name, $filterValue);
		}

		$clusterId = $app->getUserStateFromRequest($this->context . '.' . $ucmType . '.cluster', 'cluster');

		if ($clusterId)
		{
			$this->setState($ucmType . '.filter.cluster_id', $clusterId);
		}

		$categoryId = $app->getUserStateFromRequest($this->context . '.' . $ucmType . '.itemcategory', 'itemcategory');

		if ($categoryId)
		{
			$this->setState($ucmType . '.filter.category_id', $categoryId);
		}

		$draft = $app->getUserStateFromRequest($this->context . '.draft', 'draft');
		$this->setState('filter.draft', $draft);

		$this->setState('ucm.client', $ucmType);
		$this->setState("ucmType.id", $typeId);

		$createdBy = $app->input->get('created_by', "", "INT");
		$this->setState("created_by", $createdBy);

		if ($this->getUserStateFromRequest($this->context . $ucmType . '.filter.order', 'filter_order', '', 'string'))
		{
			$ordering = $this->getUserStateFromRequest($this->context . $ucmType . '.filter.order', 'filter_order', '', 'string');
		}

		if ($this->getUserStateFromRequest($this->context . $ucmType . '.filter.order_Dir', 'filter_order_Dir', '', 'string'))
		{
			$direction = $this->getUserStateFromRequest($this->context . $ucmType . '.filter.order_Dir', 'filter_order_Dir', '', 'string');
		}

		$fromDate = $this->getUserStateFromRequest($this->context . '.fromDate', 'fromDate', '', 'STRING');
		$toDate = $this->getUserStateFromRequest($this->context . '.toDate', 'toDate', '', 'STRING');

		if (!empty($fromDate) || !empty($toDate))
		{
			$fromDate = empty($fromDate) ? JFactory::getDate('now -1 month')->toSql() : JFactory::getDate($fromDate)->toSql();
			$toDate = empty($toDate) ? JFactory::getDate('now')->toSql() : JFactory::getDate($toDate)->toSql();

			// If from date is less than to date then swipe the dates
			if ($fromDate > $toDate)
			{
				$tmpDate = $fromDate;
				$fromDate = $toDate;
				$toDate = $tmpDate;
			}

			$this->setState($ucmType . ".filter.fromDate", $fromDate);
			$this->setState($ucmType . ".filter.toDate", $toDate);
		}

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
		$query->select('a.*');

		foreach ($this->fields as $fieldId => $field)
		{
			if ($field->type == 'number')
			{
				$query->select('CAST(MAX(CASE WHEN fv.field_id=' . $fieldId . ' THEN value END) AS SIGNED)  `' . $fieldId . '`');
			}
			else
			{
				$query->select('MAX(CASE WHEN fv.field_id=' . $fieldId . ' THEN value END) `' . $fieldId . '`');
			}
		}

		$query->from($db->qn('#__tj_ucm_data', 'a'));

		// Join over the users for the checked out user
		$query->join(
		"LEFT", $db->qn('#__tjfields_fields_value', 'fv') . ' ON (' . $db->qn('fv.content_id') . ' = ' . $db->qn('a.id') . ')'
		);

		$client = $this->getState('ucm.client');

		if (!empty($client))
		{
			$query->where($db->qn('a.client') . ' = ' . $db->q($db->escape($client)));
		}

		$ucmTypeId = $this->getState('ucmType.id', '', 'INT');

		if (!empty($ucmTypeId))
		{
			$query->where($db->qn('a.type_id') . ' = ' . (INT) $ucmTypeId);
		}

		$createdBy = $this->getState('created_by', '', 'INT');

		if (!empty($createdBy))
		{
			$query->where($db->qn('a.created_by') . ' = ' . (INT) $createdBy);
		}

		// Filter for parent record
		$parentId = $this->getState('parent_id');

		if (is_numeric($parentId))
		{
			$query->where($db->qn('a.parent_id') . ' = ' . $parentId);
		}

		// Show records belonging to users cluster if com_cluster is installed and enabled - start
		$clusterExist = ComponentHelper::getComponent('com_cluster', true)->enabled;

		if ($clusterExist)
		{
			JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
			$fieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
			$fieldTable->load(array('client' => $client, 'type' => 'cluster'));

			if ($fieldTable->id)
			{
				JLoader::import("/components/com_cluster/includes/cluster", JPATH_ADMINISTRATOR);
				$clustersModel = ClusterFactory::model('Clusters', array('ignore_request' => true));
				$clusters = $clustersModel->getItems();
				$usersClusters = array();

				if (!empty($clusters))
				{
					foreach ($clusters as $clusterList)
					{
						if (!empty($clusterList->id))
						{
							if (TjucmAccess::canView($ucmTypeId, $clusterList->id))
							{
								$usersClusters[] = $clusterList->id;
							}
						}
					}
				}

				// If cluster array empty then we set 0 in whereclause query
				if (empty($usersClusters))
				{
					$usersClusters[] = 0;
				}

				$query->where($db->qn('a.cluster_id') . ' IN (' . implode(",", $usersClusters) . ')');
			}
		}

		// Filter by published state
		$published = $this->getState('filter.state', '');

		if (is_numeric($published))
		{
			$query->where($db->qn('a.state') . ' = ' . (INT) $published);
		}
		elseif ($published === '')
		{
			$query->where(($db->qn('a.state') . ' IN (0, 1)'));
		}

		// Filter by draft status
		$draft = $this->getState('filter.draft');

		if (in_array($draft, array('0', '1')))
		{
			$query->where($db->qn('a.draft') . ' = ' . $draft);
		}

		// Search by content id
		$search = $this->getState($client . '.filter.search');

		if (!empty($search))
		{
			$search = $db->escape(trim($search), true);

			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->qn('a.id') . ' = ' . (int) str_replace('id:', '', $search));
			}
		}

		$fromDate = $this->getState($client . '.filter.fromDate');
		$toDate = $this->getState($client . '.filter.toDate');

		if (!empty($fromDate) || !empty($toDate))
		{
			$query->where('DATE(' . $db->qn('a.created_date') . ') ' . ' BETWEEN ' . $db->q($fromDate) . ' AND ' . $db->q($toDate));
		}

		// Search on fields data
		$this->filterContent($client, $query);

		// Filter by cluster
		$clusterId = (int) $this->getState($client . '.filter.cluster_id');

		if ($clusterId)
		{
			$query->where($db->qn('a.cluster_id') . ' = ' . $clusterId);
		}

		// Filter by category
		$categoryId = (int) $this->getState($client . '.filter.category_id');

		if ($categoryId)
		{
			$query->where($db->qn('a.category_id') . ' = ' . $categoryId);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		$query->group($db->qn('a.id'));

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($db->qn($orderCol) . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get list items
	 *
	 * @return  ARRAY
	 *
	 * @since    1.6
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get id of multi-select fields
		$fields = implode(',', array_keys($this->fields));

		if ($fields != '')
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->qn('id'));
			$query->from($db->qn('#__tjfields_fields'));
			$query->where($db->qn('id') . ' IN(' . $fields . ')');
			$query->where($db->qn('type') . ' IN("multi_select", "tjlist")');
			$query->where('(' . $db->qn('params') . ' LIKE ' . $db->q('%multiple":"true%') . ' OR ' . $db->qn('params') . ' LIKE ' . $db->q('%multiple":"1%') . ')');
			$db->setQuery($query);

			$fieldsList = $db->loadColumn();

			foreach ($items as $k => &$item)
			{
				$item = (ARRAY) $item;

				foreach ($fieldsList as $field)
				{
					if ($item[$field] == '')
					{
						continue;
					}

					$query = $db->getQuery(true);
					$query->select($db->qn('value'));
					$query->from($db->qn('#__tjfields_fields_value'));
					$query->where($db->qn('content_id') . ' = ' . $item['id']);
					$query->where($db->qn('client') . ' = ' . $db->q($item['client']));
					$query->where($db->qn('field_id') . ' = ' . $field);
					$db->setQuery($query);
					$values = $db->loadColumn();

					if (count($values) > 1)
					{
						$item[$field] = $values;
					}
				}

				$item = (OBJECT) $item;
			}
		}

		return $items;
	}

	/**
	 * Function to filter content as per field values
	 *
	 * @param   string  $client  Client
	 * 
	 * @param   OBJECT  &$query  query object
	 *
	 * @return   Array  Content Ids
	 *
	 * @since    1.2.1
	 */
	private function filterContent($client, &$query)
	{
		$db = $this->getDbo();
		$subQuery = $db->getQuery(true);
		$subQuery->select(1);
		$subQuery->from($db->qn('#__tjfields_fields_value', 'v'));

		// Flag to mark if field specific search is done from the search box
		$filterFieldFound = 0;

		// Variable to store count of the self joins on the fields_value table
		$filterFieldsCount = 0;

		// Filter by field value
		$search = $this->getState($client . '.filter.search');

		if (!empty($this->fields) && (stripos($search, 'id:') !== 0))
		{
			foreach ($this->fields as $fieldId => $field)
			{
				// For field specific search
				if (stripos($search, $field->label . ':') === 0)
				{
					$filterFieldsCount++;

					$subQuery->join('LEFT', $db->qn('#__tjfields_fields_value', 'v' . $filterFieldsCount) . ' ON (' . $db->qn('v' .
					'.content_id') . ' = ' . $db->qn('v' . $filterFieldsCount . '.content_id') . ')');

					$search = trim(str_replace($field->label . ':', '', $search));
					$subQuery->where($db->qn('v' . $filterFieldsCount . '.field_id') . ' = ' . $fieldId);
					$subQuery->where($db->qn('v' . $filterFieldsCount . '.value') . ' LIKE ' . $db->q('%' . $search . '%'));
					$filterFieldFound = 1;

					break;
				}
			}
		}

		// For generic search
		if ($filterFieldFound == 0 && !empty($search)  && (stripos($search, 'id:') !== 0))
		{
			$filterFieldsCount++;

			$subQuery->join('LEFT', $db->qn('#__tjfields_fields_value', 'v' . $filterFieldsCount) . ' ON (' . $db->qn('v' .
			'.content_id') . ' = ' . $db->qn('v' . $filterFieldsCount . '.content_id') . ')');
			$subQuery->where($db->qn('v' . $filterFieldsCount . '.value') . ' LIKE ' . $db->q('%' . $search . '%'));
		}

		// For filterable fields
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		$fieldsModel = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
		$fieldsModel->setState('filter.client', $client);
		$fieldsModel->setState('filter.filterable', 1);
		$fields = $fieldsModel->getItems();

		foreach ($fields as $field)
		{
			$filterValue = $this->getState('filter.field.' . $field->name);
			$filteroptionId = $this->getState('filter.field.' . $field->name . '.optionId');

			if ($filterValue != '' || $filteroptionId)
			{
				$filterFieldsCount++;

				$subQuery->join('LEFT', $db->qn('#__tjfields_fields_value', 'v' . $filterFieldsCount) . ' ON (' . $db->qn('v' .
				'.content_id') . ' = ' . $db->qn('v' . $filterFieldsCount . '.content_id') . ')');
				$subQuery->where($db->qn('v' . $filterFieldsCount . '.field_id') . ' = ' . $field->id);

				if ($filteroptionId)
				{
					// Check option id blank or null
					if ($filteroptionId == 'other')
					{
						$subQuery->where('(' . $db->qn('v' . $filterFieldsCount . '.option_id') .
						' is null OR ' . $db->qn('v' . $filterFieldsCount . '.option_id') . ' = 0 )');
					}
					else
					{
						$subQuery->where($db->qn('v' . $filterFieldsCount . '.option_id') . ' = ' . $db->q($filteroptionId));
					}
				}
				else
				{
					$subQuery->where($db->qn('v' . $filterFieldsCount . '.value') . ' = ' . $db->q($filterValue));
				}
			}
		}

		if ($filterFieldsCount > 0)
		{
			$subQuery->where($db->qn('v.content_id') . '=' . $db->qn('a.id'));
			$query->where("EXISTS (" . $subQuery . ")");
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
		$fieldsModel->setState('list.ordering', 'ordering');
		$fieldsModel->setState('list.direction', 'ASC');
		$client = $this->getState('ucm.client');

		if (!empty($client))
		{
			$fieldsModel->setState('filter.client', $client);
		}

		$items = $fieldsModel->getItems();

		$data = array();

		foreach ($items as $item)
		{
			$data[$item->id] = $item;
		}

		return $data;
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
		$query->from($db->qn('#__tjfields_fields_value', 'fv'));
		$query->join('INNER', $db->qn('#__tjfields_fields', 'f') . ' ON (' . $db->qn('f.id') . ' = ' . $db->qn('fv.field_id') . ')');
		$query->where($db->qn('f.state') . '=1');
		$query->where($db->qn('fv.content_id') . ' IN (' . $contentIds . ')');
		$db->setQuery($query);

		return $db->loadObjectList();
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
			$query->select("count(" . $db->qn('id') . ")");
			$query->from($db->qn('#__tjfields_fields'));
			$query->where($db->qn('client') . '=' . $db->q($client));
			$query->where($db->qn('showonlist') . '=1');
			$db->setQuery($query);

			$result = $db->loadResult();

			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to check the compatibility between ucm types
	 *
	 * @param   string  $client  Client
	 * 
	 * @return  mixed
	 * 
	 * @since    __DEPLOY_VERSION__
	 */
	public function canCopyToSameUcmType($client)
	{
		JLoader::import('components.com_tjucm.models.types', JPATH_ADMINISTRATOR);
		$typesModel = BaseDatabaseModel::getInstance('Types', 'TjucmModel');
		$typesModel->setState('filter.state', 1);
		$ucmTypes 	= $typesModel->getItems();

		JLoader::import('components.com_tjucm.models.type', JPATH_ADMINISTRATOR);
		$typeModel = BaseDatabaseModel::getInstance('Type', 'TjucmModel');

		$checkUcmCompatability = false;

		foreach ($ucmTypes as $key => $type)
		{
			if ($client != $type->unique_identifier)
			{
				$result = $typeModel->getCompatibleUcmTypes($client, $type->unique_identifier);

				if ($result)
				{
					$checkUcmCompatability = true;
				}
			}
		}

		JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
		$fieldTable = Table::getInstance('Field', 'TjfieldsTable', array('dbo', JFactory::getDbo()));
		$fieldTable->load(array('client' => $client, 'type' => 'cluster'));

		if (!$checkUcmCompatability && !$fieldTable->id)
		{
			return true;
		}

		return false;
	}
}
