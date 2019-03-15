<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldStaff extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     */
    public $type = 'Staff';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $options = array();
        $db = JFactory::getDBO();
        $q  = 'SELECT u.id, u.name FROM `#__users` u ';
        $q .= 'JOIN `#__user_usergroup_map` ugm ON u.id = ugm.user_id ';
        $q .= 'JOIN `#__usergroups` ug ON ugm.group_id = ug.id ';
        $q .= 'WHERE ug.title = "Staff" ';
        $q .= 'AND u.block = 0 ';
        $q .= 'ORDER BY u.name;';

        $db->setQuery($q);
        if (!$db->execute($q)) {
            JError::raiseError( 500, $db->stderr() );
            return false;
        }

        $staff_members = $db->loadAssocList();

        $i = 0;
        foreach ($staff_members as $staff_member) {
            $options[] = JHtml::_('select.option', $staff_member['id'], $staff_member['name']);
            $i++;
        }
        if ($i > 0) {
            // Merge any additional options in the XML definition.
            $options = array_merge(parent::getOptions(), $options);
        } else {
            $options = parent::getOptions();
            $options[0]->text = JText::_('COM_CSVUPLOADS_CONTACT_DEFAULT_NO_STAFF');
        }
        return $options;
    }
}