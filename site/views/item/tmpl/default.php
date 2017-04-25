<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjucm
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$canEdit = JFactory::getUser()->authorise('core.type.edititem', 'com_tjucm.type.' . $this->ucmTypeId);

if (!$canEdit && JFactory::getUser()->authorise('core.type.editownitem', 'com_tjucm.type.' . $this->ucmTypeId))
{
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}
?>

<div class="item_fields">

	<table class="table">
		<tr>
			<th><?php echo JText::_('COM_TJUCM_FORM_LBL_ITEM_STATE'); ?></th>
			<td>
			<i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_TJUCM_FORM_LBL_ITEM_TYPE_ID'); ?></th>
			<td><?php echo $this->item->type_id; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_TJUCM_FORM_LBL_ITEM_CREATED_BY'); ?></th>
			<td><?php echo $this->item->created_by_name; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_TJUCM_FORM_LBL_ITEM_CREATED_DATE'); ?></th>
			<td><?php echo $this->item->created_date; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_TJUCM_FORM_LBL_ITEM_MODIFIED_BY'); ?></th>
			<td><?php echo $this->item->modified_by_name; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_TJUCM_FORM_LBL_ITEM_MODIFIED_DATE'); ?></th>
			<td><?php echo $this->item->modified_date; ?></td>
		</tr>

	</table>

</div>

<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn" href="<?php echo JRoute::_('index.php?option=com_tjucm&task=item.edit&id='.$this->item->id); ?>"><?php echo JText::_("COM_TJUCM_EDIT_ITEM"); ?></a>

<?php endif; ?>

<?php if (JFactory::getUser()->authorise('core.type.deleteitem','com_tjucm.type.' . $this->ucmTypeId)) : ?>

	<a class="btn" href="<?php echo JRoute::_('index.php?option=com_tjucm&task=item.remove&id=' . $this->item->id, false, 2); ?>"><?php echo JText::_("COM_TJUCM_DELETE_ITEM"); ?></a>

<?php endif; ?>
