<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

$full_client  = JFactory::getApplication()->input->get('client');
$full_client =  explode('.',$full_client);
$client = $full_client[0];
$client_type = $full_client[1];
?>
<?php if ($this->form_extra): ?>
	<!-- Iterate through the normal form fieldsets and display each one. -->
	<?php foreach ($this->form_extra as $fieldKey => $fieldArray): ?>
		<?php foreach ($fieldArray->getFieldsets() as $fieldName => $fieldset): ?>
				<div class="form-horizontal">
					<?php foreach($this->form_extra as $fieldKeyArray): ?>
						<?php foreach($fieldKeyArray->getFieldset($fieldset->name) as $field): ?>
						<?php // echo "<pre>"; print_r($field->type); echo "</pre>";?>
							<?php if ($field->hidden): ?>
								<?php echo $field->input; ?>
							<?php elseif ($field->type == 'File') : ?>
								<div class="form-group">
									<?php
									if ($field->value)
									{
										?>
										<div class="col-sm-3 control-label">
											<?php echo $field->label; ?>
										</div>

										<div class="col-sm-6 control-label">
										<?php
											$fileType = explode(".", $field->value);
											$arr_image_type = array('jpeg', 'JPEG', 'png', 'PNG', 'jpg','JPG');
											if (in_array(end($fileType), $arr_image_type))
											{
												echo '<div><img height="80" width="100" src="' . JUri::root(true) . $field->value . '"></div>';
											}
											else
											{
												echo '<div><a href="' . JUri::root(true) . $field->value . '" target="_blank" src="' . JUri::root() . $field->value . '">' . JText::_("JGLOBAL_PREVIEW") . '</a></div>';
											}?>
										</div>
										<?php
									} ?>

								</div>
							<?php elseif ($field->type == 'Subform') : ?>
								<div class="form-group">
									<?php

									if ($field->value)
									{
										?>
										<div class="col-sm-3 control-label">
											<?php echo $field->label; ?>
										</div>

										<div class="col-sm-6 control-label">
										<?php
											foreach ($field->value as $val)
											{
												foreach ($val as $lab => $valu)
												{
													$db    = JFactory::getDbo();
													$query = $db->getQuery(true);
													$query->select('label FROM #__tjfields_fields');
													$query->where('name="' . $lab . '"');
													$db->setQuery($query);
													$field_label = $db->loadObject();

													$html = '<div class="form-group">';
														$html .= '<div class="col-sm-6 control-label">' . $field_label->label . '</div>';
														$html .= '<div class="col-sm-6 control-label"> : ' . $valu . '</div>';
													$html .= '</div>';

													echo  $html;
												}

												echo '<hr>';
											}
										?>
										</div>
										<?php
									} ?>

								</div>
							<?php elseif ($field->type == 'Checkbox') : ?>
								<div class="form-group">
									<?php

									if ($field->value)
									{
										?>
										<div class="col-sm-3 control-label">
											<?php echo $field->label; ?>
										</div>

										<div class="col-sm-6 control-label">
										<?php
											// echo"<pre>"; print_r($field->value); echo"</pre>";

											$checked = "";

											if ($field->value = 1)
											{
												$checked = 'checked="checked"';
											}

											echo '<input type="checkbox" disabled="disabled" value="1" ' . $checked . '>';


										?>
										</div>
										<?php
									} ?>

								</div>
							<?php else: ?>
									<div class="form-group">
										<?php if ($field->value): ?>
											<div class="col-sm-3 control-label">
												<?php echo $field->label; ?>
											</div>
											<div class="col-sm-6 control-label">
												<?php if (is_array($field->value)): ?>
													<?php
														foreach($field->value as $eachFieldValue):
															echo '<p> - ' . $eachFieldValue . '</p>';
														endforeach;
													?>
													<?php else: ?>
														<?php echo $field->value; ?>
													<?php endif ?>
											</div>
										<?php endif ?>
									</div>
							<?php endif; ?>
						<?php endforeach;?>
					<?php endforeach;?>
				</div>
		<?php endforeach; ?>
	<?php endforeach; ?>
<?php else: ?>
	<div class="alert alert-info">
		<?php echo JText::_('There are no activities here yet');?>
	</div>
<?php endif; ?>

