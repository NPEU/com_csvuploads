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
 * CSVUploads Records View
 */
class CSVUploadsViewRecords extends JViewLegacy
{
    /**
     * Display the CSVUploads view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    function display($tpl = null)
    {
        
        // Get application
        $app = JFactory::getApplication();
        $context = "csvuploads.list.admin.record";
        // Get data from the model
        $this->items            = $this->get('Items');
        $this->pagination       = $this->get('Pagination');
        $this->state            = $this->get('State');
        $this->filter_order     = $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'record', 'cmd');
        $this->filter_order_Dir = $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
        $this->filterForm       = $this->get('FilterForm');
        $this->activeFilters    = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the submenu
        #HelloWorldHelper::addSubmenu('helloworlds');

        // Set the toolbar and number of found items
        $this->addToolBar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolBar()
    {
        $title = JText::_('COM_CSVUPLOADS_MANAGER_RECORDS');

        if ($this->pagination->total)
        {
            $title .= "<span style='font-size: 0.5em; vertical-align: middle;'> (" . $this->pagination->total . ")</span>";
        }

        JToolBarHelper::title($title, 'record');
        JToolBarHelper::addNew('record.add');
        if (!empty($this->items)) {
            JToolBarHelper::editList('record.edit');
            JToolBarHelper::deleteList('', 'records.delete');
        }
        JToolBarHelper::preferences('com_csvuploads');
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
}