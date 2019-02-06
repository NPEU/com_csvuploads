<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->filter_order);
$listDirn  = $this->escape($this->filter_order_Dir);

?>
<form action="index.php?option=com_csvuploads&view=records" method="post" id="adminForm" name="adminForm">
    <?php if (!empty($this->items)): ?>
    <div class="row-fluid">
        <div class="span6">
            <?php echo JText::_('COM_CSVUPLOADS_RECORDS_FILTER'); ?>
            <?php
                echo JLayoutHelper::render(
                    'joomla.searchtools.default',
                    array('view' => $this)
                );
            ?>
        </div>
    </div>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th width="2%"><?php echo JText::_('COM_CSVUPLOADS_NUM'); ?></th>
                <th width="4%">
                    <?php echo JHtml::_('grid.checkall'); ?>
                </th>
                <th width="25%">
                    <?php echo JHtml::_('grid.sort', 'COM_CSVUPLOADS_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
                </th>
                <th width="25%">
                    <?php echo JHtml::_('grid.sort', 'COM_CSVUPLOADS_HEADING_DESCRIPTION', 'description', $listDirn, $listOrder); ?>
                </th>
                <th width="30%">
                    <?php echo JHtml::_('grid.sort', 'COM_CSVUPLOADS_HEADING_CONTACT', 'contact', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('grid.sort', 'COM_CSVUPLOADS_HEADING_CREATED_DATE', 'created', $listDirn, $listOrder); ?>
                </th>
                <th width="4%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
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
        <?php foreach ($this->items as $i => $item) :
            $link = JRoute::_('index.php?option=com_csvuploads&task=record.edit&id=' . $item->id);
            $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
            $canEdit    = $user->authorise('core.edit', 'com_content.category.' . $item->catid);
        ?>
            <tr>
                <td><?php echo $this->pagination->getRowOffset($i); ?></td>
                <td>
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td>
                <?php if ($item->checked_out) : ?>
                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'records.', $canCheckin); ?>
                <?php endif; ?>
                <?php if ($canEdit) : ?>
                    <a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_CSVUPLOADS_EDIT_RECORD'); ?>">
                        <?php echo $this->escape($item->name); ?>
                    </a>
                <?php else: ?>
                    <?php echo $this->escape($item->name); ?>
                <?php endif; ?>
                </td>
                <td class="">
                    <?php echo $this->escape($item->description); ?>
                </td>
                <td class="">
                    <?php echo $this->escape($item->contact_name); ?> (<?php echo $this->escape($item->contact_email); ?>)
                </td>
                <td align="center">
                    <?php #echo JHtml::_('jgrid.created', $item->created, $i, 'records.', true, 'cb'); ?>
                    <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
                </td>
                <td align="">
                    <?php echo $item->id; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <?php echo JText::_('COM_CSVUPLOADS_NO_RECORDS'); ?>
    <?php endif; ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
