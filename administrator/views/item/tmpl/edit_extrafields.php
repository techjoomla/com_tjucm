<?php
/**
 * @version    SVN: <svn_id>
 * @package    Your_extension_name
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
echo "komal";
?>

<?php if ($this->form_extra): ?>
	<!-- Iterate through the normal form fieldsets and display each one. -->
	<?php foreach ($this->form_extra->getFieldsets() as $fieldsets => $fieldset): ?>
		<!-- Fields go here -->

		<!-- Iterate through the fields and display them. -->
		<?php foreach($this->form_extra->getFieldset($fieldset->name) as $field): ?>

			<!-- If the field is hidden, only use the input. -->
			<?php if ($field->hidden): ?>
				<?php echo $field->input; ?>
			<?php else: ?>
					<div class="form-group">
						<div class="col-sm-3 control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="col-sm-9">
							<?php echo $field->input; ?>
						</div>
					</div>
			<?php endif; ?>
		<?php endforeach;?>

	<?php endforeach; ?>
<?php endif; ?>
