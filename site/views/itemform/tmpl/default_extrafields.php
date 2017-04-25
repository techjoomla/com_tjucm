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

//~ echo"<pre>"; print_r($this->form_extra); echo"</pre>";
$fieldsets_counter = 0;
$layout  = JFactory::getApplication()->input->get('layout');

?>

<?php if ($this->form_extra): ?>
	<!-- Iterate through the normal form fieldsets and display each one. -->
	<?php foreach ($this->form_extra as $fieldKey => $fieldArray): ?>
		<?php foreach ($fieldArray->getFieldsets() as $fieldName => $fieldset): ?>
			<!-- Fields go here -->
				<?php
				if (count($fieldArray->getFieldsets()) > 1)
				{
					if ($fieldsets_counter == 0) {
						echo JHtml::_('bootstrap.startTabSet', 'tjucm_myTab', array('active' => 'personal-information'));
					}

					$fieldsets_counter ++;

					$tabName = JFilterOutput::stringURLUnicodeSlug(trim($fieldset->name));
					echo JHtml::_("bootstrap.addTab", "tjucm_myTab", $tabName, $fieldset->name);
				}
				?>
					<div class="form-horizontal">
						<!-- Iterate through the fields and display them. -->
						<?php foreach($this->form_extra as $fieldKeyArray): ?>
							<?php foreach($fieldKeyArray->getFieldset($fieldset->name) as $field): ?>
								<!-- If the field is hidden, only use the input. -->
								<?php if ($field->hidden): ?>
									<?php echo $field->input; ?>
								<?php else: ?>
										<div class="form-group">
											<div class="col-sm-3 control-label">
												<?php echo $field->label; ?>
											</div>

											<div class="col-sm-6 control-label">
												<?php echo $field->input; ?>
											</div>										?>
											<?php if ($field->type == 'File') { ?>
												<script type="text/javascript">
													jQuery(document).ready(function ()
													{
														var fieldValue = "<?php echo $field->value; ?>";
														var AttrRequired = jQuery('#<?php echo $field->id;?>').attr('required');

														if (typeof AttrRequired !== typeof undefined && AttrRequired !== false)
														{
															if (fieldValue)
															{
																jQuery('#<?php echo $field->id;?>').removeAttr("required");
																jQuery('#<?php echo $field->id;?>').removeClass("required");
															}
														}
													});
												</script>
											<?php } ?>
											<?php
											/*echo"<pre>"; print_r($field->class); echo"</pre>";
											$classList = explode(" ", $field->class);
											echo"<pre>"; print_r($classList); echo"</pre>";*/
											?>
											<?php /*if ($field->type == 'Textarea') { ?>
												<div class="formFieldHint">
													<p id="warning_activity" class="text-warning">Characters count
														<span id="counter_<?php echo $field->id;?>"></span> (Min 250 characters)
													</p>
												</div>
											<?php }*/ ?>
										</div>
								<?php endif; ?>
							<?php endforeach;?>
						<?php endforeach;?>
					</div>
				<?php
				if (count($fieldArray->getFieldsets()) > 1)
				{
					echo JHtml::_("bootstrap.endTab");
				}

				if ($fieldsets_counter == 0) {
						echo JHtml::_('bootstrap.startTabSet');
				}
				?>
		<?php endforeach; ?>
	<?php endforeach; ?>
<?php else: ?>
	<div class="alert alert-info">
		<?php echo JText::_('COM_TJLMS_NO_EXTRA_FIELDS_FOUND');?>
	</div>
<?php endif; ?>
