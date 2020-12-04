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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

?>
	<div>
		<div class="modal-body">
			<div class="container-fluid">
				<div class="control-group span6 ucmListField">
					<label class="control-label"><strong><?php echo Text::_('COM_TJUCM_COPY_ITEMS_SELECT_UCM_TYPE'); ?></strong></label>
					<?php echo JHTML::_('select.genericlist', '', 'filter[target_ucm]', 'class="target_ucm" onchange=""', 'text', 'value', $this->state->get('filter.target_ucm'), 'target_ucm' ); ?>
				</div>
				<div class="control-group span6 clusterListField">
					<label class="control-label"><strong><?php echo Text::_('COM_TJUCM_COPY_ITEMS_SELECT_CLUSTER'); ?></strong></label>
					<?php echo JHTML::_('select.genericlist', '', 'filter[cluster_list]', 'class="cluster_list" onchange="" multiple', 'text', 'value', $this->state->get('filter.cluster_list'), 'cluster_list' ); ?>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn" onclick="document.getElementById('target_ucm').value='';document.getElementById('cluster_list').value='';" data-dismiss="modal">
			Cancel</button>
			<button class="btn btn-primary" onclick="jQuery('#item-form #tjucm_loader').show(); tjUcmItems.copyItem();">
				<i class="fa fa-clone"></i>
				<?php echo Text::_('COM_TJUCM_COPY_ITEMS_BUTTON'); ?>
			</button>
		</div>
	</div>
	<input type="hidden" name="option" value="com_tjucm"/>
