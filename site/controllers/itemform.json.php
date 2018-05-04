<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

require_once JPATH_SITE . "/components/com_tjfields/filterFields.php";

/**
 * Item controller class.
 *
 * @since  1.6
 */
class TjucmControllerItemForm extends JControllerForm
{
	/**
	 * Delete File .
	 *
	 * @return boolean|string
	 *
	 * @since	1.6
	 */

	public function deleteFile()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();
		$jinput = $app->input;

		// Here, fpht means file encoded path
		$filePath = $jinput->get('filePath', '', 'BASE64');
		require_once JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		$tjFieldsHelper = new TjfieldsHelper;
		$filePath = base64_decode($filePath);

		$returnValue = $tjFieldsHelper->tjFileDelete($filePath);
		$msg = $returnValue ? JText::_('COM_TJUCM_FILE_DELETE_SUCCESS') : JText::_('COM_TJUCM_FILE_DELETE_ERROR');

		echo new JResponseJson($returnValue, $msg);
	}
}
