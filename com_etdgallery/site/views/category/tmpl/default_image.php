<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_etdgallery
 *
 * @version     1.1.12
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

$sizes = json_decode($this->params->get('sizes'));

?>
<div class="col-sm-6 col-lg-3 col-md-4">
    <a class="thumbnail image" data-id="<?php echo $this->_item->id; ?>" data-gallery="etdgalery-images" data-parent=".etdgalery-images" data-toggle="lightbox"<?php if (!empty($this->_item->title)): ?> title="<?php echo htmlspecialchars($this->_item->title); ?>"<?php endif; ?> href="<?php echo $this->_item->src->{$this->params->get('zoomed_size', 'zoomed')}; ?>">
        <img class="img-responsive" src="<?php echo $this->_item->src->{$this->params->get('thumb_size', 'thumb')}; ?>" alt="<?php echo htmlspecialchars($this->_item->title); ?>">
        <?php if ($this->params->get('show_hits')) : ?>
        <div class="caption small">
            <span class="fa fa-eye"></span> <span class="hits"><?php echo $this->_item->hits ?></span>
        </div>
        <?php endif; ?>
    </a>
</div>