<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * CSVUploads Record Controller
 */
class CSVUploadsControllerCSVUpload extends JControllerForm
{
    /**
     * Method to check if you can edit record.
     *
     * Uses assigned category id to check for permissions, so users that can edit
     * the category can also edit this record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $model = $this->getModel();
        $item  = $model->getItem($data['id']);
        $user  = JFactory::getUser();

        return !!($user->authorise('core.edit', $this->option)
               || $user->authorise('core.edit', 'com_content.category.' . $item->catid));
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
        jimport('joomla.filesystem.file');

        $app          = JFactory::getApplication();
        $option       = $this->option;
        $view_item    = $this->view_item;
        $context      = "$option.edit.$this->context";
        $model        = $this->getModel();
        $form         = $model->getForm(array(), false);
        $control      = $form->getFormControl();

        $data         = $app->input->post->get($control, array(), 'array');
        $db           = JFactory::getDBO();
        $query        = $db->getQuery(true);

        $form         = $model->getForm($data, false);

        $accept_types = explode(', ', $form->getFieldAttribute('file', 'accept'));

        $query->select('alias');
        $query->from('#__categories');
        $query->where('id = ' . $data['catid']);
        $db->setQuery($query);

        $recordId     = $model->getState($this->context . '.id');
        $params       = clone JComponentHelper::getParams($option);
        $uploadfolder = $params->get('uploadfolder');
        $csvfolder    = $params->get('csvfolder');
        $catfolder    = $db->loadResult();
        $path         = JPATH_ROOT . '/' . $uploadfolder . '/' . $csvfolder . '/' . $catfolder . '/';

        // Upload the file:
        $files = JFactory::getApplication()->input->files->get($control);

        if(!empty($files['file']['name'])){
            $filename1    = str_replace(' ', '-', JFile::makeSafe($data['name'])) . '-' . time() . '.csv';
            $filename2    = str_replace(' ', '-', JFile::makeSafe($data['name'])) . '.csv';
            $data['file'] = $filename2;
            $max          = $this->return_bytes(ini_get('upload_max_filesize'));

            if ($files['file']['size'] > $max) {
                JError::raiseWarning(100, sprintf(JText::_('COM_CSVUPLOADS_ERROR_TOO_LARGE'), $files['file']['name'], ini_get('upload_max_filesize')));
                
                // Redirect back to the edit screen.
                $app->setUserState($context . '.data', $data);
                $this->setRedirect(
                    JRoute::_(
                        'index.php?option=' . $option . '&view=' . $view_item
                        . $this->getRedirectToItemAppend($recordId, $key), false
                    )
                );
                return false;
            }

            $src   = $files['file']['tmp_name'];
            $dest1 = $path . $filename1;
            $dest2 = $path . $filename2;

            if (in_array($files['file']['type'], $accept_types)) {
                if (JFile::upload($src, $dest1)) {
                    
                    // Check for JSON processing:
                    // @TODO: revise this to check for JSON processing Y/N, and if there's a Twig
                    // template, process that.
                    /*
                    
                        $loader = new Twig_Loader_Array(array('tpl' => $json_tpl));
                        $twig   = new Twig_Environment($loader); 
                        $output = $twig->render('tpl', array('data' => $json));
                    
                    */
                    /*if (isset($data['options']) && !empty($data['options']['processor']) && $data['options']['processor'] != 'none') {
                        #require JPATH_COMPONENT . '/csvprocessor.php';
                        
                        // Convert the CSV file to array:
                        $csv_data = $this->csvarray(file_get_contents($dest1), (bool) $data['options']['namedkeys']);
                        
                        if ($data['options']['processor'] == 'custom') {
                            $processor_file = JPATH_ROOT . '/' . $params->get('processorsfolder') . '/processor' . $data['id'] . '.php';
                            $classname = 'Processor' . $data['id'];
                        }
                        if ($data['options']['processor'] == 'json') {
                            $processor_file = JPATH_COMPONENT . '/processors/' . $data['options']['processor'] . '.php';
                            $classname = $data['options']['processor'] . 'Processor';
                        }
                        
                        $processor_error = true;
                        if (file_exists($processor_file)) {
                            require $processor_file;
                            
                            if (class_exists($classname)) {
                                $processor = new $classname();
                                
                                if (is_a($processor, 'CSVuploadsProcessor')) {
                                    $new_csv_data = $processor->process($csv_data, $dest2);
                                    $new_csv_string = $this->arraycsv($new_csv_data);

                                    // Overwrite uploaded file with modified data:
                                    JFile::write($dest1, $new_csv_string);
                                    $processor_error = false;
                                }
                            }
                        }
                        if ($processor_error) {
                            JError::raiseWarning(100, sprintf(JText::_('COM_CSVUPLOADS_PROCESSOR_NOT_FOUND'), $processor_file, $classname));
                        }
                    }*/
                    
                    // Copy the the file to overwrite the unstamped version:
                    JFile::copy($dest1, $dest2);
                    
                    //Redirect to a page of your choice
                    $app->enqueueMessage(sprintf(JText::_('COM_CSVUPLOADS_MESSAGE_SUCCESS'), $files['file']['name'], $filename2));
                } else {
                    //Redirect and throw an error message
                    JError::raiseWarning(100, sprintf(JText::_('COM_CSVUPLOADS_ERROR_FAILED_UPLOAD'), $files['file']['name']));

                    // Redirect back to the edit screen.
                    $app->setUserState($context . '.data', $data);
                    $this->setRedirect(
                        JRoute::_(
                            'index.php?option=' . $option . '&view=' . $view_item
                            . $this->getRedirectToItemAppend($recordId, $key), false
                        )
                    );

                    return false;
                }
            } else {
                //Redirect and notify user file is not right extension
                JError::raiseWarning(100, sprintf(JText::_('COM_CSVUPLOADS_ERROR_WRONG_TYPE'), $files['file']['name']));
                $app->setUserState($context . '.data', $data);
                $this->setRedirect(
                    JRoute::_(
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
     * @param   bool     $header_keys  Does the CSV contain an intial row of column names?
     */
    public function csvarray($csv, $header_keys = false)
    {
        if (!is_string($csv)) {
            trigger_error('Function \'csvarray\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }
        $csv = preg_replace('/(\r|\n\r|\r\n)/', '\n', $csv);
        // Remove breaks from within quotes:
        if (preg_match_all('/"[^"]*"/', $csv, $matches)) {
            foreach($matches[0] as $match) {
                $new = preg_replace('/(\n)/', '{_NEWLINE_}', $match);
                // echo utf8_encode($new).'<br />'."\n";
                $csv = preg_replace('/' . preg_quote($match, '/') . '/', $new, $csv);
            }
        }
        $csv        = utf8_encode($csv);
        $csv_array  = explode("\n", $csv);
        $data       = array();
        $cell_total = 0;
        $row_count  = 0;
        $headers    = array();
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
            $row = array();

            $i = 0;
            foreach($cells as $cell) {
                $cell_count++;
                #$cell = trim(htmlentities($cell, ENT_QUOTES));
                $cell = trim($cell);
                // $cell = trim(html_characters_not_decoded(utf8_convert($cell)));
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
}
