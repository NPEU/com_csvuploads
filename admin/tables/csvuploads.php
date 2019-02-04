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
 * CSVUploads Table class
 */
class CSVUploadsTablecsvuploads extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    function __construct(&$db)
    {
        parent::__construct('#__csvuploads', 'id', $db);
    }
    /**
     * Overloaded bind function
     *
     * @param       array           named array
     * @return      null|string     null is operation was satisfactory, otherwise returns an error
     * @see JTable:bind
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params']))
        {
            // Convert the params field to a string.
            $parameter = new JRegistry;
            $parameter->loadArray($array['params']);
            $array['params'] = (string)$parameter;
        }
        return parent::bind($array, $ignore);
    }

    /**
     * Overloaded load function
     *
     * @param       int $pk primary key
     * @param       boolean $reset reset data
     * @return      boolean
     * @see JTable:load
     */
    public function load($pk = null, $reset = true)
    {
        if (parent::load($pk, $reset))
        {
            // Convert the params field to a registry.
            $params = new JRegistry;
            $params->loadString($this->params, 'JSON');

            $this->params = $params;
            return true;
        }
        else
        {
            return false;
        }
    }
}
