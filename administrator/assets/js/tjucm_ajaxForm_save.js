js = jQuery.noConflict();

/* This function carries stepped saving via ajax */
function steppedFormSave(form_id, status)
{
	var item_basic_form = jQuery('#' + form_id);
	var promise = false;
	jQuery('#form_status').val(status);

	if ('save' == status) {
		if (!document.formvalidator.isValid('#item-form')) {
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
					/*var url= window.location.href.split('#')[0],
					separator = (url.indexOf("?")===-1)?"?":"&",
					newParam=separator + "success=1";
					newUrl=url.replace(newParam,"");
					newUrl+=newParam;
					window.location.href =newUrl;*/
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
