<?php
/**
 * @package     Com_tjucm
 *
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;

?>
<a class="btn" type="button" onclick="document.getElementById('batch-ucm-id').value='';" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('itemform.batch');">
	<?php echo JText::_('COM_TJUCM_BATCH_PROCESS'); ?>
</button>
