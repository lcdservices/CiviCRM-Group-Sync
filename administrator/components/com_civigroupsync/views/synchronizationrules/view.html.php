<?php
/**
 * @version     2.0.0
 * @package     com_civigroupsync
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Brian Shaughnessy <brian@lcdservices.biz> - www.lcdservices.biz
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of CiviGroupSync.
 */
class CiviGroupSyncViewSynchronizationrules extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors));
		}

		CiviGroupSyncBackendHelper::addSubmenu('synchronizationrules');
        
		$this->addToolbar();
        
        $this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/civigroupsync.php';

		$state	= $this->get('State');
		$canDo	= CiviGroupSyncBackendHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_CIVIGROUPSYNC_TITLE_SYNCHRONIZATIONRULES'), 'generic.png');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/synchronizationrule';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
			    JToolBarHelper::addNew('synchronizationrule.add','JTOOLBAR_NEW');
		    }

		    if ($canDo->get('core.edit') && isset($this->items[0])) {
			    JToolBarHelper::editList('synchronizationrule.edit','JTOOLBAR_EDIT');
		    }

        }

		if ($canDo->get('core.edit.state')) {

            if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    JToolBarHelper::custom('synchronizationrules.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			    JToolBarHelper::custom('synchronizationrules.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            } else if (isset($this->items[0])) {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'synchronizationrules.delete','JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    JToolBarHelper::archiveList('synchronizationrules.archive','JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
            	JToolBarHelper::custom('synchronizationrules.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
		}
        
        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
		    if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			    JToolBarHelper::deleteList('', 'synchronizationrules.delete','JTOOLBAR_EMPTY_TRASH');
			    JToolBarHelper::divider();
		    } else if ($canDo->get('core.edit.state')) {
			    JToolBarHelper::trash('synchronizationrules.trash','JTOOLBAR_TRASH');
			    JToolBarHelper::divider();
		    }
        }

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_civigroupsync');
		}

        //Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_civigroupsync&view=synchronizationrules');

        $this->extra_sidebar = '';
        
		JHtmlSidebar::addFilter(

			JText::_('JOPTION_SELECT_PUBLISHED'),

			'filter_published',

			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)

		);

    //Filter for the field created_by
    $this->extra_sidebar .= '<small><label for="filter_created_by">Created by</label></small>';
    $this->extra_sidebar .= JHtmlList::users('filter_created_by', $this->state->get('filter.created_by'), 1, 'onchange="this.form.submit();"');
	}
    
	protected function getSortFields()
	{
		return array(
		'a.id' => JText::_('JGRID_HEADING_ID'),
		'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
		'a.state' => JText::_('JSTATUS'),
		'a.checked_out' => JText::_('COM_CIVIGROUPSYNC_SYNCHRONIZATIONRULES_CHECKED_OUT'),
		'a.checked_out_time' => JText::_('COM_CIVIGROUPSYNC_SYNCHRONIZATIONRULES_CHECKED_OUT_TIME'),
		'a.jgroup_id' => JText::_('COM_CIVIGROUPSYNC_SYNCHRONIZATIONRULES_JGROUP_ID'),
		'a.cgroup_id' => JText::_('COM_CIVIGROUPSYNC_SYNCHRONIZATIONRULES_CGROUP_ID'),
		'a.created_by' => JText::_('COM_CIVIGROUPSYNC_SYNCHRONIZATIONRULES_CREATED_BY'),
		);
	}

    
}
