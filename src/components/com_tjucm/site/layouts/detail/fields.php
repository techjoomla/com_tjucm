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

if (!key_exists('formObject', $displayData) || !key_exists('xmlFormObject', $displayData))
{
	return;
}

$app = JFactory::getApplication();
$user = JFactory::getUser();

// Layout for field types
$fieldLayout = array();
$fieldLayout['File'] = $fieldLayout['Image'] = "file";
$fieldLayout['Checkbox'] = "checkbox";
$fieldLayout['multi_select'] = $fieldLayout['single_select'] = $fieldLayout['Radio'] = $fieldLayout['List'] = $fieldLayout['tjlist'] = "list";
$fieldLayout['Itemcategory'] = "itemcategory";
$fieldLayout['Video'] = $fieldLayout['Audio'] = $fieldLayout['Url'] = "link";
$fieldLayout['Calendar'] = "calendar";
$fieldLayout['Cluster'] = "cluster";
$fieldLayout['Related'] = $fieldLayout['SQL'] = "sql";
$fieldLayout['Subform'] = "subform";
$fieldLayout['Ownership'] = "ownership";

// Load the tj-fields helper
JLoader::import('components.com_tjfields.helpers.tjfields', JPATH_SITE);
$TjfieldsHelper = new TjfieldsHelper;

// Get JLayout data
$xmlFormObject = $displayData['xmlFormObject'];
$formObject = $displayData['formObject'];
$itemData = $displayData['itemData'];
$isSubForm = isset($displayData['isSubForm']) ? $displayData['isSubForm'] : '';

// Define the classes for subform and normal form rendering
$controlGroupDivClass = ($isSubForm) ? 'col-xs-12' : 'col-xs-12 col-md-6';
$labelDivClass = ($isSubForm) ? 'col-xs-6' : 'col-xs-4';
$controlDivClass = ($isSubForm) ? 'col-xs-6' : 'col-xs-8';

// Get Field table
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');
$tjFieldsFieldTable = JTable::getInstance('field', 'TjfieldsTable');

$fieldSets = $formObject->getFieldsets();
$count = 0;

// Iterate through the normal form fieldsets and display each one
foreach ($fieldSets as $fieldset)
{
	$xmlFieldSet = $xmlFormObject[$count];
	$count++;
	$fieldCount = 0;
	?>
	<div class="row">
		<?php
		foreach ($formObject->getFieldset($fieldset->name) as $field)
		{
			// No need to show tooltip/description for field on details view
			$field->description = '';

			// Get the field data by field name to check the field type
			$tjFieldsFieldTable->load(array('name' => $field->__get("fieldname")));
			$canView = false;

			if ($user->authorise('core.field.viewfieldvalue', 'com_tjfields.group.' . $tjFieldsFieldTable->group_id))
			{
				$canView = $user->authorise('core.field.viewfieldvalue', 'com_tjfields.field.' . $tjFieldsFieldTable->id);
			}

			if ($canView || ($itemData->created_by == $user->id))
			{
				// Get xml for the field
				$xmlField = $xmlFieldSet->field[$fieldCount];
				$fieldCount++;

				if ($field->hidden)
				{
					echo $field->input;
					continue;
				}

				if ($field->type == 'Ucmsubform')
				{
					?>
					<div class="col-xs-12 col-md-6">
						<div class="col-xs-4"><?php echo $field->label; ?>:</div>
						<div class="col-xs-8">
							<?php
							$count = 0;
							$ucmSubFormXmlFieldSets = array();

							// Call to extra fields
							JLoader::import('components.com_tjucm.models.item', JPATH_SITE);
							$tjucmItemModel = JModelLegacy::getInstance('Item', 'TjucmModel');

							// Get Subform field data
							$formData = $TjfieldsHelper->getFieldData($field->getAttribute('name'));
							$ucmSubFormFieldValue = json_decode($formObject->getvalue($field->getAttribute('name')));

							$ucmSubFormFieldParams = json_decode($formData->params);
							$ucmSubFormFormSource = explode('/', $ucmSubFormFieldParams->formsource);
							$ucmSubFormClient = $ucmSubFormFormSource[1] . '.' . str_replace('form_extra.xml', '', $ucmSubFormFormSource[4]);
							$view = explode('.', $ucmSubFormClient);

							if (!empty($ucmSubFormFieldValue))
							{
								foreach ($ucmSubFormFieldValue as $ucmSubFormData)
								{
									$contentIdFieldname = str_replace('.', '_', $ucmSubFormClient) . '_contentid';

									$ucmSubformFormObject = $tjucmItemModel->getFormExtra(
										array(
											"clientComponent" => 'com_tjucm',
											"client" => $ucmSubFormClient,
											"view" => $view[1],
											"layout" => 'default',
											"content_id" => $ucmSubFormData->$contentIdFieldname, )
											);

									$ucmSubFormFormXml = simplexml_load_file($field->formsource);

									$ucmSubFormCount = 0;

									foreach ($ucmSubFormFormXml as $ucmSubFormXmlFieldSet)
									{
										$ucmSubFormXmlFieldSets[$ucmSubFormCount] = $ucmSubFormXmlFieldSet;
										$ucmSubFormCount++;
									}

									$ucmSubFormRecordData = $tjucmItemModel->getData($ucmSubFormData->$contentIdFieldname);

									// Call the JLayout recursively to render fields of ucmsubform
									$layout = new JLayoutFile('fields', JPATH_ROOT . '/components/com_tjucm/layouts/detail');
									echo $layout->render(array('xmlFormObject' => $ucmSubFormXmlFieldSets, 'formObject' => $ucmSubformFormObject, 'itemData' => $ucmSubFormRecordData, 'isSubForm' => 1));
									echo "<hr>";
								}
							}
							?>
						</div>
					</div>
					<?php
				}
				else
				{
					$layoutToUse = (array_key_exists($field->type, $fieldLayout)) ? $fieldLayout[$field->type] : 'field';
					?>
					<div class="<?php echo $controlGroupDivClass;?>">
						<div class="<?php echo $labelDivClass;?>"><?php echo $field->label; ?>:</div>
						<div class="<?php echo $controlDivClass;?>">
							<?php
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
	<?php
}
