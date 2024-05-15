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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;


$user    = Factory::getApplication()->getIdentity();
$user_id = $user->get('id');
#$this->document->getWebAssetManager()->useScript('com_csvuploads.enable-tooltips');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

function make_links_callback($match) {
    // Prepend http:// if no protocol specified
    $completeUrl = $match[1] ? $match[0] : "http://{$match[0]}";

    //return '<a href="' . $completeUrl . '">' . $match[2] . $match[3] . $match[4] . '</a>';
    return '<a href="' . $completeUrl . '" target="_blank">' . $completeUrl . '</a>';
}
$rexProtocol = '(https?://)?';
$rexDomain   = '((?:[-a-zA-Z0-9]{1,63}\.)+[-a-zA-Z0-9]{2,63}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})';
$rexPort     = '(:[0-9]{1,5})?';
$rexPath     = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
$rexQuery    = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
$rexFragment = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
?>
<form action="<?php echo Route::_('index.php?option=com_csvuploads&view=csvuploads'); ?>" method="post" id="adminForm" name="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
                ?>
                <?php if (empty($this->items)) : ?>
                <div class="alert alert-info">
                    <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
                <?php else : ?>
                <table class="table" id="csvuploadsList">
                    <caption class="visually-hidden">
                        <?php echo Text::_('COM_CSVUPLOADS_TABLE_CAPTION'); ?>,
                        <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                        <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                    </caption>
                    <thead>
                        <tr>
                            <td class="w-1 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </td>
                            <th class="w-1 text-center">
                                <?php echo Text::_('COM_CSVUPLOADS_NUM'); ?>
                            </th>
                            <th scope="col" style="min-width:85px" class="w-1 text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_CSVUPLOADS_RECORDS_TITLE', 'a.name', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_CSVUPLOADS_RECORDS_DESCRIPTION', 'a.description', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_CSVUPLOADS_RECORDS_CONTACT', 'a.contact', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-5 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_CSVUPLOADS_ID', 'a.id', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->items as $i => $item) : ?>
                        <?php $canEdit        = $user->authorise('core.edit',       'com_csvuploads.' . $item->id); ?>
                        <?php $canCheckin     = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || !$item->checked_out; ?>
                        <?php $canEditOwn     = $user->authorise('core.edit.own',   'com_csvuploads.' . $item->id) && $item->created_by == $user->id; ?>
                        <?php $canChange      = $user->authorise('core.edit.state', 'com_csvuploads.' . $item->id) && $canCheckin; ?>

                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="text-center">
                                <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td>
                                <?php echo $this->pagination->getRowOffset($i); ?>
                            </td>
                            <td class="text-center">
                                <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'csvuploads.', $canChange, 'cb'); ?>
                            </td>
                            <th scope="row" class="has-context">
                                <div>
                                    <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'csvuploads.', $canCheckin); ?>
                                    <?php endif; ?>
                                    <?php if ($canEdit || $canEditOwn) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_csvuploads&task=csvupload.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
                                        <?php echo $this->escape($item->name); ?>
                                    </a>
                                    <?php else : ?>
                                        <?php echo $this->escape($item->name); ?>
                                    <?php endif; ?>

                                </div>
                            </th>
                            <td>
                                <?php echo preg_replace_callback("&\\b$rexProtocol$rexDomain$rexPort$rexPath$rexQuery$rexFragment(?=[?.!,;:\"]?(\s|$))&", 'make_links_callback', htmlspecialchars($item->description)); ?>
                            </td>
                            <td>
                                <a href="mailto:<?php echo $item->contact_email; ?>"><?php echo $item->contact_name; ?></a>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo $item->id; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif;?>

                <?php // Load the pagination. ?>
                <?php echo $this->pagination->getListFooter(); ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>