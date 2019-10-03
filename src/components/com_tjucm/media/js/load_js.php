<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();

// Add Javascript vars in array
$doc->addScriptOptions('tjucm', array());

// Load JS files
JHtml::script(JUri::root() . 'media/com_tjucm/js/core/class.js');
JHtml::script(JUri::root() . 'media/com_tjucm/js/com_tjucm.js');
JHtml::script(JUri::root() . 'media/com_tjucm/js/core/base.js');
JHtml::script(Juri::root() . 'media/com_tjucm/js/services/item.js');
