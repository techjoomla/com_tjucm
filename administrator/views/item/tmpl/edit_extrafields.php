<?php
/**
 * @version    SVN: <svn_id>
 * @package    Your_extension_name
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

// Add javascript for fields
$document = JFactory::getDocument();
$document->addScript('/component/com_jgive/assets/javascript/fieldlayout.js');

// Code to get TJ-fileds field form - end
$filterFields = array();
$this->filterFieldSet = array();

foreach ($this->form_extra as $tjFieldForm)
{
	if (!empty($tjFieldForm))
	{
		$fieldsArray = array();

		foreach ($tjFieldForm->getFieldsets() as $fieldsets => $fieldset)
		{
			foreach ($tjFieldForm->getFieldset($fieldset->name) as $field)
			{
				$fieldsArray[] = $field;
			}
		}

		if (array_key_exists($fieldset->name, $this->filterFieldSet))
		{
			$this->filterFieldSet[$fieldset->name] = array_merge($fieldsArray, $this->filterFieldSet[$fieldset->name]);
		}
		else
		{
			$this->filterFieldSet[$fieldset->name] = $fieldsArray;
		}
	}
}
// Sort fields according to there field sets - end
?>
<div class="accordion">
	<?php if (!empty($this->filterFieldSet)): ?>
		<!-- Iterate through the normal form fieldsets and display each one. -->
		<?php foreach ($this->filterFieldSet as $fieldSetsName => $fieldset):?>
			<!-- Fields go here -->
				<div class="accordion-section">
					<div class="accordion-section-title" href="#accordion-<?php echo str_replace(' ', '', $fieldSetsName);?>">
						<h4><?php print_r(ucfirst($fieldSetsName));?></h4>
					</div>
					<!-- Iterate through the fields and display them. -->
					<div id="accordion-<?php echo str_replace(' ', '', $fieldSetsName);?>" class="accordion-section-content">
					<?php foreach($fieldset as $field):?>
						<!-- If the field is hidden, only use the input. -->
						<?php if ($field->hidden): ?>
							<?php echo $field->input;?>
						<?php else: ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
						<?php endif; ?>
							<div class="clearfix">&nbsp;</div>
					<?php endforeach;?>
					</div>
				</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
