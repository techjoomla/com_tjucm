<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjucm
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('jquery.token');

/*
* Script to show alert box if form changes are made and user is closing/refreshing/navigating the tab
* without saving the content
*/
HTMLHelper::script('media/com_tjucm/js/vendor/jquery/jquery.are-you-sure.js');

/*
* Script to show alert box if form changes are made and user is closing/refreshing/navigating the tab
* without saving the content on iphone|ipad|ipod|opera
*/
HTMLHelper::script('media/com_tjucm/js/vendor/shim/ays-beforeunload-shim.js');

HTMLHelper::script('administrator/components/com_tjfields/assets/js/tjfields.js');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_tjucm', JPATH_SITE);

$jinput                    = Factory::getApplication();
$editRecordId              = $jinput->input->get("id", '', 'INT');
$tmpl                      = $jinput->input->get('tmpl', '', 'STRING');
$baseUrl                   = $jinput->input->server->get('REQUEST_URI', '', 'STRING');
$calledFrom                = (strpos($baseUrl, 'administrator')) ? 'backend' : 'frontend';
$layout                    = ($calledFrom == 'frontend') ? 'default' : 'edit';
$fieldsets_counter_deafult = 0;
$setnavigation             = false;
$fieldArray                = $this->form_extra;
$params                    = ComponentHelper::getParams('com_dpe');
$reverseListClients        = explode (",", $params->get('coredataReverseUcmTypes'));

if (!empty($tmpl))
{
	$doc = Factory::getDocument();
	$doc->addStyleSheet('templates/shaper_helix3/css/custom.css');
	$doc->addStyleSheet('templates/shaper_helix3/css/bootstrap.min.css');
	$doc->addStyleSheet('templates/shaper_helix3/js/bootstrap.min.js');
	$doc->addStyleSheet('templates/shaper_helix3/js/jquery.sticky.js');
	$doc->addStyleSheet('templates/shaper_helix3/js/main.js');
	$doc->addStyleSheet('templates/shaper_helix3/js/frontend-edit.js');
	$doc->addStyleSheet('media/system/js/frontediting.js');
}

if ($this->item->id)
{
	$itemState = ($this->item->draft && ($this->allow_auto_save || $this->allow_draft_save)) ? 1 : 0;
}
else
{
	$itemState = ($this->allow_auto_save || $this->allow_draft_save) ? 1 : 0;
}

// DPE - Hack - Start - Code check cluster Id of URL with saved cluster_id both are equal in edit mode
if ($this->id && !$this->copyRecId && $this->client == 'com_tjucm.rop')
{
	$clusterId = $jinput->input->getInt("cluster_id", 0);

	if ($clusterId != $this->item->cluster_id)
	{
		$jinput->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
		$jinput->setHeader('status', 403, true);

		return;
	}
}

// DPE - Hack - End

Factory::getDocument()->addScriptDeclaration('
	jQuery(function() {
		jQuery("#item-form").areYouSure();
	});

	jQuery(window).load(function ()
	{
		jQuery("#item-form .nav-tabs li a").first().click();
	});

	Joomla.submitbutton = function (task)
	{
		if (task == "itemform.cancel")
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
		else
		{
			if (task != "itemform.cancel" && document.formvalidator.isValid(document.id("item-form")))
			{
				Joomla.submitform(task, document.getElementById("item-form"));
			}
			else
			{
				alert("' . $this->escape(Text::_("JGLOBAL_VALIDATION_FORM_FAILED")) . '");
			}
		}
	};
');
?>
<?php if (!empty($tmpl)): ?>
	<h3 class="rop-popup-header ml-20 mr-20"><?php echo Text::_('COM_TJUCM_CORE_DATA_ADD_NEW_RECORD_TITLE'); ?></h3>
<?php endif;?>
<form action="<?php echo Route::_('index.php');?>" method="post" enctype="multipart/form-data" name="adminForm" id="item-form" class="form-validate  ucm-form-styling vendors-form-view rop-form-design <?php echo !empty($tmpl)? 'vendors-popup-form' : '' ?>
">
	<?php
	if ($this->allow_auto_save == '1' && $this->item->draft == 1 && $this->item->state == 0)
	{
	?>
	<div class="alert alert-info" style="display:none;" id="tjucm-auto-save-disabled-msg">
		<a class="close" data-dismiss="alert">×</a>
		<div class="msg">
			<div>
			<?php echo Text::_("COM_TJUCM_MSG_FOR_AUTOSAVE_FEATURE_DISABLED"); ?>
			</div>
		</div>
	</div>
	<?php
	}
	?>
	<div>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
		<fieldset>
			<input type="hidden" name="jform[id]" id="recordId" value="<?php echo $editRecordId; ?>" />
			<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
			<input type="hidden" name="jform[state]" value="<?php echo $this->item->state;?>" />
			<input type="hidden" id="ucm-client" name="jform[client]" value="<?php echo $this->client;?>" />
			<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
			<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
			<input type="hidden" name="itemState" id="itemState" value="<?php echo $itemState; ?>"/>
			<?php echo $this->form->renderField('created_by'); ?>
			<?php echo $this->form->renderField('created_date'); ?>
			<?php echo $this->form->renderField('modified_by'); ?>
			<?php echo $this->form->renderField('modified_date'); ?>
		</fieldset>
			</div>
		</div>
	<?php
	if ($this->form_extra)
	{
		?>
		<div class="form-horizontal ">
			<?php
			// Code to display the form
			echo $this->loadTemplate('extrafieldsaccordian');

			if ($this->item->cluster_id)
			{
				// Show reverse relation in case of masterlist

				// We may need config in future we wants to show reverse relation on other pages
				if ($this->item->cluster_id && in_array($this->client, array('com_tjucm.ropvendors')))
				{
					// Get Cluster name
					Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_cluster/tables');
					$clusterTable = Table::getInstance('clusters', 'ClusterTable');
					$clusterTable->load(array('id' => $this->item->cluster_id));

					$UcmTypes = $reverseListClients;
					$tjUcmFrontendHelper = new TjucmHelpersTjucm;

					foreach ($UcmTypes as $UcmType)
					{
						if ($UcmType == 'com_tjucm.role')
						{
							continue;
						}

						// Get UCM Type name
						Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');
						$ucmTable = Table::getInstance('type', 'TjucmTable');
						$ucmTable->load(array('unique_identifier' => $UcmType));

						// Get Process Addtion form Itemid
						$TypeItemId = $tjUcmFrontendHelper->getItemId('index.php?option=com_tjucm&view=items&client='.$UcmType);

						if ($UcmType == 'com_tjucm.software')
						{
							$reverseSoftListLink = Route::_('index.php?option=com_tjucm&view=items&tmpl=component&Itemid=' . $TypeItemId.'&cluster_id=' . $this->item->cluster_id.'&softwareManagedby='.$this->id);
						}
						elseif ($UcmType == 'com_tjucm.ithardware')
						{
							$reverseHardListLink = Route::_('index.php?option=com_tjucm&view=items&tmpl=component&Itemid=' . $TypeItemId.'&cluster_id=' . $this->item->cluster_id);
						}

						$relatedSoftUrl = addslashes(Route::_($reverseSoftListLink . '&reverselist=1'));
						$relatedHardUrl = addslashes(Route::_($reverseHardListLink . '&reverselist=1'));
					}
				}
			}

			?>
			<?php if (in_array($this->client, array('com_tjucm.ropvendors')) && empty($tmpl) && $this->id) : ?>
			<div id="reverseListCover" class="buttons mt-10">
				<div class="mb-20">
					<a href="javascript:void(0);" class="btn btn-primary" onclick="tjucm.itmes.openMasterlistPopups('<?php echo addslashes(Route::_($relatedSoftUrl));?>', this)"><?php echo Text::_("COM_TJUCM_RELATED_SOFT_BTN_TITLE"); ?></a>
				</div>
				<div class="mb-20">
					<a href="javascript:void(0);" class="btn btn-primary" onclick="tjucm.itmes.openMasterlistPopups('<?php echo addslashes(Route::_($relatedHardUrl));?>', this)"><?php echo Text::_("COM_TJUCM_RELATED_HARD_BTN_TITLE"); ?></a>
				</div>
			</div>
			<?php endif;?>
		</div>
		<?php
	}
	?>
	<!-- DPE - Hack - Removed extra messages and added design ( Div's) -->

	<div id="draft_msg" class="alert alert-success" style="display: none;">
		<a class="close" data-dismiss="alert">×</a>
		<?php echo Text::_("COM_TJUCM_MSG_ON_DRAFT_FORM"); ?>
	</div>


	<input type="hidden" name="layout" value="<?php echo $layout ?>"/>
	<input type="hidden" name="task" value="itemform.save"/>
	<input type="hidden" name="cluster_id" id="cluster_id" value="<?php echo $this->item->cluster_id? $this->item->cluster_id : 0; ?>"/>
	<input type="hidden" name="isROP" id="isROPForm" value="0"/>
	<input type="hidden" name="form_status" id="form_status" value=""/>
	<input type="hidden" name="tjucm-autosave" id="tjucm-autosave" value="<?php echo $this->allow_auto_save;?>"/>
	<input type="hidden" name="tjucm-bitrate" id="tjucm-bitrate" value="<?php echo $this->allow_bit_rate;?>"/>
	<input type="hidden" name="tjucm-bitrate_seconds" id="tjucm-bitrate_seconds" value="<?php echo $this->allow_bit_rate_seconds;?>"/>
	<input type="hidden" name="tmplPopUp" id="tmplPopUp" value="<?php echo !empty($tmpl) ? 1:0 ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if (count($fieldArray->getFieldsets()) > 1) : ?>
<?php endif; ?>


<script>



jQuery(document).ready(function()
{
	// Update URL params
	jQuery("#jform_"+jQuery("#clusterFieldUniqueName").val()).change(function()
	{
		/* Update item id in the URL if the data is stored successfully */
		var tjucmUrl = window.location.href.split('#')[0];
		var tjucmUrlSeparator = (tjucmUrl.indexOf("?")===-1)?"?":"&";
		var tjucmNewParam = "cluster_id=" + this.value;
		jQuery("#cluster_id").val(this.value);
		//jQuery("#clusterId").val(this.value);

		if (!tjucmUrl.includes("cluster_id"))
		{
			tjucmUrl+=tjucmUrlSeparator+tjucmNewParam;
			history.pushState(null, null, tjucmUrl);
		}
		else
		{
			var href = new URL(window.location.href);
			href.searchParams.set('cluster_id', this.value);
			history.pushState(null, null, href);
		}
	});

});
<?php if (count($fieldArray->getFieldsets()) > 1) : ?>

function heightbeforescroll(){
	var windowheight = jQuery(window).height();

	var headertop = jQuery("#sp-top-bar").outerHeight();
	var header = jQuery("#sp-header").outerHeight();
	var breadcrumb= jQuery("#sp-page-title").outerHeight();
	var topheight = headertop + header + breadcrumb;
	jQuery("#tjucm_myTabTabs.nav.nav-tabs").css("top", topheight);
	jQuery("#tjucm_myTabTabs.nav.nav-tabs").css("margin-top","30px");
	var licounts= document.getElementById("tjucm_myTabTabs").childElementCount;
	var lielementhight = jQuery("#tjucm_myTabTabs li").outerHeight();
	var lihight= licounts * lielementhight;
	var buttons_height = topheight + lihight;
	jQuery(".buttons").css("top", buttons_height);
	jQuery(".buttons").css("margin-top","30px");
	var footerheight = jQuery("#sp-footer").outerHeight();
	var spbottom =  jQuery("#sp-bottom").outerHeight();
	jQuery(".header-changes #sp-bottom").css("bottom", footerheight);
	var bottomheight = footerheight + spbottom;
	var tabwidth= jQuery(".tab-pane").width();
	jQuery(".form-actions.action-btns").css("bottom", bottomheight);
	jQuery(".form-actions.action-btns").css("width", tabwidth);
}
function heightafterscroll(){
	var windowheight = jQuery(window).height();

	var headertop = jQuery("#sp-top-bar").outerHeight();
	var header = jQuery("#sp-header").outerHeight();
	var breadcrumb= jQuery("#sp-page-title").outerHeight();
	var topheight = breadcrumb- (headertop + header);
	jQuery("#tjucm_myTabTabs.nav.nav-tabs").css("top", header);
	jQuery("#tjucm_myTabTabs.nav.nav-tabs").css("margin-top","30px");
	var licounts= document.getElementById("tjucm_myTabTabs").childElementCount;
	var lielementhight = jQuery("#tjucm_myTabTabs li").outerHeight();
	var lihight= licounts * lielementhight;
	var buttons_height = header + lihight;
	jQuery(".buttons").css("top", buttons_height);
	jQuery(".buttons").css("margin-top","30px");

}

jQuery(document).ready(function(){
	var licount= document.getElementById("tjucm_myTabTabs").childElementCount;
if (window.matchMedia("(max-width: 700px)").matches) {
	var width= document.getElementById("tjucm_myTabTabs").childElementCount;
	var liwidth= (100/width);
	jQuery("ul#tjucm_myTabTabs > li").css("width",liwidth+'%');
	jQuery("ul#tjucm_myTabTabs > li").css("float","left");
  }


  heightbeforescroll();
	var action_btn_form;
	function actionbtn(){
		action_btn_form = jQuery(".form-actions").outerHeight();
		formHeight(action_btn_form);
	}

	function formHeight(action_btn_form){
		var windowheight1 = jQuery(window).height();
		var headertop_for_form1 = jQuery("#sp-top-bar").outerHeight();
		var header_for_form1 = jQuery("#sp-header").outerHeight();
		var breadcrumb_for_form1= jQuery("#sp-page-title").outerHeight();
		var footerheight_for_form1 = jQuery("#sp-footer").outerHeight();
		var spbottom_for_form1 =  jQuery("#sp-bottom").outerHeight();
		//var action_btn_form1 = jQuery(".form-actions").outerHeight();
		var totalheight_form1 = windowheight1 - (headertop_for_form1 + header_for_form1 + breadcrumb_for_form1 + footerheight_for_form1 + spbottom_for_form1 + action_btn_form);
		jQuery(".tab-content .tab-pane").css("height", totalheight_form1-30);
		jQuery(".tab-content").css("top", "0");
	}
	function formHeightafterscroll(action_btn_form){
		var windowheight = jQuery(window).height();
		var headertop_for_form = jQuery("#sp-top-bar").outerHeight();
		var header_for_form = jQuery("#sp-header").outerHeight();
		//var breadcrumb_for_form= jQuery("#sp-page-title").outerHeight();
		var footerheight_for_form = jQuery("#sp-footer").outerHeight();
		var spbottom_for_form =  jQuery("#sp-bottom").outerHeight();
		//var action_btn_form = jQuery(".form-actions").outerHeight();
		var totalheight_form =windowheight-(headertop_for_form + header_for_form + footerheight_for_form + spbottom_for_form + action_btn_form);
		jQuery(".tab-content .tab-pane").css("height", totalheight_form-30);
		jQuery(".tab-content").css("top", header_for_form);
	}


	setTimeout(actionbtn,200);

    jQuery(window).scroll(function () {

		// For form height
		if (jQuery(window).scrollTop()){
			formHeightafterscroll(action_btn_form);
			heightafterscroll();

		}
		else{
			formHeight(action_btn_form);
			heightbeforescroll();

		}
	});
});
<?php endif; ?>


</script>
