<?php
namespace NPEU\Component\Csvuploads\Administrator\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\CategoryField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\Database\DatabaseInterface;

defined('_JEXEC') or die;

#JFormHelper::loadFieldClass('list');

/**
 * Form field for a list of brands.
 */
class FilteredcategoriesField extends CategoryField
{
    /**
     * The form field type.
     *
     * @var     string
     */
    public $type = 'FilteredCategories';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        //{"cat_exclude":"uncategorised, biographies","cat_depth":"*"}
        $options = parent::getOptions();
        #$options = [];

        $first = array_shift($options);

        // @TODO autodetect the component for ease of resuse:
        $params      = clone ComponentHelper::getParams('com_csvuploads');
        #echo '<pre>'; var_dump($params); echo '</pre>'; exit;
        $depth       = is_numeric($params->get('cat_depth'))
                     ? (int) $params->get('cat_depth')
                     : false;
        $exclude     = $params->get('cat_exclude', false)
                     ? explode(',', trim(preg_replace('/,\s+/', ',', $params->get('cat_exclude'))))
                     :[];

        $exclude_ids =[];
        $db    = Factory::getDBO();
        $query = 'SELECT id, alias, title
                  FROM ' . $db->quoteName('#__categories') . '
                  WHERE extension = "com_content";';
        $db->setQuery($query);
        $result = $db->loadAssocList();
        #echo '<pre>'; var_dump($exclude); echo '</pre>'; exit;
        foreach ($result as $row) {
            if (in_array($row['alias'], $exclude) || in_array($row['title'], $exclude)) {
                $exclude_ids[] = $row['id'];
            }

        }


        usort($options, function($a, $b) {
            if (strtolower($a->text) == strtolower($b->text)) {
                return 0;
            }
            return (strtolower($a->text) < strtolower($b->text)) ? -1 : 1;
        });
        #echo '<pre>'; var_dump($exclude_ids); echo '</pre>'; #exit;
        #echo '<pre>'; var_dump($options); echo '</pre>'; exit;
        foreach ($options as $i => $option) {
            // Skip the first item to show 'Select' option:
            /*if ($i == 0) {
                continue;
            }*/
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

        array_unshift($options, $first);


        #echo '<pre>'; var_dump($options); echo '</pre>'; exit;
        return $options;
    }

}