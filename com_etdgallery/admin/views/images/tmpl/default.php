<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_etdgallery
 *
 * @version		1.1.0
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
//JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$params     = JComponentHelper::getParams('com_etdgallery');
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', 'com_etdgallery');
$archived	= $this->state->get('filter.state') == 2 ? true : false;
$trashed	= $this->state->get('filter.state') == -2 ? true : false;
$saveOrder	= $listOrder == 'a.ordering';

if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_etdgallery&task=images.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

JText::script('COM_ETDGALLERY_IMAGES_CONFIRM_DELETE_PROMPT');

JFactory::getDocument()->addScriptDeclaration('
Joomla.submitbutton = function(task) {
	if (task == "images.delete") {
		if (!confirm(Joomla.JText._("COM_ETDGALLERY_IMAGES_CONFIRM_DELETE_PROMPT"))) {
			return false;
		}
	}
	Joomla.submitform(task);
};
');

?>

<form action="<?php echo JRoute::_('index.php?option=com_etdgallery&view=images'); ?>" method="post" name="adminForm" id="adminForm" >
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="articleList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="center">
							&nbsp;
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_ETDGALLERY_HEADING_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_ETDGALLERY_HEADING_ARTICLE', 'article_title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="13">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) :
						$ordering  = ($listOrder == 'ordering');
						$item->cat_link = JRoute::_('index.php?option=com_categories&extension=com_etdgallery&task=edit&type=other&cid[]=' . $item->catid);
						$canCreate  = $user->authorise('core.create',     'com_etdgallery');
						$canEdit    = $user->authorise('core.edit',       'com_etdgallery');
						$canChange  = $user->authorise('core.edit.state', 'com_etdgallery');
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								$iconClass = '';
								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler <?php echo $iconClass ?>">
									<i class="icon-menu"></i>
								</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5"
										value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
								<?php endif; ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'images.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
									<?php
									// Create dropdown items
									$action = $archived ? 'unarchive' : 'archive';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'images');

									$action = $trashed ? 'untrash' : 'trash';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'images');

									// Render dropdown list
									echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
									?>
								</div>
							</td>
							<td>
								<?php if ($item->type == 'image') : ?>
									<img width="40" height="40" src="<?php echo JUri::root(); ?>/<?php echo $item->dirname; ?>/<?php echo $item->id; ?>_<?php echo $params->get('admin_size'); ?>_<?php echo $item->filename; ?>" alt="">
								<?php endif; ?>
							</td>
							<td class="has-context">
								<div class="pull-left">
									<?php if ($canEdit) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_etdgallery&task=image.edit&id=' . (int) $item->id); ?>">
											<?php if (empty($item->title)) : ?>Sans titre<?php else: ?><?php echo $item->title; ?><?php endif; ?></a>
									<?php else : ?>
										<?php if (empty($item->title)) : ?>Sans titre<?php else: ?><?php echo $item->title; ?><?php endif; ?>
									<?php endif; ?>
									<span class="small">
										<?php echo JText::sprintf('COM_ETDGALLERY_LIST_FILE', $this->escape($item->filename)); ?>
									</span>
								</div>
							</td>
							<td class="small hidden-phone">
								<?php echo $item->article_title; ?>
							</td>
							<td class="nowrap small hidden-phone">
								<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="center hidden-phone">
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php //Load the batch processing form. ?>
		<?php echo $this->loadTemplate('batch'); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
