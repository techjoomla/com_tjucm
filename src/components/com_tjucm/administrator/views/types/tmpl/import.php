<?php
/**
 * @package    TJ-UCM
 * 
 * @author     TechJoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<script>
function tjUcmImportTypes(obj)
{
	jQuery(obj).attr("disabled", true);
	jQuery("#type-import-form").submit();
}
</script>
<form action="<?php echo Route::_('index.php?option=com_tjucm&view=types&layout=default'); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="type-import-form" class="form-validate">
	<div class="form-horizontal">
		<div>
			<h1><?php echo Text::_("COM_TJUCM_TYPES_IMPORT");?></h1>
		</div>
		<hr />
		<div class="input-append">
			<input type="file" id="ucm-types-upload" required="true" name="ucm-types-upload" accept="application/json" >
			<button type="submit" form="type-form" id="tjucmImportUcm" onclick="tjUcmImportTypes(this);" class="btn btn-success" value="Submit"><?php echo Text::_("COM_TJUCM_IMPORT");?></button>
		</div>
		<input type="hidden" name="option" value="com_tjucm"/>
		<input type="hidden" name="task" value="types.import"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
