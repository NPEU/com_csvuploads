<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

// Import Joomla modelitem library
jimport('joomla.application.component.modeladmin');

/**
 * CSVUploads Record Model
 */
class CSVUploadsModelCSVUpload extends JModelAdmin
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     */
    public function getTable($type = 'csvuploads', $prefix = 'CSVUploadsTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data   An array of input data.
     * @param   string  $key    The name of the key for the primary key.
     *
     * @return  boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Check specific edit permission then general edit permission.
        return JFactory::getUser()->authorise('core.edit', 'com_csvupload.csvupload.' .
            ((int) isset($data[$key]) ? $data[$key] : 0))
            or parent::allowEdit($data, $key);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_csvuploads.csvupload',
            'csvupload',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form))
        {
            return false;
        }
        return $form;
    }

    /**
     * Method to get the script that have to be included on the form
     *
     * @return string   Script files
     */
    public function getScript()
    {
        #return 'administrator/components/com_helloworld/models/forms/helloworld.js';
        return '';
    }

    /**
     * Method to prepare the saved data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     */
    public function save($data)
    {
        $is_new      = empty($data['id']);

        // The following is generally useful for any app, but you'll need to make sure the database
        // schema includes these fields:
        $user        = JFactory::getUser();
        $user_id     = $user->get('id');
        $date_format = 'Y-m-d H:i:s A';

        $prefix      = $is_new ? 'created' : 'modified';

        $data[$prefix]         = date($date_format, time()); // created/modified
        $data[$prefix . '_by'] = $user_id; // created_by/modified_by

        $param_data = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $param_data = array_merge($param_data, $data[$key]);
                unset($data[$key]);
            }
        }
        $data['params'] = $param_data;

        return parent::save($data);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState(
            'com_csvuploads.edit.csvupload.data',
            array()
        );

        if (empty($data))
        {
            $data = $this->getItem();
            if (!empty($data->id)) {
                $data->options = $data->params;
                unset($data->params);
            }
        }

        return $data;
    }

}
