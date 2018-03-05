<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_etdgallery
 *
 * @version		1.1.0
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

$input  = JFactory::getApplication()->input;
$config = JComponentHelper::getParams('com_etdgallery');
$sizes  = json_decode($config->get('sizes', '[]'));
$thumb  = $sizes->{$params->get('thumb_size', 'thumb')};

if ($params->get('enable_gallery')) {

	$doc = JFactory::getDocument();
	$doc->addStyleSheet(JUri::root(true).'/media/com_etdgallery/vendor/Gallery-2.27.0/css/blueimp-gallery.min.css');
	$doc->addScript(JUri::root(true).'/media/com_etdgallery/vendor/Gallery-2.27.0/js/jquery.blueimp-gallery.min.js');

	$js = "jQuery(function($) {
	$(document).ready(function() {
		var \$blueimp = $('#blueimp-gallery');
		if (\$blueimp.length == 0) {
			\$('body').append('<div id=\"blueimp-gallery\" class=\"blueimp-gallery blueimp-gallery-controls\"><div class=\"slides\"></div><h3 class=\"title\"></h3><a class=\"prev\">‹</a><a class=\"next\">›</a><a class=\"close\">×</a><a class=\"play-pause\"></a><ol class=\"indicator\"></ol></div>');
		}
	});
});";

	$doc->addScriptDeclaration($js);

}

?>
<?php if (!empty($list)) : ?>
<div class="mod-etdgallery<?php if ($params->get('show_more', 0)) : ?> show-more<?php endif; ?> <?php echo $moduleclass_sfx ?>">
	<div class="panel-body">
		<div class="row">
			<?php foreach( $list as $item ) : ?>
				<div class="item <?php echo $item->type; ?> <?php echo $params->get('item_class'); ?>">
					<?php if ($item->type == "video") : ?>
						<div class="iframe-wrapper">
							<iframe src="<?php echo $item->filename; ?>" webkitallowfullscreen mozallowfullscreen allowfullscreen frameborder="0"></iframe>
						</div>
					<?php endif; ?>
					<?php if ($item->type == "image") : ?>
						<a class="thumbnail" data-gallery="mod-etdgallery-<?php echo $module->id; ?>" data-parent=".mod-etdgallery" data-toggle="lightbox"<?php if (!empty($item->title)): ?> title="<?php echo $item->title; ?>"<?php endif; ?> href="<?php echo $item->src->{$config->get('zoomed_size', 'zoomed')}; ?>">
							<img width="<?php echo $thumb->width; ?>" height="<?php echo $thumb->height; ?>" src="<?php echo $item->src->{$params->get('thumb_size', 'thumb')}; ?>" alt="<?php echo $item->title; ?>">
						</a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ($params->get('show_more', 0)) : ?>
			<?php

			$more_link = 'index.php?option=com_etdgallery&view=images';

			$base = (int) $params->get('base');

			if (!empty($base)) {
				$more_link .= '&Itemid=' . $base;
			}

			$tagid = $params->get('tag_id');
			if ($tagid) {
				if (count($tagid) == 1) {
					$more_link .= "&tag_id=" . (int) $tagid[0];
				} else {
					$more_link .= "&tag_id[]=" . implode("&tag_id[]=", $tagid);
				}
			}

			$type = $params->get('type');
			if ($type) {
				$more_link .= "&type=" . $type;
			}

			?>
			<div class="panel-footer">
				<div class="pull-right">
					<a href="<?php echo JRoute::_($more_link) ?>" class="more">&gt; <?php echo htmlspecialchars($params->get('more_text')) ?></a>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>