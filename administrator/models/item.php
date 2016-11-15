<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjucm
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.file');
/**
 * Tjucm model.
 *
 * @since  1.6
 */
class TjucmModelItem extends JModelAdmin
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
	public $typeAlias = 'com_tjucm.item';

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
		$this->common  = new TjucmFunList;
		$this->client  = JFactory::getApplication()->input->get('client');

		if (empty($this->client))
		{
			$this->client  = JFactory::getApplication()->input->get('jform', array(), 'array')['client'];
		}

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
	public function getTable($type = 'Item', $prefix = 'TjucmTable', $config = array())
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
			'com_tjucm.item', 'item',
			array('control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the form for extra fields.
	 * This form file will be created by field manager.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   Array    $data      An optional array of data for the form to interogate.
	 * @param   Boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm    A JForm    object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getFormExtra($data = array(), $loadData = true)
	{
		$category_id = $this->common->getDataValues('#__categories', 'DISTINCT id as category_id', 'extension = "' . $this->client . '"', 'loadResult');

		/* Explode client 1. Componet name 2.type */
		$client = explode(".", $this->client);
		/* End */

		// Check if form file is present.

		$filePath = JPATH_ADMINISTRATOR . '/components/com_tjucm/models/forms/' . $client[1] . '_extra.xml';

		if (!empty($category_id))
		{
			$filePath = JPATH_ADMINISTRATOR . '/components/com_tjucm/models/forms/' . $category_id . $client[1] . '_extra.xml';
		}

		if (!JFile::exists($filePath))
		{
			return false;
		}

		// Get the form.
		$form = $this->loadForm($client[0] . '.' . $client[1] . '_extra', $client[1] . '_extra', array('control' => 'jform', 'load_data' => $loadData));

		if (!empty($category_id))
		{
			$form = $this->loadForm(
				$client[0] . '.' . $category_id . $client[1] . '_extra',
				$category_id . $client[1] . '_extra',
				array('control' => 'jform', 'load_data' => $loadData)
			);
		}

		if (empty($form))
		{
			return false;
		}

		// Load form data for extra fields (needed for editing).
		$dataExtra = $this->loadFormDataExtra();

		// Bind the data for extra fields to this form.
		$form->bind($dataExtra);

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
	 * Method to get the form for extra fields.
	 * This form file will be created by field manager.
	 *
	 * The base form is loaded from XML
	 *
	 * @return  JForm    A JForm    object on success, false on failure
	 *
	 * @since	1.6
	 */
	protected function loadFormDataExtra()
	{
		$data = JFactory::getApplication()->getUserState('com_tjucm.edit.directory.data', array());

		if (empty($data))
		{
			$data = $this->getDataExtraFields();
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
			// Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Method to get the extra fields information
	 *
	 * @param   array  $id  Id of the record
	 *
	 * @return	Extra field data
	 *
	 * @since	1.8.5
	 */
	public function getDataExtra($id = null)
	{
		if (empty($id))
		{
			$input = JFactory::getApplication()->input;
			$id = $input->get('id', '', 'INT');
		}

		if (empty($id))
		{
			return false;
		}

		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;
		$data               = array();
		$data['client']     = $this->client;
		$data['content_id'] = $id;
		$extra_fields_data = $tjFieldsHelper->FetchDatavalue($data);

		return $extra_fields_data;
	}

	/**
	 * Method to get the data of extra form fields
	 * This form file will be created by field manager.
	 *
	 * @param   INT  $id  Id of record
	 *
	 * @return  JForm    A JForm    object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getDataExtraFields($id = null)
	{
		$input = JFactory::getApplication()->input;
		$user = JFactory::getUser();

		if (empty($id))
		{
			$id = $input->get('id', '', 'INT');
		}

		if (empty($id))
		{
			return false;
		}

		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;

		$data = array();
		$data['client']      = $this->client;
		$data['content_id']  = $id;
		$data['user_id']     = JFactory::getUser()->id;

		$extra_fields_data = $tjFieldsHelper->FetchDatavalue($data);

		$extra_fields_data_formatted = array();

		foreach ($extra_fields_data as $efd)
		{
			if (!is_array($efd->value))
			{
				$extra_fields_data_formatted[$efd->name] = $efd->value;
			}
			else
			{
				switch ($efd->type)
				{
					case 'multi_select':
						foreach ($efd->value as $option)
						{
							$temp[] = $option->value;
						}

						if (!empty($temp))
						{
							$extra_fields_data_formatted[$efd->name] = $temp;
						}
					break;

					case 'single_select':
						foreach ($efd->value as $option)
						{
							$extra_fields_data_formatted[$efd->name] = $option->value;
						}
					break;

					case 'radio':
					default:
						foreach ($efd->value as $option)
						{
							$extra_fields_data_formatted[$efd->name] = $option->value;
						}
					break;
				}
			}
		}

		$this->_item_extra_fields = $extra_fields_data_formatted;

		return $this->_item_extra_fields;
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
				$db->setQuery('SELECT MAX(ordering) FROM #__tj_ucm_data');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data              The form data.
	 * @param   array  $extra_jform_data  Exra field data.
	 * @param   array  $post              all form field data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data, $extra_jform_data = '', $post = '')
	{
		$input  = JFactory::getApplication()->input;
		$filter = JFilterInput::getInstance();

		$data['type_id'] = $this->common->getDataValues('#__tj_ucm_types', 'id AS type_id', 'unique_identifier = "' . $this->client . '"', 'loadResult');

		if (parent::save($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');

			if (!empty($extra_jform_data))
			{
				// Save extra fields data.
				$this->saveExtraFields($extra_jform_data, $id);
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to save the extra fields data.
	 *
	 * @param   array  $extra_jform_data  Extra fields data
	 * @param   INT    $id                Id of the record
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since  1.6
	 */
	public function saveExtraFields($extra_jform_data, $id)
	{
		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;

		$data = array();
		$data['client']      = $this->client;
		$data['content_id']  = $id;
		$data['fieldsvalue'] = array();
		$data['fieldsvalue'] = $extra_jform_data;
		$data['user_id']     = JFactory::getUser()->id;

		$tjFieldsHelper->saveFieldsValue($data);
	}

	/**
	 * Method to validate the extraform data.
	 *
	 * Added by manoj.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validateExtra($form, $data, $group = null)
	{
		$data = parent::validate($form, $data);

		return $data;
	}
}
