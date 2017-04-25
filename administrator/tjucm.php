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

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_tjucm'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

$path = JPATH_COMPONENT_ADMINISTRATOR . '/classes/' . 'funlist.php';

if (!class_exists('TjucmFunList'))
{
	// Require_once $path;
	JLoader::register('TjucmFunList', $path);
	JLoader::load('TjucmFunList');
}


// Load backend helper
$path = JPATH_ADMINISTRATOR . '/components/com_tjucm/helpers/tjucm.php';

if (!class_exists('TjucmHelper'))
{
	JLoader::register('TjucmHelper', $path);
	JLoader::load('TjucmHelper');
}

JLoader::registerPrefix('Tjucm', JPATH_COMPONENT_ADMINISTRATOR);

$controller = JControllerLegacy::getInstance('Tjucm');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
