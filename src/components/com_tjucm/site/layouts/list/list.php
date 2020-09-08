<?php
/**
 * @package	    TJ-UCM
 *
 * @author	     TechJoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license  	  GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

if (!key_exists('itemsData', $displayData))
{
	return;
}

$fieldsData = $displayData['fieldsData'];
$app = Factory::getApplication();
$user = Factory::getUser();

// Layout for field types
$fieldLayout = array();
$fieldLayout['File'] = $fieldLayout['Image'] = "file";
$fieldLayout['Checkbox'] = "checkbox";
$fieldLayout['Color'] = "color";
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

// Load itemForm model
JLoader::import('components.com_tjucm.models.itemform', JPATH_SITE);
$tjucmItemFormModel = JModelLegacy::getInstance('ItemForm', 'TjucmModel');

// Get JLayout data
$item          = $displayData['itemsData'];
$created_by    = $displayData['created_by'];
$client        = $displayData['client'];
$xmlFormObject = $displayData['xmlFormObject'];
$formObject    = $displayData['formObject'];
$ucmTypeId     = $displayData['ucmTypeId'];
$allowDraftSave = $displayData['ucmTypeParams']->allow_draft_save;
$i = isset($displayData['key']) ? $displayData['key'] : '';

$appendUrl = '';
$csrf = "&" . Session::getFormToken() . '=1';

$canEditOwn   = TjucmAccess::canEditOwn($ucmTypeId, $item->id);
$canDeleteOwn = TjucmAccess::canDeleteOwn($ucmTypeId, $item->id);
$canEditState = TjucmAccess::canEditState($ucmTypeId, $item->id);
$canEdit      = TjucmAccess::canEdit($ucmTypeId, $item->id);
$canDelete    = TjucmAccess::canDelete($ucmTypeId, $item->id);

$canCopyItem        = $user->authorise('core.type.copyitem', 'com_tjucm.type.' . $ucmTypeId);

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

$link = Route::_('index.php?option=com_tjucm&view=item&id=' . $item->id . "&client=" . $client . '&Itemid=' . $itemId, false);

$editown = false;

if ($canEditOwn)
{
	$editown = (Factory::getUser()->id == $item->created_by ? true : false);
}

$deleteOwn = false;

if ($canDeleteOwn)
{
	$deleteOwn = (Factory::getUser()->id == $item->created_by ? true : false);
}

?>
<div class="tjucm-wrapper">
<tr class="row<?php echo $item->id?>">
	<?php if ($canCopyItem) { ?>
	<!-- TODO- copy and copy to other feature is not fully stable hence relate buttons are hidden-->
	<td class="center">
		<?php echo JHtml::_('grid.id', $i, $item->id); ?>
	</td>
	<?php } ?>
	<?php
	if (isset($item->state))
	{
		$class = ($canEditState) ? 'active' : 'disabled'; ?>
		<td class="center">
			<a class="<?php echo $class; ?>"
				href="<?php echo ($canEditState) ? 'index.php?option=com_tjucm&task=item.publish&id=' .
				$item->id . '&state=' . (($item->state + 1) % 2) . $appendUrl . $csrf : '#'; ?>">
			<?php
			if ($item->state == 1)
			{
				?><span class="icon-checkmark-circle" title="<?php echo Text::_('COM_TJUCM_UNPUBLISH_ITEM');?>"></span><?php
			}
			else
			{
				?><span class="icon-cancel-circle" title="<?php echo Text::_('COM_TJUCM_PUBLISH_ITEM');?>"></span><?php
			}
			?>
			</a>
		</td>
	<?php
	}
	?>
	<td>
		<a href="<?php echo Route::_(
		'index.php?option=com_tjucm&view=item&id=' .
		(int) $item->id . "&client=" . $client . '&Itemid=' . $itemId, false
		); ?>">
			<?php echo $this->escape($item->id); ?>
		</a>
	</td>
	<?php
	if ($allowDraftSave)
	{
		?>
		<td><?php echo ($item->draft) ? Text::_('COM_TJUCM_DATA_STATUS_DRAFT') : Text::_('COM_TJUCM_DATA_STATUS_SAVE'); ?></td>
	<?php
	}

	if (!empty($item))
	{
		foreach ($item as $key => $fieldValue)
		{
			if (array_key_exists($key, $displayData['listcolumn']))
			{
				$tjFieldsFieldTable = $fieldsData[$key];

				$canView = false;

				if ($user->authorise('core.field.viewfieldvalue', 'com_tjfields.group.' . $tjFieldsFieldTable->group_id))
				{
					$canView = $user->authorise('core.field.viewfieldvalue', 'com_tjfields.field.' . $tjFieldsFieldTable->id);
				}

				$fieldXml = $formObject->getFieldXml($tjFieldsFieldTable->name);
				?>
				<td style="word-break: break-word;"  width="<?php echo (85 - $displayData['statusColumnWidth']) / count($displayData['listcolumn']) . '%';?>">
					<?php
						if ($canView || ($item->created_by == $user->id))
						{
							$field = $formObject->getField($tjFieldsFieldTable->name);
							$field->setValue($fieldValue);

							if ($field->type == 'Ucmsubform' && $fieldValue)
							{
								$ucmSubFormData = json_decode($tjucmItemFormModel->getUcmSubFormFieldDataJson($item->id, $field));
								$field->setValue($ucmSubFormData);
								?>
								<div>
									<div class="col-xs-4"><?php echo $field->label; ?>:</div>
									<div class="col-xs-8">
										<?php
										$count = 0;
										$ucmSubFormXmlFieldSets = array();

										// Call to extra fields
										JLoader::import('components.com_tjucm.models.item', JPATH_SITE);
										$tjucmItemModel = JModelLegacy::getInstance('Item', 'TjucmModel');

										// Get Subform field data
										$fieldData = $TjfieldsHelper->getFieldData($field->getAttribute('name'));

										$ucmSubFormFieldParams = json_decode($fieldData->params);
										$ucmSubFormFormSource = explode('/', $ucmSubFormFieldParams->formsource);
										$ucmSubFormClient = $ucmSubFormFormSource[1] . '.' . str_replace('form_extra.xml', '', $ucmSubFormFormSource[4]);
										$view = explode('.', $ucmSubFormClient);
										$ucmSubFormData = (array) $ucmSubFormData;

										if (!empty($ucmSubFormData))
										{
											$count = 0;

											foreach ($ucmSubFormData as $subFormData)
											{
												$count++;
												$contentIdFieldname = str_replace('.', '_', $ucmSubFormClient) . '_contentid';

												$ucmSubformFormObject = $tjucmItemModel->getFormExtra(
													array(
														"clientComponent" => 'com_tjucm',
														"client" => $ucmSubFormClient,
														"view" => $view[1],
														"layout" => 'default',
														"content_id" => $subFormData->$contentIdFieldname)
														);

												$ucmSubFormFormXml = simplexml_load_file($field->formsource);

												$ucmSubFormCount = 0;

												foreach ($ucmSubFormFormXml as $ucmSubFormXmlFieldSet)
												{
													$ucmSubFormXmlFieldSets[$ucmSubFormCount] = $ucmSubFormXmlFieldSet;
													$ucmSubFormCount++;
												}

												$ucmSubFormRecordData = $tjucmItemModel->getData($subFormData->$contentIdFieldname);

												// Call the JLayout recursively to render fields of ucmsubform
												$layout = new JLayoutFile('fields', JPATH_ROOT . '/components/com_tjucm/layouts/detail');
												echo $layout->render(array('xmlFormObject' => $ucmSubFormXmlFieldSets, 'formObject' => $ucmSubformFormObject, 'itemData' => $ucmSubFormRecordData, 'isSubForm' => 1));

												if (count($ucmSubFormData) > $count)
												{
													echo "<hr>";
												}
											}
										}
										?>
									</div>
								</div>
								<?php
							}
							else
							{
								$layoutToUse = (
									array_key_exists(
										ucfirst($tjFieldsFieldTable->type), $fieldLayout
									)
								) ? $fieldLayout[ucfirst($tjFieldsFieldTable->type)] : 'field';
								$layout = new JLayoutFile($layoutToUse, JPATH_ROOT . '/components/com_tjfields/layouts/fields');
								$output = $layout->render(array('fieldXml' => $fieldXml, 'field' => $field));
								echo $output;
							}
						}
					?>
				</td>
				<?php
			}
		}
	}
	?>
	<td class="center">
		<a href="<?php echo $link; ?>" type="button" title="<?php echo Text::_('COM_TJUCM_VIEW_RECORD');?>"><i class="icon-eye-open"></i></a>
	<?php
	if ($canEdit || $editown)
	{
		?>
		<a href="<?php echo 'index.php?option=com_tjucm&task=itemform.edit&id=' . $item->id . $appendUrl; ?>" type="button" title="<?php echo Text::_('COM_TJUCM_EDIT_ITEM');?>"> | <i class="icon-apply" aria-hidden="true"></i></a>
		<?php
	}

	if ($canDelete || $deleteOwn)
	{
		?>
		<a href="<?php echo 'index.php?option=com_tjucm&task=itemform.remove' . '&id=' . $item->id . $appendUrl . $csrf; ?>"
			class="delete-button" type="button"
			title="<?php echo Text::_('COM_TJUCM_DELETE_ITEM');?>"> |
				<i class="icon-delete" aria-hidden="true"></i>
		</a>
		<?php
	}
	?>
	</td>
</tr>
</div>
