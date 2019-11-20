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

$fieldsData = $displayData['fieldsData'];

$app = JFactory::getApplication();
$user = JFactory::getUser();

// Layout for field types
$fieldLayout = array();
$fieldLayout['File'] = $fieldLayout['Image'] = "file";
$fieldLayout['Checkbox'] = "checkbox";
$fieldLayout['Tjlist'] = $fieldLayout['Radio'] = $fieldLayout['List'] = $fieldLayout['Single_select'] = $fieldLayout['Multi_select'] = "list";
$fieldLayout['Itemcategory'] = "itemcategory";
$fieldLayout['Video'] = $fieldLayout['Audio'] = $fieldLayout['Url'] = "link";
$fieldLayout['Calendar'] = "calendar";
$fieldLayout['Cluster'] = "cluster";
$fieldLayout['Related'] = $fieldLayout['Sql'] = "sql";
$fieldLayout['Ownership'] = "ownership";
$fieldLayout['Editor'] = "editor";

// Load the tj-fields helper
JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);
$TjfieldsHelper = new TjfieldsHelper;

// Get JLayout data
$item          = $displayData['itemsData'];
$created_by    = $displayData['created_by'];
$client        = $displayData['client'];
$xmlFormObject = $displayData['xmlFormObject'];
$formObject    = $displayData['formObject'];
$ucmTypeId     = $displayData['ucmTypeId'];

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
<tr class="row<?php echo $item->id?>">
	<?php
	if (isset($item->state))
	{
		$class = ($canChange) ? 'active' : 'disabled'; ?>
		<td class="center">
			<a class="<?php echo $class; ?>" href="<?php echo ($canChange) ? 'index.php?option=com_tjucm&task=item.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2) . $appendUrl . $csrf : '#'; ?>">
			<?php
			if ($item->state == 1)
			{
				?><span class="icon-checkmark-circle" title="<?php echo JText::_('COM_TJUCM_UNPUBLISH_ITEM');?>"></span><?php
			}
			else
			{
				?><span class="icon-cancel-circle" title="<?php echo JText::_('COM_TJUCM_PUBLISH_ITEM');?>"></span><?php
			}
			?>
			</a>
		</td>
	<?php
	}
	?>
	<td>
		<a href="<?php echo JRoute::_('index.php?option=com_tjucm&view=item&id=' . (int) $item->id . "&client=" . $client . '&Itemid=' . $itemId, false); ?>">
			<?php echo $this->escape($item->id); ?>
		</a>
	</td>
	<?php
	if (!empty($item->field_values))
	{
		foreach ($item->field_values as $key => $fieldValue)
		{
			$tjFieldsFieldTable = $fieldsData[$key];

			$canView = false;

			if ($user->authorise('core.field.viewfieldvalue', 'com_tjfields.group.' . $tjFieldsFieldTable->group_id))
			{
				$canView = $user->authorise('core.field.viewfieldvalue', 'com_tjfields.field.' . $tjFieldsFieldTable->id);
			}

			$fieldXml = $formObject->getFieldXml($tjFieldsFieldTable->name);
			?>
			<td style="word-break: break-word;">
				<?php
					if ($canView || ($item->created_by == $user->id))
					{
						$field = $formObject->getField($tjFieldsFieldTable->name);
						$field->setValue($fieldValue);
						$layoutToUse = (array_key_exists(ucfirst($tjFieldsFieldTable->type), $fieldLayout)) ? $fieldLayout[ucfirst($tjFieldsFieldTable->type)] : 'field';
						$layout = new JLayoutFile($layoutToUse, JPATH_ROOT . '/components/com_tjfields/layouts/fields');
						$output = $layout->render(array('fieldXml' => $fieldXml, 'field' => $field));
						echo $output;
					}
				?>
			</td><?php
		}
	}
	if ($canEdit || $canDelete || $editown || $deleteOwn)
	{
		?>
		<td class="center">
			<a target="_blank" href="<?php echo $link; ?>" class="btn btn-mini" type="button" title="<?php echo JText::_('COM_TJUCM_VIEW_RECORD');?>"><i class="icon-eye-open"></i></a>
		<?php
		if ($canEdit || $editown)
		{
			?>
			<a target="_blank" href="<?php echo 'index.php?option=com_tjucm&task=itemform.edit&id=' . $item->id . $appendUrl; ?>" class="btn btn-mini" type="button" title="<?php echo JText::_('COM_TJUCM_EDIT_ITEM');?>"><i class="icon-apply" aria-hidden="true"></i></a>
			<?php
		}
		if ($canDelete || $deleteOwn)
		{
			?>
			<a href="<?php echo 'index.php?option=com_tjucm&task=itemform.remove' . '&id=' . $item->id . $appendUrl . $csrf; ?>" class="btn btn-mini delete-button" type="button" title="<?php echo JText::_('COM_TJUCM_DELETE_ITEM');?>"><i class="icon-delete" aria-hidden="true"></i></a>
			<?php
		}
		?>
		</td>
	<?php
	}
	?>
</tr>
