<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Component\Csvuploads\Administrator\Controller;

defined('_JEXEC') or die;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\CMS\Event\AbstractEvent;
#use Joomla\Event\Dispatcher as EventDispatcher;

class CsvuploadController extends FormController
{
    #use VersionableControllerTrait;

    /**
    * Implement to allowAdd or not
    *
    * Not used at this time (but you can look at how other components use it....)
    * Overwrites: JControllerForm::allowAdd
    *
    * @param array $data
    * @return bool
    */
    protected function allowAdd($data = [])
    {
        return parent::allowAdd($data);
    }
    /**
    * Implement to allow edit or not
    * Overwrites: JControllerForm::allowEdit
    *
    * @param array $data
    * @param string $key
    * @return bool
    */
    protected function allowEdit($data = [], $key = 'id')
    {
        $id = isset( $data[ $key ] ) ? $data[ $key ] : 0;
        if( !empty( $id ) )
        {
            return (
                Factory::getApplication()->getIdentity()->authorise( "core.edit", "com_csvuploads.csvupload." . $id )
             || Factory::getApplication()->getIdentity()->authorise( "core.edit.own", "com_csvuploads.csvupload." . $id )
            );
        }
    }

        /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        // Register events group:
        PluginHelper::importPlugin('csvuploads');
        #$dispatcher = EventDispatcher::getInstance();
        #$dispatcher = Factory::getApplication()->getDispatcher();

        jimport('joomla.filesystem.file');

        $app          = Factory::getApplication();
        $option       = $this->option;
        $view_item    = $this->view_item;
        $context      = "$option.edit.$this->context";
        $model        = $this->getModel();
        $form         = $model->getForm([], false);
        $control      = $form->getFormControl();

        $data         = $app->input->post->get($control, [], 'array');
        $db           = Factory::getDBO();
        $query        = $db->getQuery(true);

        $form         = $model->getForm($data, false);

        $accept_types = explode(', ', $form->getFieldAttribute('file', 'accept'));

        $query->select('alias');
        $query->from('#__categories');
        $query->where('id = ' . $data['catid']);
        $db->setQuery($query);

        $recordId     = $model->getState($this->context . '.id');
        $params       = clone ComponentHelper::getParams($option);
        $uploadfolder = $params->get('uploadfolder');
        $csvfolder    = $params->get('csvfolder', 'csv');
        $jsonfolder   = $params->get('jsonfolder', 'json');

        $catfolder    = $db->loadResult();

        // Upload the file:
        $files = $app->input->files->get($control);

        if (!empty($files['file']['name'])) {
            $filename1    = str_replace(' ', '-', strtolower(File::makeSafe($data['name']))) . '-' . time() . '.csv';
            $filename2    = str_replace(' ', '-', strtolower(File::makeSafe($data['name']))) . '.csv';
            $data['file'] = $filename2;
            $max          = $this->return_bytes(ini_get('upload_max_filesize'));

            if ($files['file']['size'] > $max) {
                #JError::raiseWarning(100, sprintf(Text::_('COM_CSVUPLOADS_ERROR_TOO_LARGE'), $files['file']['name'], ini_get('upload_max_filesize')));
                $this->setError(sprintf(Text::_('COM_CSVUPLOADS_ERROR_TOO_LARGE'), $files['file']['name'], ini_get('upload_max_filesize')));


                // Redirect back to the edit screen.
                $app->setUserState($context . '.data', $data);
                $this->setRedirect(
                    Route::_(
                        'index.php?option=' . $option . '&view=' . $view_item
                        . $this->getRedirectToItemAppend($recordId, $key), false
                    )
                );
                return false;
            }

            $src = $files['file']['tmp_name'];

            if (in_array($files['file']['type'], $accept_types)) {

                // Convert the CSV file to array:
                $csv_data = $this->csvarray(file_get_contents($src), (bool) $data['params']['namedkeys']);

                // This feels a bit hacky, but it allows plugins that repsond to 'onAfterLoadCSV' to
                // return 'STOP' and prevent the storage of the CSV files at all.
                // For example your plugin may need to intercept the default behavior in order to
                // save the data to a database instead, and in such a case it may be desirable not
                // to also have the CSV file stored.
                $save_to_csv_file = true;

                // Pass to any plugins looking to take action on the CSV data.
                // Note this may or may not transform the actual data itself.
                $event_results = $app->triggerEvent('onAfterLoadCSV', [$csv_data, $filename2]);

                /*
                This is the Joomla 4 way but it's not working so I meed tp imvestigate why
                https://docs.joomla.org/J4.x:Creating_a_Plugin_for_Joomla
                */
                /*
                /$event = AbstractEvent::create(
                    'onAfterLoadCSV',
                    [
                        &$csv_data,
                        $filename2
                    ]
                );
                #echo '<pre>'; var_dump($event); echo '</pre>'; exit;
                $event_results = $dispatcher->dispatch('onAfterLoadCSV', $event);

                if (in_array('STOP', $event_results, true)) {
                    $save_to_csv_file = false;
                }
                */

                if ($save_to_csv_file) {
                    $csv_folder_path = JPATH_ROOT . '/' . $uploadfolder . '/' . $csvfolder . '/';

                    $folder_perms = octdec(substr(sprintf('%o', fileperms($csv_folder_path)), -4));
                    //$file_perms = octdec(substr(sprintf('%o', fileperms(__FILE__)), -4));
                    $file_own   = posix_getpwuid(fileowner(__FILE__))['name'];
                    $file_grp   = posix_getgrgid(filegroup(__FILE__))['name'];

                    if (!file_exists($csv_folder_path)) {
                        mkdir($csv_folder_path);
                        chmod($csv_folder_path, $folder_perms);
                        chown($csv_folder_path, $file_own);
                        chgrp($csv_folder_path, $file_grp);

                    }

                    $csv_path = $csv_folder_path . $catfolder . '/';
                    if (!file_exists($csv_path)) {
                        mkdir($csv_path);
                        chmod($csv_path, $folder_perms);
                        chown($csv_path, $file_own);
                        chgrp($csv_path, $file_grp);
                    }

                    $json_folder_path = JPATH_ROOT . '/' . $uploadfolder . '/' . $jsonfolder . '/';
                    if (!file_exists($json_folder_path)) {
                        mkdir($json_folder_path);
                        chmod($json_folder_path, $folder_perms);
                        chown($json_folder_path, $file_own);
                        chgrp($json_folder_path, $file_grp);
                    }

                    $json_path = $json_folder_path . $catfolder . '/';
                    if (!file_exists($json_path)) {
                        mkdir($json_path);
                        chmod($json_path, $folder_perms);
                        chown($json_path, $file_own);
                        chgrp($json_path, $file_grp);
                    }

                    $csv_file_1 = $csv_path . $filename1;
                    $csv_file_2 = $csv_path . $filename2;

                    if (File::upload($src, $csv_file_1)) {

                        // Check for JSON processing:
                        if (isset($data['params']) && !empty($data['params']['processor']) && $data['params']['processor'] == 'json') {

                            // Convert to JSON:
                            $json = json_encode($csv_data);

                            if (empty($json) || $json == 'null') {
                                #JError::raiseWarning(100, Text::_('COM_CSVUPLOADS_MESSAGE_JSON_ERROR_1'));
                                $this->setError(Text::_('COM_CSVUPLOADS_MESSAGE_JSON_ERROR_1'));
                                return false;
                            }


                            if (!empty($data['params']['json_format'])) {
                                $twig_data = $csv_data;

                                // We need to parse this to format the json:
                                $loader = new \Twig\Loader\ArrayLoader(array('tpl' => $data['params']['json_format']));
                                $twig   = new \Twig\Environment($loader);

                                // Add html_id filter:
                                $html_id_filter = new \Twig\TwigFilter('html_id', function ($string) {
                                    $new_string = '';

                                    $new_string = self::htmlID($string);

                                    return $new_string;
                                });
                                $twig->addFilter($html_id_filter);

                                $json = $twig->render('tpl', array('data' => $twig_data));

                                // Encode then re-decode to produce tidier JSON:
                                $json = json_decode($json, true);
                                $json = json_encode($json);
                            }


                            if (empty($json) || $json == 'null') {
                                #JError::raiseWarning(100, Text::_('COM_CSVUPLOADS_MESSAGE_JSON_ERROR_2'));
                                $this->setError(Text::_('COM_CSVUPLOADS_MESSAGE_JSON_ERROR_2'));
                            } else {
                                $json_filename = str_replace('.csv', '.json', $filename2);

                                // Pass to any plugins looking to take action on the JSON data.
                                // Note this may or may not transform the actual data itself.

                                $results = $app->triggerEvent('onBeforeSaveJSON', array(&$json, $json_filename));

                                File::write($json_path . $json_filename, $json);

                                $app->enqueueMessage(sprintf(Text::_('COM_CSVUPLOADS_MESSAGE_JSON_SUCCESS'), $json_filename));
                            }
                        }

                        // Copy the the file to overwrite the unstamped version:
                        File::copy($csv_file_1, $csv_file_2);

                        //Redirect to a page of your choice
                        $app->enqueueMessage(sprintf(Text::_('COM_CSVUPLOADS_MESSAGE_SUCCESS'), $files['file']['name'], $filename2));
                    } else {
                        //Redirect and throw an error message
                        #JError::raiseWarning(100, sprintf(Text::_('COM_CSVUPLOADS_ERROR_FAILED_UPLOAD'), $files['file']['name']));
                        $this->setError(sprintf(Text::_('COM_CSVUPLOADS_ERROR_FAILED_UPLOAD'), $files['file']['name']));

                        // Redirect back to the edit screen.
                        $app->setUserState($context . '.data', $data);
                        $this->setRedirect(
                            Route::_(
                                'index.php?option=' . $option . '&view=' . $view_item
                                . $this->getRedirectToItemAppend($recordId, $key), false
                            )
                        );

                        return false;
                    }
                } else {
                    $app->enqueueMessage(sprintf(Text::_('COM_CSVUPLOADS_MESSAGE_SUCCESS_NO_SAVE'), $files['file']['name'], $filename2));
                }
            } else {
                //Redirect and notify user file is not right extension
                #JError::raiseWarning(100, sprintf(Text::_('COM_CSVUPLOADS_ERROR_WRONG_TYPE'), $files['file']['name']));
                $this->setError(sprintf(Text::_('COM_CSVUPLOADS_ERROR_WRONG_TYPE'), $files['file']['name']));

                $app->setUserState($context . '.data', $data);
                $this->setRedirect(
                    Route::_(
                        'index.php?option=' . $option . '&view=' . $view_item
                        . $this->getRedirectToItemAppend($recordId, $key), false
                    )
                );
                return false;
            }
        }

        $app->input->post->set($control, $data);

        return parent::save($key, $urlVar);
    }

    /**
     * Converts CSV to array.
     *
     * @param   string   $csv          CSV string, probably sent from a file.
     * @param   bool     $header_keys  Does the CSV contain an initial row of column names?
     */
    public function csvarray($csv, $header_keys = false)
    {
        if (!is_string($csv)) {
            trigger_error('Function \'csvarray\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }

        $csv = $this->w1250_to_utf8($csv);
        $csv = preg_replace('/(\r|\n\r|\r\n)/', '\n', $csv);

        // Remove breaks from within quotes:
        if (preg_match_all('/"[^"]*"/', $csv, $matches)) {
            foreach($matches[0] as $match) {
                $new = preg_replace('/(\n)/', '{_NEWLINE_}', $match);
                // echo utf8_encode($new).'<br />'."\n";
                $csv = preg_replace('/' . preg_quote($match, '/') . '/', $new, $csv);
            }
        }

        $csv_array  = explode("\n", $csv);
        $data       = [];
        $cell_total = 0;
        $row_count  = 0;
        $headers    = [];

        // Process each line:
        foreach($csv_array as $line) {
            $cell_count = 0;
            $row_count++;
            if (preg_match_all('/"[^"]+"/', $line, $matches)) {
                foreach($matches[0] as $match) {
                    $new = preg_replace('/,/', '{_COMMA_}', $match);
                    $line = preg_replace('/' . preg_quote($match, '/') . '/', $new, $line);
                }
            }
            $line = preg_replace('/""/', '{_QUOTE_}', $line);
            $line = preg_replace('/"/', '', $line);
            $line = preg_replace('/{_QUOTE_}/', '"', $line);
            $line = preg_replace('/,/', '\n', $line);
            $line = preg_replace('/{_COMMA_}/', ',', $line);
            $line = preg_replace('/\s{2,}/', ' ', $line);
            $line = preg_replace('/{_NEWLINE_}/', "\n", $line);
            $cells = explode('\n', $line);
            $row = [];

            $i = 0;

            foreach($cells as $cell) {
                $cell_count++;
                $cell = trim($cell);
                if ($row_count == 1) {
                    if (mb_strlen($cell) > 0) {
                        $cell_total = $cell_count;
                    }
                }
                if ($cell_count <= $cell_total) {
                    if ($header_keys && $row_count == 1) {
                        $headers[] = $cell;
                    }
                    if (!$header_keys || $row_count == 1) {
                        $row[] = $cell;
                    } else {
                        $row[$headers[$i]] = $cell;
                    }
                }
                $i++;
            }

            if (!$header_keys || $row_count == 1) {
                $first = 0;
            } else {
                $first = $headers[0];
            }

            if (mb_strlen($row[$first]) > 0) {
                $data[] = $row;
            }
        }

        if ($header_keys) {
            unset($data[0]);
        }
        return $data;
    }

    /**
     * Windows encoding to utf8.
     *
     * @param   string  $csv    CSV data.
     *
     * Taken from: https://www.php.net/manual/en/function.mb-convert-encoding.php
    */
    public function w1250_to_utf8($csv) {
        // map based on:
        // http://konfiguracja.c0.pl/iso02vscp1250en.html
        // http://konfiguracja.c0.pl/webpl/index_en.html#examp
        // http://www.htmlentities.com/html/entities/
        $map = array(
            chr(0x8A) => chr(0xA9),
            chr(0x8C) => chr(0xA6),
            chr(0x8D) => chr(0xAB),
            chr(0x8E) => chr(0xAE),
            chr(0x8F) => chr(0xAC),
            chr(0x9C) => chr(0xB6),
            chr(0x9D) => chr(0xBB),
            chr(0xA1) => chr(0xB7),
            chr(0xA5) => chr(0xA1),
            chr(0xBC) => chr(0xA5),
            chr(0x9F) => chr(0xBC),
            chr(0xB9) => chr(0xB1),
            chr(0x9A) => chr(0xB9),
            chr(0xBE) => chr(0xB5),
            chr(0x9E) => chr(0xBE),
            chr(0x80) => '&euro;',
            chr(0x82) => '&sbquo;',
            chr(0x84) => '&bdquo;',
            chr(0x85) => '&hellip;',
            chr(0x86) => '&dagger;',
            chr(0x87) => '&Dagger;',
            chr(0x89) => '&permil;',
            chr(0x8B) => '&lsaquo;',
            chr(0x91) => '&lsquo;',
            chr(0x92) => '&rsquo;',
            chr(0x93) => '&ldquo;',
            chr(0x94) => '&rdquo;',
            chr(0x95) => '&bull;',
            chr(0x96) => '&ndash;',
            chr(0x97) => '&mdash;',
            chr(0x99) => '&trade;',
            chr(0x9B) => '&rsquo;',
            chr(0xA6) => '&brvbar;',
            chr(0xA9) => '&copy;',
            chr(0xAB) => '&laquo;',
            chr(0xAE) => '&reg;',
            chr(0xB1) => '&plusmn;',
            chr(0xB5) => '&micro;',
            chr(0xB6) => '&para;',
            chr(0xB7) => '&middot;',
            chr(0xBB) => '&raquo;',
        );
        return html_entity_decode(mb_convert_encoding(strtr($csv, $map), 'UTF-8', 'ISO-8859-2'), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Converts array to CSV.
     *
     * @param   array   $data        2D data.
     * @param   bool    $delimeter   See http://php.net/manual/en/function.fputcsv.php
     * @param   string  $enclosure   See http://php.net/manual/en/function.fputcsv.php
     */
    public function arraycsv($data, $delimiter = ',', $enclosure = '"') {
        $handle = fopen('php://temp', 'r+');

        foreach ($data as $line) {
            fputcsv($handle, $line, $delimiter, $enclosure);
        }

        rewind($handle);
        $contents = '';

        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }

        fclose($handle);
        return $contents;
    }

    /**
     * Converts filesize string to real bytes.
     *
     * @param   string   $val  Filesize string.
     */
    public function return_bytes($val)
    {
        if (empty($val)) {
            return 0;
        }

        $val = trim($val);

        preg_match('#([0-9]+)[\s]*([a-z]+)#i', $val, $matches);

        $last = '';
        if (isset($matches[2])) {
            $last = $matches[2];
        }

        if (isset($matches[1])) {
            $val = (int) $matches[1];
        }

        switch (strtolower($last)) {
            case 'g':
            case 'gb':
                $val *= 1024;
            case 'm':
            case 'mb':
                $val *= 1024;
            case 'k':
            case 'kb':
                $val *= 1024;
        }

        return (int) $val;
    }

    /**
     * Strips punctuation from a string
     *
     * @param string $text
     * @return string
     * @access public
     */
    public static function stripPunctuation($text)
    {
        if (!is_string($text)) {
            trigger_error('Function \'strip_punctuation\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }
        $text = html_entity_decode($text, ENT_QUOTES);

        $urlbrackets = '\[\]\(\)';
        $urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
        $urlspaceafter = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
        $urlall = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;

        $specialquotes = '\'"\*<>';

        $fullstop = '\x{002E}\x{FE52}\x{FF0E}';
        $comma = '\x{002C}\x{FE50}\x{FF0C}';
        $arabsep = '\x{066B}\x{066C}';
        $numseparators = $fullstop . $comma . $arabsep;

        $numbersign = '\x{0023}\x{FE5F}\x{FF03}';
        $percent = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
        $prime = '\x{2032}\x{2033}\x{2034}\x{2057}';
        $nummodifiers = $numbersign . $percent . $prime;
        $return = preg_replace(
        [
            // Remove separator, control, formatting, surrogate,
            // open/close quotes.
            '/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
            // Remove other punctuation except special cases
            '/\p{Po}(?<![' . $specialquotes .
            $numseparators . $urlall . $nummodifiers . '])/u',
            // Remove non-URL open/close brackets, except URL brackets.
            '/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
            // Remove special quotes, dashes, connectors, number
            // separators, and URL characters followed by a space
            '/[' . $specialquotes . $numseparators . $urlspaceafter .
            '\p{Pd}\p{Pc}]+((?= )|$)/u',
            // Remove special quotes, connectors, and URL characters
            // preceded by a space
            '/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
            // Remove dashes preceded by a space, but not followed by a number
            '/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
            // Remove consecutive spaces
            '/ +/',
        ], ' ', $text);
        $return = str_replace('/', '_', $return);
        return str_replace("'", '', $return);
    }

    /**
     * Creates an HTML-friendly string for use in id's
     *
     * @param string $text
     * @return string
     * @access public
     */
    public function htmlID($text)
    {
        if (!is_string($text)) {
            trigger_error('Function \'html_id\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }
        $return = strtolower(trim(preg_replace('/\s+/', '-', self::stripPunctuation($text))));
        return $return;
    }
}
