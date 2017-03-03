<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjucm
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_tjucm', JPATH_SITE);
$doc = JFactory::getDocument();

$doc->addScript(JUri::base() . 'administrator/components/com_tjucm/assets/js/jquery.form.js');
$doc->addScript(JUri::base() . 'administrator/components/com_tjucm/assets/js/tjucm_ajaxForm_save.js');
$doc->addScript(JUri::base() . 'administrator/components/com_tjucm/assets/js/tjfield.js');
$doc->addScript(JUri::base() . 'media/com_tjucm/js/form.js');

$jinput                    = JFactory::getApplication();
$baseUrl                   = $jinput->input->server->get('REQUEST_URI', '', 'STRING');
$calledFrom                = (strpos($baseUrl, 'administrator')) ? 'backend' : 'frontend';
$layout                    = ($calledFrom == 'frontend') ? 'default' : 'edit';
$client                    = JFactory::getApplication()->input->get('client');
$is_saved                  = $jinput->input->get("success", '', INT);
$fieldsets_counter_deafult = 0;
$app                       = JFactory::getApplication();
$menu                      = $app->getMenu();
$setnavigation             = false;
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {
	});

	Joomla.submitbutton = function (task) {
		if (task == 'itemform.cancel') {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else {

			if (task != 'itemform.cancel' && document.formvalidator.isValid(document.id('item-form'))) {

				Joomla.submitform(task, document.getElementById('item-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="item-form" class="form-validate">
		<?php

		if ($is_saved) :?>
		<div id="success_msg" class="alert alert-success">
			<a class="close" data-dismiss="alert">×</a>
			<div class="msg">
				<?php
				//$menuItem = $menu->getItems( 'link', 'index.php?option=com_tjucm&view=itemform', true );
				//$link = JRoute::_("index.php?option=com_jlike&view=ruleset&Itemid=" . $menuItem->id);
				echo JText::sprintf( 'COM_TJUCM_MSG_ON_SAVED_FORM');
				?>
			</div>
		</div>
		<?php endif;?>

	<div class="">
		<?php // Add active calass?>
		<?php //echo JHtml::_('bootstrap.startTabSet', 'tjucm_myTab', array('active' => 'personal-information')); ?>
		<?php /*if (!$this->form_extra): ?>
			<?php echo JHtml::_('bootstrap.startTabSet', 'tjucm_myTab', array('active' => 'personal-information')); ?>
				<?php //echo JHtml::_('bootstrap.addTab', 'tjucm_myTab', 'general', JText::_('COM_TJUCM_TITLE_ITEM', true)); ?>
		<?php endif; */?>
					<div class="row-fluid">
						<div class="span10 form-horizontal">
							<fieldset class="adminform">
								<input type="hidden" name="jform[id]" id="recordId" value="<?php echo JFactory::getApplication()->input->get('id'); ?>" />
								<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
								<input type="hidden" name="jform[state]" value="<?php echo $this->item->state;?>" />
								<input type="hidden" name="jform[client]" value="<?php echo $client;?>" />
								<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
								<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
								<?php echo $this->form->renderField('created_by'); ?>
								<?php echo $this->form->renderField('created_date'); ?>
								<?php echo $this->form->renderField('modified_by'); ?>
								<?php echo $this->form->renderField('modified_date'); ?>

								<?php if ($this->state->params->get('save_history', 1)) : ?>
									<div class="control-group">
										<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
										<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
									</div>
								<?php endif; ?>
							</fieldset>
						</div>
					</div>


				<?php if ($this->form_extra): ?>
					<?php echo $this->loadTemplate('extrafields'); ?>
				<?php endif; ?>

			<?php /*if (!$this->form_extra): ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; */?>

		<div class="alert alert-success" style="display: block;">
			<a class="close" data-dismiss="alert">×</a>
			<div class="msg">
				<div>
				<?php echo JText::_("COM_TJUCM_NOTE_ON_FORM"); ?>
				</div>
			</div>
		</div>

		<div id="draft_msg" class="alert alert-success" style="display: none;">
				<a class="close" data-dismiss="alert">×</a>
				<?php echo JText::_("COM_TJUCM_MSG_ON_DRAFT_FORM"); ?>
		</div>

		<div class="form-actions">

			<?php
			/* Show next previous buttons only when there are mulitple tabs/groups present under that field type*/
				foreach ($this->form_extra as $fieldKey => $fieldArray):
					foreach ($fieldArray->getFieldsets() as $fieldName => $fieldset):
						if (count($fieldArray->getFieldsets()) > 1):
							$setnavigation = true;
						endif;
					endforeach;
				endforeach;

				if (isset($setnavigation) && $setnavigation == true):
			?>
					<button type="button" class="btn btn-primary" id="previous_button" onclick="itemformactions('tjucm_myTab','prev')"><?php echo JText::_('COM_TJUCM_PREVIOUS_BUTTON'); ?><i class="fa fa-arrow-circle-o-right"></i></button>
					<button type="button" class="btn btn-primary" id="next_button" onclick="itemformactions('tjucm_myTab','next')"><?php echo JText::_('COM_TJUCM_NEXT_BUTTON'); ?><i class="fa fa-arrow-circle-o-right"></i></button>
			<?php
				endif;

			if ($calledFrom == 'frontend')
			{
			?>
				<input type="button" class="btn btn-success" value="<?php echo JText::_("COM_TJUCM_SAVE_ITEM"); ?>" id="finalSave" onclick="steppedFormSave(this.form.id, 'save');">
				<input type="button" class="btn btn-success" value="<?php echo JText::_("COM_TJUCM_SAVE_AS_DRAFT_ITEM"); ?>" onclick="steppedFormSave(this.form.id, 'draft');">
			<?php
			}
			?>
		</div>

		<input type="hidden" name="layout" value="<?php echo $layout ?>"/>
		<input type="hidden" name="task" value="itemform.save"/>
		<input type="hidden" name="form_status" id="form_status" value=""/>
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
