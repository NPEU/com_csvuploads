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
 * CSVUploads Record View
 */
class CSVUploadsViewCSVUpload extends JViewLegacy
{
    /**
     * View form
     *
     * @var         form
     */
    protected $form = null;

    /**
     * Display the CSVUploads view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get the root folder from the extension params:
        $option       = JFactory::getApplication()->input->get('option');
        $params       = clone JComponentHelper::getParams($option);
        $uploadfolder = $params->get('uploadfolder');
        if (!$uploadfolder || !file_exists(JPATH_ROOT . '/' . $uploadfolder)) {
            // The root folder hasn't been set, set message:
            $app =& JFactory::getApplication();
            $app->redirect('index.php?option=com_csvuploads');
            return;
        }

        // Get the Data
        $this->form   = $this->get('Form');
        $this->item   = $this->get('Item');
        #$this->script = $this->get('Script');
        $this->is_new = (bool) !$this->item->id;

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        $this->canDo = CSVuploadsHelper::getActions($this->item->id, $this->getModel());

        // Set the toolbar
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
        $input = JFactory::getApplication()->input;

        // Hide Joomla Administrator Main menu
        $input->set('hidemainmenu', true);

        $canDo  = $this->canDo;
        $isNew = ($this->item->id == 0);

        if ($isNew) {
            $title = JText::_('COM_CSVUPLOADS_MANAGER_RECORD_NEW');

            // For new records, check the create permission.
            if ($canDo->get('core.create')) {
                JToolBarHelper::apply('csvupload.apply');
                JToolBarHelper::save('csvupload.save');
                JToolBarHelper::save2new('csvupload.save2new');
            }
            JToolBarHelper::cancel('csvupload.cancel');
        } else {
            $title = JText::_('COM_CSVUPLOADS_MANAGER_RECORD_EDIT');

            if ($canDo->get('core.edit')) {
                // We can save the new record
                JToolBarHelper::apply('csvupload.apply');
                JToolBarHelper::save('csvupload.save');

                // We can save this record, but check the create permission to see
                // if we can return to make a new one.
                if ($canDo->get('core.create')) {
                    JToolBarHelper::save2new('csvupload.save2new');
                }
            }
            if ($canDo->get('core.create')) {
                JToolBarHelper::save2copy('csvupload.save2copy');
            }
            JToolBarHelper::cancel('csvupload.cancel', 'JTOOLBAR_CLOSE');
        }



    }
    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $isNew = ($this->item->id < 1);
        $document = JFactory::getDocument();
        $document->setTitle($isNew ? JText::_('COM_CSVUPLOADS_RECORD_CREATING') :
                JText::_('COM_CSVUPLOADS_RECORD_EDITING'));
        #$document->addScript(JURI::root() . $this->script);
        $document->addScript(JURI::root() . "/administrator/components/com_csvuploads"
                                          . "/views/com_csvupload/submitbutton.js");
        $document->addStyleDeclaration('textarea.monospace {font-family: monospace}');
        JText::script('COM_CSVUPLOADS_RECORD_ERROR_UNACCEPTABLE');
    }
}
