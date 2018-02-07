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

$fieldsets_counter = 0;
$layout  = JFactory::getApplication()->input->get('layout');

if ($this->form_extra)
{
	// Iterate through the normal form fieldsets and display each one
	$fieldSets = $this->form_extra->getFieldsets();
	foreach ($fieldSets as $fieldName => $fieldset)
	{
		if (count($this->form_extra->getFieldsets()) > 1)
		{
			if ($fieldsets_counter == 0)
			{
				echo JHtml::_('bootstrap.startTabSet', 'tjucm_myTab', array('active' => 'personal-information'));
			}

			$fieldsets_counter ++;

			$tabName = JFilterOutput::stringURLUnicodeSlug(trim($fieldset->name));
			echo JHtml::_("bootstrap.addTab", "tjucm_myTab", $tabName, $fieldset->name);
		}
		?>
		<div class="form-horizontal">
			<?php
			// Iterate through the fields and display them
			foreach($this->form_extra->getFieldset($fieldset->name) as $field)
			{
				// If the field is hidden, only use the input
				if ($field->hidden)
				{
					echo $field->input;
				}
				else
				{
					?>
					<div class="form-group">
						<div class="control-label col-sm-2">
							<?php echo $field->label; ?>
						</div>
						<div class="col-sm-10">
							<?php echo $field->input; ?>
						</div>
						<?php

						// TODO :- Check and remove
						if ($field->type == 'File')
						{
							?>
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
		if (count($this->form_extra->getFieldsets()) > 1)
		{
			echo JHtml::_("bootstrap.endTab");
		}

		if ($fieldsets_counter == 0)
		{
			echo JHtml::_('bootstrap.startTabSet');
		}
	}
}
else
{
	?>
	<div class="alert alert-info">
		<?php echo JText::_('COM_TJLMS_NO_EXTRA_FIELDS_FOUND');?>
	</div>
	<?php
}
