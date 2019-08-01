<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * CSVUploads CSVUploads View
 */
class CSVUploadsViewCSVUploads extends JViewLegacy
{
    protected $items;

    protected $pagination;

    protected $state;

    /**
     * Display the CSVUploads view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    function display($tpl = null)
    {
        $this->state         = $this->get('State');
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        CSVUploadsHelper::addSubmenu('csvuploads');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolBar()
    {
        //$canDo = CSVUploadsHelper::getActions();
        $canDo = JHelperContent::getActions('com_csvuploads');
        $user  = JFactory::getUser();

        $title = JText::_('COM_CSVUPLOADS_MANAGER_RECORDS');

        if ($this->pagination->total) {
            $title .= "<span style='font-size: 0.5em; vertical-align: middle;'> (" . $this->pagination->total . ")</span>";
        }

        // Note 'question-circle' is an icon/classname. Change to suit in all views.
        JToolBarHelper::title($title, 'upload');
        /*
        JToolBarHelper::addNew('csvupload.add');
        if (!empty($this->items)) {
            JToolBarHelper::editList('csvupload.edit');
            JToolBarHelper::deleteList('', 'csvuploads.delete');
        }
        */
        if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_csvuploads', 'core.create')) > 0) {
            JToolbarHelper::addNew('csvupload.add');
        }

        if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
        {
            JToolbarHelper::editList('csvupload.edit');
        }

        if ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::publish('csvuploads.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('csvuploads.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }


        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
        {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'csvuploads.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::trash('csvuploads.trash');
        }

        if ($user->authorise('core.admin', 'com_csvuploads') || $user->authorise('core.options', 'com_csvuploads'))
        {
            JToolbarHelper::preferences('com_csvuploads');
        }
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_CSVUPLOADS_ADMINISTRATION'));
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array(
            'a.name'            => JText::_('COM_CSVUPLOADS_HEADING_NAME'),
            'a.contact_user_id' => JText::_('COM_CSVUPLOADS_HEADING_CONTACT'),
            'a.description'     => JText::_('COM_CSVUPLOADS_HEADING_DESCRIPTION'),
            'a.state'           => JText::_('COM_CSVUPLOADS_HEADING_PUBLISHED'),
            'a.id'              => JText::_('JGRID_HEADING_ID')
        );
    }
}
