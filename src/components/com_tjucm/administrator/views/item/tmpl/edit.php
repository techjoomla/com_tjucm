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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('jquery.token');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_tjucm', JPATH_SITE);
$doc = Factory::getDocument();
$doc->addScript(Uri::root() . 'administrator/components/com_tjucm/assets/js/jquery.form.js');
$doc->addScript(Uri::root() . 'administrator/components/com_tjucm/assets/js/itemform.js');
$doc->addScript(Uri::root() . 'administrator/components/com_tjucm/assets/js/tjfield.js');

$jinput = Factory::getApplication();
$baseUrl = $jinput->input->server->get('REQUEST_URI', '', 'STRING');
$calledFrom = (strpos($baseUrl, 'administrator')) ? 'backend' : 'frontend';
$layout = ($calledFrom == 'frontend') ? 'default' : 'edit';
$client  = Factory::getApplication()->input->get('client');
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
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form action="<?php echo Route::_('index.php?option=com_tjucm&view=item&layout=' . $layout . '&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="item-form" class="form-validate">
	<div class="form-horizontal">
		<?php if (!$this->form_extra): ?>
			<?php echo HTMLHelper::_('bootstrap.startTabSet', 'tjucm_myTab', array('active' => 'personal-information')); ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'tjucm_myTab', 'general', Text::_('COM_TJUCM_TITLE_ITEM', true)); ?>
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
						<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
		<div class="alert alert-success" style="display: block;">
			<div class="msg">
				<div>
				<?php echo Text::_("COM_TJUCM_NOTE_ON_FORM"); ?>
				</div>
			</div>
		</div>
		<div class="form-actions">
			<button type="button" class="btn btn-primary" id="previous_button" onclick="itemformactions('tjucm_myTab','prev')"><?php echo Text::_('COM_TJUCM_PREVIOUS_BUTTON'); ?><i class="fa fa-arrow-circle-o-right"></i></button>
			<button type="button" class="btn btn-primary" id="next_button" onclick="itemformactions('tjucm_myTab','next')"><?php echo Text::_('COM_TJUCM_NEXT_BUTTON'); ?><i class="fa fa-arrow-circle-o-right"></i></button>
			<?php
			if ($calledFrom == 'frontend')
			{
			?>
				<input type="button" class="btn btn-success" value="<?php echo Text::_("COM_TJUCM_SAVE_ITEM"); ?>" id="finalSave" onclick="finalsave('item-form');">
				<input type="button" class="btn btn-success" value="<?php echo Text::_("COM_TJUCM_SAVE_AS_DRAFT_ITEM"); ?>" onclick="saveAsDraft('item-form');">
				<input type="button" class="btn btn-danger" value="<?php echo Text::_("COM_TJUCM_CANCEL_BUTTON"); ?>" onclick="Joomla.submitbutton('itemform.cancel');">
			<?php
			}
			?>
		</div>
		<input type="hidden" name="layout" value="<?php echo $layout ?>"/>
		<input type="hidden" name="task" value="item.save"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
