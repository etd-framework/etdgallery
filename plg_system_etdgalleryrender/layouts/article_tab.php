<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Etdgalleryrender
 *
 * @version		1.1.0
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

$app        = JFactory::getApplication();
$doc        = JFactory::getDocument();
$config     = JComponentHelper::getParams('com_etdgallery');
$sizes      = json_decode($config->get('sizes', '[]'));
$admin_size = $sizes->{$config->get('admin_size', 'thumb')};
$article_id = $app->input->get('id', 0, 'uint');
JHtml::_('jquery.framework');
JHtml::_('jquery.ui', array('core', 'sortable'));

$doc->addStyleSheet(JUri::root(true)."/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/css/jquery.fileupload.css");
$doc->addStyleSheet(JUri::root(true)."/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/css/jquery.fileupload-ui.css");
$doc->addStyleSheet(JUri::root(true)."/media/com_etdgallery/vendor/Jcrop-2.0.3/css/Jcrop.min.css");
$doc->addStyleSheet(JUri::root(true)."/media/com_etdgallery/css/admin.css");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/JavaScript-Templates-2.5.5/js/tmpl.min.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/JavaScript-Load-Image-1.14.0/js/load-image.all.min.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/JavaScript-Canvas-to-Blob-2.2.0/js/canvas-to-blob.min.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/js/jquery.iframe-transport.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/js/jquery.fileupload.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/js/jquery.fileupload-process.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/js/jquery.fileupload-image.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/js/jquery.fileupload-validate.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/js/jquery.fileupload-ui.js");
//$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/cropper-1.0.0/dist/cropper.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/vendor/Jcrop-2.0.3/js/Jcrop.min.js");
$doc->addScript(JUri::root(true)."/media/com_etdgallery/js/admin.js");

$doc->addScriptDeclaration("jQuery(function ($) {
    $(document).ready(function() {
         $('#fileupload').etdgallery({
            maxFileSize: " . $config->get('max_upload_size', 100000) . ",
            previewMaxWidth: " . $admin_size->width . ",
            previewMaxHeight: " . $admin_size->height . ",
            previewCrop: " . ($admin_size->crop ? 'true' : 'false') . ",
            token: '" . JSession::getFormToken() ."',
            articleId: " . $article_id . ",
            sizes: " . $config->get('sizes', '[]') . ",
            introSize: '" . $config->get('intro_size', 'intro') . "',
            fullSize: '" . $config->get('full_size', 'regular') . "',
            urls: {
                upload: '" . JRoute::_('index.php?option=com_etdgallery&task=image.upload', false) . "',
                order: '" . JRoute::_('index.php?option=com_etdgallery&task=images.saveOrderAjax', false) . "',
                featured: '" . JRoute::_('index.php?option=com_etdgallery&task=image.featured', false) . "',
                images: '" . JRoute::_('index.php?option=com_etdgallery&task=images.getImages', false) . "'
            }
         });
    });
});");

?>
<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'etdgallery', JText::_('PLG_SYSTEM_ETDGALLERYRENDER_TAB_TITLE', true)); ?>
    <div id="fileupload">
        <fieldset>
            <legend><?php echo JText::_('PLG_SYSTEM_ETDGALLERYRENDER_FIELDSET_UPLOAD_IMAGES_LABEL') ?></legend>

            <div class="fileupload-buttonbar clearfix">
                <div class="btn-toolbar">
                    <div class="btn-group">
                        <span class="btn btn-success fileinput-button">
                            <i class="icon-plus"></i>
                            <span><?php echo JText::_('PLG_SYSTEM_ETDGALLERYRENDER_UPLOAD_ADD_FILES') ?></span>
                            <input type="file" name="image" multiple>
                        </span>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary start">
                            <i class="icon-upload"></i>
                            <span><?php echo JText::_('PLG_SYSTEM_ETDGALLERYRENDER_UPLOAD_START') ?></span>
                        </button>
                        <button type="button" class="btn btn-warning cancel">
                            <i class="icon-minus-circle"></i>
                            <span><?php echo JText::_('PLG_SYSTEM_ETDGALLERYRENDER_UPLOAD_CANCEL') ?></span>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default select">
                            <input type="checkbox">
                            <span class="on"><?php echo JText::_('PLG_SYSTEM_ETDGALLERYRENDER_UPLOAD_SELECT_ALL') ?></span>
                            <span class="off hide"><?php echo JText::_('PLG_SYSTEM_ETDGALLERYRENDER_UPLOAD_SELECT_NONE') ?></span>
                        </button>
                        <button type="button" class="btn btn-danger delete" disabled>
                            <i class="icon-trash"></i>
                            <span><?php echo JText::_('PLG_SYSTEM_ETDGALLERYRENDER_UPLOAD_DELETE') ?></span>
                        </button>
                    </div>
                </div>

                <span class="fileupload-process"></span>
            </div>

            <div class="fileupload-progress fade">
                <div class="progress progress-striped active" role="progressbar">
                    <div class="progress-bar bar" style="width:0"></div>
                </div>
                <div class="progress-extended">&nbsp;</div>
            </div>

        </fieldset>
        <ul class="thumbnails files">

        </ul>
    </div>

    <script id="template-upload" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
        <li class="template-upload span2 fade" data-name="{%=file.name%}">
            <div class="thumbnail">
                <div class="preview-container">
                    <div class="preview"></div>
                </div>
                <div class="form-group">
                    <input type="text" name="title" placeholder="Titre" class="input-block-level">
                </div>
                <div class="form-group">
                    <textarea rows="2" name="description" placeholder="Description" class="input-block-level"></textarea>
                </div>
                <div class="form-group">
                    <div class="radio">
                      <label>
                        <input type="radio" name="featured" value="1">
                        <?php echo JText::_('PLG_SYSTEM_ETDGALLERYRENDER_UPLOAD_FEATURED') ?>
                      </label>
                    </div>
                </div>
                <div class="form-group">
                    <strong class="error label label-important"></strong>
                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                        <div class="bar progress-bar progress-bar-success" style="width:0%;"></div>
                    </div>
                </div>
                <div class="actions">
                    <div class="btn-group">
                    <button type="button" class="btn btn-default crop" data-name="{%=file.name%}"><i class="icon-expand"></i></button>
                    {% if (!i && !o.options.autoUpload) { %}
                    <button type="button" class="btn btn-success start" disabled><i class="icon-upload"></i></button>
                    {% } %}
                    {% if (!i) { %}
                    <button type="button" class="btn btn-danger cancel"><i class="icon-delete"></i></button>
                    {% } %}
                    </div>
                </div>
            </div>
        </li>
    {% } %}
    </script>
    <script id="template-download" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
        <li class="template-download span2 fade">
            <div class="thumbnail">
                <div class="preview-container">
                    <div class="preview">
                    {% if (file.thumbnailUrl) { %}
                        <img src="{%=file.thumbnailUrl%}" class="img-responsive">
                    {% } %}
                    </div>
                </div>
                <div class="form-group">
                    <strong class="error label label-important"></strong>
                </div>
                <div class="actions">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default select">
                            <input type="checkbox">
                        </button>
                        <button type="button" class="btn btn-primary featured{% if (file.featured == "1") { %} active{% } %}" data-id="{%=file.id%}">
                            <i class="icon-star{% if (file.featured == "0") { %} hide{% } %}"></i>
                            <i class="icon-star-empty{% if (file.featured == "1") { %} hide{% } %}"></i>
                            <input type="radio" name="featured" id="featured_{%=file.id%}" autocomplete="off"{% if (file.featured == "1") { %} checked{% } %}>
                        </button>
                    <?php /*<button type="button" class="btn btn-primary edit"><i class="icon-pencil"></i></button>
                    <button type="button" class="btn btn-success save hide" data-id="{%=file.id%}"><i class="icon-checkmark"></i></button>*/ ?>
                    {% if (file.deleteUrl) { %}
                        <button type="button" class="btn btn-danger delete" data-type="POST" data-data="<?php echo JSession::getFormToken() ?>=1"  data-url="{%#file.deleteUrl%}"><i class="icon-trash"></i></button>
                    {% } %}
                    </div>
                </div>
            </div>
            <input type="hidden" name="etdgallery[]" value="{%=file.id%}">
        </li>
    {% } %}
    </script>
<?php echo JHtml::_('bootstrap.endTab'); ?>