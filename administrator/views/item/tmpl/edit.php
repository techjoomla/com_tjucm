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
JHtml::_('jquery.token');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_tjucm', JPATH_SITE);
$doc = JFactory::getDocument();
$doc->addScript(JUri::root() . 'administrator/components/com_tjucm/assets/js/jquery.form.js');
$doc->addScript(JUri::root() . 'administrator/components/com_tjucm/assets/js/tjucm_ajaxForm_save.js');
$doc->addScript(JUri::root() . 'administrator/components/com_tjucm/assets/js/tjfield.js');

$jinput = JFactory::getApplication();
$baseUrl = $jinput->input->server->get('REQUEST_URI', '', 'STRING');
$calledFrom = (strpos($baseUrl, 'administrator')) ? 'backend' : 'frontend';
$layout = ($calledFrom == 'frontend') ? 'default' : 'edit';
$client  = JFactory::getApplication()->input->get('client');
?>
<script type="text/javascript">


	Joomla.submitbutton = function (task)
	{
		if (task == 'item.cancel')
		{
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else
		{
			if (task != 'item.cancel' && document.formvalidator.isValid(document.id('item-form')))
			{
				Joomla.submitform(task, document.getElementById('item-form'));
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_tjucm&view=item&layout=' . $layout . '&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="item-form" class="form-validate">
	<div class="form-horizontal">
		<?php if (!$this->form_extra): ?>
			<?php echo JHtml::_('bootstrap.startTabSet', 'tjucm_myTab', array('active' => 'personal-information')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'tjucm_myTab', 'general', JText::_('COM_TJUCM_TITLE_ITEM', true)); ?>
		<?php endif; ?>
					<div class="row-fluid">
						<div class="span10 form-horizontal">
							<fieldset class="adminform">
								<input type="hidden" name="jform[id]" id="recordId" value="<?php echo $this->item->id; ?>" />
								<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
								<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
								<input type="hidden" name="jform[client]" value="<?php echo $client;?>" />
								<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
								<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
								<?php echo $this->form->renderField('created_by'); ?>
								<?php echo $this->form->renderField('created_date'); ?>
								<?php echo $this->form->renderField('modified_by'); ?>
								<?php echo $this->form->renderField('modified_date'); ?>
								<?php echo $this->form->renderField('category_id'); ?>
							</fieldset>
						</div>
					</div>
				<?php if ($this->form_extra): ?>
					<?php echo $this->loadTemplate('extrafields'); ?>
				<?php endif; ?>

			<?php if (!$this->form_extra): ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<div class="alert alert-success" style="display: block;">
			<div class="msg">
				<div>
				<?php echo JText::_("COM_TJUCM_NOTE_ON_FORM"); ?>
				</div>
			</div>
		</div>
		<div class="form-actions">
			<button type="button" class="btn btn-primary" id="previous_button" onclick="itemformactions('tjucm_myTab','prev')"><?php echo JText::_('COM_TJUCM_PREVIOUS_BUTTON'); ?><i class="fa fa-arrow-circle-o-right"></i></button>
			<button type="button" class="btn btn-primary" id="next_button" onclick="itemformactions('tjucm_myTab','next')"><?php echo JText::_('COM_TJUCM_NEXT_BUTTON'); ?><i class="fa fa-arrow-circle-o-right"></i></button>
			<?php
			if ($calledFrom == 'frontend')
			{
			?>
				<input type="button" class="btn btn-success" value="<?php echo JText::_("COM_TJUCM_SAVE_ITEM"); ?>" id="finalSave" onclick="finalsave('item-form');">
				<input type="button" class="btn btn-success" value="<?php echo JText::_("COM_TJUCM_SAVE_AS_DRAFT_ITEM"); ?>" onclick="saveAsDraft('item-form');">
				<input type="button" class="btn btn-danger" value="<?php echo JText::_("COM_TJUCM_CANCEL_BUTTON"); ?>" onclick="Joomla.submitbutton('itemform.cancel');">
			<?php
			}
			?>
		</div>
		<input type="hidden" name="layout" value="<?php echo $layout ?>"/>
		<input type="hidden" name="task" value="item.save"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
