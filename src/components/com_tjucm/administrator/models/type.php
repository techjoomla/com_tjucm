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

jimport('joomla.application.component.modeladmin');

/**
 * Tjucm model.
 *
 * @since  1.6
 */
class TjucmModelType extends JModelAdmin
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_TJUCM';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_tjucm.type';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

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

		$config['event_after_delete'] = 'tjUcmOnAfterTypeDelete';
		$config['event_change_state'] = 'tjUcmOnAfterTypeChangeState';

		parent::__construct($config);
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
	public function getTable($type = 'Type', $prefix = 'TjucmTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
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
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_tjucm.type', 'type',
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
		$data = JFactory::getApplication()->getUserState('com_tjucm.edit.type.data', array());

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
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Get Params data
			$params = $item->params;

			foreach ($params as $key => $param)
			{
				$item->$key = $param;
			}
		}

		return $item;
	}

	/**
	 * Method to duplicate an Type
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

		// Access checks.
		if (!$user->authorise('core.create', 'com_tjucm'))
		{
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
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
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable  $table  Table Object
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__tj_ucm_types');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$input  = JFactory::getApplication()->input;
		$filter = JFilterInput::getInstance();

		// Alter the title for save as copy
		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['title'] == $origTable->title)
			{
				list($title, $alias) = $this->generateNewAlias($data['alias'], $data['title']);
				$data['title'] = $title;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}

			$data['state'] = 0;
		}

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0))
		{
			if ($data['alias'] == null)
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['title']);
				}
				else
				{
					$data['alias'] = JFilterOutput::stringURLSafe($data['title']);
				}

				$table = $this->getTable();

				if ($table->load(array('alias' => $data['alias'])))
				{
					$msg = JText::_('COM_TJUCM_SAVE_WARNING');
				}

				list($title, $alias) = $this->generateNewAlias($data['alias'], $data['title']);
				$data['alias'] = $alias;

				if (isset($msg))
				{
					JFactory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}

		// Remove white spaces from alias if any
		$data['alias'] = str_replace(" ", "_", trim($data['alias']));

		if (!empty($data['id']))
		{
			$field_group = $this->getGroupCount($data['unique_identifier']);

			// Not able to get count using getTotal method of category model
			$field_category = $this->common->getDataValues('#__categories', 'count(*)', 'extension = "' . $data['unique_identifier'] . '"', 'loadResult');

			// $field_category = $this->getCategoryCount($data['unique_identifier']);

			if ($field_group == 0 && $field_category == 0)
			{
				$data['unique_identifier'] = 'com_tjucm.' . $data['alias'];
			}
		}
		else
		{
			$data['unique_identifier'] = 'com_tjucm.' . $data['alias'];
		}

		$params = array();
		$params['is_subform'] = $data['is_subform'];
		$params['allow_draft_save'] = $data['allow_draft_save'];
		$params['allow_auto_save'] = $data['allow_auto_save'];
		$params['publish_items'] = $data['publish_items'];
		$params['allowed_count'] = $data['allowed_count'];
		$params['layout'] = $data['layout'];
		$params['details_layout'] = $data['details_layout'];
		$params['list_layout'] = $data['list_layout'];

		// If UCM type is a subform then need to add content_id as hidden field in the form - For flat subform storage
		JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
		$db = JFactory::getDbo();
		$tjfieldsFieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
		$tjfieldsFieldTable->load(array('name' => str_replace('.', '_', $data['unique_identifier']) . '_contentid'));

		if ($params['is_subform'] == 1 && empty($tjfieldsFieldTable->id))
		{
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models');
			$fieldGroup = array("name" => "hidden", "title" => "hidden", "client" => $data['unique_identifier'], "state" => 1);
			$tjFieldsGroupModel = JModelLegacy::getInstance('Group', 'TjfieldsModel', array('ignore_request' => true));
			$tjFieldsGroupModel->save($fieldGroup);
			$fieldGroupId = (int) $tjFieldsGroupModel->getState($tjFieldsGroupModel->getName() . '.id');
			$field = array(
			"label" => "contentid",
			"name" => "contentid",
			"type" => "hidden",
			"client" => $data['unique_identifier'],
			"state" => 1,
			"group_id" => $fieldGroupId, );
			$tjFieldsFieldModel = JModelLegacy::getInstance('Field', 'TjfieldsModel', array('ignore_request' => true));
			$input->post->set('client_type', end(explode(".", $data['unique_identifier'])));
			$tjFieldsFieldModel->save($field);
			$input->post->set('client_type', '');
		}

		// If UCM type is a subform then it cant be saved as draft and auto save is also disabled
		if ($params['is_subform'] == 1)
		{
			$params['allow_draft_save'] = $params['allow_auto_save'] = $params['allowed_count'] = 0;
		}

		// If auto save is enabled then draft save is enabled by default
		if ($params['allow_auto_save'] == 1)
		{
			$params['allow_draft_save'] = 1;
		}

		$data['params'] = json_encode($params);

		if (parent::save($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');
			$data['typeId'] = $id;
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('actionlog', 'tjucm');
			$isNew = ($data['id'] != 0) ? false : true;
			$dispatcher->trigger('tjUcmOnAfterTypeSave', array($data, $isNew));

			return true;
		}

		return false;
	}

	/**
	 * Method to get count of group.
	 *
	 * @param   string  $client  The client.
	 *
	 * @return	Int  Count of group
	 *
	 * @since	12.2
	 */
	public function getGroupCount($client)
	{
		JLoader::import('components.com_tjfields.models.groups', JPATH_ADMINISTRATOR);
		$items_model = JModelLegacy::getInstance('Groups', 'TjfieldsModel');
		$items_model->setState('filter.client', $client);

		return $items_model->getTotal();
	}

	/**
	 * Method to get count of category.
	 *
	 * @param   string  $client  The client.
	 *
	 * @return	Int  Count of category
	 *
	 * @since	12.2
	 */
	public function getCategoryCount($client)
	{
		JLoader::import('components.com_categories.models.categories', JPATH_ADMINISTRATOR);
		$categories_model = JModelLegacy::getInstance('Categories', 'CategoriesModel');
		$categories_model->setState('filter.extension', $client);

		return $categories_model->getTotal();
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   string  $alias  The alias.
	 * @param   string  $title  The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 * @since	12.2
	 */
	protected function generateNewAlias($alias, $title)
	{
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias)))
		{
			$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	/**
	 * Method to get UCM type id
	 *
	 * @param   string  $client  The client.
	 *
	 * @return	INT  ucm type id
	 *
	 * @since	1.0
	 */
	public function getTypeId($client)
	{
		$table = $this->getTable();
		$table->load(array('unique_identifier' => $client));

		return $table->id;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   1.2.1
	 */
	public function delete(&$pks)
	{
		$pks = (array) $pks;

		if (empty($pks))
		{
			return false;
		}

		// Load the required files in the delete operation
		JLoader::import('components.com_tjfields.models.groups', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_tjfields.models.group', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_tjucm.models.items', JPATH_SITE);
		JLoader::import('components.com_tjucm.models.itemform', JPATH_SITE);

		// Delete related field groups and fields and items data related to the UCM Type
		foreach ($pks as $pk)
		{
			if (empty($pk))
			{
				continue;
			}

			// Get ucm data
			$table = $this->getTable();
			$table->load(array('id' => $pk));

			// Get all field groups in the UCM type
			$fieldGroupsModel = JModelLegacy::getInstance('Groups', 'TjfieldsModel', array('ignore_request' => true));
			$fieldGroupsModel->setState("filter.client", $table->unique_identifier);
			$fieldGroups = $fieldGroupsModel->getItems();

			if (!empty($fieldGroups))
			{
				$tjFieldsGroupModel = JModelLegacy::getInstance('group', 'TjfieldsModel', array('ignore_request' => true));

				foreach ($fieldGroups as $fieldGroup)
				{
					// Delete field groups in UCM type
					$status = $tjFieldsGroupModel->delete($fieldGroup->id);

					if ($status === false)
					{
						return false;
					}
				}
			}

			// Get all field records related to the UCM type
			$itemsModel = JModelLegacy::getInstance('Items', 'TjucmModel', array('ignore_request' => true));
			$itemsModel->setState("ucm.client", $table->unique_identifier);
			$items = $itemsModel->getItems();

			// Delete records related to the UCM type
			if (!empty($items))
			{
				$itemFormModel = JModelLegacy::getInstance('ItemForm', 'TjucmModel', array('ignore_request' => true));

				foreach ($items as $item)
				{
					$status = $itemFormModel->delete($item->id);

					if ($status === false)
					{
						return false;
					}
				}
			}

			// Delete UCM type
			if (!parent::delete($pk))
			{	
				return false;
			}
		}

		return true;
	}
}
