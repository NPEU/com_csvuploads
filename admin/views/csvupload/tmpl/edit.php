<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;


// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$app = JFactory::getApplication();
$input = $app->input;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$action    = 'index.php?option=com_csvuploads&amp;view=csvupload&amp;layout=edit';
$fieldsets = $this->form->getFieldsets();
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'csvupload.cancel' || document.formvalidator.isValid(document.id('item-form')))
		{
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_($action . '&amp;id=' . (int) $this->item->id); ?>"
      method="post"
      name="adminForm"
      id="item-form"
      class="form-validate form-horizontal"
      enctype="multipart/form-data"
>
	<div class="row-fluid">
	<!-- Begin Content -->
		<div class="span12 form-horizontal">
			<ul class="nav nav-tabs">
				<?php $i=0; foreach ($fieldsets as $fieldset): $i++; ?>
				<li<?php echo $i == 1 ? ' class="active"' : ''; ?>><a href="#<?php echo $fieldset->name; ?>" data-toggle="tab"><?php echo JText::_($fieldset->label);?></a></li>
				<?php endforeach; ?>
			</ul>
			<div class="tab-content">
			<?php $i=0; foreach ($fieldsets as $fieldset): $i++; ?>
			<?php $form_fieldset = $this->form->getFieldset($fieldset->name); ?>
				<!-- Begin Tabs -->
				<div class="tab-pane<?php echo $i == 1 ? ' active' : ''; ?>" id="<?php echo $fieldset->name; ?>">
					<?php $hidden_fields = array(); foreach($form_fieldset as $field): ?>
					<?php if($field->type == 'Hidden'){$hidden_fields[] = $field->input; continue;} ?>
					<div class="control-group">
						<?php if ($field->type != 'Button'): ?>
						<div class="control-label">
							<?php echo JText::_($field->label); ?>
						</div>
						<?php endif; ?>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div><!-- End control-group -->
					<?php endforeach; ?>
					<?php echo implode("\n", $hidden_fields); ?>
				</div><!-- End tab-pane -->
			<?php endforeach; ?>
			</div><!-- End tab-content -->
		</div><!-- End span12 form-horizontal -->
	</div><!-- End row-fluid -->
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>