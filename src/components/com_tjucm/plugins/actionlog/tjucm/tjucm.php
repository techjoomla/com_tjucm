<?php
/**
 * @package     TJUCM
 * @subpackage  PlgActionlogTjucm
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
use Joomla\CMS\Table\Table;

/**
 * JGive Actions Logging Plugin.
 *
 * @since  __DEPLOY__VERSION__
 */
class PlgActionlogTjUcm extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY__VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY__VERSION__
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  __DEPLOY__VERSION__
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
	 * @since   __DEPLOY__VERSION__
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		JLoader::register('ActionlogsModelActionlog', JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php');

		/* @var ActionlogsModelActionlog $model */
		$model = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog($messages, $messageLanguageKey, $context, $userId);
	}

	/**
	 * On saving UCM type data - logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any data of UCM type
	 *
	 * @param   Object   $type   Holds the report data
	 * @param   Boolean  $isNew  True if a new report is stored.
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjUcmOnAfterTypeSave($type, $isNew)
	{
		if ($isNew)
		{
			if (!$this->params->get('logActionForTypeSave', 1))
			{
				return;
			}

			$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_TYPE_ADDED';
			$action             = 'add';
		}
		else
		{
			if (!$this->params->get('logActionForTypeUpdate', 1))
			{
				return;
			}

			$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_TYPE_UPDATED';
			$action             = 'update';
		}

		$context = Factory::getApplication()->input->get('option');
		$user = Factory::getUser();

		$message = array(
			'action'      => $action,
			'id'          => $type['id'],
			'title'       => $type['title'],
			'identifier'  => $type['unique_identifier'],
			'itemlink'    => 'index.php?option=com_tjucm&view=type&layout=edit&id=' . $type['id'],
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On deleting UCM type data - logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited UCM type ,user's data
	 *
	 * @param   string  $context  com_jticketing.
	 * @param   Object  $table    Holds the coupon data.
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjUcmOnAfterTypeDelete($context, $table)
	{
		if (!$this->params->get('logActionForTypeDelete', 1))
		{
			return;
		}

		$context = Factory::getApplication()->input->get('option');
		$user    = Factory::getUser();

		$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_TYPE_DELETED';
		$message = array(
				'action'      => 'delete',
				'id'          => $table->id,
				'title'       => $table->title,
				'identifier'  => $table->unique_identifier,
				'userid'      => $user->id,
				'username'    => $user->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On deleting UCM type data - logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited UCM type ,user's data
	 *
	 * @param   Object  $pks  Holds the UCM type data.
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjUcmOnAfterTypeImport($pks)
	{
		if (!$this->params->get('logActionForTypeImport', 1) || !$pks)
		{
			return;
		}

		$context = Factory::getApplication()->input->get('option');
		$user    = Factory::getUser();

		$tjucmTableType = Table::getInstance('type', 'TjucmTable', array());
		$tjucmTableType->load(array('id' => $pks));

		$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_TYPE_IMPORTED';
		$message = array(
				'action'      => 'import',
				'id'          => $tjucmTableType->id,
				'title'       => $tjucmTableType->title,
				'identifier'  => $tjucmTableType->unique_identifier,
				'itemlink'    => 'index.php?option=com_tjucm&view=type&layout=edit&id=' . $tjucmTableType->id,
				'userid'      => $user->id,
				'username'    => $user->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $user->id);
	}

	/**
	 * On deleting UCM type data - logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited UCM type ,user's data
	 *
	 * @param   Object  $pks  Holds the UCM type data.
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjUcmOnAfterTypeExport($pks)
	{
		if (!$this->params->get('logActionForTypeExport', 1))
		{
			return;
		}

		$context  = Factory::getApplication()->input->get('option');
		$jUser    = Factory::getUser();
		$userId   = $jUser->id;
		$userName = $jUser->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_TJUCM_TYPE_EXPORTED';

		$tjucmTableType = Table::getInstance('type', 'TjucmTable', array());

		foreach ($pks as $pk)
		{
			$tjucmTableType->load(array('id' => $pk));

			$message = array(
					/*'action'      => $action,*/
					'id'          => $tjucmTableType->id,
					'title'       => $tjucmTableType->title,
					'identifier'  => $tjucmTableType->unique_identifier,
					'itemlink'    => 'index.php?option=com_tjucm&view=type&layout=edit&id=' . $tjucmTableType->id,
					'userid'      => $userId,
					'username'    => $userName,
					'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
				);

			$this->addLog(array($message), $messageLanguageKey, $context, $userId);
		}
	}

	/**
	 * On changing state of UCM Type - logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who changed state of UCM type
	 *
	 * @param   String  $context  com_jgive
	 * @param   array   $pks      Holds array of primary key.
	 * @param   int     $value    Switch case value.
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY__VERSION__
	 */
	public function tjUcmOnAfterTypeChangeState($context, $pks, $value)
	{
		if (!$this->params->get('logActionForTypeStateChange', 1))
		{
			return;
		}

		$tjucmTableType = Table::getInstance('type', 'TjucmTable', array());

		$context  = Factory::getApplication()->input->get('option');
		$jUser    = Factory::getUser();
		$userId   = $jUser->id;
		$userName = $jUser->username;

		switch ($value)
		{
			case 0:
				$messageLanguageKey = 'PLG_ACTIONLOGS_TJUCM_TYPE_UNPUBLISHED';
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = 'PLG_ACTIONLOGS_TJUCM_TYPE_PUBLISHED';
				$action             = 'publish';
				break;
			case 2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_TJUCM_TYPE_ARCHIVED';
				$action             = 'archive';
				break;
			case -2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_TJUCM_TYPE_TRASHED';
				$action             = 'trash';
				break;
			default:
				$messageLanguageKey = '';
				$action             = '';
				break;
		}

		foreach ($pks as $pk)
		{
			$tjucmTableType->load(array('id' => $pk));

			$message = array(
					'action'      => $action,
					'id'          => $tjucmTableType->id,
					'title'       => $tjucmTableType->title,
					'identifier'  => $tjucmTableType->unique_identifier,
					'itemlink'    => 'index.php?option=com_tjucm&view=type&layout=edit&id=' . $tjucmTableType->id,
					'userid'      => $userId,
					'username'    => $userName,
					'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
				);

			$this->addLog(array($message), $messageLanguageKey, $context, $userId);
		}
	}
}
