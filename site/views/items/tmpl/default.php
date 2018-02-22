<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canCreate = $user->authorise('core.type.createitem', 'com_tjucm.type.' . $this->ucmTypeId);
$canEdit = $user->authorise('core.type.edititem', 'com_tjucm.type' . $this->ucmTypeId);
$canChange = $user->authorise('core.type.edititemstate', 'com_tjucm.type.' . $this->ucmTypeId);
$canEditOwn = $user->authorise('core.type.editownitem', 'com_tjucm.type.' . $this->ucmTypeId);

$appendUrl = "";

if (!empty($this->created_by))
{
	$appendUrl .= "&created_by=" . $this->created_by;
}

if (!empty($this->client))
{
	$appendUrl .= "&client=" . $this->client;
}

$canDelete  = $user->authorise('core.type.deleteitem', 'com_tjucm.type.' . $this->ucmTypeId);
?>
<form action="<?php echo JRoute::_('index.php?option=com_tjucm&view=items' . $appendUrl); ?>" method="post" name="adminForm" id="adminForm">
	<table class="table table-striped" id="itemList">
		<?php
		if (!empty($this->showList))
		{
			if (!empty($this->items))
			{?>
		<thead>
			<tr>
				<?php
				if (isset($this->items[0]->state))
				{
					?>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<?php
				}
				?>
				<th class=''>
					<?php echo JHtml::_('grid.sort',  'COM_TJUCM_ITEMS_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
				<?php

				if (!empty($this->listcolumn))
				{
					foreach ($this->listcolumn as $col_name)
					{
						?>
						<th class='left'>
							<?php echo htmlspecialchars($col_name, ENT_COMPAT, 'UTF-8'); ?>
						</th>
						<?php
					}
				}

				if ($canEdit || $canDelete)
				{
					?>
					<th class="center">
						<?php echo JText::_('COM_TJUCM_ITEMS_ACTIONS'); ?>
					</th>
				<?php
				}
				?>
			</tr>
		</thead>
		<?php
			}
		}?>
		<?php
		if (!empty($this->items))
		{
		?>
		<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<?php
		}
		?>
		<tbody>
		<?php
		if (!empty($this->showList))
		{
			if (!empty($this->items))
			{
				foreach ($this->items as $i => $item)
				{
					 $link = JRoute::_('index.php?option=com_tjucm&view=item&id=' . $item->id . "&client=" . $this->client, false);
					 $canEdit = $user->authorise('core.type.edititem', 'com_tjucm.type' . $this->ucmTypeId);

					if (!$canEdit && $canEditOwn)
					{
						$canEdit = JFactory::getUser()->id == $item->createdBy_id;
					}

					?>
					<tr class="row<?php echo $i % 2; ?>">
						<?php
						if (isset($this->items[0]->state))
						{
							$class = ($canChange) ? 'active' : 'disabled'; ?>
							<td class="center">
								<a class="<?php echo $class; ?>" href="<?php echo ($canChange) ? 'index.php?option=com_tjucm&task=item.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2) . $appendUrl : '#'; ?>">
								<?php
								if ($item->state == 1)
								{
									?><i class="icon-publish"></i><?php
								}
								else
								{
									?><i class="icon-unpublish"></i><?php
								}
								?>
								</a>
							</td>
						<?php
						}
						?>
						<td>
							<?php
							if (isset($item->checked_out) && $item->checked_out)
							{
								echo JHtml::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'items.', $canCheckin);
							}
							?>
							<a href="<?php echo JRoute::_('index.php?option=com_tjucm&view=item&id='.(int) $item->id) . "&client=" . $this->client; ?>">
								<?php echo $this->escape($item->id); ?>
							</a>
						</td>
						<?php
							if (!empty ($item->field_values))
							{
								foreach ($item->field_values as $field_values)
								{
									?>
									<td>
										<a href="<?php echo $link;?>"><?php echo $field_values; ?></a>
									</td><?php
								}
							}

							if ($canEdit || $canDelete)
							{
								?>
								<td class="center">
								<?php
								if ($canEdit)
								{
									 ?>
									<a target="_blank" href="<?php echo 'index.php?option=com_tjucm&task=itemform.edit&id=' . $item->id . $appendUrl; ?>" class="btn btn-mini" type="button"><i class="icon-apply" aria-hidden="true"></i></a>
								<?php
								}

								if ($canDelete)
								{
									?>
									<a href="<?php echo 'index.php?option=com_tjucm&task=itemform.remove' . '&id=' . $item->id . $appendUrl; ?>" class="btn btn-mini delete-button" type="button"><i class="icon-delete" aria-hidden="true"></i></a>
									<?php
								}
								?>
								</td>
							<?php
							}
							?>
					</tr>
				<?php
				}
			}
			else
			{
				?>
				<div class="alert alert-warning"><?php echo JText::_('COM_TJUCM_NO_DATA_FOUND');?></div>
			<?php
			}
		}
		else
		{
		?>
			<div class="alert alert-warning"><?php echo JText::_('COM_TJUCM_NO_DATA_FOUND');?></div>
		<?php
		}
		?>
		</tbody>
	</table>
	<?php
	if ($this->allowedToAdd)
	{
		?>
		<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_tjucm&task=itemform.edit&id=0' . $appendUrl, false, 2); ?>" class="btn btn-success btn-small">
			<i class="icon-plus"></i><?php echo JText::_('COM_TJUCM_ADD_ITEM'); ?>
		</a>
		<?php
	}
	?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php
if ($canDelete)
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
