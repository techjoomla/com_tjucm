/*Variable to store the updated options of related field*/
var tjucmRelatedFieldUpdatedOptions = '';
/*Variable to store the data of editor field*/
var tjUcmTinyMCEFieldIds = new Array();
/*Variable to store if the next button is clicked*/
var tjUcmClickedOnNext = 0;
/*Variable to store if the prev button is clicked*/
var tjUcmClickedOnPrev = 0;
/*Variable to store if autosave is currently allowed for the form*/
var tjUcmCurrentAutoSaveState = 0;
/*Variable to store if form is submited for final save*/
var tjUcmFormFinalSave = 0;

/* This function executes for autosave form */
jQuery(window).load(function()
{
	/*Code to get item state*/
	var tjUcmCurrentDraftSaveState = Number(jQuery('#itemState').val());

	/* If record is submitted and no longet in the draft state then dont allow autosave to work*/
	if (tjUcmCurrentDraftSaveState === 1)
	{
		var tjUcmAllowAutoSave = jQuery('#item-form #tjucm-autosave').val();

		/*Check if auto save is enabled for UCM type*/
		if (tjUcmAllowAutoSave == 1)
		{
			tjUcmCurrentAutoSaveState = 1;

			/* Save form values */
			jQuery("#item-form").on("change select", ":input", function(){
				if (tjUcmCurrentAutoSaveState)
				{
					// Call function if field name & value exist in request data
					if (jQuery(this).attr('name') !='' && jQuery(this).attr('name') != undefined)
					{
						// If field is required and user tried to remove value then no need to call function
						if ((jQuery(this).attr('required') == 'required' || jQuery(this).attr('required') == true) && jQuery.trim(jQuery(this).val()) =='')
						{

							return false;
						}

						tjUcmItemForm.onUcmFormChange(this);
					}
				}
			});

			/* To save calendar field value */
			jQuery("#item-form .field-calendar input:text").blur(function(){
				if (tjUcmCurrentAutoSaveState)
				{
					// Call function if field name & value exist in request data
					if (jQuery(this).attr('name') !='' && jQuery(this).attr('name') != undefined)
					{
						// If field is required and user tried to remove value then no need to call function
						if ((jQuery(this).attr('required') == 'required' || jQuery(this).attr('required') == true) && jQuery.trim(jQuery(this).val()) =='')
						{

							return false;
						}

						tjUcmItemForm.onUcmFormChange(this);
					}
				}
			});

			var tjUcmTinyMCE = Joomla.getOptions("plg_editor_tinymce");

			/* Get the value of editor fields*/
			if (tjUcmTinyMCE != undefined)
			{
				jQuery.each(tjUcmTinyMCE.tinyMCE, function(index, value){
					if (jQuery("#item-form #jform_"+index).length)
					{
						var tjUcmEditorFieldContent = jQuery("#jform_"+index+"_ifr").contents().find('body').html();
						tjUcmTinyMCEFieldIds[index] = tjUcmEditorFieldContent;
					}
					else if ((jQuery("#item-form #jform_"+index).length == 0) && (index != 'default'))
					{
						var tjUcmSubFormEditorFields = jQuery("textarea[id$='__"+index+"']");

						if (tjUcmSubFormEditorFields.length)
						{
							jQuery.each(tjUcmSubFormEditorFields, function(findex, fvalue){
								var tjUcmEditorFieldContentId = jQuery(fvalue).attr('id');
								var tjUcmEditorFieldContent = jQuery("#"+tjUcmEditorFieldContentId+"_ifr").contents().find('body').html();
								var tjucmTempIndex = tjUcmEditorFieldContentId.replace("jform_", "");
								tjUcmTinyMCEFieldIds[tjucmTempIndex] = tjUcmEditorFieldContent;
							});
						}
					}
				});

				/* Check after some time if the content of editor is changed and if so then save it in DB*/
				setInterval(function () {
					for (var key in tjUcmTinyMCEFieldIds) {
						if (tjUcmTinyMCEFieldIds.hasOwnProperty(key)) {
							var tjUcmEditorFieldContent = jQuery("#jform_"+key+"_ifr").contents().find('body').html();

							if (tjUcmTinyMCEFieldIds[key] != tjUcmEditorFieldContent)
							{
								var tjUcmTempFieldObj = jQuery("#jform_"+key);

								if (tjUcmTempFieldObj.length)
								{
									tjUcmTempFieldObj.val(tjUcmEditorFieldContent);
									tjUcmTinyMCEFieldIds[key] = tjUcmEditorFieldContent;
									tjUcmItemForm.onUcmFormChange(tjUcmTempFieldObj);
								}
							}
						}
					}
				},7000);
			}
		}
	}
	else
	{
		jQuery("#tjucm-auto-save-disabled-msg").show();
	}

	/* Set the visibility of navigation buttons on tab change*/
	jQuery("#tjucm_myTabTabs a").on('click', function(){
		if (jQuery(this).parent().next('li').length)
		{
			jQuery("#next_button").attr('disabled', false);
		}
		else
		{
			jQuery("#next_button").attr('disabled', true);
		}

		if (jQuery(this).parent().prev('li').length)
		{
			jQuery("#previous_button").attr('disabled', false);
		}
		else
		{
			jQuery("#previous_button").attr('disabled', true);
		}
	});

	/*Update the options of related field for new record of subform*/
	jQuery(document).on('subform-row-add', function(event, row){
		var tjucmSubFormCount = jQuery(row).attr('data-group').replace(jQuery(row).attr('data-base-name'), "");

		/* If there is any editor field in sub-form then add its reference in variable tjUcmTinyMCEFieldIds*/
		if (jQuery(row).find('.js-editor-tinymce textarea'))
		{
			var tjUcmIdOfEditorFieldInSubForm = jQuery(row).find('.js-editor-tinymce textarea').attr('id');

			if (tjUcmIdOfEditorFieldInSubForm)
			{
				var tjUcmSubFormEditorFieldContent = jQuery("#"+tjUcmIdOfEditorFieldInSubForm+"_ifr").contents().find('body').html();
				tjUcmIdOfEditorFieldInSubForm = tjUcmIdOfEditorFieldInSubForm.replace('jform_', '');
				tjUcmTinyMCEFieldIds[tjUcmIdOfEditorFieldInSubForm] = tjUcmSubFormEditorFieldContent;
			}
		}

		/* Update options of related fields*/
		jQuery.each(tjucmRelatedFieldUpdatedOptions, function(index, value) {
			if (value.templateId)
			{
				var tjucmNewTemplateId = value.templateId.replace("XXX_XXX", tjucmSubFormCount);
				jQuery(row).find("#"+tjucmNewTemplateId).html('');
				jQuery.each(value.options, function(i, val) {
					jQuery(row).find("#"+tjucmNewTemplateId).append('<option value="'+val.value+'">'+val.text+'</option>');
				});

				jQuery(row).find("#"+tjucmNewTemplateId).trigger("liszt:updated");
			}
		});
	});

	/* Handle next and previous button click on the form*/
	jQuery("#next_button, #previous_button").on('click', function (){
		if (jQuery(this).attr('id') == 'next_button')
		{
			tjUcmClickedOnNext = 1;
		}
		else
		{
			tjUcmClickedOnPrev = 1;
		}

		if (jQuery('#item-form').hasClass('dirty'))
		{
			if (tjUcmCurrentAutoSaveState)
			{
				var tjUcmSectionInputElements = jQuery(jQuery('#tjucm_myTabTabs > .active a').attr('href')).find('input, textarea, select, fieldset');

				if (tjUcmItemForm.validateSection(tjUcmSectionInputElements))
				{
					tjUcmItemForm.saveSectionData(jQuery('#tjucm_myTabTabs > .active a').attr('href'));
				}
				else
				{
					tjUcmClickedOnNext = 0;
					tjUcmClickedOnPrev = 0;
				}
			}
			else
			{
				var tjUcmSectionInputElements = jQuery(jQuery('#tjucm_myTabTabs > .active a').attr('href')).find('input, textarea, select, fieldset');

				if (tjUcmItemForm.validateSection(tjUcmSectionInputElements))
				{
					/* Clear the error messages first if any before processing the data*/
					jQuery("#system-message-container").html("");

					if (tjUcmClickedOnNext)
					{
						tjUcmClickedOnNext = 0;
						jQuery('#tjucm_myTabTabs > .active').next('li').find('a').trigger('click');
						tjUcmItemForm.setVisibilityOfNavigationButtons();
					}

					if (tjUcmClickedOnPrev)
					{
						tjUcmClickedOnPrev = 0;
						jQuery('#tjucm_myTabTabs > .active').prev('li').find('a').trigger('click');
						tjUcmItemForm.setVisibilityOfNavigationButtons();
					}
				}
				else
				{
					tjUcmClickedOnNext = 0;
					tjUcmClickedOnPrev = 0;
				}

				jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");
			}
		}
		else
		{
			var tjUcmSectionInputElements = jQuery(jQuery('#tjucm_myTabTabs > .active a').attr('href')).find('input, textarea, select, fieldset');

			if (tjUcmItemForm.validateSection(tjUcmSectionInputElements))
			{
				/* Clear the error messages first if any before processing the data*/
				jQuery("#system-message-container").html("");

				if (tjUcmClickedOnNext)
				{
					tjUcmClickedOnNext = 0;
					jQuery('#tjucm_myTabTabs > .active').next('li').find('a').trigger('click');
					tjUcmItemForm.setVisibilityOfNavigationButtons();
				}

				if (tjUcmClickedOnPrev)
				{
					tjUcmClickedOnPrev = 0;
					jQuery('#tjucm_myTabTabs > .active').prev('li').find('a').trigger('click');
					tjUcmItemForm.setVisibilityOfNavigationButtons();
				}
			}
			else
			{
				tjUcmClickedOnNext = 0;
				tjUcmClickedOnPrev = 0;
			}

			jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");
		}
	});
});

var tjUcmItemForm = {
	getUcmParentRecordId: function (draft, callback){
		var tjUcmParentClient = jQuery('#item-form').find("input[name='jform[client]']").val();

		var getParentRecordid = new Promise(function(resolve, reject) {
			var tjucmParentRecordId = jQuery('#item-form').find("input[name='jform[id]']").val();

			if (tjucmParentRecordId == '')
			{
				var tjUcmItemFormData = new FormData();

				/* Set parent client in the data form*/
				if (tjUcmParentClient != '')
				{
					tjUcmItemFormData.append('client', tjUcmParentClient);
				}

				/* Add CSRF token to the form*/
				tjUcmItemFormData.append(Joomla.getOptions('csrf.token'), 1);

				/* Callback function after creating the parent UCM record */
				var afterCreateParentUcmRecord = function (error, response){
					response = JSON.parse(response);

					if (error == null)
					{
						if (response.data !== null && jQuery.isNumeric(response.data.id))
						{
							/* Set parent record id in the form*/
							jQuery('#item-form').find("input[name='jform[id]']").val(response.data.id);

							/* Update parent record id in the URL if the parent record is created successfully*/
							var tjucmUrl = window.location.href.split('#')[0];
							var tjucmUrlSeparator = (tjucmUrl.indexOf("?")===-1)?"?":"&";
							var tjucmNewParam = "id=" + response.data.id;

							if (!(tjucmUrl.indexOf(tjucmNewParam) >= 0))
							{
								tjucmUrl+=tjucmUrlSeparator+tjucmNewParam;
							}

							history.pushState(null, null, tjucmUrl);

							resolve(response.data.id);
						}
						else
						{
							reject(response);
						}
					}
				};

				/* Create the record in draft mode*/
				tjUcmItemFormData.append('draft', draft);

				/* Add new UCM parent record for UCM type if its not created yet*/
				com_tjucm.Services.Item.create(tjUcmItemFormData, afterCreateParentUcmRecord);
			}
			else if (jQuery.isNumeric(tjucmParentRecordId) && tjucmParentRecordId != 0)
			{
				resolve(tjucmParentRecordId);
			}
		});

		// Action on after creating the parent UCM record
		getParentRecordid.then(function (response){
			callback(response);
		}).catch(function (error){
			console.log(error);
			return false;
		});
	},
	onUcmFormChange: function (fieldObj){
		/* Disable the action buttons before performing the action*/
		jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', true);

		tjUcmItemForm.getUcmParentRecordId(1, function (tjucmParentRecordId){
			var tjUcmParentClient = jQuery('#item-form').find("input[name='jform[client]']").val();
			tjUcmItemForm.initUcmFormFieldDataSave(fieldObj, tjUcmParentClient, tjucmParentRecordId);
		});
	},
	initUcmFormFieldDataSave: function (fieldObj, tjUcmParentClient, tjUcmParentRecordId){
		/* Disable the action buttons before performing the action*/
		jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', true);

		var childRecordContentIdFieldId = '';
		var tjUcmItemFormData = new FormData();

		/* Add CSRF token to the form*/
		tjUcmItemFormData.append(Joomla.getOptions('csrf.token'), 1);

		if (jQuery(fieldObj).parent().parent().parent().attr('data-base-name') !== undefined || jQuery(fieldObj).parent().parent().parent().parent().attr('data-base-name') !== undefined)
		{
			// In case of field of subform
			var tjucmSubFormFieldName = jQuery(fieldObj).parent().parent().parent().attr('data-base-name');

			// In case of editor field of subform
			if (tjucmSubFormFieldName == undefined)
			{
				tjucmSubFormFieldName = jQuery(fieldObj).parent().parent().parent().parent().attr('data-base-name');
			}

			/* This block is executed when the field which is updated/added is from ucmsubform field under the parent form*/
			var tjUcmCurrentFieldId = jQuery(fieldObj).attr('id');
			childRecordContentIdFieldId = tjUcmCurrentFieldId.replace(tjUcmCurrentFieldId.split('_').pop(), "contentid");
			var tjucmClient = 'com_tjucm.'+childRecordContentIdFieldId.split('__').pop().replace('_contentid', '').replace('com_tjucm_', '');
			var tjucmRecordId = jQuery('#'+childRecordContentIdFieldId).val();

			/* If record is being edited then send recordId in the request else create the record*/
			if (tjucmRecordId == '')
			{
				/* Callback function after creating the UCM subform record */
				var afterCreateUcmSubFormRecord = function (error, response){
					response = JSON.parse(response);

					if (error == null)
					{
						if (response.data !== null && jQuery.isNumeric(response.data.id))
						{
							jQuery('#'+childRecordContentIdFieldId).val(response.data.id);
						}

						/* Save the ucm-subform field data*/
						var afterAddFieldValueForUcmSubFormField = function (err, rsp){
							var fieldName = jQuery(fieldObj).attr('name');
							var tjUcmIsMultiSelect = (fieldName.slice(-2) == '[]') ? '[]' : '';
							var tjUcmUpdatedSubFormFieldName = 'jform['+jQuery(fieldObj).attr('id').split('__').pop()+']'+tjUcmIsMultiSelect;

							if (jQuery(fieldObj).attr('type') == 'radio')
							{
								var tjUcmUpdatedSubFormFieldName = 'jform['+jQuery(fieldObj).attr('name').split('][').pop();
							}

							jQuery(fieldObj).attr('name', tjUcmUpdatedSubFormFieldName);

							tjUcmItemForm.saveUcmFormFieldData(tjucmClient, response.data.id, fieldObj);

							jQuery(fieldObj).attr('name', fieldName);
						}

						/* Add entry for ucm-subform-field in field_value table for the parent record*/
						tjUcmItemFormData.append('jform['+tjucmSubFormFieldName+']', tjucmClient);
						tjUcmItemFormData.append('client', tjUcmParentClient);
						tjUcmItemFormData.append('recordid', tjUcmParentRecordId);
						com_tjucm.Services.Item.saveFieldData(tjUcmItemFormData, afterAddFieldValueForUcmSubFormField);

						return true;
					}
				};

				/* Add new UCM record for UCM type is its not created yet*/
				tjUcmItemFormData.append('parent_id', tjUcmParentRecordId);
				tjUcmItemFormData.append('client', tjucmClient);

				/* Create the record in draft mode*/
				tjUcmItemFormData.append('draft', 1);
				com_tjucm.Services.Item.create(tjUcmItemFormData, afterCreateUcmSubFormRecord);
			}
			else if (jQuery.isNumeric(tjucmRecordId) && tjucmRecordId != 0)
			{
				var fieldName = jQuery(fieldObj).attr('name');
				var tjUcmIsMultiSelect = (fieldName.slice(-2) == '[]') ? '[]' : '';
				var tjUcmUpdatedSubFormFieldName = 'jform['+jQuery(fieldObj).attr('id').split('__').pop()+']'+tjUcmIsMultiSelect;

				if (jQuery(fieldObj).attr('type') == 'radio')
				{
					var tjUcmUpdatedSubFormFieldName = 'jform['+jQuery(fieldObj).attr('name').split('][').pop();
				}

				jQuery(fieldObj).attr('name', tjUcmUpdatedSubFormFieldName);

				tjUcmItemForm.saveUcmFormFieldData(tjucmClient, tjucmRecordId, fieldObj);

				jQuery(fieldObj).attr('name', fieldName);

				return true;
			}

			return false;
		}
		else
		{
			/* This block is executed when the field which is updated/added is from the parent form*/
			tjUcmItemForm.saveUcmFormFieldData(tjUcmParentClient, tjUcmParentRecordId, fieldObj);

			return true;
		}
	},
	saveUcmFormFieldData: function (tjUcmClient, tjUcmRecordId, fieldObj){
		/* Disable the action buttons before performing the action*/
		jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', true);
		var tjUcmItemFieldFormData = new FormData();

		/* Add CSRF token to the form*/
		tjUcmItemFieldFormData.append(Joomla.getOptions('csrf.token'), 1);
		tjUcmItemFieldFormData.append('client', tjUcmClient);
		tjUcmItemFieldFormData.append('recordid', tjUcmRecordId);

		if (jQuery(fieldObj).attr('type') == 'checkbox')
		{
			if (jQuery(fieldObj).prop('checked') == true)
			{
				tjUcmItemFieldFormData.append(jQuery(fieldObj).attr('name'), 1);
			}
			else
			{
				tjUcmItemFieldFormData.append(jQuery(fieldObj).attr('name'), 0);
			}
		}
		else if(jQuery(fieldObj).hasClass('tjfieldTjList'))
		{
			/* This condition used for tjlist option actial values updated  - This is used for single & multiple values*/

			if (jQuery(fieldObj).val() !='' && jQuery(fieldObj).val() != undefined)
			{
				tjUcmItemFieldFormData.append(jQuery(fieldObj).attr('name'), jQuery(fieldObj).val());
			}

			/* Check other options multiple values exist and its not empty */
			if (jQuery('input#'+jQuery(fieldObj).attr('id')).val() !='' && jQuery('input#'+jQuery(fieldObj).attr('id')).val() != undefined)
			{
				tjUcmItemFieldFormData.append(jQuery(fieldObj).attr('name'), jQuery('input#'+jQuery(fieldObj).attr('id')).val());
			}
		}
		else if(jQuery('input#'+jQuery(fieldObj).attr('id')).data('role') == "tagsinput")
		{
			/* This condition used for tjlist Other option multiple values textbox */

			if (jQuery('#'+jQuery(fieldObj).attr('id')).val() !='' && jQuery('#'+jQuery(fieldObj).attr('id')).val() != undefined)
			{
				tjUcmItemFieldFormData.append(jQuery(fieldObj).attr('name'), jQuery('#'+jQuery(fieldObj).attr('id')).val());
			}

			/* Check other options multiple values exist and its not empty */
			if (jQuery(fieldObj).val() !='' && jQuery(fieldObj).val() != undefined)
			{
				tjUcmItemFieldFormData.append(jQuery(fieldObj).attr('name'), jQuery(fieldObj).val());
			}
		}
		else if (jQuery(fieldObj).attr('type') != 'file')
		{
			tjUcmItemFieldFormData.append(jQuery(fieldObj).attr('name'), jQuery(fieldObj).val());
		}
		else
		{
			tjUcmItemFieldFormData.append(jQuery(fieldObj).attr('name'), jQuery(fieldObj)[0].files[0]);
		}

		// Call function if field name exist in request data
		if (jQuery(fieldObj).attr('name') !='' && jQuery(fieldObj).attr('name') != undefined)
		{
			com_tjucm.Services.Item.saveFieldData(tjUcmItemFieldFormData, tjUcmItemForm.afterDataSave);
		}

		return true;
	},
	afterDataSave: function (error, response){
		response = JSON.parse(response);
		/* Remove the dirty class fromt the form once the field data is saved*/
		jQuery('#item-form').removeClass('dirty');

		if (response == null)
		{
			return false;
		}

		/* Enable the save buttons once the field data is saved*/
		jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', false);

		/* Add content_id in ucmsubform records */
		if (response.data != null)
		{
			if (response.data.childContentIds)
			{
				jQuery.each(response.data.childContentIds, function(elementId, val) {
					jQuery("#"+elementId).val(val);
				});
			}
		}

		if (response.data && tjUcmFormFinalSave)
		{
			jQuery("#tjucm-auto-save-disabled-msg").show();
			jQuery("#itemState").val(0);
			jQuery("#tjUcmSectionDraftSave").remove();
			tjUcmCurrentAutoSaveState = 0;
			tjUcmFormFinalSave = 0;
		}

		if (tjUcmClickedOnNext)
		{
			tjUcmClickedOnNext = 0;
			jQuery('#tjucm_myTabTabs > .active').next('li').find('a').trigger('click');
		}

		if (tjUcmClickedOnPrev)
		{
			tjUcmClickedOnPrev = 0;
			jQuery('#tjucm_myTabTabs > .active').prev('li').find('a').trigger('click');
		}

		tjUcmItemForm.setVisibilityOfNavigationButtons();

		/* Update the options of related field */
		if (response.data)
		{
			var tjUcmParentClient = jQuery('#item-form').find("input[name='jform[client]']").val();
			var tjucmParentRecordId = jQuery('#item-form').find("input[name='jform[id]']").val();
			tjUcmItemForm.updateRelatedFieldsOptions(tjUcmParentClient, tjucmParentRecordId);
		}

		/* If there are errors in the response then show them on the screen*/
		tjUcmItemForm.renderResponseMessages(response);
	},
	renderResponseMessages: function (response)
	{
		if (response != null)
		{
			if (response.message !== null)
			{
				if (response.data)
				{
					Joomla.renderMessages({'success':[response.message]});
				}
				else
				{
					Joomla.renderMessages({'error':[response.message]});
				}

				jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");
			}

			if (response.messages !== null)
			{
				if (response.messages.error !== null)
				{
					jQuery.each(response.messages.error, function(index, value) {
						Joomla.renderMessages({'error':[value]});
					});

					jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");
				}
			}
		}
	},
	updateRelatedFieldsOptions: function (tjUcmParentClient, tjUcmParentRecordId) {
		var tjUcmItemFormData = new FormData();

		var tjUcmUpdateRelatedFieldsOptions = function (error, response){
			response = JSON.parse(response);
			tjucmRelatedFieldUpdatedOptions = response.data;

			if(tjucmRelatedFieldUpdatedOptions == '')
			{
				return false;
			}

			jQuery.each(response.data, function(index, value) {
				jQuery("#"+value.elementId).html('');

				jQuery.each(value.options, function(i, val) {
					var tjucmSelectedFieldOption = '';

					if (val.selected == '1'){
						tjucmSelectedFieldOption = ' selected="selected" ';
					}

					jQuery("#"+value.elementId).append('<option value="'+val.value+'" '+tjucmSelectedFieldOption+'>'+val.text+'</option>');
				});

				jQuery("#"+value.elementId).trigger("liszt:updated");
			});
		};

		tjUcmItemFormData.append('client', tjUcmParentClient);
		tjUcmItemFormData.append('content_id', tjUcmParentRecordId);
		com_tjucm.Services.Item.getUpdatedRelatedFieldsOptions(tjUcmItemFormData, tjUcmUpdateRelatedFieldsOptions);
	},
	saveUcmFormData: function(){
		/* Disable the action buttons before performing the action*/
		jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', true);
		var tjUcmFormSubmitCallingButtonId = event.target.id;
		var tjUcmSaveRecordAsDraft = 1;

		if (tjUcmFormSubmitCallingButtonId == 'tjUcmSectionFinalSave')
		{
			if (document.formvalidator.isValid(document.getElementById('item-form')))
			{
				if(!confirm(Joomla.JText._("COM_TJUCM_ITEMFORM_SUBMIT_ALERT")))
				{
					jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', false);

					return false;
				}

				/* Clear the error messages first if any before processing the data*/
				jQuery("#system-message-container").html("");

				/* Disable the save button till the record is saved*/
				jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', true);
			}
			else
			{
				tjUcmItemForm.setVisibilityOfNavigationButtons();
				jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', false);
				jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");

				return false;
			}

			tjUcmSaveRecordAsDraft = 0;
		}

		/* For AJAX save need to assign values to the editor field containers*/
		jQuery("#item-form .toggle-editor a").each(function(index) {
			this.click();
		});

		tjUcmItemForm.getUcmParentRecordId(tjUcmSaveRecordAsDraft, function (){
			var tjUcmForm = document.getElementById('item-form');
			var tjUcmItemFormData = new FormData(tjUcmForm);
			tjUcmItemFormData.delete('task');
			tjUcmItemFormData.delete('option');
			tjUcmItemFormData.delete('view');
			tjUcmItemFormData.delete('layout');
			var tjUcmClient = jQuery('#item-form').find("input[name='jform[client]']").val();
			var tjUcmRecordId = jQuery('#item-form').find("input[name='jform[id]']").val();

			/* Add CSRF token to the form*/
			tjUcmItemFormData.append(Joomla.getOptions('csrf.token'), 1);
			tjUcmItemFormData.append('client', tjUcmClient);
			tjUcmItemFormData.append('recordid', tjUcmRecordId);

			if (tjUcmFormSubmitCallingButtonId == 'tjUcmSectionDraftSave')
			{
				tjUcmItemFormData.append('draft', 1);
			}

			if (tjUcmFormSubmitCallingButtonId == 'tjUcmSectionFinalSave')
			{
				tjUcmFormFinalSave = 1;
			}

			jQuery('input[type="checkbox"]').each(function (){
					if (jQuery(this).prop('checked') == true)
					{
						tjUcmItemFormData.append(jQuery(this).attr('name'), 1);
					}
					else
					{
						tjUcmItemFormData.append(jQuery(this).attr('name'), 0);
					}
			});

			com_tjucm.Services.Item.saveFormData(tjUcmItemFormData, tjUcmItemForm.afterDataSave);
		});

		/* Once data is assigned to the textarea toggle the editors*/
		jQuery("#item-form .toggle-editor a").each(function(index) {
			this.click();
		});
	},
	saveSectionData: function (tabId){
		/* Disable the action buttons before performing the action*/
		jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', true);

		var tjUcmSectionFormData = new FormData();
		var tjUcmSectionInputElements = jQuery(tabId).find('input, textarea, select, fieldset');

		if (tjUcmItemForm.validateSection(tjUcmSectionInputElements))
		{
			/* Clear the error messages first if any before processing the data*/
			jQuery("#system-message-container").html("");

			/* For AJAX save need to assign values to the editor field containers*/
			jQuery("#item-form .toggle-editor a").each(function(index) {
				this.click();
			});

			if (tjUcmSectionInputElements.length)
			{
				tjUcmSectionInputElements.each(function (){
					if (jQuery(this).attr('type') == 'file')
					{
						if (jQuery(this)[0].files[0] != undefined)
						{
							tjUcmSectionFormData.append(jQuery(this).attr('name'), jQuery(this)[0].files[0]);
						}
					}
					else if(jQuery(this).attr('type') == 'checkbox')
					{
						if (jQuery(this).prop('checked') == true)
						{
							jQuery(this).val(1);
						}
						else
						{
							jQuery(this).val(0);
						}
					}
					else
					{
						if (jQuery(this).val() != undefined)
						{
							tjUcmSectionFormData.append(jQuery(this).attr('name'), jQuery(this).val());
						}
					}
				});
			}

			/* Disable the save button till the record is saved*/
			jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', true);


			tjUcmItemForm.getUcmParentRecordId(1, function (){
				tjUcmSectionFormData.delete('task');
				tjUcmSectionFormData.delete('option');
				tjUcmSectionFormData.delete('view');
				tjUcmSectionFormData.delete('layout');
				var tjUcmClient = jQuery('#item-form').find("input[name='jform[client]']").val();
				var tjUcmRecordId = jQuery('#item-form').find("input[name='jform[id]']").val();

				/* Add CSRF token to the form*/
				tjUcmSectionFormData.append(Joomla.getOptions('csrf.token'), 1);
				tjUcmSectionFormData.append('client', tjUcmClient);
				tjUcmSectionFormData.append('recordid', tjUcmRecordId);
				tjUcmSectionFormData.append('tjUcmFormSection', jQuery("a[href='"+tabId+"']").html());

				com_tjucm.Services.Item.saveFormData(tjUcmSectionFormData, tjUcmItemForm.afterDataSave);
			});
		}
		else
		{
			jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");

			return false;
		}
	},
	validateSection: function (fields){
		var valid = true, message, error, label, invalid = [], i, l;

		// Validate section fields
		for (i = 0, l = fields.length; i < l; i++) {
			// Ignore Rule/Filters/Assigned field for spead up validation
			// And other fields that has class="novalidate"
			if(jQuery(fields[i]).hasClass('novalidate')) {
				continue;
			}
			if (document.formvalidator.validate(fields[i]) === false) {
				valid = false;
				invalid.push(fields[i]);
			}
		}

		// Run custom form validators if present
		jQuery.each(document.formvalidator.custom, function(key, validator) {
			if (validator.exec() !== true) {
				valid = false;
			}
		});

		if (!valid && invalid.length > 0) {
			message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
			error = {"error": []};
			for (i = invalid.length - 1; i >= 0; i--) {
				label = jQuery(invalid[i]).data("label");
				if (label) {
					error.error.push(message + label.text().replace("*", ""));
					}
			}
			Joomla.renderMessages(error);
		}

		return valid;
	},
	setVisibilityOfNavigationButtons: function(){
		var tjUcmCurrentFormTab = jQuery('#tjucm_myTabTabs').find('li.active');

		if (jQuery(tjUcmCurrentFormTab).length)
		{
			if (jQuery(tjUcmCurrentFormTab).next('li').length)
			{
				jQuery("#next_button").attr('disabled', false);
			}
			else
			{
				jQuery("#next_button").attr('disabled', true);
			}

			if (jQuery(tjUcmCurrentFormTab).prev('li').length)
			{
				jQuery("#previous_button").attr('disabled', false);
			}
			else
			{
				jQuery("#previous_button").attr('disabled', true);
			}
		}
	}
};

/* This function carries stepped saving via ajax */
function steppedFormSave(form_id, status, showDraftSuccessMsg)
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

		if (document.formvalidator.isValid('#item-form'))
		{
			if(!confirm(Joomla.JText._("COM_TJUCM_ITEMFORM_SUBMIT_ALERT")))
			{
				jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', false);
				jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");

				return false;
			}

			/* code to remove the class added by are-you-sure alert box */
			jQuery('#item-form').removeClass('dirty');
		}
		else
		{
			jQuery(".form-actions button[type='button'], .form-actions input[type='button']").attr('disabled', false);
			jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");

			return false;
		}
	}

	if(item_basic_form)
	{
		jQuery(item_basic_form).ajaxSubmit({
			datatype:'JSON',
			async: false,
			success: function(data) {
				var returnedData = JSON.parse(data);

				if (returnedData.messages !== null)
				{
					if (returnedData.messages.error !== null)
					{
						jQuery.each(returnedData.messages.error, function(index, value) {
							Joomla.renderMessages({'error':[value]});
						});

						jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");
					}
				}

				if (returnedData.message !== null && returnedData.message != '')
				{
					Joomla.renderMessages({'info':[returnedData.message]});

					jQuery("html, body").animate({scrollTop: jQuery("#item-form").position().top}, "slow");
				}

				if (returnedData.data !== null)
				{
					jQuery("#recordId").val(returnedData.data.id);

					if ('save' == status)
					{
						jQuery("#tjUcmSectionFinalSave").attr("disabled", "disabled");
						Joomla.renderMessages({'success':[Joomla.JText._('COM_TJUCM_MSG_ON_SAVED_FORM')]});
						jQuery('html, body').animate({
							scrollTop: jQuery("#system-message-container").offset().top-40
						}, "slow");
					}
					else
					{
						promise = true;

						if (showDraftSuccessMsg === "1")
						{
							jQuery("#draft_msg").show();
							setTimeout(function() { jQuery("#draft_msg").hide(); }, 5000);
						}
					}

					/* Update item id in the URL if the data is stored successfully */
					var tjucmUrl = window.location.href.split('#')[0];
					var tjucmUrlSeparator = (tjucmUrl.indexOf("?")===-1)?"?":"&";
					var tjucmNewParam = "id=" + returnedData.data.id;

					/* Add content_id in ucmsubform records */
					jQuery.each(returnedData.data.childContentIds, function(i, val) {
						jQuery("input[name='"+val.elementName+"']").val(val.content_id);
					});

					/* Add content_id in ucmsubform records */
					tjucmRelatedFieldUpdatedOptions = returnedData.data.relatedFieldOptions;
					jQuery.each(returnedData.data.relatedFieldOptions, function(index, value) {
						jQuery("#"+value.elementId).html('');

						jQuery.each(value.options, function(i, val) {
							var tjucmSelectedFieldOption = '';

							if (val.selected == '1'){
								tjucmSelectedFieldOption = ' selected="selected" ';
							}

							jQuery("#"+value.elementId).append('<option value="'+val.value+'" '+tjucmSelectedFieldOption+'>'+val.text+'</option>');
						});

						jQuery("#"+value.elementId).trigger("liszt:updated");
					});

					if (!(tjucmUrl.indexOf(tjucmNewParam) >= 0))
					{
						tjucmUrl+=tjucmUrlSeparator+tjucmNewParam;
					}

					history.pushState(null, null, tjucmUrl);
				}

				jQuery('#tjUcmSectionDraftSave').attr('disabled', false);
				jQuery('#tjUcmSectionFinalSave').attr('disabled', false);

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
	var tjUcmCurrentFormTab = jQuery('ul#tjucm_myTabTabs').find('li.active a');

	if (jQuery(tjUcmCurrentFormTab).next('li') == undefined)
	{
		jQuery("#previous_button").attr('disabled', true);
	}
	else
	{
		jQuery("#previous_button").attr('disabled', false);
	}

	if (jQuery(tjUcmCurrentFormTab).prev('li') == undefined)
	{
		jQuery("#next_button").attr('disabled', true);
	}
	else
	{
		jQuery("#next_button").attr('disabled', false);
	}

	if (next)
	{
		jQuery('#tjucm_myTabTabs > .active').next('li').find('a').trigger('click');
	}
	else
	{
		jQuery('#tjucm_myTabTabs > .active').next('li').prev('a').trigger('click');
	}


	var nextTabName = jQuery('ul#' + getTabId).find('li.active').next('li').children('a').attr('href');
	var prevTabName = jQuery('ul#' + getTabId).find('li.active').prev('li').children('a').attr('href');

	if (nextTabName == undefined)
	{
		jQuery('#next_button').attr('disabled', true);
	}
	else
	{
		jQuery('#next_button').attr('disabled', false);
	}

	if (prevTabName == undefined)
	{
		jQuery('#previous_button').attr('disabled', true);
	}
	else
	{
		jQuery('#previous_button').attr('disabled', false);
	}

	/* Once all fields are validated, enable Final Save*/
	steppedFormSave('item-form', 'draft', 1);

	if (navDirection == "next")
	{
		jQuery('#' + getTabId + ' > .active').next('li').find('a').trigger('click');
	}

	if (navDirection == "prev")
	{
		jQuery('#' + getTabId + ' > .active').prev('li').find('a').trigger('click');
	}
}
