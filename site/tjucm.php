<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjucm
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Tjucm', JPATH_COMPONENT);
JLoader::register('TjucmController', JPATH_COMPONENT . '/controller.php');

// Load backend helper
$path = JPATH_ADMINISTRATOR . '/components/com_tjucm/helpers/tjucm.php';

if (!class_exists('TjucmHelper'))
{
	JLoader::register('TjucmHelper', $path);
	JLoader::load('TjucmHelper');
}

// Execute the task.
$controller = JControllerLegacy::getInstance('Tjucm');

$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
