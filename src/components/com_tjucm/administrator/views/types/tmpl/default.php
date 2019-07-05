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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_tjucm');
$saveOrder = $listOrder == 'a.`ordering`';

$document = JFactory::getDocument();
$document->addScript(JUri::root() . 'administrator/components/com_tjucm/assets/js/tjucm.js');
$document->addScript(JUri::root(true) . '/libraries/techjoomla/assets/js/houseKeeping.js');
$document->addScriptDeclaration("var tjHouseKeepingView='types';");

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjucm&task=types.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'typeList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'types.export')
		{
			tjUcm.types.export();
		}
		else
		{
			Joomla.submitform(task);
		}
	}

	Joomla.orderTable = function () {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	};

	jQuery(document).ready(function () {
		jQuery('#clear-search-button').on('click', function () {
			jQuery('#filter_search').val('');
			jQuery('#adminForm').submit();
		});
	});

	window.toggleField = function (id, task, field) {

		var f = document.adminForm,
			i = 0, cbx,
			cb = f[ id ];

		if (!cb) return false;

		while (true) {
			cbx = f[ 'cb' + i ];

			if (!cbx) break;

			cbx.checked = false;
			i++;
		}

		var inputField   = document.createElement('input');
		inputField.type  = 'hidden';
		inputField.name  = 'field';
		inputField.value = field;
		f.appendChild(inputField);

		cb.checked = true;
		f.boxchecked.value = 1;
		window.submitform(task);

		return false;
	};

</script>
<?php // Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_tjucm&view=types'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2"><?php echo $this->sidebar; ?></div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>
		<div class="tjBs3">
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<label for="filter_search" class="element-invisible">
						<?php echo JText::_('JSEARCH_FILTER'); ?>
					</label>
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>"/>
				</div>
				<div class="btn-group pull-left">
					<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
						<i class="icon-search"></i>
					</button>
					<button class="btn hasTooltip" id="clear-search-button" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>">
						<i class="icon-remove"></i>
					</button>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="limit" class="element-invisible">
						<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="directionTable" class="element-invisible">
						<?php echo JText::_('JFIELD_ORDERING_DESC'); ?>
					</label>
					<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
						<option value="asc" <?php echo $listDirn == 'asc' ? 'selected="selected"' : ''; ?>>
							<?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
						</option>
						<option value="desc" <?php echo $listDirn == 'desc' ? 'selected="selected"' : ''; ?>>
							<?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?>
						</option>
					</select>
				</div>
				<div class="btn-group pull-right">
					<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
					<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
						<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
					</select>
				</div>
			</div>
			<div class="clearfix"></div>
			<table class="table table-striped" id="typeList">
				<?php
				if (!empty($this->items))
				{
				?>
				<thead>
					<tr>
						<?php if (isset($this->items[0]->ordering)): ?>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.`ordering`', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
						</th>
						<?php endif; ?>

						<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>

						<?php if (isset($this->items[0]->state)): ?>
							<th width="1%" class="nowrap center">
								<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.`state`', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>

						<th class='left'>
							<?php echo JHtml::_('grid.sort',  'COM_TJUCM_TYPES_ID', 'a.`id`', $listDirn, $listOrder); ?>
						</th>

						<th class='left'>
							<?php echo JHtml::_('grid.sort',  'COM_TJUCM_TYPES_TITLE', 'a.`title`', $listDirn, $listOrder); ?>

						<th class='left'>
							<?php echo JHtml::_('grid.sort',  'COM_TJUCM_TYPES_ALIAS', 'a.`alias`', $listDirn, $listOrder); ?>
						</th>

						<th class='center'>
							<?php echo JText::_('COM_TJUCM_TYPES_MANAGER'); ?>
						</th>
					</tr>
				</thead>
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
						if (!empty($this->items))
						{
							foreach ($this->items as $i => $item)
							{
							$category_url    = JRoute::_('index.php?option=com_categories&extension=' . $item->unique_identifier);
							$field_group_url = JRoute::_('index.php?option=com_tjfields&view=groups&client=' . $item->unique_identifier);
							$fields_url      = JRoute::_('index.php?option=com_tjfields&view=fields&client=' . $item->unique_identifier . '&extension=' . $item->unique_identifier);
							$type_addnew_data_url   = JRoute::_('index.php?option=com_tjucm&view=item&layout=edit&client=' . $item->unique_identifier);
							$type_data_url   = JRoute::_('index.php?option=com_tjucm&view=items&client=' . $item->unique_identifier);

							$ordering   = ($listOrder == 'a.ordering');
							$canCreate  = $user->authorise('core.create', 'com_tjucm');
							$canEdit    = $user->authorise('core.edit', 'com_tjucm');
							$canCheckin = $user->authorise('core.manage', 'com_tjucm');
							$canChange  = $user->authorise('core.edit.state', 'com_tjucm');
							?>
							<tr class="row<?php echo $i % 2; ?>">

							<?php if (isset($this->items[0]->ordering)) : ?>
								<td class="order nowrap center hidden-phone">
									<?php if ($canChange) :
										$disableClassName = '';
										$disabledLabel    = '';

										if (!$saveOrder) :
											$disabledLabel    = JText::_('JORDERINGDISABLED');
											$disableClassName = 'inactive tip-top';
										endif; ?>
										<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
											<i class="icon-menu"></i>
										</span>
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
									<?php else : ?>
										<span class="sortable-handler inactive">
											<i class="icon-menu"></i>
										</span>
									<?php endif; ?>
								</td>
							<?php endif; ?>

							<td class="hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>

							<?php if (isset($this->items[0]->state)): ?>
								<td class="center">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'types.', $canChange, 'cb'); ?>
								</td>
							<?php endif; ?>

							<td>
								<?php echo $item->id; ?>
							</td>

							<td>
								<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'types.', $canCheckin); ?>
								<?php endif; ?>

								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_tjucm&task=type.edit&id='.(int) $item->id); ?>">
										<?php echo $this->escape($item->title); ?>
									</a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</td>

							<td>
								<?php echo $this->escape($item->alias); ?>
							</td>

							<td class='center'>
								<a href="<?php echo $category_url; ?>"><?php echo JText::_('COM_TJUCM_TYPES_CATEGORY_URL');?></a>
								&nbsp;|&nbsp;<a href="<?php echo $field_group_url; ?>"><?php echo JText::_('COM_TJUCM_TYPES_FIELD_GROUP_URL');?></a>
								&nbsp;|&nbsp;<a href="<?php echo $fields_url; ?>"><?php echo JText::_('COM_TJUCM_TYPES_FIELDS_URL');?></a>
								<!-- &nbsp;|&nbsp;<a href="<?php echo $type_addnew_data_url; ?>"><?php echo JText::_('COM_TJUCM_TYPES_ADD_NEW_DATA_URL');?></a>
								&nbsp;|&nbsp;<a href="<?php echo $type_data_url; ?>"><?php echo JText::_('COM_TJUCM_TYPES_DATA_URL');?></a> -->
							</td>
						</tr>
						<?php
							}
						}
						else
						{
							?>
							<div class="alert alert-warning"><?php echo JText::_("COM_TJUCM_NO_DATA_FOUND");?></div>
							<?php
						}
						?>
				</tbody>
			</table>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
