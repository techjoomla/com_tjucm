<?php
/**
 * @package     Tjucm
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

if (defined('COM_TJUCM_SITE_DEFINE_FILE'))
{
	return;
}

define('COM_TJUCM_ITEM_STATE_PUBLISHED', 1);
define('COM_TJUCM_ITEM_STATE_UNPUBLISHED', 0);
define('COM_TJUCM_ITEM_STATE_ARCHIVED', 2);
define('COM_TJUCM_ITEM_STATE_TRASHED', -2);
define('COM_TJUCM_ITEM_STATE_DRAFT', -1);

// Need this constant for performance purpose Always define this at the end of file
define('COM_TJUCM_SITE_DEFINE_FILE', true);
