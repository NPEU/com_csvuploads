<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_CSVUPLOADS',
    'formURL'    => 'index.php?option=com_csvuploads',
];

/*
$displayData = [
    'textPrefix' => 'COM_CSVUPLOADS',
    'formURL'    => 'index.php?option=com_csvuploads',
    'helpURL'    => '',
    'icon'       => 'icon-globe csvuploads',
];
*/

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_csvuploads') || count($user->getAuthorisedCategories('com_csvuploads', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_csvuploads&task=csvupload.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);