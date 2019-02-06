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
 * Form Rule class for the Joomla Platform
 */
class JFormRuleCreateFolder extends JFormRule
{
    /**
     * Method to create a project folder
     *
     * @param   SimpleXMLElement  &$element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed             $value     The form field value to validate.
     * @param   string            $group     The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   JRegistry         &$input    An optional JRegistry object with the entire data set to validate against the entire form.
     * @param   object            &$form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @throws  JException on invalid rule.
     */
    public function test(&$element, $value, $group = null, &$input = null, &$form = null)
    {
        $data = JFactory::getApplication()->input->get('jform', array(), 'post', 'array');
        $path = JPATH_ROOT . '/' . $data['uploadfolder'] . '/' . $data['csvfolder'];
        if (!file_exists($path)) {
            mkdir($path);
        }
        return true;
    }
}