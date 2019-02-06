<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

require (JPATH_LIBRARIES . '/legacy/form/field/category.php');

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 */
class JFormFieldFilteredCategories extends JFormFieldCategory
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'FilteredCategories';

    /**
     * Method to get the field options for category
     * Use the extension attribute in a form to specify the.specific extension for
     * which categories should be displayed.
     * Use the show_root attribute to specify whether to show the global category root in the list.
     *
     * @return  array    The field option objects.
     */
    protected function getOptions()
    {
        //{"cat_exclude":"uncategorised, biographies","cat_depth":"*"}
        $options = parent::getOptions();

        // @TODO autodetect the component for ease of resuse:
        $params      = JComponentHelper::getParams('com_csvuploads');
        $depth       = is_numeric($params->get('cat_depth'))
                     ? (int) $params->get('cat_depth')
                     : false;
        $exclude     = $params->get('cat_exclude', false)
                     ? explode(',', str_replace(' ', '', $params->get('cat_exclude')))
                     : array();

        $exclude_ids = array();
        $db    = JFactory::getDBO();
        $query = 'SELECT id, alias, title
                  FROM ' . $db->quoteName('#__categories') . '
                  WHERE extension = "com_content";';
        $db->setQuery($query);
        $result = $db->loadAssocList();
        foreach ($result as $row) {
            if (in_array($row['alias'], $exclude) || in_array($row['title'], $exclude)) {
                $exclude_ids[] = $row['id'];
            }

        }

        foreach ($options as $i => $option) {
            // Skip the first item to show 'Select' option:
            if ($i == 0) {
                continue;
            }
            // Exclude specified categories:
            if (in_array($option->value, $exclude_ids)) {
                unset($options[$i]);
            }
            // Exclude categories below $depth:
            if ($depth) {
                $n = substr_count($option->text, '- ');
                if ($n >= $depth) {
                    unset($options[$i]);
                }
            }
        }
        return $options;
    }
}