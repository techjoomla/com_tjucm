/*
 * @package	TJ-UCM
 * 
 * @author	 Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license	GNU General Public License version 2, or later
 */
var tjUcm = {
	types:{
		export: function () {

			if (document.adminForm.boxchecked.value == 0)
			{
				alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));

				return false;
			}

			var cid = jQuery("input[name='cid[]']:checked");
			var url = Joomla.getOptions('system.paths').base + "/index.php?option=com_tjucm&task=types.export&tmpl=component&"+Joomla.getOptions('csrf.token')+"=1";

			jQuery.each(cid, function(index, ele) {
				url += '&cid[]='+ele.value;
			});

			window.location.href = url;
		}
	},
	admin:{
		openTjUcmSqueezeBox: function (link, modalWidth, modalHeight){
			var width = jQuery(window).width();
			var height = jQuery(window).height();

			var wwidth = width-(width*((100-modalWidth)/100));
			var hheight = height-(height*((100-modalHeight)/100));
			parent.SqueezeBox.open(link, { handler: 'iframe', size: {x: wwidth, y: hheight},classWindow: 'tjucm-modal'});
		}
	}
}
