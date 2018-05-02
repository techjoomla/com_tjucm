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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

JHtml::script( JUri::root().'administrator/components/com_tjucm/assets/js/tjucm_type.js' );
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {

	});

	Joomla.submitbutton = function (task) {
		if (task == 'type.cancel')
		{
			Joomla.submitform(task, document.getElementById('type-form'));
		}
		else
		{

			if (task != 'type.cancel' && document.formvalidator.isValid(document.id('type-form'))) {

				Joomla.submitform(task, document.getElementById('type-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_tjucm&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="type-form" class="form-validate">
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_TJUCM_TITLE_TYPE', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset class="adminform">
							<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
							<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
							<?php echo $this->form->renderField('title'); ?>
							<?php echo $this->form->renderField('alias'); ?>
							<?php echo $this->form->renderField('unique_identifier'); ?>
							<?php echo $this->form->renderField('state'); ?>
							<?php echo $this->form->renderField('allowed_count'); ?>
							<?php echo $this->form->renderField('is_subform'); ?>
							<?php echo $this->form->renderField('type_description'); ?>

							<?php echo $this->form->renderField('params'); ?>
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
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php if (JFactory::getUser()->authorise('core.admin','tjucm')) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
				<?php echo $this->form->getInput('rules'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
