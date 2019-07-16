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

/**
 * View to edit
 *
 * @since  1.6
 */
class TjucmViewItem extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

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
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Get form for extra fields.

		$this->client  = JFactory::getApplication()->input->get('client');
		$id  = JFactory::getApplication()->input->get('id');
		$model = $this->getModel('item');
		$view = explode('.', $this->client);

		// Call to extra fields
		$this->form_extra = $model->getFormExtra(
		array(
			"category" => $this->item->category_id,
			"clientComponent" => 'com_tjucm',
			"client" => $this->client,
			"view" => $view[1],
			"layout" => 'default',
			"content_id" => $id)
			);

		$this->form_extra = array_filter($this->form_extra);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user  = JFactory::getUser();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = TjucmHelper::getActions();

		JToolBarHelper::title(JText::_('COM_TJUCM_TITLE_ITEM'), 'item.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			JToolBarHelper::apply('item.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('item.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('item.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolBarHelper::custom('item.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		// Button for version control
		if ($this->state->params->get('save_history', 1) && $user->authorise('core.edit'))
		{
			JToolbarHelper::versions('com_tjucm.item', $this->item->id);
		}

		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('item.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
