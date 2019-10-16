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

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

require_once JPATH_SITE . "/components/com_tjfields/filterFields.php";

use Joomla\Utilities\ArrayHelper;

/**
 * Tjucm model.
 *
 * @since  1.6
 */
class TjucmModelItem extends JModelAdmin
{
	private $client = '';

	private $item = '';

	// Use imported Trait in model
	use TjfieldsFilterField;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app  = JFactory::getApplication('com_tjucm');
		$user = JFactory::getUser();

		// Load state from the request.
		$id = $app->input->getInt('id');

		$this->setState('item.id', $id);

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
		$params = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			if ($params_array['item_id'])
			{
				$this->setState('item.id', $params_array['item_id']);
			}
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get item data.
	 *
	 * @param   integer  $pk  The id of the item.
	 *
	 * @return  object|boolean|JException  Menu item data object on success, boolean false or JException instance on error
	 *
	 * @since    _DEPLOY_VERSION_
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('item.id');
		$db = $this->getDbo();
		$query = $db->getQuery(true)->select("*");
		$query->from($db->qn('#__tj_ucm_data', 'a'))->where($db->qn('a.id') . ' = ' . (int) $pk);
		$db->setQuery($query);
		$item = $db->loadObject();

		// Variable to store creator name and id
		$created_by = JFactory::getUser($item->created_by);
		$created_by = array("id" => $created_by->id, "name" => $created_by->name);
		$item->created_by = $created_by;

		// Variable to store modifier name and id
		$modified_by = JFactory::getUser($item->modified_by);
		$modified_by = array("id" => $modified_by->id, "name" => $modified_by->name);
		$item->modified_by = $modified_by;

		// Getting UCM Type Details
		$query = $db->getQuery(true)->select($db->qn(array('id', 'title', 'unique_identifier')));
		$query->from($db->qn('#__tj_ucm_types', 'a'))->where($db->qn('a.id') . ' = ' . (int) $item->type_id);
		$db = $db->setQuery($query);
		$ucmType = $db->loadObject();
		$item->ucmType = $ucmType;

		$query = $db->getQuery(true)->select($db->qn(array('id', 'title')));
		$query->from($db->qn('#__tjfields_groups', 'a'))->where($db->qn('a.client') . ' = ' . $db->quote($item->client));
		$db = $db->setQuery($query);
		$fieldGroups = $db->loadObjectList();

		// Getting fields of respective fieldgroups
		foreach ($fieldGroups as $groupKey => $groupValue)
		{
			$query = $db->getQuery(true);
			$query->select($db->qn(array('a.label', 'b.id', 'b.value', 'b.option_id')));
			$query->from($db->qn('#__tjfields_fields', 'a'));
			$query->join('INNER', $db->qn('#__tjfields_fields_value', 'b') . ' ON (' . $db->qn('b.field_id') . ' = ' . $db->qn('a.id') . ')');
			$query->where($db->qn('a.group_id') . ' = ' . (int) $groupValue->id . ' AND ' . $db->qn('b.content_id') . ' = ' . (int) $item->id);
			$db = $db->setQuery($query);
			$fields = $db->loadObjectList();
			$fieldGroups[$groupKey]->fields = $fields;
		}

		$item->fieldGroups = $fieldGroups;

		return $item;
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
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
		$canView = $user->authorise('core.type.viewitem', 'com_tjucm.type.' . $ucmTypeId);

		// Get a level row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		if ($table->load($id))
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

			if (!empty($this->item->id))
			{
				if ($canView || ($this->item->created_by == $user->id))
				{
					$this->item->params->set('access-view', true);
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
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	public function getTable($type = 'Item', $prefix = 'TjucmTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 *
	 * @param   string  $alias  Item alias
	 *
	 * @return  mixed
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
	 * Get the name of a category by id
	 *
	 * @param   int  $id  Category id
	 *
	 * @return  Object|null	Object if success, null in case of failure
	 */
	public function getCategoryName($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('title')
			->from('#__categories')
			->where('id = ' . $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Publish the element
	 *
	 * @param   int  &$id    Item id
	 * @param   int  $state  Publish state
	 *
	 * @return  boolean
	 */
	public function publish(&$id, $state = 1)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->draft = $state == 1 ? 0 : 1;
		$table->state = $state;

		if ($table->store())
		{
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('actionlog', 'tjucm');
			$dispatcher->trigger('tjUcmOnAfterItemChangeState', array($id,$state));

			return true;
		}

		return false;
	}

	/**
	 * Method to delete an item
	 *
	 * @param   int  &$id  Element id
	 *
	 * @return  bool
	 */
	public function delete(&$id)
	{
		$app = JFactory::getApplication('com_tjucm');

		$ucmTypeId = $this->getState('ucmType.id');
		$user = JFactory::getUser();
		$canDelete = $user->authorise('core.type.deleteitem', 'com_tjucm.type.' . $ucmTypeId);

		if ($canDelete)
		{
			$table = $this->getTable();

			return $table->delete($id);
		}
		else
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);

			return false;
		}
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
	 * Method to check if a user has permissions to view ucm items of given type
	 *
	 * @param   int  $typeId  Type Id
	 *
	 * @return  boolean
	 */
	public function canView($typeId)
	{
		$user = JFactory::getUser();

		return $user->authorise('core.type.viewitem', 'com_tjucm.type.' . $typeId);
	}
}
