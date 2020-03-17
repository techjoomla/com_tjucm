<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

$tmpListColumn = $this->listcolumn;
reset($tmpListColumn);
$firstListColumn = key($tmpListColumn);
?>
<div id="filter-progress-bar">
	<div class="pull-left">
		<input type="text" name="filter_search" id="filter_search"
			title="<?php echo empty($firstListColumn) ? JText::_('JSEARCH_FILTER') :
			JText::sprintf('COM_TJUCM_ITEMS_SEARCH_TITLE', $this->listcolumn[$firstListColumn]); ?>"
			value="<?php echo $this->escape($this->state->get($this->client . '.filter.search')); ?>"
			placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"/>
	</div>
	<div class="btn-group pull-right hidden-xs">
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
	<div class="pull-left">
		<button class="btn btn-default" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
		<button class="btn btn-default qtc-hasTooltip" id="clear-search-button"
		onclick="document.getElementById('filter_search').value='';this.form.submit();"
		type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><span class="icon-remove"></span></button>
		</div>
		<div class="btn-group pull-right hidden-xs">
		<?php
			echo JHtml::_(
				'select.genericlist', $this->draft, "draft", 'class="input-medium"
				size="1" onchange="this.form.submit();"', "value", "text", $this->state->get('filter.draft')
			);
		?>
	</div>
	<?php
	$db = JFactory::getDbo();

	// Check if com_cluster component is installed
	if (ComponentHelper::getComponent('com_cluster', true)->enabled)
	{
		JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
		$fieldTable = JTable::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
		$fieldTable->load(array('client' => $this->client, 'type' => 'cluster'));

		if ($fieldTable->id)
		{
			JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);
			JLoader::import("/components/com_cluster/includes/cluster", JPATH_ADMINISTRATOR);
			$clustersModel = ClusterFactory::model('Clusters', array('ignore_request' => true));
			$clusters = $clustersModel->getItems();
			$usersClusters = array();

			$clusterObj = new stdclass;
			$clusterObj->text = JText::_("COM_TJFIELDS_OWNERSHIP_CLUSTER");
			$clusterObj->value = "";

			$usersClusters[] = $clusterObj;

			if (!empty($clusters))
			{
				foreach ($clusters as $clusterList)
				{
					if (RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.viewitem', $clusterList->id) || RBACL::check(JFactory::getUser()->id, 'com_cluster', 'core.viewallitem'))
					{
						if (!empty($clusterList->id))
						{
							$clusterObj = new stdclass;
							$clusterObj->text = $clusterList->name;
							$clusterObj->value = $clusterList->id;

							$usersClusters[] = $clusterObj;
						}
					}
				}
			}
			?>
			<div class="btn-group pull-right hidden-xs">
				<?php
					echo JHtml::_(
						'select.genericlist', $usersClusters, "cluster", 'class="input-medium"
						size="1" onchange="this.form.submit();"', "value", "text",
						$this->state->get($this->client . '.filter.cluster_id', '', 'INT')
					);
				?>
			</div>
			<?php
		}
	}
	// Load filter fields
	JLoader::import('components.com_tjfields.models.options', JPATH_ADMINISTRATOR);
	JLoader::import('components.com_tjfields.models.fields', JPATH_ADMINISTRATOR);
	$fieldsModel = JModelLegacy::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
	$fieldsModel->setState('filter.client', $this->client);
	$fieldsModel->setState('filter.filterable', 1);
	$fields = $fieldsModel->getItems();

	foreach ($fields as $field)
	{
		$tjFieldsOptionsModel = JModelLegacy::getInstance('Options', 'TjfieldsModel', array('ignore_request' => true));
		$tjFieldsOptionsModel->setState('filter.field_id', $field->id);
		$options = $tjFieldsOptionsModel->getItems();

		if (!empty($options))
		{
			$defaultOption = new stdclass;
			$defaultOption->value = "";
			$defaultOption->options = JText::_("JSELECT") . ' ' . ucfirst($field->label);

			$options = array_merge(array($defaultOption), $options);
			?>
			<div class="btn-group pull-right hidden-xs">
				<?php
					echo JHtml::_(
						'select.genericlist', $options, $field->name, 'class="input-medium"
						size="1" onchange="this.form.submit();"', "value", "options",
						$this->state->get('filter.field.' . $field->name)
					);
				?>
			</div>
			<?php
		}
	}
	?>
</div>
<br><br>
