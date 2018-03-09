<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Etdgallery
 *
 * @version     1.1.5
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

$config = JComponentHelper::getParams('com_etdgallery');
$sizes  = json_decode($config->get('sizes', '[]'));
$thumb  = $sizes->{$config->get('thumb_size', 'thumb')};

if ($config->get('enable_gallery', 1)) {

    $doc = JFactory::getDocument();
    $doc->addStyleSheet(JUri::root(true) . '/media/com_etdgallery/vendor/Gallery-2.27.0/css/blueimp-gallery.min.css');
    $doc->addScript(JUri::root(true) . '/media/com_etdgallery/vendor/Gallery-2.27.0/js/jquery.blueimp-gallery.min.js');

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
<div class="etdgallery">
    <div class="panel-body">
        <div class="row">
            <?php foreach($images as $image) : ?>
                <div class="col-xs-12 col-sm-6 col-md-4 item <?php echo $image->type; ?> <?php echo $config->get('item_class'); ?>">
                    <?php if ($image->type == "video") : ?>
                        <div class="iframe-wrapper">
                            <iframe src="<?php echo $image->filename; ?>" webkitallowfullscreen mozallowfullscreen allowfullscreen frameborder="0"></iframe>
                        </div>
                    <?php endif; ?>
                    <?php if ($image->type == "image") : ?>
                        <a class="thumbnail" data-gallery="etdgallery-<?php echo $article->id; ?>" data-parent=".etdgallery" data-toggle="lightbox"<?php if (!empty($image->title)): ?> title="<?php echo $image->title; ?>"<?php endif; ?> href="<?php echo $image->src->{$config->get('zoomed_size', 'zoomed')}; ?>">
                            <img width="<?php echo $thumb->width; ?>" height="<?php echo $thumb->height; ?>" src="<?php echo $image->src->{$config->get('thumb_size', 'thumb')}; ?>" alt="<?php echo $image->title; ?>">
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($config->get('show_more', 0)) : ?>
            <?php

            $more_link = 'index.php?option=com_etdgallery&view=images';

            $base = (int) $config->get('base');

            if (!empty($base)) {
                $more_link .= '&Itemid=' . $base;
            }

            $tagid = $config->get('tag_id');
            if ($tagid) {
                if (count($tagid) == 1) {
                    $more_link .= "&tag_id=" . (int) $tagid[0];
                } else {
                    $more_link .= "&tag_id[]=" . implode("&tag_id[]=", $tagid);
                }
            }

            $type = $config->get('type');
            if ($type) {
                $more_link .= "&type=" . $type;
            }

            ?>
            <div class="panel-footer">
                <div class="pull-right">
                    <a href="<?php echo JRoute::_($more_link) ?>" class="more">&gt; <?php echo htmlspecialchars($config->get('more_text')) ?></a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>