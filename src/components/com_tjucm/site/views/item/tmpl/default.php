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
$app = JFactory::getApplication();
$user = JFactory::getUser();
JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);
$TjfieldsHelper = new TjfieldsHelper;

// Get Field table
$fieldTableData = new stdClass;
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');
$fieldTableData->tjFieldFieldTable = JTable::getInstance('field', 'TjfieldsTable');

if ($this->form_extra)
{
	$fieldSets = $this->form_extra->getFieldsets();

	// Iterate through the normal form fieldsets and display each one
	foreach ($fieldSets as $fieldName => $fieldset)
	{
		?>
		<div class="form-horizontal">
			<?php
			foreach($this->form_extra->getFieldset($fieldset->name) as $field)
			{
				if ($field->hidden)
				{
					echo $field->input;
				}
				elseif ($field->type == 'File')
				{
					if ($field->value)
					{
						$layout = new JLayoutFile('file', JPATH_ROOT .'/components/com_tjfields/layouts/fields');
						$mediaLink = $layout->render(array('fieldValue'=>$field->value));
						?>
						<div class="form-group">
							<div class="col-sm-2 col-xs-12"><?php echo $field->label; ?>:</div>
							<div class="col-sm-2">
								<?php echo $mediaLink;?>
							</div>
						</div>
						<?php
					}
				}
				elseif ($field->type == 'Subform' || $field->type == 'Ucmsubform')
				{
					// Get Subform field data
					$formData = $TjfieldsHelper->getFieldData($field->getAttribute('name'));

					if ($field->value)
					{
						?>
						<div class="form-group">
							<div class="col-sm-2 col-xs-12"><?php echo $field->label; ?>:</div>
							<div class="col-sm-10">
							<?php
								foreach ($field->value as $val)
								{
									foreach ($val as $name => $value)
									{
										// Get the field data by field name to check the field type
										$fieldTableData->tjFieldFieldTable->load(array('name' => $name));

										if ($value){?>
										<div class="col-sm-2 col-xs-12"><?php echo $fieldTableData->tjFieldFieldTable->label; ?>:</div>
										<?php  ?>
											<div class="col-sm-10">
											<?php
											// If field type is file
											if ($fieldTableData->tjFieldFieldTable->type == 'file')
											{
												$layout = new JLayoutFile('file', JPATH_ROOT .'/components/com_tjfields/layouts/fields');
												$mediaLink = $layout->render(array('fieldValue'=>$value, 'isSubformField'=>'1', 'content_id'=>$app->input->get('id', '', 'INT'), 'subformFieldId'=>$formData->id,'subformFileFieldName'=>$name));
												echo $mediaLink;
											}
											// If field type is checkbox
											elseif ($fieldTableData->tjFieldFieldTable->type == 'checkbox')
											{
												$checked = ($value == 1) ? ' checked="checked"' : '';
												?>
												<input type="checkbox" disabled="disabled" value="1" <?php echo $checked;?> />
												<?php
											}
											else
											{
												$html = '<div class="form-group">';
												$html .= '<div class="col-sm-10"> ' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '</div>';
												$html .= '</div>';

												echo  $html;
											}
										}
										?>
										</div>
										<?php
									}

									echo '<hr>';
								}
							?>
							</div>
						</div>
						<?php
					}
				}
				elseif ($field->type == 'Checkbox')
				{
					if ($field->value)
					{
						?>
						<div class="form-group">
							<div class="col-sm-2 col-xs-12"><?php echo $field->label; ?>:</div>
							<div class="col-sm-2">
							<?php
								$checked = ($field->value == 1) ? ' checked="checked"' : '';
								?>
								<input type="checkbox" disabled="disabled" value="1" <?php echo $checked;?> />
							</div>
						</div>
						<?php
					}
				}
				else
				{
					if ($field->value)
					{
						?>
						<div class="form-group">
							<div class="col-sm-2 col-xs-12"><?php echo $field->label; ?>:</div>
							<div class="col-sm-2">
								<?php
								if (is_array($field->value))
								{
									foreach($field->value as $eachFieldValue)
									{
										?>
										<p><?php echo "-" . htmlspecialchars($eachFieldValue, ENT_COMPAT, 'UTF-8'); ?></p>
										<?php
									}
								}
								else
								{
									echo htmlspecialchars($field->value, ENT_COMPAT, 'UTF-8');
								}
								?>
							</div>
						</div>
					<?php
					}
				}
			}
		?>
		</div>
		<?php
	}
}
else
{
	?>
	<div class="alert alert-info">
		<?php echo JText::_('COM_TJUCM_NO_ACTIVITIES');?>
	</div>
	<?php
}
?>
<div class="form-group">
<?php
$itemid     = $app->input->getInt('Itemid', 0);

if (($user->authorise('core.type.edititem', 'com_tjucm.type.' . $this->ucmTypeId)) || ($user->authorise('core.type.editownitem', 'com_tjucm.type.' . $this->ucmTypeId) && JFactory::getUser()->id == $this->item->created_by))
{
	$redirectURL = JRoute::_('index.php?option=com_tjucm&task=item.edit&id=' . $this->item->id . '&client=' . $this->client . '&Itemid=' . $itemid, false);
	?>
	<a class="btn btn-default" href="<?php echo $redirectURL; ?>"><?php echo JText::_("COM_TJUCM_EDIT_ITEM"); ?></a>
	<?php
}

if ($user->authorise('core.type.deleteitem','com_tjucm.type.' . $this->ucmTypeId))
{
	$redirectURL = JRoute::_('index.php?option=com_tjucm&task=item.remove&id=' . $this->item->id . '&client=' . $this->client . '&Itemid=' . $itemid, false);
	?>
	<a class="btn btn-default" href="<?php echo $redirectURL; ?>"><?php echo JText::_("COM_TJUCM_DELETE_ITEM"); ?></a>
	<?php
}
?>
</div>
