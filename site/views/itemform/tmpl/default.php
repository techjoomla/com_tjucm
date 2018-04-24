<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
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
$doc->addScriptDeclaration('const site_root = "' . JUri::root() . '"');
$doc->addScript(JUri::root() . 'administrator/components/com_tjucm/assets/js/jquery.form.js');
$doc->addScript(JUri::root() . 'administrator/components/com_tjucm/assets/js/tjucm_ajaxForm_save.js');
$doc->addScript(JUri::root() . 'administrator/components/com_tjfields/assets/js/tjfields.js');
$doc->addScript(JUri::root() . 'media/com_tjucm/js/form.js');
$doc->addStyleSheet(JUri::root() . 'media/com_tjucm/css/tjucm.css');

$jinput                    = JFactory::getApplication();
$baseUrl                   = $jinput->input->server->get('REQUEST_URI', '', 'STRING');
$calledFrom                = (strpos($baseUrl, 'administrator')) ? 'backend' : 'frontend';
$layout                    = ($calledFrom == 'frontend') ? 'default' : 'edit';
$is_saved                  = $jinput->input->get("success", '', 'INT');
$fieldsets_counter_deafult = 0;
$app                       = JFactory::getApplication();
$menu                      = $app->getMenu();
$setnavigation             = false;
?>
<script type="text/javascript">

	jQuery(window).load(function ()
	{
		jQuery('#item-form .nav-tabs li a').first().click();
	});

	Joomla.submitbutton = function (task)
	{
		if (task == 'itemform.cancel')
		{
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else
		{
			if (task != 'itemform.cancel' && document.formvalidator.isValid(document.id('item-form')))
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

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="item-form" class="form-validate">
	<?php
	if ($is_saved)
	{
		?>
		<div id="success_msg" class="alert alert-success">
			<a class="close" data-dismiss="alert">×</a>
			<div class="msg">
				<?php
					echo JText::sprintf( 'COM_TJUCM_MSG_ON_SAVED_FORM');
				?>
			</div>
		</div>
		<?php
	}?>

	<div>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">
					<input type="hidden" name="jform[id]" id="recordId" value="<?php echo JFactory::getApplication()->input->get('id'); ?>" />
					<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
					<input type="hidden" name="jform[state]" value="<?php echo $this->item->state;?>" />
					<input type="hidden" name="jform[client]" value="<?php echo $this->client;?>" />
					<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
					<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
					<?php echo $this->form->renderField('created_by'); ?>
					<?php echo $this->form->renderField('created_date'); ?>
					<?php echo $this->form->renderField('modified_by'); ?>
					<?php echo $this->form->renderField('modified_date'); ?>
				</fieldset>
			</div>
		</div>

		<?php
		if ($this->form_extra)
		{
			// Code to display the form
			echo $this->loadTemplate('extrafields');
		}
		?>

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
			// Show next previous buttons only when there are mulitple tabs/groups present under that field type
			$fieldArray = $this->form_extra;

			foreach ($fieldArray->getFieldsets() as $fieldName => $fieldset)
			{
				if (count($fieldArray->getFieldsets()) > 1)
				{
					$setnavigation = true;
				}
			}

			if (isset($setnavigation) && $setnavigation == true)
			{
				if (!empty($this->allow_draft_save))
				{
				?>
					<button type="button" class="btn btn-primary" id="previous_button" onclick="itemformactions('tjucm_myTab','prev')"><?php echo JText::_('COM_TJUCM_PREVIOUS_BUTTON'); ?><i class="icon-arrow-right-2"></i></button>
					<button type="button" class="btn btn-primary" id="next_button" onclick="itemformactions('tjucm_myTab','next')"><?php echo JText::_('COM_TJUCM_NEXT_BUTTON'); ?><i class="icon-arrow-right-2"></i></button>
				<?php
				}
			}

			if ($calledFrom == 'frontend')
			{
				?>
				<input type="button" class="btn btn-success" value="<?php echo JText::_("COM_TJUCM_SAVE_ITEM"); ?>" id="finalSave" onclick="steppedFormSave(this.form.id, 'save');" />
				<?php
				if (!empty($this->allow_draft_save))
				{
					?>
					<input type="button" class="btn btn-success" value="<?php echo JText::_("COM_TJUCM_SAVE_AS_DRAFT_ITEM"); ?>" onclick="steppedFormSave(this.form.id, 'draft');" />
					<?php
				}
			}
			?>
		</div>
		<input type="hidden" name="layout" value="<?php echo $layout ?>"/>
		<input type="hidden" name="task" value="itemform.save"/>
		<input type="hidden" name="form_status" id="form_status" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
