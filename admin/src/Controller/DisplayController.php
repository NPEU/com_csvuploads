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

use Joomla\CMS\MVC\Controller\BaseController;


/**
 * Csvuploads Component Controller
 */
class DisplayController extends BaseController {
    protected $default_view = 'csvuploads';

    public function display($cachable = false, $urlparams = []) {
        return parent::display($cachable, $urlparams);
    }
}