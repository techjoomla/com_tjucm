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

if (!key_exists('ucmSubFormData', $displayData) || !key_exists('xmlFormObject', $displayData))
{
	return;
}

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
$xmlFormObject = $displayData['xmlFormObject'];
$ucmSubFormData = $displayData['ucmSubFormData'];
$isSubForm = $displayData['isSubForm'];

// Define the classes for subform and normal form rendering
$controlGroupDivClass = ($isSubForm) ? 'col-xs-12' : 'col-xs-12 col-md-6';
$labelDivClass = ($isSubForm) ? 'col-xs-6' : 'col-xs-4';
$controlDivClass = ($isSubForm) ? 'col-xs-6' : 'col-xs-8';

// Get Field table
$fieldTableData = new stdClass;
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');
$fieldTableData->tjFieldFieldTable = JTable::getInstance('field', 'TjfieldsTable');

$count = 0;
$fieldCount = 0;
$xmlFieldSet = $xmlFormObject[$count];

?>
<div class="row">
	<?php
	foreach ($ucmSubFormData as $fieldName => $fieldValue)
	{	
		// Get the field data by field name to check the field type
		$fieldTableData->tjFieldFieldTable->load(array('name' => $fieldName));
		$canView = false;

		if ($user->authorise('core.field.viewfieldvalue', 'com_tjfields.group.' . $fieldTableData->tjFieldFieldTable->group_id))
		{
			$canView = $user->authorise('core.field.viewfieldvalue', 'com_tjfields.field.' . $fieldTableData->tjFieldFieldTable->id);
		}

		if ($canView || ($itemData->created_by == $user->id))
		{
			// Get xml for the field
			$xmlField = $xmlFieldSet->field[$fieldCount];
			$fieldCount++;

			if ($fieldTableData->tjFieldFieldTable->type == 'hidden')
			{
				continue;
			}
			else
			{
				$layoutToUse = (array_key_exists($fieldTableData->tjFieldFieldTable->type, $fieldLayout)) ? $fieldLayout[$field->type] : 'field';
				?>
				<div class="<?php echo $controlGroupDivClass;?>">
					<div class="<?php echo $labelDivClass;?>"><?php echo $fieldTableData->tjFieldFieldTable->label; ?>:</div>
					<div class="<?php echo $controlDivClass;?>">
						<?php
						$field = new stdClass;
						$field->value = $fieldValue;
						$layout = new JLayoutFile($layoutToUse, JPATH_ROOT . '/components/com_tjfields/layouts/fields');
						$output = $layout->render(array('fieldXml' => $xmlField, 'field' => $field));
						echo $output;
						?>
					</div>
				</div>
				<?php
			}
		}
	}
?>
</div>
