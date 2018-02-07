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
JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);

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
						$tjFieldHelper = new TjfieldsHelper;
						$mediaLink = $tjFieldHelper->getMediaUrl($field->value);
						if (!empty($mediaLink))
						{
							?>
							<div class="form-group">
								<div class="control-label col-sm-2">
									<?php echo $field->label; ?>
								</div>

								<div class="col-sm-10">
									<a href="<?php echo $mediaLink;?>">
										<?php echo JText::_("COM_TJUCM_FILE_DOWNLOAD");?></a>
								</div>
							</div>
							<?php
						}
					}
				}
				elseif ($field->type == 'Subform')
				{
					if ($field->value)
					{
						?>
						<div class="form-group">
							<div class="control-label col-sm-2">
								<?php echo $field->label; ?>
							</div>

							<div class="col-sm-10">
								<?php
								foreach ($field->value as $val)
								{
									foreach ($val as $label => $value)
									{
										// TODO : SubForm rendering
										$html = '<div class="form-group">';
											//$html .= '<div class="col-sm-6 control-label">' . $fieldData->label . '</div>';
											$html .= '<div class="col-sm-6 control-label"> : ' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '</div>';
										$html .= '</div>';

										echo  $html;
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
							<div class="control-label col-sm-2">
								<?php echo $field->label; ?>
							</div>

							<div class="col-sm-10">
								<?php
								$checked = "";

								if ($field->value = 1)
								{
									$checked = ' checked="checked"';
								}
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
											<p><?php echo "-" . htmlspecialchars($eachFieldValue, ENT_COMPAT, 'UTF-8'); ?></p>
											<?php
										}
									}
									else
									{
										echo htmlspecialchars($field->value, ENT_COMPAT, 'UTF-8');
									}
								}
								else
								{
									echo $field->value;
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

if ($user->authorise('core.type.edititem', 'com_tjucm.type.' . $this->ucmTypeId) && $this->item->checked_out == 0)
{
	?>
	<a class="btn" href="<?php echo 'index.php?option=com_tjucm&task=item.edit&id=' . $this->item->id . '&client=' . $this->client; ?>"><?php echo JText::_("COM_TJUCM_EDIT_ITEM"); ?></a>
	<?php
}

if ($user->authorise('core.type.deleteitem','com_tjucm.type.' . $this->ucmTypeId))
{
	?>
	<a class="btn" href="<?php echo 'index.php?option=com_tjucm&task=item.remove&id=' . $this->item->id; ?>"><?php echo JText::_("COM_TJUCM_DELETE_ITEM"); ?></a>
	<?php
}
