<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Csvuploads\Administrator\Table;

defined('_JEXEC') or die;

#use Joomla\CMS\Tag\TaggableTableInterface;
#use Joomla\CMS\Tag\TaggableTableTrait;
#use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Nested;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;


/**
 * Csvupload Table class.
 *
 * @since  1.0
 */
#class CsvuploadTable extends Nested implements VersionableTableInterface, TaggableTableInterface
class CsvuploadTable extends Table
{
    #use TaggableTableTrait;

    /**
     * Array with alias for "special" columns such as ordering, hits etc etc
     * Note the admin listing uses 'published' in the special un/publish switcher, so the
     * published/state alias is neeed for that at least.
     *
     * @var    array
     * @since  3.4.0
     */
    protected $_columnAlias = [
        'published' => 'state'
    ];

    public function __construct(DatabaseDriver $db) {
        $this->typeAlias = 'com_csvuploads.csvupload';

        parent::__construct('#__csvuploads', 'id', $db);

        // In functions such as generateTitle() Joomla looks for the 'title' field ...
        #$this->setColumnAlias('title', 'greeting');
    }

    public function bind($array, $ignore = '') {
        if (isset($array['params']) && is_array($array['params'])) {
            // Convert the params field to a string.
            $parameter = new Registry;
            $parameter->loadArray($array['params']);
            $array['params'] = (string)$parameter;
        }

        // Bind the rules.
        if (isset($array['rules']) && \is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    public function store($updateNulls = true) {
        // add the 'created by' and 'created' date fields if it's a new record
        // and these fields aren't already set
        $date = date('Y-m-d h:i:s');
        $user_id = Factory::getApplication()->getIdentity()->get('id');
        if (!$this->id) {
            // new record
            if (empty($this->created_by)) {
                $this->created_by = $user_id;
                $this->created    = $date;
            }
        }

        return parent::store();
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return    string
     * @since    2.5
     */
    protected function _getAssetName() {
        $k = $this->_tbl_key;
        return 'com_csvuploads.csvupload.'.(int) $this->$k;
    }
    /**
     * Method to return the name to use for the asset table.
     *
     * @return    string
     * @since    2.5
     */
    protected function _getAssetTitle() {
        return $this->name;
    }

    public function check() {

        // Check for valid name
        if (trim($this->name) == '') {
            $this->setError(JText::_('COM_CSVUPLOADS_ERR_TABLES_TITLE'));
            return false;
        }

        // Check for existing name
        $db = Factory::getDBO();

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__csvuploads'))
            ->where($db->quoteName('name') . ' = ' . $db->quote($this->name));
        $db->setQuery($query);

        $xid = (int) $db->loadResult();

        if ($xid && $xid != (int) $this->id) {
            $this->setError(JText::_('COM_CSVUPLOADS_ERR_TABLES_NAME'));

            return false;
        }

        return true;
    }

    public function delete($pk = null, $children = false) {
        return parent::delete($pk, $children);
    }

    /**
     * typeAlias is the key used to find the content_types record
     * needed for creating the history record
     */
    public function getTypeAlias() {
        return $this->typeAlias;
    }
}
