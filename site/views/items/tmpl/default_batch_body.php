<?php
/**
 * @package     Com_tjucm
 *
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
//echo "<pre>"; print_r($this->types); echo"</pre>";
?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="span6">
			<div class="control-group">
				<label id="batch-choose-action-lbl" for="batch-ucm-id" class="control-label">
					<?php echo JText::_('COM_TJUCM_HTML_BATCH_MENU_LABEL'); ?>
				</label>
				<div id="batch-choose-action" class="combo controls">
					<?php echo $this->types; ?>
				</div>
			</div>
		</div>
	</div>
</div>

