<?php
/**
 * @package     Tjfield
 * @subpackage  PlgActionlogTjfield
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * JGive Actions Logging Plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgActionlogTjfield extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Proxy for ActionlogsModelUserlog addLog method
	 *
	 * This method adds a record to #__action_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array   $messages            The contents of the messages to be logged
	 * @param   string  $messageLanguageKey  The language key of the message
	 * @param   string  $context             The context of the content passed to the plugin
	 * @param   int     $userId              ID of user perform the action, usually ID of current logged in user
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		JLoader::register('ActionlogsModelActionlog', JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php');

		/* @var ActionlogsModelActionlog $model */
		$model = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog($messages, $messageLanguageKey, $context, $userId);
	}

	/**
	 * On saving field data logging method
	 *
	 * Method is called after field data is stored in the database.
	 * This method logs who created/edited any field group data
	 *
	 * @param   Array   $field  Holds the Field data
	 * @param   Boolean  $isNew         True if a new report is stored.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function tjfieldOnAfterFieldSave($field, $fieldGroupID, $typeID, $isNew)
	{
		if (!$this->params->get('logActionForFieldSave', 1))
		{
			return;
		}

		$context = JFactory::getApplication()->input->get('option');

		$user = JFactory::getUser();
		$tjucmTableType = Table::getInstance('type', 'TjucmTablet', array());
		$tjucmTableType->load(array('id' => $typeID));

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_TJFIELD_FIELD_CREATED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_TJFIELD_FIELD_UPDATED';
			$action             = 'update';
		}

		// User X has created field PQR under type ABC
		$message = array(
			'action'      => $action,
			'id'          => $field->id,
			'title'       => ucfirst($field->title),
			'type'        => $tjucmTableType->title,
			'itemlink'    => 'index.php?option=com_tjfield&task=field.edit&id=' . $field->id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);

	}
}
