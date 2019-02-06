<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('file');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Extras
 * @subpackage  com_extras
 */
class JFormFieldUpload extends JFormFieldFile
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Upload';


    /**
     * Method to attach a JForm object to the field.
     *
     * @param   object  &$element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed   $value     The form field value to validate.
     * @param   string  $group     The field name group control value. This acts as as an array container for the field.
     *                             For example if the field has name="foo" and the group value is set to "bar" then the
     *                             full field name would end up being "bar[foo]".
     */
    public function setup(SimpleXMLElement $element, $value, $group = NULL)
    {
        $is_new = empty($value);
        // If the field isn't new, remove the required flag:
        if (!$is_new) {
            $element['required'] = 'false';
        }

        return parent::setup($element, $value, $group);
    }

    /**
     * Method to get the field input markup for the file field.
     * Field attributes allow specification of a maximum file size and a string
     * of accepted file extensions.
     *
     * @return  string  The field input markup.
     */
    protected function getInput()
    {
        $return = parent::getInput();

        $doc = JFactory::getDocument();

        // Initialize some field attributes.
        $hiddenclass = $this->element['hiddenclass'] ? ' class="' . (string) $this->element['hiddenclass'] . '"' : '';
        $currentclass = $this->element['currentclass'] ? ' class="' . (string) $this->element['currentclass'] . '"' : '';

        $current_file  = empty($this->value)
                       ? false
                       : htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

        // Don't know of a way of forcing jasny upload to set the value,
        // so switch markup instead:
        if ($current_file) {
            $output = array(
            '<div class="fileupload fileupload-exists" data-provides="fileupload">',
            '   <div class="input-append">',
            '        <div class="uneditable-input">',
            '            <i class="icon-file  fileupload-exists"></i> <span class="fileupload-preview">' . $current_file . '</span>',
            '       </div>',
            '       <span class="btn btn-file"><span class="fileupload-new">Select file</span><span class="fileupload-exists">Change</span>',
            '       <input type="hidden" name="' . $this->name . '" id="' . $this->id . '_hidden" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $hiddenclass . ' />' . $return,
            '       </span><a href="#" class="btn  fileupload-exists" data-dismiss="fileupload">Remove</a>',
            '   </div>',
            '</div>');
        } else {
            $output = array(
            '<div class="fileupload fileupload-new" data-provides="fileupload">',
            '   <div class="input-append">',
            '        <div class="uneditable-input">',
            '            <i class="icon-file  fileupload-exists"></i> <span class="fileupload-preview"></span>',
            '       </div>',
            '       <span class="btn btn-file"><span class="fileupload-new">Select file</span><span class="fileupload-exists">Change</span>',
            '       <input type="hidden" name="' . $this->name . '" id="' . $this->id . '_hidden" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $hiddenclass . ' />' . $return,
            '       </span><a href="#" class="btn  fileupload-exists" data-dismiss="fileupload">Remove</a>',
            '   </div>',
            '</div>');
        }

        $assets = '';
        if (empty($doc->uploadAssetsAdded)) {
            $assets =  "\n" . '<link rel="stylesheet" href="/administrator/components/com_csvuploads/assets/jasny-bootstrap.min.css" type="text/css" />' . "\n";
            $assets .= '<script src="/administrator/components/com_csvuploads/assets/jasny-bootstrap.min.js" type="text/javascript"></script>' . "\n";
            $assets .= '<link rel="stylesheet" href="/administrator/components/com_csvuploads/assets/fix-fileuploads.css" type="text/css" />' . "\n";
            $assets .= '<script src="/administrator/components/com_csvuploads/assets/fix-fileuploads.js" type="text/javascript"></script>' . "\n";
            $doc->uploadAssetsAdded = true;
        }

        $output = implode("\n", $output);

        $return =  $output . $assets;

        return $return;
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     */
    protected function getLabel()
    {
        return parent::getLabel();

        // This field type now uses jasny bootstrap to present a better looking field
        // As such the way an existing file is presented is handled by the input
        // so we don't need a special label any more, but keep for reference
        // in case other inputs do.

        $label = parent::getLabel();

        // Initialize some field attributes.
        $current_class = $this->element['currentclass'] ? ' class="' . (string) $this->element['currentclass'] . '"' : '';

        $current_file  = empty($this->value)
                       ? JText::_($this->element['nofile'])
                       : htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

        return str_replace(
            '</label>',
            '<br /><span' . $current_class . '>(' . $current_file . ')</span></label>',
            $label);
    }
}
