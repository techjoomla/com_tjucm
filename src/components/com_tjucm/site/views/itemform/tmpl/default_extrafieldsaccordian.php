<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\TagsHelper;

HTMLHelper::script('media/com_dpe/js/tjucm.js');
Text::script('COM_TJUCM_ROP_ITEM_FORM_NEXT_DATE_REVIEW_VALIDATION_MESSAGE');
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjucm/tables');
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');
JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_ADMINISTRATOR);


$fieldsets_counter = 0;
$layout             = Factory::getApplication()->input->get('layout');
$params             = ComponentHelper::getParams('com_dpe');
$reverseListClients = explode (",", $params->get('coredataReverseUcmTypes'));
$clusterFieldName   = '';
$app                = Factory::getApplication();
$calledFrom                = (strpos($baseUrl, 'administrator')) ? 'backend' : 'frontend';
$app                = Factory::getApplication();
$tmpl               = $app->input->get('tmpl', '', 'STRING');

$ucmConfigs = ComponentHelper::getParams('com_tjucm');
$useTooltip = $ucmConfigs->get('enable_custom_tooltip');

if ($this->item->id)
{
	$itemState = ($this->item->draft && ($this->allow_auto_save || $this->allow_draft_save)) ? 1 : 0;
}
else
{
	$itemState = ($this->allow_auto_save || $this->allow_draft_save) ? 1 : 0;
}

 
$tjfieldsHelper = new TjfieldsHelper;
?>
<?php

if ($this->form_extra)
{
	// Iterate through the normal form fieldsets and display each one
	$fieldSets = $this->form_extra->getFieldsets();

	foreach ($fieldSets as $fieldset)
	{
		if (count($fieldSets) > 1)
		{
			if ($fieldsets_counter == 0)
			{
				echo HTMLHelper::_('bootstrap.startTabSet', 'tjucm_myTab');
			}

			$fieldsets_counter++;

			if (count($this->form_extra->getFieldset($fieldset->name)))
			{
				foreach ($this->form_extra->getFieldset($fieldset->name) as $field)
				{

					if (!$field->hidden)
					{
						$tabName = OutputFilter::stringURLUnicodeSlug(trim($fieldset->name));
						echo HTMLHelper::_("bootstrap.addTab", "tjucm_myTab", $tabName, $fieldset->name);
						break;
					}
				}
			}
		}
		?>
		<div class="form-horizontal clear-both pull-left pb-10 w-100 dp-rop-form d-flex flex-wrap">
			<?php			

			$arr = array();

			// Iterate through the fields and display them
			foreach ($this->form_extra->getFieldset($fieldset->name) as $field)
			{	
				
				if(!empty($field->getAttribute('tags')))
				{
					$temp = new TagsHelper;
					$tagnames = $temp->getTagNames(array($field->getAttribute('tags')));

						if(array_key_exists($arr, $tagnames[0]))
						{
							$arr[$tagnames[0]][] = $field;
						}
						else
						{
							$arr[$tagnames[0]][] = $field;
						}
															
				}
			}

		
		
			if(!empty($arr)){
			foreach ($arr as $key => $fieldTagarray)
			{
				$i =0;
				
				?>
				<div class="accordion" id="accordion<?php echo $i++; ?>"><?php echo  ucfirst(str_replace('_', ' ', $key)); ?></div>
				<div id="pan" class="panel">

				<?php foreach($fieldTagarray as $fieldTag)
				{
					
					$isUcmsubform = 0;

					if ($fieldTag->type == 'Ucmsubform')
					{
						$customColClass = 'col-xs-12 col-md-12 ucmsubform';
					}
					else
					{
						$customColClass = 'col-md-4 col-xs-12';
					}

					if (strpos($fieldTag->class, 'twoColumnUcmsubform') !== false)
					{
						$isUcmsubform   = 0;
					}


					if (!$fieldTag->hidden)
					{
					$className = ($field->type == 'Spacer') ? 'w-100' : '';
					?>
				<div class="<?php echo $customColClass . ' ' . $className;  ?> custom-form-style">
						<div class="form-group">
								<div class="col-sm-12 control-label w-100 text-left">
									<?php echo $fieldTag->label; ?>
								</div>

								<?php
								// TODO :- Check and remove
								if ($fieldTag->type == 'File')
								{
									if ($this->copyRecId)
									{
										$fieldTag->setValue('');
									}

									?>
									<script type="text/javascript">
										jQuery(document).ready(function ()
										{
											var fieldValue = "<?php echo $fieldTag->value; ?>";
											var AttrRequired = jQuery('#<?php echo $field->id;?>').attr('required');
											if (typeof AttrRequired !== typeof undefined && AttrRequired !== false)
											{
												if (fieldValue)
												{
													jQuery('#<?php echo $fieldTag->id;?>').removeAttr("required");
													jQuery('#<?php echo $fieldTag->id;?>').removeClass("required");
												}
											}
										});
									</script>
								<?php
								}
								?>


							<div class="col-sm-12 rop-inputs w-100">
								<?php echo $fieldTag->input; ?>
									<div>
									<?php
									if (strpos($fieldTag->fieldname, 'clusterclusterid'))
									{
										$clusterFieldName = $fieldTag->fieldname;
									}
									?>
									</div>
							</div>			

						</div>
					</div>
			<?php
				}
				}?>
				</div>

			<?php	
			}
		}
		else
		{
			
			foreach ($this->form_extra->getFieldset($fieldset->name) as $field)
			{				
				$description = $field->description;

				if($useTooltip)
				{
					$field->description = '';
				}

				$isUcmsubform = 0;

				if ($field->type == 'Ucmsubform')
				{
					$customColClass = 'col-xs-12 col-md-12 ucmsubform';
				}
				else
				{
					$customColClass = 'col-md-4 col-xs-12';
				}

				if (strpos($field->class, 'twoColumnUcmsubform') !== false)
				{
					$isUcmsubform   = 0;
				}

				if (!$field->hidden)
				{
					$className = ($field->type == 'Spacer') ? 'w-100' : '';

				?>
					<div class="<?php echo $customColClass . ' ' . $className;  ?> custom-form-style">
						<div class="form-group">
								<div class="col-sm-12 control-label w-100 text-left">
									<?php echo $field->label; ?>
	
								<?php if($useTooltip && $description){?>
								<i class="fa fa-info-circle"  title=""  data-toggle="tooltip" data-content="<?php echo $description; ?>" data-original-title="<?php echo $description; ?>"></i>
								<?php }?>
								</div>
								
								
								<?php
								// TODO :- Check and remove
								if ($field->type == 'File')
								{
									if ($this->copyRecId)
									{
										$field->setValue('');
									}

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
								<div class="col-sm-12 rop-inputs w-100">
									<?php echo $field->input; ?>
										<div>
										<?php
										if (strpos($field->fieldname, 'clusterclusterid'))
										{
											$clusterFieldName = $field->fieldname;
										}
										?>
										</div>
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

		/* //if (count($fieldSets) > 1)
		{ */
			if (count($this->form_extra->getFieldset($fieldset->name)))
			{

				foreach ($this->form_extra->getFieldset($fieldset->name) as $field)
				{
					if (!$field->hidden)
					{
						echo HTMLHelper::_("bootstrap.endTab");?>

						<?php
							break;
					}
				}


			}
		//}



	}?>
		<div class="form-actions buttons-mobile-view border-0 bg-none action-btns">
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

		if (isset($setnavigation) && $setnavigation == true && empty($tmpl))
		{
			?>
		<!-- <button type="button" class="btn btn-primary mt-20" id="previous_button" >
			<i class="icon-arrow-left-2"></i>
			<?php //echo Text::_('COM_TJUCM_PREVIOUS_BUTTON'); ?>
		</button>
		<button type="button" class="btn btn-primary mt-20" id="next_button" >
			<?php //echo Text::_('COM_TJUCM_NEXT_BUTTON'); ?>
			<i class="icon-arrow-right-2"></i>
		</button> -->
		<?php
		}

		if ($calledFrom == 'frontend')
		{
			?>
			<span class="pull-right mt-20">
			<?php

			if (($this->allow_auto_save || $this->allow_draft_save) && $itemState)
			{
				?>
				<input type="button" class="btn btn-default px-25 mobile-space" id="tjUcmSectionDraftSave"
				value="<?php echo Text::_("COM_TJUCM_SAVE_AS_DRAFT_ITEM"); ?>"
				onclick="tjUcmItemForm.saveUcmFormData();" />
				<?php
			}
			?>

			<input type="button" class="btn btn-primary px-25 mobile-space" value="<?php echo Text::_('COM_TJUCM_SAVE_ITEM'); ?>" id="tjUcmSectionFinalSave" onclick="tjUcmItemForm.saveUcmFormData();" />

			<?php if (empty($tmpl)) : ?>
			<input type="button" class="btn btn-primary px-25 mobile-space" value="<?php echo Text::_("COM_TJUCM_SAVE_CLOSE_ITEM"); ?>" id="tjUcmSectionFinalSaveClose" onclick="tjUcmItemForm.saveUcmFormData();" />
			<input type="button" class="btn btn-warning mobile-space" value="<?php echo Text::_('COM_TJUCM_CANCEL_BUTTON'); ?>" onclick="Joomla.submitbutton('itemform.cancel');" />
			<?php endif; ?>
			</span>
			<?php
		}
	?>
</div>
	<?php

	if (count($fieldSets) > 1)
	{
		echo HTMLHelper::_('bootstrap.endTabSet');
	}
}
else
{
?>
	<div class="alert alert-info">
		<?php echo Text::_('COM_TJLMS_NO_EXTRA_FIELDS_FOUND');?>
	</div>
<?php
}?>

<?php

// DPE - Hack - To copy the record

if ($this->copyRecId)
{
?>
<script type="text/javascript">
	jQuery(window).load(function ()
	{
		// Check record id is empty and user tried to copy record
		if (jQuery.trim(jQuery('#recordId').val()) == '' || jQuery('#recordId').val() == undefined)
		{
			// Find the all parent contentid fields of subforms
			jQuery('.ucmsubform').find("input[name*='_contentid']").each(function(){

				// Check the field type is hidden and confirm its parent reference number
				if (jQuery(this).attr('type') == 'hidden')
				{
					// Reset the field value if trying to copy the record
					jQuery(this).val('');
				}
			});
		}
	});


</script>
<?php
}
?>
<input type="hidden" name="clusterFieldName" id="clusterFieldUniqueName" value="<?php echo $clusterFieldName; ?>"/>
<?php
$tmpl  = $app->input->get('tmpl', '', 'STRING');

if ($clusterFieldName == 'com_tjucm_ropvendors_clusterclusterid' && empty($tmpl))
{
$doc = Factory::getDocument();
$doc->addScript(Uri::root() . 'media/com_dpe/js/tjucmreverselist.js');
?>

<script type="text/javascript">
jQuery(document).ready(function() {
	alert(" bv");
	jQuery("#jform_<?php echo $clusterFieldName; ?>").change(function(){
		tjucmreverselist.getReverseListUrl();
	});
});
</script>
<?php
}
// DPE - Hack - End
?>
<script type="text/javascript">
jQuery(document).ready(function() {

var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
 this.classList.toggle("active"); 
 
    var panel = this.nextElementSibling;
    if (panel.style.display === "block") {
      panel.style.display = "none";
    } else {
      panel.style.display = "block";
    }
  });
}
});
</script>
