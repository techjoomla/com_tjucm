<?php
/**
 * @package    TJ-UCM
 * 
 * @author     TechJoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_tjucm&view=types&layout=default'); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="type-form" class="form-validate">
	<div class="form-horizontal">
		<div>
			<h1><?php echo JText::_("COM_TJUCM_TYPES_IMPORT");?></h1>
		</div>
		<hr />
		<div class="input-append">
			<input type="file" id="ucm-types-upload" required="true" name="ucm-types-upload" accept="application/json" >
			<button type="submit" form="type-form" class="btn btn-success" value="Submit"><?php echo JText::_("COM_TJUCM_IMPORT");?></button>
		</div>
		<input type="hidden" name="option" value="com_tjucm"/>
		<input type="hidden" name="task" value="types.import"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>