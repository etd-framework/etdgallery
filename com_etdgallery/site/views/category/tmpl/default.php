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

$class = "";
$pageclass_sfx = $this->params->get('pageclass_sfx');

if (!empty($pageclass_sfx)) {
	$class .=  " " . $pageclass_sfx;
}

if ($this->state->get('filter.type')) {
	$class .= " " . $this->state->get('filter.type');
}

$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::root(true).'/media/com_etdgallery/vendor/Gallery-2.27.0/css/blueimp-gallery.min.css');
$doc->addScript(JUri::root(true).'/media/com_etdgallery/vendor/Gallery-2.27.0/js/jquery.blueimp-gallery.min.js');

$js = "jQuery(function($) {
	$(document).ready(function() {

		var \$blueimp = $('#blueimp-gallery');
		if (\$blueimp.length == 0) {
			\$('body').append('<div id=\"blueimp-gallery\" class=\"blueimp-gallery blueimp-gallery-controls\"><div class=\"slides\"></div><h3 class=\"title\"></h3><a class=\"prev\">‹</a><a class=\"next\">›</a><a class=\"close\">×</a><a class=\"play-pause\"></a><ol class=\"indicator\"></ol></div>');
		}

		$('a.thumbnail.image').on('click', function() {
            var \$this = $(this),
            	id = \$this.data('id');
            $.ajax('" . JUri::root() . "index.php?option=com_etdgallery&task=image.hit', {
                dataType: 'json',
                data: {
                    id: id
                }
            }).done(function(response) {
                if (response.success) { console.log(response.data);
                	\$this.find('.hits').text(response.data.hits);
                	console.log(\$this.find('.hits').text());
                }
            });
		});

	});
});";
$doc->addScriptDeclaration($js);

?>
<div class="etdgalery-images<?php echo $class; ?>">
	<?php if ($this->params->get('show_page_heading') != 0 ) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>
	<div class="row">
		<?php foreach ($this->items as $item) : ?>
		<?php
			$this->_item = &$item;
			echo $this->loadTemplate($item->type);
		?>
		<?php endforeach; ?>
	</div>
	<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->pagesTotal > 1)) : ?>
	<div class="pagination-wrapper">
		<div class="row">
			<div class="col-md-9 col-lg-10">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
				<div class="col-md-3 col-lg-2">
					<span class="counter"><?php echo $this->pagination->getPagesCounter(); ?></span>
				</div>
			<?php  endif; ?>
		</div>
	</div>
	<?php endif; ?>
</div>