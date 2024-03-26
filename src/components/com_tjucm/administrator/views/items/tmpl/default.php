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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'administrator/components/com_tjucm/assets/css/tjucm.css');
$document->addStyleSheet(Uri::root() . 'media/com_tjucm/css/list.css');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_tjucm');
$saveOrder = $listOrder == 'a.`ordering`';

$client  = Factory::getApplication()->input->get('client');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjucm&task=items.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'itemList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
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

			if (!cbx) break;this->items

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

<?php

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<form action="<?php echo Route::_('index.php?option=com_tjucm&view=items'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>

		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible">
					<?php echo Text::_('JSEARCH_FILTER'); ?>
				</label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo Text::_('JSEARCH_FILTER'); ?>"/>
			</div>

			<div class="btn-group pull-left">
				<button class="btn hasTooltip" type="submit" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
					<i class="icon-search"></i>
				</button>

				<button class="btn hasTooltip" id="clear-search-button" type="button" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>">
					<i class="icon-remove"></i>
				</button>
			</div>

			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible">
					<?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>

			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible">
					<?php echo Text::_('JFIELD_ORDERING_DESC'); ?>
				</label>

				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo Text::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php echo $listDirn == 'asc' ? 'selected="selected"' : ''; ?>><?php echo Text::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php echo $listDirn == 'desc' ? 'selected="selected"' : ''; ?>><?php echo Text::_('JGLOBAL_ORDER_DESCENDING'); ?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo Text::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo Text::_('JGLOBAL_SORT_BY'); ?></option>
					<?php echo HTMLHelper::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php
		if (!empty($this->showList))
		{
			if (!empty($this->items))
			{
			?>
				<table class="table table-striped" id="itemList">
					<thead>
						<tr>
							<?php if (isset($this->items[0]->ordering)): ?>
								<th width="1%" class="nowrap center hidden-phone">
									<?php echo HTMLHelper::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.`ordering`', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
								</th>
							<?php endif; ?>

							<th width="1%" class="hidden-phone">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
							</th>

							<?php if (isset($this->items[0]->state)): ?>
								<th width="1%" class="nowrap center">
									<?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.`state`', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>

							<th class='left'>
								<?php echo HTMLHelper::_('grid.sort', 'COM_TJUCM_ITEMS_ID', 'a.`id`', $listDirn, $listOrder); ?>
							</th>
							<?php
							if (!empty($this->listcolumn))
							{
								foreach ($this->listcolumn as $col_name)
								{ ?>
									<th class='left'>
										<?php echo htmlspecialchars($col_name, ENT_COMPAT, 'UTF-8'); ?>
									</th> <?php
								}
							}?>
						</tr>
					</thead>

					<tfoot>
						<tr>
							<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>

					<tbody>
						<?php foreach ($this->items as $i => $item) :
							$link = Route::_('index.php?option=com_tjucm&view=item&layout=edit&id=' . $item->id . '&client=' . $client, false);
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
													$disabledLabel    = Text::_('JORDERINGDISABLED');
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
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>

								<?php if (isset($this->items[0]->state)): ?>
									<td class="center">
										<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'items.', $canChange, 'cb'); ?>
									</td>
								<?php endif; ?>

								<td>
									<a href="<?php echo $link;?>"><?php echo $item->id; ?></a>
								</td>

								<?php
								if (!empty($item->field_values))
								{
									foreach ($item->field_values as $field_values)
									{?>
										<td>
											<a href="<?php echo $link;?>"><?php echo $field_values; ?></a>
										</td><?php
									}
								}
								?>

							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php
			}
			else
			{
			?>
				<div class="alert alert-warrning"><?php echo Text::_("COM_TJUCM_NO_DATA_FOUND");?></div>
			<?php
			}
		}
		else
		{
			?>
			<div class="alert alert-warrning"><?php echo Text::_("COM_TJUCM_NO_FIELDS_TO_SHOW_ON_LIST_VIEW");?></div>
			<?php
		}
		?>
		<input type="hidden" name="jform[client]" value="<?php echo $client;?>" />
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
