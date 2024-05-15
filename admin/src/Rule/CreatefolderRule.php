<?php
namespace NPEU\Component\Csvuploads\Administrator\Rule;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Form\Rule\OptionsRule;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;


/**
 * The ExecutionRulesRule Class.
 * Validates execution rules, with input for other fields as context.
 *
 * @since  4.1.0
 */
class CreatefolderRule extends FormRule
{
    /**
     * @var string  RULE_TYPE_FIELD   The field containing the rule type to test against
     * @since  4.1.0
     */
    private const RULE_TYPE_FIELD = "createfolder.rule-type";

    /**
     * @var string CUSTOM_RULE_GROUP  The field group containing custom execution rules
     * @since  4.1.0
     */
    private const CUSTOM_RULE_GROUP = "createfolder.custom";

    /**
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form
     *                                       field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   ?string            $group    The field name group control value. This acts as an array container for the
     *                                       field. For example if the field has `name="foo"` and the group value is set
     *                                       to "bar" then the full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against
     *                                       the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return boolean
     *
     * @since  4.1.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null): bool
    {
        $data = Factory::getApplication()->input->get('jform', array(), 'post', 'array');
        #echo '<pre>'; var_dump($data); echo '</pre>'; exit;

        $path = JPATH_ROOT . '/' . $data['uploadfolder'] . '/' . $data['csvfolder'];
        if (!file_exists($path)) {
            mkdir($path);
        }
        return true;

        /*$fieldName = (string) $element['name'];
        $ruleType  = $input->get(self::RULE_TYPE_FIELD);

        if ($ruleType === $fieldName || ($ruleType === 'custom' && $group === self::CUSTOM_RULE_GROUP)) {
            return $this->validateField($element, $value, $group, $form);
        }

        return true;*/
    }

    /**
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement for the field.
     * @param   mixed              $value    The field value.
     * @param   ?string            $group    The form field group the element belongs to.
     * @param   Form|null          $form     The Form object against which the field is tested/
     *
     * @return boolean  True if field is valid
     *
     * @since  4.1.0
     */
    /*private function validateField(\SimpleXMLElement $element, $value, ?string $group = null, ?Form $form = null): bool
    {
        $elementType = (string) $element['type'];

        // If element is of cron type, we test against options and return
        if ($elementType === 'cron') {
            return (new OptionsRule())->test($element, $value, $group, null, $form);
        }

        // Test for a positive integer value and return
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    }*/
}
