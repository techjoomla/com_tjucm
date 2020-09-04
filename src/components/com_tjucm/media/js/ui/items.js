jQuery(window).load(function()
{
	var client = jQuery('#client').val();

	var currentUcmType = new FormData();
	currentUcmType.append('client', client);
	var afterCheckCompatibilityOfUcmType = function(error, response){
		response = JSON.parse(response);

		if (response.data)
		{
			jQuery.each(response.data, function(key, value) {
			 jQuery('#target_ucm').append(jQuery('<option></option>').attr('value',value.value).text(value.text)); 
			 jQuery('#target_ucm').trigger('liszt:updated');
			});
		}
		else
		{
			jQuery('.ucmListField').addClass('hide');
		}
	};
	
	// Code to check ucm type compatibility to copy item
	com_tjucm.Services.Items.chekCompatibility(currentUcmType, afterCheckCompatibilityOfUcmType);

	var afterGetClusterField = function(error, response){
		response = JSON.parse(response);
		if (response.data != null)
		{
			jQuery.each(response.data, function(key, value) {
			 jQuery('#cluster_list').append(jQuery('<option></option>').attr('value',value.value).text(value.text)); 
			 jQuery('#cluster_list').trigger('liszt:updated');
			});
		}
		else
		{
			jQuery('.clusterListField').addClass('hide');
		}
	};
	
	// To get the cluster fields options
	com_tjucm.Services.Items.getClusterFieldOptions(currentUcmType, afterGetClusterField);
});

// Method to Copy items
function copyItem()
{
	var afterCopyItem = function(error, response){
		response = JSON.parse(response);
		
		sessionStorage.setItem('message', response.message);
		if(response.data !== null)
		{
			sessionStorage.setItem('class', 'alert alert-success');
		}
		else
		{
			sessionStorage.setItem('class', 'alert alert-danger');
		}
	}

	var copyItemData =  jQuery('#adminForm').serialize();

	// Code to copy item to ucm type
	com_tjucm.Services.Items.copyItem(copyItemData, afterCopyItem);
}
