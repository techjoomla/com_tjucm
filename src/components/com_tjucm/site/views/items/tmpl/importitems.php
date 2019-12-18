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

HTMLHelper::_('bootstrap.tooltip');

Factory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function(){
		jQuery("#uploadForm #upload-submit").click(function() {
			if (jQuery("#uploadForm #csv-file-upload").val() == "")
			{
				jQuery("#uploadForm #csv-file-upload").css("border-color", "red");

				return false;
			}
			else
			{
				var tjUcmUploadFileName = jQuery("#uploadForm #csv-file-upload").val();
				var tjUcmUploadFileExtension = tjUcmUploadFileName.substr((tjUcmUploadFileName.lastIndexOf(".") +1));

				if (tjUcmUploadFileExtension === "csv")
				{
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
<form action="<?php echo JUri::root(); ?>index.php?option=com_tjucm&task=item.import&tmpl=component&client=<?php echo $this->client;?>" id="uploadForm" name="uploadForm" method="post" enctype="multipart/form-data">
	<h2>
		<?php echo Text::_("COM_TJUCM_IMPORT_ITEM");?>
	</h2>
	<hr class="hr hr-condensed">
	<div>
		<div class="col-sm-4">
			<label for="csv-file-upload" class="control-label"><strong><?php echo Text::_('COM_TJUCM_ITEMS_UPLOAD_CSV_FILE'); ?></strong></label>
			<input type="file" required name="csv-file-upload" id="csv-file-upload" />
		</div>
		<div class="col-sm-8">
			<div>&nbsp;</div>
			<button class="btn btn-primary" id="upload-submit">
				<i class="icon-upload icon-white"></i>
				<?php echo Text::_('COM_TJUCM_IMPORT_ITEM'); ?>
			</button>
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
