<?php
/**
 * @package	   TJ-UCM
 * @author	   TechJoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$user = JFactory::getUser();

if ($this->form_extra)
{
	$count = 0;
	$xmlFieldSets = array();

	foreach ($this->formXml as $k => $xmlFieldSet)
	{
		$xmlFieldSets[$count] = $xmlFieldSet;
		$count++;
	}

	// Call the JLayout to render the fields in the details view
	$layout = new JLayoutFile('detail.fields', JPATH_ROOT . '/components/com_tjucm');
	echo $layout->render(array('xmlFormObject' => $xmlFieldSets, 'formObject' => $this->form_extra, 'itemData' => $this->item));
}
else
{
	?>
	<div class="alert alert-info">
		<?php echo JText::_('COM_TJUCM_NO_DATA_FOUND');?>
	</div>
	<?php
}
?>
<div>&nbsp;</div>
<div>
	<div class="form-group">
		<?php
		if (($user->authorise('core.type.edititem', 'com_tjucm.type.' . $this->ucmTypeId)) || ($user->authorise('core.type.editownitem', 'com_tjucm.type.' . $this->ucmTypeId) && JFactory::getUser()->id == $this->item->created_by))
		{
			$redirectURL = JRoute::_('index.php?option=com_tjucm&task=item.edit&id=' . $this->item->id . '&client=' . $this->client, false);
			?>
			<a class="btn btn-default" href="<?php echo $redirectURL; ?>"><?php echo JText::_("COM_TJUCM_EDIT_ITEM"); ?></a>
			<?php
		}

		$deleteOwn = false;

		if ($user->authorise('core.type.deleteownitem', 'com_tjucm.type.' . $this->ucmTypeId))
		{
			$deleteOwn = (JFactory::getUser()->id == $this->item->created_by ? true : false);
		}

		if ($user->authorise('core.type.deleteitem', 'com_tjucm.type.' . $this->ucmTypeId) || $deleteOwn)
		{
			$redirectURL = JRoute::_('index.php?option=com_tjucm&task=itemform.remove&id=' . $this->item->id . '&client=' . $this->client . "&" . JSession::getFormToken() . '=1', false);
			?>
			<a class="btn btn-default delete-button" href="<?php echo $redirectURL; ?>"><?php echo JText::_("COM_TJUCM_DELETE_ITEM"); ?></a>
			<?php
		}
		?>
	</div>
</div>
<?php
if ($deleteOwn)
{
	?>
	<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem()
	{
		if (!confirm("<?php echo JText::_('COM_TJUCM_DELETE_MESSAGE'); ?>"))
		{
			return false;
		}
	}
	</script>
<?php
}