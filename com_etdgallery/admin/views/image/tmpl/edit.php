<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_etdgallery
 *
 * @version     1.1.4
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('jquery.framework');
JHtml::_('jquery.ui', array('core', 'sortable'));

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task) {
		if (task == "image.cancel" || document.formvalidator.isValid(document.getElementById("image-form"))) {
			Joomla.submitform(task, document.getElementById("image-form"));
		}
	};
	jQuery(document).ready(function ($){
		$(\'input[name="jform[type]"]\').change(function(){
			if($(this).val() == "image") {
				$("#video").css("display", "none");
				$("#image").css("display", "block");
			} else {
				$("#video").css("display", "block");
				$("#image").css("display", "none");
			}
		});
		$(\'input[name="jform[type]"]\').filter(":checked").trigger("change");
	});
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_etdgallery&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="image-form" class="form-validate" enctype="multipart/form-data">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_ETDGALLERY_IMAGE_DETAILS', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<?php
				echo $this->form->getControlGroup('type');
				?>
				<div id="image">
					<?php echo $this->form->getControlGroups('image'); ?>
				</div>
				<div id="video">
					<?php echo $this->form->getControlGroups('video'); ?>
				</div>
				<?php
				echo $this->form->getControlGroup('article_id');
				echo $this->form->getControlGroup('description');
				?>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
