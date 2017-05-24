js = jQuery.noConflict();

/* This function carries stepped saving via ajax */
function steppedFormSave(form_id, status)
{
	var item_basic_form = jQuery('#' + form_id);
	var promise = false;
	jQuery('#form_status').val(status);

	if ('save' == status) {
		if(confirm(Joomla.JText._('COM_TJUCM_ITEMFORM_ALERT')) == true)
		{
			if (!document.formvalidator.isValid('#item-form')) {
					return false;
			}
		}
		else
		{
			return false;
		}
	}

	if(item_basic_form)
	{
		jQuery(item_basic_form).ajaxSubmit({
			datatype:'JSON',
			success: function(data) {

				var returnedData = JSON.parse(data);

				if ('save' == status) {
					jQuery("#finalSave").attr("disabled", "disabled");
					var url= window.location.href.split('#')[0],
					separator = (url.indexOf("?")===-1)?"?":"&",
					newParam=separator + "success=1&layout=details";
					//~ newParam=separator + "layout=details";
					newUrl=url.replace(newParam,"");
					newUrl+=newParam;
					window.location.href =newUrl;

					opener.location.reload();
					window.close();
				}
				else
				{
					jQuery("#recordId").val(returnedData.data);
					promise = true;
					jQuery("#draft_msg").show();
					setTimeout(function() { jQuery("#draft_msg").hide(); }, 5000);
				}
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
	steppedFormSave('item-form');

	if (navDirection == "next")
	{
		jQuery('#' + getTabId + ' > .active').next('li').find('a').trigger('click');
	}
	if (navDirection == "prev")
	{
		jQuery('#' + getTabId + ' > .active').prev('li').find('a').trigger('click');
	}
}
