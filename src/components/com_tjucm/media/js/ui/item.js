/* confirmation message before item deletion */
	jQuery(window).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem()
	{
		if (!confirm(Joomla.JText._('COM_TJUCM_DELETE_MESSAGE')))
		{
			return false;
		}
	}
