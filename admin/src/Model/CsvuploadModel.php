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


#use Joomla\CMS\Form\Form;
#use Joomla\CMS\Helper\TagsHelper;
#use Joomla\CMS\Language\Associations;
#use Joomla\CMS\Language\LanguageHelper;
#use Joomla\CMS\UCM\UCMType;
#use Joomla\CMS\Versioning\VersionableModelTrait;
#use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
#use Joomla\Registry\Registry;
#use Joomla\String\StringHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;



/**
 * Csvupload Model
 */
class CsvuploadModel extends AdminModel
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\Table\Table  A \Joomla\CMS\Table\Table object
     */
    /*public function getTable($type = 'Csvuploads', $prefix = 'CsvuploadsTable', $config = array())
    {
        return \Joomla\CMS\Table\Table::getInstance($type, $prefix, $config);
    }*/

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_csvuploads.csvupload',
            'csvupload',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object) $data)) {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState(
            'com_csvuploads.edit.csvupload.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     */
    /*public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the metadata field to an array.
            $registry = new Registry;
            $registry->loadString($item->metadata);
            $item->metadata = $registry->toArray();

            // Convert the images field to an array.
            $registry = new Registry;
            $registry->loadString($item->images);
            $item->images = $registry->toArray();

            if (!empty($item->id)) {
                $item->tags = new JHelperTags;
                $item->tags->getTagIds($item->id, 'com_weblinks.weblink');
                $item->metadata['tags'] = $item->tags;
            }
        }

        return $item;
    }*/



    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param   \Joomla\CMS\Table\Table  $table  A reference to a \Joomla\CMS\Table\Table object.
     *
     * @return  void
     */
    protected function prepareTable($table)
    {
        $date = Factory::getDate();
        $user = Factory::getApplication()->getIdentity();

        $table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);

        $table->modified    = $date->toSql();
        $table->modified_by = $user->id;

        if (empty($table->id)) {
            $table->created    = $date->toSql();
            #$table->created_by = $user->id;
        }
    }

    /**
     * Method to prepare the saved data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     */
    public function save($data)
    {
        $is_new = empty($data['id']);
        $app    = Factory::getApplication();
        $input  = $app->input;


        // Get parameters:
        #$params = \Joomla\CMS\Component\ComponentHelper::getParams(JRequest::getVar('option'));
        $params = \Joomla\CMS\Component\ComponentHelper::getParams($input->get('option'));

        // For reference if needed:
        // By default we're only looking for and acting upon the 'email admins' setting.
        // If any other settings are related to this save method, add them here.
        /*$email_admins_string = $params->get('email_admins');
        if (!empty($email_admins_string) && $is_new) {
            $email_admins = explode(PHP_EOL, trim($email_admins_string));
            foreach ($email_admins as $email) {
                // Sending email as an array to make it easier to expand; it's quite likely that a
                // real app would need more info here.
                $email_data = array('email' => $email);
                $this->_sendEmail($email_data);
            }
        }*/

        // Alter the name for save as copy
        if ($app->input->get('task') == 'save2copy') {
            $name          = $this->generateNewTitle(null, null, $data['name']);
            $data['name']  = $name;
            $data['state'] = 0;
        }

        return parent::save($data);
    }

    /**
     * Method to change the name & alias.
     *
     * @param   integer  $category_id  The id of the parent.
     * @param   string   $alias        The alias.
     * @param   string   $name         The name.
     *
     * @return  STRING  The modified name.
     */
    protected function generateNewTitle($category_id, $alias, $name)
    {
        // Alter the name & alias
        $table = $this->getTable();

        while ($table->load(['name' => $name])) {
            if ($name == $table->name) {
                $name = \Joomla\String\StringHelper::increment($name);
            }

            $name = \Joomla\String\StringHelper::increment($name, 'dash');
        }

        return $name;
    }

    /**
     * Copied from libraries/src/MVC/Model/AdminModel.php because it uses a hard-coded field name:
     * catid.
     *
     * Method to change the name & alias.
     *
     * @param   string   $alias        The alias.
     * @param   string   $name        The name.
     *
     * @return  array  Contains the modified name and alias.
     */
    /*protected function generateNewCsvuploadsTitle($alias, $name)
    {
        // Alter the name & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias)))
        {
            $name = StringHelper::increment($name);
            $alias = StringHelper::increment($alias, 'dash');
        }

        return array($name, $alias);
    }*/


    /**
     * Method to get the script that have to be included on the form
     *
     * @return string   Script files
     */
    /*public function getScript()
    {
        #return 'administrator/components/com_csvuploads/models/forms/csvuploads.js';
        return '';
    }*/

    /**
     * Delete this if not needed. Here for reference.
     * Method to get the data that should be injected in the form.
     *
     * @return  bool  Email success/failed to send.
     */
    /*private function _sendEmail($email_data)
    {
            $app        = Factory::getApplication();
            $mailfrom   = $app->getCfg('mailfrom');
            $fromname   = $app->getCfg('fromname');
            $sitename   = $app->getCfg('sitename');
            $email      = \Joomla\String\StringHelperPunycode::emailToPunycode($email_data['email']);

            // Ref: Text::sprintf('LANG_STR', $var, ...);

            $mail = Factory::getMailer();
            $mail->addRecipient($email);
            $mail->addReplyTo($mailfrom);
            $mail->setSender(array($mailfrom, $fromname));
            $mail->setSubject(Text::_('COM_CSVUPLOADS_EMAIL_ADMINS_SUBJECT'));
            $mail->setBody(Text::_('COM_CSVUPLOADS_EMAIL_ADMINS_BODY'));
            $sent = $mail->Send();

            return $sent;
    }*/
}
