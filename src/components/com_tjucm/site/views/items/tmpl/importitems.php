<?php
/**
 * @package     TJ-UCM
 * @subpackage  com_tjucm
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('bootstrap.tooltip');

JLoader::import('components.com_tjfields.tables.type', JPATH_ADMINISTRATOR);
$ucmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
$ucmTypeTable->load(array('unique_identifier' => $this->client));

// Get decoded data object
$typeParams = new Registry($ucmTypeTable->params);

Factory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function(){
		jQuery("#uploadForm #upload-submit").click(function() {

			// Check cluster exist in upload/ import form
			if (jQuery("#uploadForm #cluster").hasClass("import-cluster"))
			{
				var clusterId = jQuery("#uploadForm #cluster").val();

				if (jQuery.trim(clusterId) =="" || clusterId == undefined)
				{
					jQuery("#uploadForm #cluster").next(".chzn-container").css("border-color", "red");
					jQuery("#system-message-container").html();
					Joomla.renderMessages({"error":[Joomla.JText._("COM_TJUCM_ITEMS_CLUSTER_ERROR")]});

					return false;
				}
			}

			if (jQuery("#uploadForm #csv-file-upload").val() == "")
			{
				jQuery("#uploadForm #csv-file-upload").css("border-color", "red");
				jQuery("#system-message-container").html();
				Joomla.renderMessages({"error":[Joomla.JText._("COM_TJUCM_ITEMS_UPLOAD_CSV_FILE")]});

				return false;
			}
			else
			{
				var tjUcmUploadFileName = jQuery("#uploadForm #csv-file-upload").val();
				var tjUcmUploadFileExtension = tjUcmUploadFileName.substr((tjUcmUploadFileName.lastIndexOf(".") +1));

				if (tjUcmUploadFileExtension === "csv")
				{
					jQuery("#uploadForm #records-import-msg").show();
					jQuery("#uploadForm #upload-submit").attr("disabled", "disabled");
					jQuery("#uploadForm #csv-file-upload").css("border-color", "");
					document.getElementById("uploadForm").submit();
				}
				else
				{
					jQuery("#uploadForm #csv-file-upload").css("border-color", "red");
					jQuery("#system-message-container").html();
					Joomla.renderMessages({"error":[Joomla.JText._("COM_TJUCM_ITEMS_INVALID_CSV_FILE")]});

					return false;
				}
			}

			return false;
		});
	});
');
?>
<form action="<?php echo Uri::root(); ?>index.php?option=com_tjucm&task=item.import&tmpl=component&client=<?php echo $this->client;?>"
id="uploadForm" name="uploadForm" method="post" enctype="multipart/form-data">
	<h2>
		<?php echo Text::_("COM_TJUCM_IMPORT_ITEM");?>
	</h2>
	<hr class="hr hr-condensed">
	<div>

	<?php
		// Check com_cluster component is installed and configuration set Yes in UCM type form
		if (ComponentHelper::getComponent('com_cluster', true)->enabled && $typeParams->get('import_items'))
		{
			JLoader::import('components.com_tjfields.tables.field', JPATH_ADMINISTRATOR);
			$fieldTable = Table::getInstance('Field', 'TjfieldsTable', array('dbo', $db));
			$fieldTable->load(array('client' => $this->client, 'type' => 'cluster'));

			if ($fieldTable->id)
			{
				FormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_tjfields/models/fields/');
				$cluster           = FormHelper::loadFieldType('cluster', false);
				$this->clusterList = $cluster->getOptionsExternally();
				?>
				<div class="col-sm-3">
					<label for="cluster" class="control-label">
						<strong><?php echo Text::_('COM_TJUCM_ITEMS_CLUSTER'); ?><span class="star"> *</span></strong>

					</label>
					<div class="clear-both">
					<?php
						echo HTMLHelper::_('select.genericlist', $this->clusterList, "cluster", ' id="cluster" class="import-cluster" required size="1"',
						"value", "text");
					?>
					</div>
				</div>
				<?php
			}
		}
		// Load filter fields
	?>
		<div class="col-sm-4">
			<label for="csv-file-upload" class="control-label"><strong><?php echo Text::_('COM_TJUCM_ITEMS_UPLOAD_CSV_FILE'); ?>
			<span class="star"> *</span></strong></label>
			<input type="file" required name="csv-file-upload" id="csv-file-upload" />
		</div>
		<div class="col-sm-5">
			<div>&nbsp;</div>
			<button class="btn btn-primary" id="upload-submit">
				<i class="icon-upload icon-white"></i>
				<?php echo Text::_('COM_TJUCM_IMPORT_ITEM'); ?>
			</button>
		</div>
		<div class="clearfix">&nbsp;</div>
		<div class="col-sm-12">
			<div id="records-import-msg" style="display:none;" class="alert alert-info"><?php echo Text::_("COM_TJUCM_ITEMS_IMPORTING_MSG")?></div>
		</div>
		<div class="col-sm-12">
			<hr class="hr hr-condensed">
			<div class="alert alert-warning" role="alert"><i class="icon-info"></i>
				<?php
					$path = Uri::root() . 'index.php?option=com_tjucm&task=items.getCsvImportFormat&tmpl=component&client=' . $this->client;
					$path .= '&' . Session::getFormToken() . '=1';
					$link = '<a href="' . $path . '">' . Text::_("COM_TJUCM_CLICK_HERE") . '</a>';
					echo Text::sprintf('COM_TJUCM_ITEMS_UPLOAD_CSV_FILE_HELP', $link);
				?>
			</div>
		</div>
	</div>
	<input type="hidden" name="client" value="<?php echo $this->client;?>"/>
	<input type="hidden" name="option" value="com_tjucm"/>
	<input type="hidden" name="task" value="items.importCsv"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
