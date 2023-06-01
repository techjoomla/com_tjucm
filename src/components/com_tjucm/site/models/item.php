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
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
jimport('joomla.event.dispatcher');

require_once JPATH_SITE . "/components/com_tjfields/filterFields.php";

use Joomla\Utilities\ArrayHelper;

/**
 * Tjucm model.
 *
 * @since  1.6
 */
class TjucmModelItem extends AdminModel
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
		$app  = Factory::getApplication('com_tjucm');

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
			$menuparams = $menuitem->getparams();

			if (!empty($menuparams))
			{
				$ucm_type   = $menuparams->get('ucm_type');

				if (!empty($ucm_type))
				{
					JLoader::import('components.com_tjfields.tables.type', JPATH_ADMINISTRATOR);
					$ucmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
					$ucmTypeTable->load(array('alias' => $ucm_type));
					$ucmType = $ucmTypeTable->unique_identifier;
				}
			}
		}

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/models');
		$tjUcmModelType = BaseDatabaseModel::getInstance('Type', 'TjucmModel');
		$ucmId = $tjUcmModelType->getTypeId($ucmType);

		$this->setState('ucmType.id', $ucmId);

		// Check published state
		if ((!TjucmAccess::canEdit($ucmId, $id)) && (!TjucmAccess::canEditOwn($ucmId, $id)) && (!TjucmAccess::canEditState($ucmId, $id)))
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
		$created_by = Factory::getUser($item->created_by);
		$created_by = array("id" => $created_by->id, "name" => $created_by->name);
		$item->created_by = $created_by;

		// Variable to store modifier name and id
		$modified_by = Factory::getUser($item->modified_by);
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
		$this->item = false;

		if (empty($id))
		{
			$id = $this->getState('item.id');
		}

		// Get UCM type id (Get if user is autorised to edit the items for this UCM type)
		$ucmTypeId = $this->getState('ucmType.id');
		$canView = TjucmAccess::canView($ucmTypeId, $id);

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
					return JError::raiseError(404, Text::_('COM_TJUCM_ITEM_DOESNT_EXIST'));
				}
			}

			// Convert the JTable to a clean JObject.
			$properties  = $table->getProperties(1);
			$properties['params'] = clone $this->getState('params');

			$this->item = ArrayHelper::toObject($properties, 'JObject');
			$this->item->params->set('access-view', false);

			if (!empty($this->item->id))
			{
				if ($canView || ($this->item->created_by == Factory::getUser()->id))
				{
					$this->item->params->set('access-view', true);
				}
			}
		}
		else
		{
			return JError::raiseError(404, Text::_('COM_TJUCM_ITEM_DOESNT_EXIST'));
		}

		return $this->item;
	}

	/**
	 * Get an instance of Table class
	 *
	 * @param   string  $type    Name of the Table class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the Table object. Optional.
	 *
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Item', $prefix = 'TjucmTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');

		return Table::getInstance($type, $prefix, $config);
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
		$table->state = $state;

		// Only if item is published
		if ($state == 1)
		{
			$table->draft = 0;
		}

		if ($table->store())
		{
			JLoader::import('components.com_tjucm.models.items', JPATH_SITE);
			$itemsModel = BaseDatabaseModel::getInstance('Items', 'TjucmModel', array('ignore_request' => true));
			$itemsModel->setState("parent_id", $id);
			$children = $itemsModel->getItems();

			foreach ($children as $child)
			{
				$childTable = $this->getTable();
				$childTable->load($child->id);
				$childTable->state = $state;

				// Only if item is published
				if ($state == 1)
				{
					$childTable->draft = 0;
				}

				$childTable->store();
			}
		}
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
		$app = Factory::getApplication('com_tjucm');

		$ucmTypeId = $this->getState('ucmType.id');
		$canDelete = TjucmAccess::canDelete($ucmTypeId, $id);

		if ($canDelete)
		{
			$table = $this->getTable();

			return $table->delete($id);
		}
		else
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);

			return false;
		}
	}
}
