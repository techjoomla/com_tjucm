	js = jQuery.noConflict();

	jQuery(window).load(function(){
		jQuery("#finalSave").prop('disabled', true);
		jQuery("#previous_button").addClass('hidden');

		//jQuery('#tjucm_myTabTabs li').removeClass('active');
		//jQuery('#tjucm_myTabTabs li:first-child').addClass('active');
		//$("#tjucm_myTabTabs li:first-child").tab('show');
		jQuery("#tjucm_myTabTabs li:eq(0) a").tab('show');

		jQuery("#tjucm_myTabTabs li").click(function(e) {
			var isFirstChild = jQuery(this).is(':first-child');
			var isLastChild = jQuery(this).is(':last-child');

			if (isFirstChild == true)
			{
				jQuery("#previous_button").addClass('hidden');
				jQuery("#next_button").removeClass('hidden');
			}
			else if (isLastChild == true)
			{
				jQuery("#previous_button").removeClass('hidden');
				jQuery("#next_button").addClass('hidden');
			}
			else
			{
				jQuery("#previous_button").removeClass('hidden');
				jQuery("#next_button").removeClass('hidden');
			}

			itemformactions("tjucm_myTab", '');
		});

		/* On change event for every element in the form.. */
		jQuery("form :input, textarea, select").change(function() {

			var fields = jQuery("#item-form")
				.find("select[required=required], textarea[required=required], input[required=required]").serializeArray();
			var flag = true;

			jQuery.each(fields, function(i, field) {
				if (!field.value)
				{
					flag = false;
					return;
				}
			});

			if (flag == true)
				jQuery("#finalSave").prop('disabled', false);
			else
				jQuery("#finalSave").prop('disabled', true);

		});

	});

	/*Function triggered by clicking on the "Save and next" of the 1st Basic details tab of lesson */
	function itemformactions(tab_id, navDirection)
	{
		var getTabId = tab_id + "Tabs";
		//~ console.log(getTabId);
		var currentTabName = jQuery('ul#' + getTabId).find('li.active a').attr('href');
		var nextTabName = jQuery('ul#' + getTabId).find('li.active').next('li').children('a').attr('href');
		var prevTabName = jQuery('ul#' + getTabId).find('li.active').prev('li').children('a').attr('href');

		/* Once all fields are validated, enable Final Save*/


		if(steppedFormSave('item-form'))
		{
			if (navDirection == "next")
			{
				/*Ater validating the next "format" should be avtive*/
				jQuery(currentTabName).removeClass('active');
				jQuery(nextTabName).addClass('active');

				jQuery("[href='" + currentTabName + "']").parent('li').removeClass('active');
				jQuery("[href='" + nextTabName + "']").parent('li').addClass('active');

				var newnextTabName = jQuery('ul#' + getTabId).find('li.active').next('li').children('a').attr('href');
				var newprevTabName = jQuery('ul#' + getTabId).find('li.active').prev('li').children('a').attr('href');

				jQuery("#previous_button").removeClass('hidden');

				//~ console.log("1111111 NEXTt - " + newnextTabName + "Prevvv" + newprevTabName);
				if (typeof newnextTabName === "undefined" || newnextTabName == "#permissions")
					jQuery("#next_button").addClass('hidden');
				else
					jQuery("#next_button").removeClass('hidden');

			}
			else if (navDirection == "prev")
			{
				jQuery(currentTabName).removeClass('active');
				jQuery(prevTabName).addClass('active');

				jQuery("[href='" + currentTabName + "']").parent('li').removeClass('active');
				jQuery("[href='" + prevTabName + "']").parent('li').addClass('active');

				var newnextTabName = jQuery('ul#' + getTabId).find('li.active').next('li').children('a').attr('href');
				var newprevTabName = jQuery('ul#' + getTabId).find('li.active').prev('li').children('a').attr('href');

				//~ console.log("222222 NEXTt - " + newnextTabName + "Prevvv" + newprevTabName);
				jQuery("#next_button").removeClass('hidden');

				if (typeof newprevTabName === "undefined" || newprevTabName == "#permissions")
					jQuery("#previous_button").addClass('hidden');
				else
					jQuery("#previous_button").removeClass('hidden');

			}
		}
	}

	/* Send call on final save step */
	function finalsave(form_id)
	{
		var item_basic_form = jQuery('#' + form_id);

		if (!document.formvalidator.isValid(item_basic_form))
		{
			alert("Please ensure you have filled all neccessay fields");
			return false;
		}

		confirm("Do you want to proceed further!");

		if (steppedFormSave(form_id))
		{
			/* SEt redirection to List view, once done*/
			window.location.reload();
		}

	}

	/* Called on Save as Draft*/
	function saveAsDraft(form_id)
	{
		var item_basic_form = jQuery('#' + form_id);

		if (steppedFormSave(form_id))
		{
			/* SEt redirection to List view, once done*/
			window.location.reload();
		}

	}

	/* This function carries stepped saving via ajax */
	function steppedFormSave(form_id)
	{
		var item_basic_form = jQuery('#' + form_id);
		var return_var = 1;

		/*Make the all form inputs Script safe*/
		//~ formInputsWithoutScript(lesson_basic_form);

		if(item_basic_form)
		{
			jQuery(item_basic_form).ajaxSubmit({
				datatype:'json',
				async:false,
				beforeSend: function() {
					jQuery('.loading',item_basic_form).show();
				},
				success: function(data)
				{
					var response = jQuery.parseJSON(data);
					var output	=	response.OUTPUT;
					var res	=	output[0];
					var msg	=	output[1];
					var record_id	=	output[2];
					jQuery("#recordId").val(record_id);

					if(res == 1)
					{
						return_var	= 1 ;
					}
					else
					{
						return_var =  0;
						return return_var;
						return false;
					}
				},
				complete: function(xhr) {
					jQuery('.loading',item_basic_form).hide();
				}
			});
		}
		else
		{
			return_var =  0;
		}

		if (return_var == 1)
		{
			//~ techjoomla.jQuery(".tjlms_form_errors", lessonform).hide();
		}

		// always return false to prevent standard browser submit and page navigation
		return return_var;
	}


	function formInputsWithoutScript(givenform)
	{
		jQuery('input[type=text], textarea',givenform).each(
		function(){
			var input = jQuery(this);
			var noScriptVal = noScript(input.val())
			jQuery(this).val(noScriptVal);
			}
		);
	}

	/*TO remove the script tags from str*/
	function noScript(str)
	{
		var div = jQuery('<div>').html(str);
		div.find('script').remove();

		var noscriptStr = str = div.html();
		return noscriptStr;
	}
