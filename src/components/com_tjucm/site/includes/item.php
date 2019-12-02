<?php
/**
 * @package     Tjucm
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Object\CMSObject;

/**
 * Tjucm item class.
 *
 * @since  __DEPLOY_VERSION__
 */
class TjucmItem extends CMSObject
{
	/**
	 * The auto incremental primary key of the item
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $id = 0;

	/**
	 * In case of UCM-Subform parent_id is used to point to the parent record of the UCM-Subform records
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $parent_id = 0;

	/**
	 * Id of assets table for the item
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $asset_id = 0;

	/**
	 * Defines ordering of the item - Not Used As Of Now
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $ordering = 0;

	/**
	 * State of item
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $state = COM_TJUCM_ITEM_STATE_UNPUBLISHED;

	/**
	 * Category of the item - Category from the categories created for UCM type
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $category_id = '';

	/**
	 * Id of UCM type to which the item belongs
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $type_id = 0;

	/**
	 * Unique identifier of the UCM type
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $client = '';

	/**
	 * Cluster id to which the item belongs
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $cluster_id = '';

	/**
	 * Joomla user id by whom the record is being checked out
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $checked_out = '';

	/**
	 * Joomla user id by whom the record is created
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $created_by = '';

	/**
	 * Joomla user id by whom the record is modified
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $modified_by = '';

	/**
	 * Flag to mark if the item is a draft
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $draft = '';

	/**
	 * Date time when the item was last checked out
	 *
	 * @var    datetime
	 * @since  __DEPLOY_VERSION__
	 */
	public $checked_out_time = '';

	/**
	 * Date time when the item was created
	 *
	 * @var    datetime
	 * @since  __DEPLOY_VERSION__
	 */
	public $created_date = '';

	/**
	 * Date time when the item was last modified
	 *
	 * @var    datetime
	 * @since  __DEPLOY_VERSION__
	 */
	public $modified_date = '';

	/**
	 * holds the already loaded instances of the Item
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $itemObj = array();

	/**
	 * Holds the fields values in the item
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $fieldsValues = array();

	/**
	 * Constructor activating the default information of the item
	 *
	 * @param   int  $id  The unique item key to load.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($id = 0)
	{
		if (!empty($id))
		{
			$this->load($id);
		}
	}

	/**
	 * Returns the item object
	 *
	 * @param   integer  $id  The primary key of the item to load (optional).
	 *
	 * @return  TjucmItem  The item object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new TjucmItem;
		}

		if (empty(self::$itemObj[$id]))
		{
			self::$itemObj[$id] = new TjucmItem($id);
		}

		return self::$itemObj[$id];
	}

	/**
	 * Method to load a item properties
	 *
	 * @param   int  $id  The item id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function load($id)
	{
		$table = Tjucm::table("item");

		if ($table->load($id))
		{
			$this->setProperties($table->getProperties());

			// Load field values for the item
			// $this->setFieldsValues($table->id);

			return true;
		}

		return false;
	}

	/**
	 * Method to save the Item object to the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save()
	{
		$isNew = $this->isNew();

		// Create the item table object
		$table = Tjucm::table('item');

		// Allow an exception to be thrown.
		try
		{
			$table->bind(get_object_vars($this));

			// Check and store the object.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the item data in the database
			$result = $table->store();

			// Set the id for the item object in case we created a new item.
			if ($result && $isNew)
			{
				$this->load($table->get('id'));
				$item = Tjucm::model('item');
				$this->item_id = $item->generateItemID($this->id);

				return $this->save();
			}
			elseif ($result && !$isNew)
			{
				return $this->load($this->id);
			}
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to check is item new or not
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function isNew()
	{
		return $this->id < 1;
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed    The value of the property.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get($property, $default = null)
	{
		if (isset($this->$property))
		{
			return $this->$property;
		}

		return $default;
	}
}
