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
 * CSVUploads Component Controller
 */
class CSVUploadsController extends JControllerLegacy
{
    /**
     * Method to display a view.
     *
     * @param   boolean  $cacheable  If true, the view output will be cached
     * @param   array    $urlparams  An array of safe url parameters and their variable types,
     *                               for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JControllerLegacy  This object to support chaining.
     */
    public function display($cacheable = false, $urlparams = false)
    {
        require_once JPATH_COMPONENT . '/helpers/csvuploads.php';

        $view   = $this->input->get('view', 'csvuploads');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');

        // Check for edit form.
        if ($view == 'csvupload' && $layout == 'edit' && !$this->checkEditId('com_csvuploads.edit.csvupload', $id))
        {
            // Somehow the person just went to the form - we don't allow that.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_csvuploads&view=csvuploads', false));

            return false;
        }

        return parent::display();
    }
}
