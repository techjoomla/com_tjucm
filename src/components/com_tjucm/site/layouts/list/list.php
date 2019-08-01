<?php
/**
 * @package	TJ-UCM
 * 
 * @author	 TechJoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

if (!key_exists('itemsData', $displayData))
{
	return;
}

$app = JFactory::getApplication();
$user = JFactory::getUser();

// Layout for field types
$fieldLayout = array();
$fieldLayout['File'] = $fieldLayout['Image'] = "file";
$fieldLayout['Checkbox'] = "checkbox";
$fieldLayout['Radio'] = $fieldLayout['List'] = "list";
$fieldLayout['Itemcategory'] = "itemcategory";
$fieldLayout['Video'] = $fieldLayout['Audio'] = $fieldLayout['Url'] = "link";
$fieldLayout['Calendar'] = "calendar";

// Load the tj-fields helper
JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);
$TjfieldsHelper = new TjfieldsHelper;

// Get JLayout data
$item = $displayData['itemsData'];
$created_by = $displayData['created_by'];
$client = $displayData['client'];
$xmlFieldSet = $displayData['xmlFormObject'];
$ucmTypeId = $displayData['ucmTypeId'];

// Get Field table
$fieldTableData = new stdClass;
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');
$fieldTableData->tjFieldFieldTable = JTable::getInstance('field', 'TjfieldsTable');
$appendUrl = '';
$csrf = "&" . JSession::getFormToken() . '=1';

$canEditOwn 		= $user->authorise('core.type.editownitem', 'com_tjucm.type.' . $ucmTypeId);
$canDeleteOwn       = $user->authorise('core.type.deleteownitem', 'com_tjucm.type.' . $ucmTypeId);
$canChange          = $user->authorise('core.type.edititemstate', 'com_tjucm.type.' . $ucmTypeId);
$canEdit 			= $user->authorise('core.type.edititem', 'com_tjucm.type.' . $ucmTypeId);
$canDelete          = $user->authorise('core.type.deleteitem', 'com_tjucm.type.' . $ucmTypeId);

if (!empty($created_by))
{
	$appendUrl .= "&created_by=" . $created_by;
}

if (!empty($client))
{
	$appendUrl .= "&client=" . $client;
}

$link = 'index.php?option=com_tjucm&view=items' . $appendUrl;
$tjUcmFrontendHelper = new TjucmHelpersTjucm;
$itemId = $tjUcmFrontendHelper->getItemId($link);

	$link = JRoute::_('index.php?option=com_tjucm&view=item&id=' . $item->id . "&client=" . $client . '&Itemid=' . $itemId, false);
	
	$editown = false;
	
	if ($canEditOwn)
	{
		$editown = (JFactory::getUser()->id == $item->created_by ? true : false);
	}

	$deleteOwn = false;
	if ($canDeleteOwn)
	{
		$deleteOwn = (JFactory::getUser()->id == $item->created_by ? true : false);
	}
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<?php
		if (isset($item->state))
		{
			$class = ($canChange) ? 'active' : 'disabled'; ?>
			<td class="center">
				<a class="<?php echo $class; ?>" href="<?php echo ($canChange) ? 'index.php?option=com_tjucm&task=item.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2) . $appendUrl . $csrf : '#'; ?>">
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
			<a href="<?php echo JRoute::_('index.php?option=com_tjucm&view=item&id=' . (int) $item->id . "&client=" . $client . '&Itemid=' . $itemId, false); ?>">
				<?php echo $this->escape($item->id); ?>
			</a>
		</td>
		<?php
		if (!empty($item->field_values))
		{
			$fieldCount = 0;
			foreach ($item->field_values as $key => $field_values)
			{
				$fieldTableData->tjFieldFieldTable->load(array('id' => $key));
				$type = $fieldTableData->tjFieldFieldTable->type;
	
				// Get xml for the field
				$xmlField = $xmlFieldSet->fieldset->field[$fieldCount];
				$fieldCount++;

				if($xmlField['type'][0] == 'ucmsubform')
				{
					$xmlField = $xmlFieldSet->fieldset->field[$fieldCount];
					$fieldCount++;
				}
				?>
				<td>
					<?php
						$layoutToUse = "";
						$layoutToUse = (array_key_exists($type, $fieldLayout)) ? $fieldLayout[$field->type] : 'field'; 
						$field = new stdClass;
						$field->value = $field_values;

						$layout = new JLayoutFile($layoutToUse, JPATH_ROOT . '/components/com_tjfields/layouts/fields');
						$output = $layout->render(array('fieldXml' => $xmlField, 'field' => $field));
						echo $output;
					?>

				</td><?php
			}
		}
		if ($canEdit || $canDelete || $editown || $deleteOwn)
		{
			?>
			<td class="center">
				<a target="_blank" href="<?php echo $link; ?>" class="btn btn-mini" type="button"><i class="icon-eye-open"></i></a>
			<?php
			if ($canEdit || $editown)
			{
				?>
				<a target="_blank" href="<?php echo 'index.php?option=com_tjucm&task=itemform.edit&id=' . $item->id . $appendUrl; ?>" class="btn btn-mini" type="button"><i class="icon-apply" aria-hidden="true"></i></a>
				<?php
			}
			if ($canDelete || $deleteOwn)
			{
				?>
				<a href="<?php echo 'index.php?option=com_tjucm&task=itemform.remove' . '&id=' . $item->id . $appendUrl . $csrf; ?>" class="btn btn-mini delete-button" type="button"><i class="icon-delete" aria-hidden="true"></i></a>
				<?php
			}
			?>
			</td>
		<?php
		}
		?>
	</tr>

	
