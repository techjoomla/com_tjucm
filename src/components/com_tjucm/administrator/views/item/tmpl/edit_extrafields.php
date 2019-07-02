<?php
/**
 * @version    SVN: <svn_id>
 * @package    Your_extension_name
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
?>

<?php
	if ($this->form_extra):
		//~ echo $this->form_extra->getFieldsets(0)->name;
		echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'personal-information'));
	endif;
?>

<?php if ($this->form_extra): ?>
	<!-- Iterate through the normal form fieldsets and display each one. -->
	<?php foreach ($this->form_extra as $fieldKey => $fieldArray): ?>
		<?php foreach ($fieldArray->getFieldsets() as $fieldName => $fieldset): ?>
			<!-- Fields go here -->
				<?php
				$tabName = JFilterOutput::stringURLUnicodeSlug(trim($fieldset->name));
				echo JHtml::_("bootstrap.addTab", "myTab", $tabName, $fieldset->name);
				?>
				<!-- Iterate through the fields and display them. -->
					<?php foreach($this->form_extra as $field1): ?>
						<?php foreach($field1->getFieldset($fieldset->name) as $field): ?>
							<!-- If the field is hidden, only use the input. -->
							<?php if ($field->hidden): ?>
								<?php echo $field->input; ?>
							<?php else: ?>
									<div class="form-group">
										<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="form-control col-lg-10 col-md-10 col-sm-9 col-xs-12">
											<?php echo $field->input; ?>
										</div>
									</div>
							<?php endif; ?>
						<?php endforeach;?>
					<?php endforeach;?>
				<?php
				echo JHtml::_("bootstrap.endTab");
				?>
		<?php endforeach; ?>
	<?php endforeach; ?>
<?php else: ?>
	<div class="alert alert-info">
		<?php echo JText::_('COM_TJLMS_NO_EXTRA_FIELDS_FOUND');?>
	</div>
<?php endif; ?>

