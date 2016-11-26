<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjucm
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Tjucm records.
 *
 * @since  1.6
 */
class TjucmModelItems extends JModelList
{
	private $client = '';
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
				'id', 'a.`id`',
				'ordering', 'a.`ordering`',
				'state', 'a.`state`',
				'type_id', 'a.`type_id`',
				'created_by', 'a.`created_by`',
				'created_date', 'a.`created_date`',
				'modified_by', 'a.`modified_by`',
				'modified_date', 'a.`modified_date`',
			);
		}

		$this->fields_separator = "#:";
		$this->records_separator = "#=>";

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
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_tjucm');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
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
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$group_concat = 'GROUP_CONCAT(CONCAT_WS("' . $this->fields_separator . '", fields.id, fieldValue.value)';
		$group_concat .= 'SEPARATOR "' . $this->records_separator . '") AS field_values';

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.id, ' . $group_concat
			)
		);

		$query->from('`#__tj_ucm_data` AS a');

		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the foreign key 'type_id'

		$query->join('INNER', '#__tj_ucm_types AS types ON types.`id` = a.`type_id`');
		$query->where('(types.state IN (1))');

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');

		// Join over the tjfield
		$query->join('INNER', '#__tjfields_fields AS fields ON a.client = fields.client');

		// Join over the tjfield value
		$query->join('INNER', '#__tjfields_fields_value AS fieldValue ON a.id = fieldValue.content_id');

		$query->where('a.client = ' . $db->quote($db->escape($this->client)));
		$query->where('fields.id = fieldValue.field_id');
		$query->where('fields.showonlist =  1');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
			}
		}

		$query->group('fieldValue.content_id');

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
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getFields()
	{
		JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
		$items_model = JModelLegacy::getInstance('Fields', 'TjfieldsModel');
		$items_model->setState('filter.showonlist', 1);
		$items_model->setState('filter.client', $this->client);
		$items = $items_model->getItems();

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
		$items = parent::getItems();

		foreach ($items as $item)
		{
			if (!empty ($item->field_values))
			{
				$explode_field_values = explode($this->records_separator, $item->field_values);

				$colValue = array();

				foreach ($explode_field_values as $field_values)
				{
					$explode_explode_field_values = explode($this->fields_separator, $field_values);

					$fieldId = $explode_explode_field_values[0];
					$fieldValue = $explode_explode_field_values[1];

					$colValue[$fieldId] = $fieldValue;
				}

				$listcolumns = $this->getFields();

				if (!empty($listcolumns))
				{
					$fieldData = array();

					foreach ($listcolumns as $col_id => $col_name)
					{
						if (array_key_exists($col_id, $colValue))
						{
							$fieldData[$col_id] = $colValue[$col_id];
						}
						else
						{
							$fieldData[$col_id] = "";
						}

						$item->field_values = $fieldData;
					}
				}
			}
		}

		return $items;
	}
}
