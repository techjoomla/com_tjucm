<?php
/**
 * @package	   TJ-UCM
 * @author	   TechJoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/*To load language constant of js file*/
JText::script('COM_TJUCM_DELETE_MESSAGE');

$user = Factory::getUser();
$tjUcmFrontendHelper = new TjucmHelpersTjucm;

if ($this->form_extra)
{
	if (isset($this->title))
	{
		?>
		<div class="page-header">
			<h1 class="page-title">
			<?php echo strtoupper($this->title); ?>
			</h1>
		</div> 
		<?php
	}

	$count = 0;
	$xmlFieldSets = array();

	foreach ($this->formXml as $k => $xmlFieldSet)
	{
		$xmlFieldSets[$count] = $xmlFieldSet;
		$count++;
	}

	// Call the JLayout to render the fields in the details view
	$layout = new FileLayout('detail.fields', JPATH_ROOT . '/components/com_tjucm');
	echo $layout->render(array('xmlFormObject' => $xmlFieldSets, 'formObject' => $this->form_extra, 'itemData' => $this->item));
}
else
{
	?>
	<div class="alert alert-info">
		<?php echo Text::_('COM_TJUCM_NO_DATA_FOUND');?>
	</div>
	<?php
}
?>
<div>&nbsp;</div>
<div>
	<div class="form-group">
		<?php
		if ((TjucmAccess::canEdit($this->ucmTypeId, $this->item->id)) || (TjucmAccess::canEditOwn($this->ucmTypeId, $this->item->id) && Factory::getUser()->id == $this->item->created_by))
		{
			$redirectURL = Route::_('index.php?option=com_tjucm&task=item.edit&id=' . $this->item->id . '&client=' . $this->client, false);
			?>
			<a class="btn btn-default" href="<?php echo $redirectURL; ?>"><?php echo Text::_("COM_TJUCM_EDIT_ITEM"); ?></a>
			<?php
		}

		$deleteOwn = false;

		if (TjucmAccess::canDeleteOwn($this->ucmTypeId, $this->item->id))
		{
			$deleteOwn = (Factory::getUser()->id == $this->item->created_by ? true : false);
		}

		if (TjucmAccess::canDelete($this->ucmTypeId, $this->item->id) || $deleteOwn)
		{
			$redirectURL = Route::_('index.php?option=com_tjucm&task=itemform.remove&id=' . $this->item->id . '&client=' . $this->client . "&" . Session::getFormToken() . '=1', false);
			?>
			<a class="btn btn-default delete-button" href="<?php echo $redirectURL; ?>"><?php echo Text::_("COM_TJUCM_DELETE_ITEM"); ?></a>
			<?php
		}

		$link = 'index.php?option=com_tjucm&view=items&client=' . $this->client;
		$itemId = $tjUcmFrontendHelper->getItemId($link);
		?>
		<a class="btn btn-default" href="<?php echo Route::_($link . '&Itemid=' . $itemId); ?>"><?php echo Text::_("COM_TJUCM_CANCEL_BUTTON"); ?></a>
	</div>
</div>