<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Csvuploads\Administrator\Model;

defined('_JEXEC') or die;


use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Csvuploads List Model
 */
class CsvuploadsModel extends ListModel
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
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
                'description', 'a.description',
                'params', 'a.params',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'c.name', 'contact_name',
                'c.username', 'contact_username',
                'c.email', 'contact_email',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'modified', 'a.modified',
                'modified_by', 'a.modified_by',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'access', 'a.access'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @note    Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = 'a.name', $direction = 'ASC')
    {
        // Load the filter state.
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.published', $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string'));

        // Load the parameters.
        $params = ComponentHelper::getParams('com_csvuploads');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');

        return parent::getStoreId($id);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        // Initialize variables.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.name, a.created_by, a.description, a.checked_out, a.checked_out_time, a.created_by, a.state'
            )
        );
        $query->from($db->quoteName('#__csvuploads', 'a'));

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
            ->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the users for the contact user.
        $query->select($db->quoteName('c.name', 'contact_name'))
            ->select($db->quoteName('c.username', 'contact_username'))
            ->select($db->quoteName('c.email', 'contact_email'))
            ->join('LEFT', $db->quoteName('#__users', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.created_by'));

        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search))
        {
            $like = $db->quote('%' . $search . '%');
            $query->where('a.name LIKE ' . $like);
            $query->where('a.description LIKE ' . $like);
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published))
        {
            $query->where($db->quoteName('a.state') . ' = ' . (int) $published);
        }
        elseif ($published === '')
        {
            $query->where('(' . $db->quoteName('a.state') . ' IN (0, 1))');
        }

        // Add the list ordering clause.
        $orderCol   = $this->state->get('list.ordering', 'a.name');
        $orderDirn  = $this->state->get('list.direction', 'ASC');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
