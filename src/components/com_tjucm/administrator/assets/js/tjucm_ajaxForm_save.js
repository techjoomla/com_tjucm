/*Variable to store the updated options of related field*/
var tjucmRelatedFieldUpdatedOptions = '';

/* This function executes for autosave form */
jQuery(document).ready(function()
{
	/*Code to get item state*/
	let itemState = jQuery('#itemState').val();

	/*Code for auto save on blur event add new record or editing draft record only*/
	if (itemState == '' || itemState === 0)
	{
		let showDraftSuccessMsg = "0";

		let tjUcmAutoSave = jQuery('#item-form #tjucm-autosave').val();

		/*Check if auto save is enabled for UCM type*/
		if (tjUcmAutoSave == 1)
		{
			/* Save form values */
			jQuery("#item-form").on("change select", ":input", function(){
				steppedFormSave(this.form.id, "draft", showDraftSuccessMsg);
			});

			/* To save calendar field value */
			jQuery("#item-form .field-calendar input:text").blur(function(){
				let tjUcmFormDirty = jQuery('#item-form').hasClass('dirty');

				if (tjUcmFormDirty === true)
				{
					steppedFormSave(this.form.id, "draft", showDraftSuccessMsg);
				}
			});
		}
	}

	/*Update the options of related field for new record of subform*/
	jQuery(document).on('subform-row-add', function(event, row){
		let count = jQuery(row).attr('data-group').replace(jQuery(row).attr('data-base-name'), "");

		jQuery.each(tjucmRelatedFieldUpdatedOptions, function(index, value) {
			if (value.templateId)
			{
				let newTemplateId = value.templateId.replace("XXX_XXX", count);
				jQuery(row).find("#"+newTemplateId).html('');
				jQuery.each(value.options, function(i, val) {
					jQuery(row).find("#"+newTemplateId).append('<option value="'+val.value+'">'+val.text+'</option>');
				});

				jQuery(row).find("#"+newTemplateId).trigger("liszt:updated");
			}
		});
	});
});

/* This function carries stepped saving via ajax */
function steppedFormSave(form_id, status, showDraftSuccessMsg = "1")
{
	/* For AJAX save need to add this to prevent popup message for page unload*/
	window.onbeforeunload = null;

	/* For AJAX save need to assign values to the editor field containers*/
	jQuery("#item-form .toggle-editor a").each(function(index) {
		this.click();
	});

	var item_basic_form = jQuery('#' + form_id);
	var promise = false;
	jQuery('#form_status').val(status);

	if ('save' == status) {

		if(confirm(Joomla.JText._("COM_TJUCM_ITEMFORM_SUBMIT_ALERT")))
		{
			/* code to remove the class added by are-you-sure alert box */
			jQuery('#item-form').removeClass('dirty');

			if (!document.formvalidator.isValid('#item-form'))
			{
				jQuery('#finalSave').attr('disabled', false);
				jQuery('#draftSave').attr('disabled', false);
				jQuery("html, body").animate({ scrollTop: 0 }, "slow");

				return false;
			}
		}
		else
		{
			jQuery('#draftSave').attr('disabled', false);
			jQuery('#finalSave').attr('disabled', false);
			jQuery("html, body").animate({ scrollTop: 0 }, "slow");

			return false;
		}
	}

	if(item_basic_form)
	{
		jQuery(item_basic_form).ajaxSubmit({
			datatype:'JSON',
			async: false,
			success: function(data) {
				let returnedData = JSON.parse(data);

				if (returnedData.messages !== null)
				{
					if (returnedData.messages.error !== null)
					{
						jQuery.each(returnedData.messages.error, function(index, value) {
							Joomla.renderMessages({'error':[value]});
						});

						jQuery("html, body").animate({ scrollTop: 0 }, "slow");
					}
				}

				if (returnedData.message !== null && returnedData.message != '')
				{
					Joomla.renderMessages({'info':[returnedData.message]});

					jQuery("html, body").animate({ scrollTop: 0 }, "slow");
				}

				if (returnedData.data !== null)
				{
					jQuery('#item-form').removeClass('dirty');

					if ('save' == status)
					{
						jQuery("#finalSave").attr("disabled", "disabled");
						Joomla.renderMessages({'success':[Joomla.JText._('COM_TJUCM_MSG_ON_SAVED_FORM')]});
						jQuery('html, body').animate({
							scrollTop: jQuery("#system-message-container").offset().top-40
						}, "slow");
					}
					else
					{
						jQuery("#recordId").val(returnedData.data.id);
						promise = true;

						if (showDraftSuccessMsg === "1")
						{
							jQuery("#draft_msg").show();
							setTimeout(function() { jQuery("#draft_msg").hide(); }, 5000);
						}
					}

					/* Update item id in the URL if the data is stored successfully */
					let url = window.location.href.split('#')[0];
					let separator = (url.indexOf("?")===-1)?"?":"&";
					let newParam = "id=" + returnedData.data.id;

					/* Add content_id in ucmsubform records */
					jQuery.each(returnedData.data.childContentIds, function(i, val) {
						jQuery("input[name='"+val.elementName+"']").val(val.content_id);
					});

					/* Add content_id in ucmsubform records */
					tjucmRelatedFieldUpdatedOptions = returnedData.data.relatedFieldOptions;
					jQuery.each(returnedData.data.relatedFieldOptions, function(index, value) {
						jQuery("#"+value.elementId).html('');

						jQuery.each(value.options, function(i, val) {
							let selected = '';

							if (val.selected == '1'){
								selected = ' selected="selected" ';
							}

							jQuery("#"+value.elementId).append('<option value="'+val.value+'" '+selected+'>'+val.text+'</option>');
						});

						jQuery("#"+value.elementId).trigger("liszt:updated");
					});

					if (!(url.indexOf(newParam) >= 0))
					{
						url+=separator+newParam;
					}

					history.pushState(null, null, url);
				}

				jQuery('#draftSave').attr('disabled', false);
				jQuery('#finalSave').attr('disabled', false);

				/* After AJAX save need to toggle back the editors as we had previoussly toggled them to post the values*/
				jQuery("#item-form .toggle-editor a").each(function(index) {
					this.click();
				});
			}
		});
	}

	return promise;
}

/*Function triggered by clicking on the "Save and next"*/
function itemformactions(tab_id, navDirection)
{
	var getTabId = tab_id + "Tabs";

	var currentTabName = jQuery('ul#' + getTabId).find('li.active a').attr('href');
	var nextTabName = jQuery('ul#' + getTabId).find('li.active').next('li').children('a').attr('href');
	var prevTabName = jQuery('ul#' + getTabId).find('li.active').prev('li').children('a').attr('href');

	/* Once all fields are validated, enable Final Save*/
	steppedFormSave('item-form', 'draft');

	if (navDirection == "next")
	{
		jQuery('#' + getTabId + ' > .active').next('li').find('a').trigger('click');
	}

	if (navDirection == "prev")
	{
		jQuery('#' + getTabId + ' > .active').prev('li').find('a').trigger('click');
	}
}
