<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjucm
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

// Check if any filter field has been filled
$filters       = false;
$filtered      = false;
$search_filter = false;

if (isset($data['view']->filterForm))
{
	$filters = $data['view']->filterForm->getGroup('filter');
}

// Check if there are filters set.
if ($filters !== false)
{
	$filterFields = array_keys($filters);
	$filled       = false;

	foreach ($filterFields as $filterField)
	{
		$filterField = substr($filterField, 7);
		$filter      = $data['view']->getState('filter.' . $filterField);

		if (!empty($filter))
		{
			$filled = $filter;
		}

		if (!empty($filled))
		{
			$filtered = true;
			break;
		}
	}

	$search_filter = $filters['filter_search'];
	unset($filters['filter_search']);
}

$options = $data['options'];

// Set some basic options
$customOptions = array(
	'filtersHidden'       => isset($options['filtersHidden']) ? $options['filtersHidden'] : empty($data['view']->activeFilters) && !$filtered,
	'defaultLimit'        => isset($options['defaultLimit']) ? $options['defaultLimit'] : JFactory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering',
);

$data['options'] = array_unique(array_merge($customOptions, $data['options']));

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
JHtml::_('searchtools.form', $formSelector, $data['options']);
?>

<div class="js-stools clearfix">
	<div class="clearfix">
		<div class="js-stools-container-bar">
			<label for="filter_search" class="element-invisible"
				aria-invalid="false"><?php echo JText::_('COM_TJUCM_SEARCH_FILTER_SUBMIT'); ?></label>

			<div class="btn-wrapper input-append">
				<?php echo $search_filter->input; ?>
				<button type="submit" class="btn hasTooltip" title=""
					data-original-title="<?php echo JText::_('COM_TJUCM_SEARCH_FILTER_SUBMIT'); ?>">
					<i class="icon-search"></i>
				</button>
			</div>
			<?php if ($filters): ?>
				<div class="btn-wrapper hidden-phone">
					<button type="button" class="btn hasTooltip js-stools-btn-filter" title=""
						data-original-title="<?php echo JText::_('COM_TJUCM_SEARCH_TOOLS_DESC'); ?>">
						<?php echo JText::_('COM_TJUCM_SEARCH_TOOLS'); ?> <i class="caret"></i>
					</button>
				</div>
			<?php endif; ?>

			<div class="btn-wrapper">
				<button type="button" class="btn hasTooltip js-stools-btn-clear" title=""
					data-original-title="<?php echo JText::_('COM_TJUCM_SEARCH_FILTER_CLEAR'); ?>"
					onclick="jQuery(this).closest('form').find('input').val('');">
					<?php echo JText::_('COM_TJUCM_SEARCH_FILTER_CLEAR'); ?>
				</button>
			</div>
		</div>
	</div>
	<!-- Filters div -->
	<div class="js-stools-container-filters hidden-phone clearfix" style="">
		<?php // Load the form filters ?>
		<?php if ($filters) : ?>
			<?php foreach ($filters as $fieldName => $field) : ?>
				<?php if ($fieldName != 'filter_search') : ?>
					<div class="js-stools-field-filter">
						<?php echo $field->renderField(); ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>