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
$user = JFactory::getUser();

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
					?>
					<div class="form-group">
						<?php
						if ($field->value)
						{
							?>
							<div class="col-sm-3 control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="col-sm-6 control-label">
								<a href="<?php echo JUri::root(true) . $field->value ?>" target="_blank" src="<?php echo JUri::root() . $field->value; ?>"><?php echo JText::_("JGLOBAL_PREVIEW");?></a>
							</div>
							<?php
						}
						?>
					</div>
				<?php
				}
				elseif ($field->type == 'Subform')
				{
					?>
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
										// TODO : SubForm rendering
										$html = '<div class="form-group">';
											//$html .= '<div class="col-sm-6 control-label">' . $fieldData->label . '</div>';
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
					<?php
					}
					elseif ($field->type == 'Checkbox')
					{
						?>
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
									$checked = "";

									if ($field->value = 1)
									{
										$checked = ' checked="checked"';
									}
									?>
									<input type="checkbox" disabled="disabled" value="1" <?php echo $checked;?> />
								</div>
								<?php
							}
							?>
						</div>
					<?php
					}
					else
					{
						?>
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
									if (is_array($field->value))
									{
										foreach($field->value as $eachFieldValue)
										{
											?>
											<p><?php echo "-" . $eachFieldValue; ?></p>
											<?php
										}
									}
									else
									{
										echo $field->value;
									}
									?>
								</div>
							<?php
							}
							?>
						</div>
				<?php
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

if ($user->authorise('core.type.edititem', 'com_tjucm.type.' . $this->ucmTypeId) && $this->item->checked_out == 0)
{
	?>
	<a class="btn" href="<?php echo 'index.php?option=com_tjucm&task=item.edit&id='.$this->item->id; ?>"><?php echo JText::_("COM_TJUCM_EDIT_ITEM"); ?></a>
	<?php
}

if ($user->authorise('core.type.deleteitem','com_tjucm.type.' . $this->ucmTypeId))
{
	?>
	<a class="btn" href="<?php echo 'index.php?option=com_tjucm&task=item.remove&id=' . $this->item->id; ?>"><?php echo JText::_("COM_TJUCM_DELETE_ITEM"); ?></a>
	<?php
}
