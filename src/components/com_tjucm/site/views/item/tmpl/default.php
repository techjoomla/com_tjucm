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

// Layout for field types
$fieldLayout = array();
$fieldLayout['File'] = "file";
$fieldLayout['Image'] = "file";
$fieldLayout['Checkbox'] = "checkbox";
$fieldLayout['Radio'] = "list";
$fieldLayout['List'] = "list";
$fieldLayout['Itemcategory'] = "itemcategory";
$fieldLayout['Video'] = "video";
$fieldLayout['Calendar'] = "calendar";

$csrf = "&" . JSession::getFormToken() . '=1';

// Get Field table
$fieldTableData = new stdClass;
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');
$fieldTableData->tjFieldFieldTable = JTable::getInstance('field', 'TjfieldsTable');

$count = 0;
$xmlFieldSets = array();

foreach ($this->formXml as $k => $xmlFieldSet)
{
	$xmlFieldSets[$count] = $xmlFieldSet;
	$count++;
}
if ($this->form_extra)
{
	$fieldSets = $this->form_extra->getFieldsets();
	$count = 0;

	// Iterate through the normal form fieldsets and display each one
	foreach ($fieldSets as $fieldName => $fieldset)
	{
		$xmlFieldSet = $xmlFieldSets[$count];
		$count++;
		?>
		<div class="row">
			<?php
			$fieldCount = 0;

			foreach ($this->form_extra->getFieldset($fieldset->name) as $field)
			{
				// Get the field data by field name to check the field type
				$fieldTableData->tjFieldFieldTable->load(array('name' => $field->__get("fieldname")));
				$canView = false;
				if ($user->authorise('core.field.viewfieldvalue', 'com_tjfields.group.' . $fieldTableData->tjFieldFieldTable->group_id))
				{
					$canView = $user->authorise('core.field.viewfieldvalue', 'com_tjfields.field.' . $fieldTableData->tjFieldFieldTable->id);
				}

				if ($canView || ($this->item->created_by == $user->id))
				{
					// Get xml for the field
					$xmlField = $xmlFieldSet->field[$fieldCount];
					$fieldCount++;

					if ($field->hidden)
					{
						echo $field->input;
					}
					elseif ($field->type == 'Subform' || $field->type == 'Ucmsubform')
					{
						// Get Subform field data
						$formData = $TjfieldsHelper->getFieldData($field->getAttribute('name'));

						if ($field->value)
						{
							?>
							<div class="col-xs-12 col-md-6">
								<div class="row">
									<div class="col-xs-4"><?php echo $field->label; ?>:</div>
									<div class="col-xs-8">
									<?php
										foreach ($field->value as $val)
										{
											foreach ($val as $name => $value)
											{
												// Get the field data by field name to check the field type
												$fieldTableData->tjFieldFieldTable->load(array('name' => $name));

												if ($value)
												{
													?>
													<div class="row">
														<div class="col-xs-4" style="word-wrap:break-word;"><?php echo $fieldTableData->tjFieldFieldTable->label; ?>:</div>
														<div class="col-xs-8">
															<?php
															// If field type is file
															if ($fieldTableData->tjFieldFieldTable->type == 'file' || $fieldTableData->tjFieldFieldTable->type == 'image')
															{
																$layout = new JLayoutFile($fieldTableData->tjFieldFieldTable->type, JPATH_ROOT . '/components/com_tjfields/layouts/fields');
																$mediaLink = $layout->render(array('fieldValue'=>$value, 'isSubformField'=>'1', 'content_id'=>$app->input->get('id', '', 'INT'), 'subformFieldId'=>$formData->id, 'subformFileFieldName'=>$name));
																echo $mediaLink;
															}
															// If field type is checkbox
															elseif ($fieldTableData->tjFieldFieldTable->type == 'Checkbox')
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
															?>
														</div>
													</div>
													<?php
												}
											}

											echo '<hr>';
										}
									?>
									</div>
								</div>
							</div>
							<?php
						}
					}
					else
					{
						$layoutToUse = (array_key_exists($field->type, $fieldLayout)) ? $fieldLayout[$field->type] : 'field';
						?>
						<div class="col-xs-12 col-md-6">
							<div class="row">
								<div class="col-xs-4"><?php echo $field->label; ?>:</div>
								<div class="col-xs-8">
									<?php
									$layout = new JLayoutFile($layoutToUse, JPATH_ROOT . '/components/com_tjfields/layouts/fields');
									$mediaLink = $layout->render(array('fieldValue' => $field->value));
									$output = $layout->render(array('fieldXml' => $xmlField, 'field' => $field));
									echo $output;
									?>
								</div>
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
<div>&nbsp;</div>
<div>&nbsp;</div>
<div class="row">
	<div class="form-group">
	<?php
	if (($user->authorise('core.type.edititem', 'com_tjucm.type.' . $this->ucmTypeId)) || ($user->authorise('core.type.editownitem', 'com_tjucm.type.' . $this->ucmTypeId) && JFactory::getUser()->id == $this->item->created_by))
	{
		$redirectURL = JRoute::_('index.php?option=com_tjucm&task=item.edit&id=' . $this->item->id . '&client=' . $this->client, false);
		?>
		<a class="btn btn-default" href="<?php echo $redirectURL; ?>"><?php echo JText::_("COM_TJUCM_EDIT_ITEM"); ?></a>
		<?php
	}
	
	$deleteOwn = false;
	if ($user->authorise('core.type.deleteownitem','com_tjucm.type.' . $this->ucmTypeId))
	{
		$deleteOwn = (JFactory::getUser()->id == $this->item->created_by ? true : false);
	}

	if ($user->authorise('core.type.deleteitem','com_tjucm.type.' . $this->ucmTypeId) || $deleteOwn)
	{
		$redirectURL = JRoute::_('index.php?option=com_tjucm&task=itemform.remove&id=' . $this->item->id . '&client=' . $this->client . $csrf, false);
		?>
		<a class="btn btn-default" href="<?php echo $redirectURL; ?>"><?php echo JText::_("COM_TJUCM_DELETE_ITEM"); ?></a>
		<?php
	}
	?>
	</div>
</div>
