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
     * The default view for the display method.
     *
     * @var string
     */
    protected $default_view = 'csvuploads';

    /**
     * display task
     *
     * @return void
     */
    function display($cachable = false, $urlparams = false)
    {
        // Set default view if not set
        JFactory::getApplication()->input->set('view', JFactory::getApplication()->input->get('view', 'sendinvites'));

        $session = JFactory::getSession();
        $registry = $session->get('registry');

        // call parent behavior
        parent::display($cachable, $urlparams);

        // Add style
        CSVUploadsHelper::addStyle();
    }
}
