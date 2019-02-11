<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

// Import the Joomla modellist library
#jimport('joomla.application.component.modellist');

/**
 * CSVUploads Records List Model
 */
class CSVUploadsModelCSVUploads extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'name',
                'description',
                'contact',
                'id'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Create the base select statement.
        $query->select('a.*')
              ->from($db->quoteName('#__csvuploads') . ' AS a');
              
        // Join over the users for the checked out user.
        $query->select('uch.name AS editor')
              ->join('LEFT', '#__users AS uch ON uch.id = a.checked_out');

        // Join the users for the contact:
		$query->select('uc.name AS contact_name, uc.email AS contact_email')
		      ->join('LEFT', '#__users AS uc ON uc.id = a.contact_user_id');
            
        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search))
        {
            $like = $db->quote('%' . $search . '%');
            $query->where('a.name LIKE ' . $like);
            $query->where('a.description LIKE ' . $like);
        }

        // Filter by published state
        /*$published = $this->getState('filter.created');

        if (is_numeric($published))
        {
            $query->where('a.created = ' . (int) $published);
        }
        elseif ($published === '')
        {
            $query->where('(a.created IN (0, 1))');
        }*/

        // Add the list ordering clause.
        $orderCol   = $this->state->get('list.ordering', 'a.name');
        $orderDirn  = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
