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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.keepalive');
?>
<script type="text/javascript">
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
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form action="<?php echo Route::_('index.php?option=com_tjucm&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="type-form" class="form-validate">
	<div class="form-horizontal">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_TJUCM_TITLE_TYPE', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset class="adminform">
							<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
							<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
							<?php echo $this->form->renderField('title'); ?>
							<?php echo $this->form->renderField('alias'); ?>
							<?php echo $this->form->renderField('unique_identifier'); ?>
							<?php echo $this->form->renderField('state'); ?>
							<?php echo $this->form->renderField('type_description'); ?>

							<?php foreach ($this->form->getGroup('params') as $field) : ?>
								<?php echo $field->renderField(); ?>
							<?php endforeach; ?>

							<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
							<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
							<?php echo $this->form->renderField('created_by'); ?>
							<?php echo $this->form->renderField('created_date'); ?>
							<?php echo $this->form->renderField('modified_by'); ?>
							<?php echo $this->form->renderField('modified_date'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php if (Factory::getUser()->authorise('core.admin', 'tjucm')) : ?>
				<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'permissions', Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
				<?php echo $this->form->getInput('rules'); ?>
				<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php endif; ?>

		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
