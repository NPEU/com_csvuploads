<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$userId    = $user->get('id');
#$listOrder = $this->escape($this->filter_order);
#$listDirn  = $this->escape($this->filter_order_Dir);
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn    = $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo JRoute::_('index.php?option=com_csvuploads&view=csvuploads'); ?>" method="post" id="adminForm" name="adminForm">

    <?php if (!empty( $this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
    <div id="j-main-container">
    <?php endif;?>
        <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
        <div class="clearfix"> </div>
        <?php if (empty($this->items)) : ?>
        <div class="csvupload csvupload-no-items">
            <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); #COM_CSVUPLOADS_NO_RECORDS ?>
        </div>
        <?php else : ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th width="2%"><?php echo JText::_('COM_CSVUPLOADS_HEADING_NUM'); ?></th>
                    <th width="4%">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th width="25%">
                        <?php echo JHtml::_('searchtools.sort', 'COM_CSVUPLOADS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
                    </th>
                    <th width="25%">
                        <?php echo JHtml::_('searchtools.sort', 'COM_CSVUPLOADS_HEADING_DESCRIPTION', 'a.description', $listDirn, $listOrder); ?>
                    </th>
                    <th width="24%">
                        <?php echo JHtml::_('searchtools.sort', 'COM_CSVUPLOADS_HEADING_CONTACT', 'contact_name', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%">
                        <?php echo JHtml::_('searchtools.sort', 'COM_CSVUPLOADS_HEADING_PUBLISHED', 'a.state', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%">
                        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="7">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <?php $canCreate      = $user->authorise('core.create',     'com_csvuploads.' . $item->id); ?>
                <?php $canEdit        = ($user->authorise('core.edit',       'com_csvuploads.' . $item->id) || ($user->authorise('core.edit', 'com_content.category.' . $item->catid))); ?>
                <?php $canCheckin     = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0; ?>
                <?php $canEditOwn     = $user->authorise('core.edit.own',   'com_csvuploads.' . $item->id) && ($item->created_by == $user->id || $item->contact_user_id == $user->id); ?>
                <?php $canChange      = $user->authorise('core.edit.state', 'com_csvuploads.' . $item->id) && $canCheckin; ?>

                <tr>
                    <td><?php echo $this->pagination->getRowOffset($i); ?></td>
                    <td>
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td>
                    <?php if ($item->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'csvuploads.', $canCheckin); ?>
                        <?php endif; ?>
                        <?php if ($canEdit || $canEditOwn) : ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_csvuploads&task=csvupload.edit&id=' . (int) $item->id); ?>" title="<?php echo JText::_('COM_CSVUPLOADS_EDIT_RECORD'); ?>">
                                <?php echo $this->escape($item->name); ?></a>
                        <?php else : ?>
                                <?php echo $this->escape($item->name); ?>
                        <?php endif; ?>
                    </td>
                    <td class="">
                        <?php echo $this->escape($item->description); ?>
                    </td>
                    <td align="center">
                        <a href="mailto:<?php echo $item->contact_email; ?>"><?php echo $item->contact_name; ?></a>
                    </td>
                    <td align="center">
                        <?php echo JHtml::_('jgrid.published', $item->state, $i, 'csvuploads.', true, 'cb'); ?>
                    </td>
                    <td align="center">
                        <?php echo $item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
