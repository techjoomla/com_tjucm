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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Dispatcher as EventDispatcher;

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  1.6
 */
class TjucmViewItem extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();

		if (!Factory::getUser()->id)
		{
			$msg = Text::_('COM_TJUCM_LOGIN_MSG');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url = base64_encode($current);
			Factory::getApplication()->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
		}

		// Load tj-fields language file
		$lang = Factory::getLanguage();
		$lang->load('com_tjfields', JPATH_SITE);

		$this->state  = $this->get('State');
		$this->item   = $this->get('Data');
		$model        = $this->getModel("Item");
		$this->model  = $this->getModel("Item");
		$this->params = $app->getParams('com_tjucm');

		// Load tj-fields helper helper
		$path = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $path);
			JLoader::load('TjfieldsHelper');
		}

		$this->tjFieldsHelper = new TjfieldsHelper;
		$this->ucmTypeId = $model->getState('ucmType.id');

		// Check the view access to the article (the model has already computed the values).
		if ($this->item->params->get('access-view') == false)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		/* Get model instance here */
		$model = $this->getModel();
		$this->client  = Factory::getApplication()->input->get('client');

		// If did not get the client from url then get if from menu param
		if (empty($this->client))
		{
			// Get the active item
			$menuItem = $app->getMenu()->getActive();

			// Get the params
			$this->menuparams = $menuItem->params;

			if (!empty($this->menuparams))
			{
				$this->ucm_type   = $this->menuparams->get('ucm_type');

				if (!empty($this->ucm_type))
				{
					JLoader::import('components.com_tjfields.tables.type', JPATH_ADMINISTRATOR);
					$ucmTypeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
					$ucmTypeTable->load(array('alias' => $this->ucm_type));
					$this->client = $ucmTypeTable->unique_identifier;
				}
			}
		}

		$this->id = Factory::getApplication()->input->get('id');
		$view = explode('.', $this->client);

		// Call to extra fields
		$this->form_extra = $model->getFormExtra(
		array(
			"clientComponent" => 'com_tjucm',
			"client" => $this->client,
			"view" => $view[1],
			"layout" => 'edit',
			"content_id" => $this->id)
			);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JLoader::import('components.com_tjucm.tables.type', JPATH_ADMINISTRATOR);
		$typeTable = Table::getInstance('Type', 'TjucmTable', array('dbo', Factory::getDbo()));
		$typeTable->load(array('unique_identifier' => $this->client));
		$typeParams = json_decode($typeTable->params);
		$this->title = $typeTable->title;

		if (isset($typeParams->details_layout) && !empty($typeParams->details_layout))
		{
			$this->setLayout($typeParams->details_layout);
		}

		// Ucm triggger before item display
		PluginHelper::importPlugin('tjucm');
		
		Factory::getApplication()->triggerEvent('tjucmOnBeforeItemDisplay', array(&$this->item, &$this->form_extra));

		$xmlFileName = explode(".", $this->form_extra->getName());
		$this->formXml = simplexml_load_file(JPATH_SITE . "/administrator/components/com_tjucm/models/forms/" . $xmlFileName[1] . ".xml");

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// We need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_TJUCM_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
