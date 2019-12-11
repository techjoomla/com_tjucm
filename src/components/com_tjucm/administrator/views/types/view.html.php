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

jimport('joomla.application.component.view');
JHTML::_('behavior.modal');

/**
 * View class for a list of Tjucm.
 *
 * @since  1.6
 */
class TjucmViewTypes extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

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
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		TjucmHelper::addSubmenu('types');

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = TjucmHelper::getActions();

		JToolBarHelper::title(JText::_('COM_TJUCM_TITLE_TYPES'), 'list');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/type';

		$toolbar = JToolbar::getInstance('toolbar');
		$toolbar->appendButton(
		'Custom', '<a id="tjHouseKeepingFixDatabasebutton" class="btn btn-default hidden"><span class="icon-refresh"></span>'
		. JText::_('COM_TJUCM_FIX_DATABASE') . '</a>');

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('type.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('type.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('types.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('types.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'types.delete', 'JTOOLBAR_DELETE');
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'types.delete', 'JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			}
			elseif ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::trash('types.trash', 'JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.edit') || $canDo->get('core.create'))
		{
			JToolBarHelper::custom('types.export', 'download', '', 'COM_TJUCM_TYPE_EXPORT', false);

			// Add import button on UCM Types view
			$link = "'" . JUri::root() . "administrator/index.php?option=com_tjucm&view=types&layout=import&tmpl=component" . "'";
			$toolbar->appendButton(
			'Custom', '<a class="modal btn"
			onclick="tjUcm.admin.openTjUcmSqueezeBox(' . $link . ',40, 38)">
			<span class="icon-upload"></span>' . JText::_('COM_TJUCM_TYPE_IMPORT') . '</a>'
			);
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_tjucm');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_tjucm&view=types');

		$this->extra_sidebar = '';
		JHtmlSidebar::addFilter(

			JText::_('JOPTION_SELECT_PUBLISHED'),

			'filter_published',

			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)

		);
	}

	/**
	 * Method to order fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'a.`id`' => JText::_('JGRID_HEADING_ID'),
			'a.`ordering`' => JText::_('JGRID_HEADING_ORDERING'),
			'a.`title`' => JText::_('COM_TJUCM_TYPES_TITLE'),
			'a.`state`' => JText::_('JSTATUS'),
		);
	}
}
