<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('jquery.token');

$importItemsPopUpUrl = JUri::root() . '/index.php?option=com_tjucm&view=items&layout=importitems&tmpl=component&client=' . $this->client;
$copyItemPopupUrl = JUri::root() . 'index.php?option=com_tjucm&view=items&layout=copyitems&tmpl=component&client=' . $this->client;
JFactory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function(){
		jQuery("#adminForm #import-items").click(function() {
			SqueezeBox.open("' . $importItemsPopUpUrl . '" ,{handler: "iframe", size: {x: window.innerWidth-250, y: window.innerHeight-150}});
		});
	});
');

$user = JFactory::getUser();
$userId = $user->get('id');
$tjUcmFrontendHelper = new TjucmHelpersTjucm;
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$appendUrl = '';
$csrf = "&" . JSession::getFormToken() . '=1';

if (!empty($this->created_by))
{
	$appendUrl .= "&created_by=" . $this->created_by;
}

if (!empty($this->client))
{
	$appendUrl .= "&client=" . $this->client;
}

$link = 'index.php?option=com_tjucm&view=items' . $appendUrl;
$itemId = $tjUcmFrontendHelper->getItemId($link);
$fieldsData = array();

JFactory::getDocument()->addScriptDeclaration("
	function copySameUcmTypeItem()
	{
		var afterCopyItem = function(error, response){
			jQuery('#item-form #tjucm_loader').hide();
			jQuery('html, body').animate({scrollTop: jQuery('#item-form #tjucm_loader').position().top}, 'slow');
			response = JSON.parse(response);
			
			sessionStorage.setItem('message', response.message);
			if(response.data !== null)
			{
				window.parent.location.reload();
				sessionStorage.setItem('class', 'alert alert-success');
			}
			else
			{
				sessionStorage.setItem('class', 'alert alert-danger');
			}
		}
	
		var copyItemData =  jQuery('#adminForm').serialize();
		
		// Code to copy item to ucm type
		com_tjucm.Services.Items.copyItem(copyItemData, afterCopyItem);
	}
");

$statusColumnWidth = 0;

?>
<script>
	jQuery(document).ready(function(){
		if(sessionStorage.getItem('message'))
		{
			jQuery('#message').html('<div class="'+sessionStorage.getItem('class')+'"><a href="#" class="close" data-dismiss="alert">&times;</a>'+sessionStorage.getItem('message')+'</div>');
		}
		sessionStorage.removeItem("class");
		sessionStorage.removeItem("message");
	});
</script>

<div class="tjucm-wrapper">
<form action="<?php echo JRoute::_($link . '&Itemid=' . $itemId); ?>" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" class="form-validate">
<div id="message" class=""></div>
<?php
	if (isset($this->items))
	{
	?>
		<div class="page-header">
			<h1 class="page-title">
			<?php echo strtoupper($this->title) . " " . JText::_("COM_TJUCM_FORM_LIST"); ?>
			</h1>
		</div> <?php
	}

	echo $this->loadTemplate('filters');
	?>
		<div class="pull-right">
		<?php
		if ($this->allowedToAdd)
		{
			?>
			<a href="<?php echo JRoute::_('index.php?option=com_tjucm&task=itemform.edit' . $appendUrl, false); ?>" class="btn btn-success btn-small">
				<i class="icon-plus"></i> <?php echo JText::_('COM_TJUCM_ADD_ITEM'); ?>
			</a>
			<?php
			if ($this->canImport)
			{
				?>
				<a href="#" id="import-items" class="btn btn-default btn-small">
					<i class="fa fa-upload"></i> <?php echo JText::_('COM_TJUCM_IMPORT_ITEM'); ?>
				</a>
				<?php
			}
			if ($this->canCopyItem)
			{
				if ($this->canCopyToSameUcmType)
				{?>
					<a onclick="if(document.adminForm.boxchecked.value==0){alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));}else{jQuery('#item-form #tjucm_loader').show(); copySameUcmTypeItem()}" class="btn btn-default btn-small">
					<i class="fa fa-clone"></i> <?php echo JText::_('COM_TJUCM_COPY_ITEM'); ?>
					</a><?php
				}
				else
				{
				?>
				<a href="#" onclick="if(document.adminForm.boxchecked.value==0){alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));}else{jQuery( '#collapseModal' ).modal('show'); return true;}" id="copy-items" class="btn btn-default btn-small">
					<i class="fa fa-clone"></i> <?php echo JText::_('COM_TJUCM_COPY_ITEM'); ?>
				</a>
				<?php
				}
				?>
				<?php echo JHtml::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title'  => JText::_('COM_TJUCM_COPY_ITEMS'),
					),
					$this->loadTemplate('copyitems')
				); ?>
				<?php
			}
		}
		?>
	</div>
	<div class="clearfix">&nbsp;</div>
	<div class="clearfix">&nbsp;</div>
	<div class="row">
	<div class="col-xs-12">
	<div class="table-responsive">
		<table class="table table-striped" id="itemList">
			<?php
			if (!empty($this->showList))
			{
				if (!empty($this->items))
				{?>
			<thead>
				<tr>
					<?php if ($this->canCopyItem) { ?>
					<!-- TODO- copy and copy to other feature is not fully stable hence relate buttons are hidden-->
					<th width="1%" class="">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<?php } ?>
					<?php
					if (isset($this->items[0]->state))
					{
						?>
							<th class="center" width="3%">
							<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
							</th>
						<?php
						}
						?>
						<th width="2%">
							<?php echo JHtml::_('grid.sort', 'COM_TJUCM_ITEMS_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
						<?php
						if (!empty($this->ucmTypeParams->allow_draft_save) && $this->ucmTypeParams->allow_draft_save == 1)
						{
							$statusColumnWidth = 2;
						?>
							<th width="2%">
								<?php echo JHtml::_('grid.sort', 'COM_TJUCM_DATA_STATUS', 'a.draft', $listDirn, $listOrder); ?>
							</th>
						<?php
						}

						foreach ($this->listcolumn as $fieldId => $col_name)
						{
							if (isset($fieldsData[$fieldId]))
							{
								$tjFieldsFieldTable = $fieldsData[$fieldId];
							}
							else
							{
								JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjfields/tables');
								$tjFieldsFieldTable = JTable::getInstance('field', 'TjfieldsTable');
								$tjFieldsFieldTable->load($fieldId);
								$fieldsData[$fieldId] = $tjFieldsFieldTable;
							}

							if (in_array($col_name->type, $this->sortableFields))
							{
								?>
								<th style="word-break: break-word;" width="<?php echo (85 - $statusColumnWidth) / count($this->listcolumn) . '%';?>">
									<?php echo JHtml::_('grid.sort', htmlspecialchars($col_name->label, ENT_COMPAT, 'UTF-8'), $fieldId, $listDirn, $listOrder); ?>
								</th>
								<?php
							}
							else
							{
								?>
								<th style="word-break: break-word;" width="<?php echo (85 - $statusColumnWidth) / count($this->listcolumn) . '%';?>">
									<?php echo $col_name->label; ?>
								</th>
								<?php
							}
						}
						?>
						<th class="center" width="10%">
							<?php echo JText::_('COM_TJUCM_ITEMS_ACTIONS'); ?>
						</th>
				</tr>
			</thead>
			<?php
				}
			}?>
		<tbody>
		<?php
		if (!empty($this->showList))
		{
			if (!empty($this->items))
			{
				$xmlFileName = explode(".", $this->client);
				$xmlFilePath = JPATH_SITE . "/administrator/components/com_tjucm/models/forms/" . $xmlFileName[1] . "_extra" . ".xml";
				$formXml = simplexml_load_file($xmlFilePath);

				$view = explode('.', $this->client);
				JLoader::import('components.com_tjucm.models.itemform', JPATH_SITE);
				$itemFormModel    = JModelLegacy::getInstance('ItemForm', 'TjucmModel');
				$formObject = $itemFormModel->getFormExtra(
					array(
						"clientComponent" => 'com_tjucm',
						"client" => $this->client,
						"view" => $view[1],
						"layout" => 'edit')
						);

				foreach ($this->items as $i => $item)
				{
					// Call the JLayout to render the fields in the details view
					$layout = new JLayoutFile('list.list', JPATH_ROOT . '/components/com_tjucm/');
					echo $layout->render(
						array(
							'itemsData' => $item,
							'created_by' => $this->created_by,
							'client' => $this->client,
							'xmlFormObject' => $formXml,
							'ucmTypeId' => $this->ucmTypeId,
							'ucmTypeParams' => $this->ucmTypeParams,
							'fieldsData' => $fieldsData,
							'formObject' => $formObject,
							'statusColumnWidth' => $statusColumnWidth,
							'listcolumn' => $this->listcolumn
						)
					);
				}
			}
			else
			{
				?>
				<div class="alert alert-warning"><?php echo JText::_('COM_TJUCM_NO_DATA_FOUND');?></div>
			<?php
			}
		}
		else
		{
		?>
			<div class="alert alert-warning"><?php echo JText::_('COM_TJUCM_NO_DATA_FOUND');?></div>
		<?php
		}
		?>
		</tbody>
	</table>
</div>
<?php
	if (!empty($this->items))
	{
		echo $this->pagination->getPagesLinks();
	}
?>
</div>
</div>
	<?php
	if ($this->allowedToAdd)
	{
		?>
		<a href="<?php echo JRoute::_('index.php?option=com_tjucm&task=itemform.edit' . $appendUrl, false); ?>"
		class="btn btn-success btn-small">
			<i class="icon-plus"></i>
			<?php echo JText::_('COM_TJUCM_ADD_ITEM'); ?>
		</a>
		<?php
	}
	?>
	<input type="hidden" id="client" name="client" value="<?php echo $this->client ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>
<div id="item-form">
	<div id="tjucm_loader">
		<img src='<?php echo JUri::root();?>media/com_tjucm/gif/loading.gif'>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function () {
	jQuery('.delete-button').click(deleteItem);
});

function deleteItem()
{
	if (!confirm("<?php echo JText::_('COM_TJUCM_DELETE_MESSAGE'); ?>"))
	{
		return false;
	}
}
</script>
