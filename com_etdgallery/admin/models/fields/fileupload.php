<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_etdgallery
 *
 * @version     1.1.13
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class JFormFieldFileUpload extends JFormField {

    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'FileUpload';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput() {

        $html       = array();
        $doc        = JFactory::getDocument();
        $config     = JComponentHelper::getParams('com_etdgallery');
        $sizes      = json_decode($config->get('sizes', '[]'));
        $admin_size = $sizes->{$config->get('admin_size', 'thumb')};
        $filename   = $this->form->getValue('filename');
        $dirname    = $this->form->getValue('dirname');
        $id         = $this->form->getValue('id');

        $doc->addStyleSheet(JUri::root(true) . "/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/css/jquery.fileupload.css");
        $doc->addStyleSheet(JUri::root(true)."/media/com_etdgallery/vendor/Jcrop-2.0.3/css/Jcrop.min.css");
        $doc->addScript(JUri::root(true) . "/media/com_etdgallery/vendor/JavaScript-Load-Image-1.14.0/js/load-image.all.min.js");
        $doc->addScript(JUri::root(true) . "/media/com_etdgallery/vendor/JavaScript-Canvas-to-Blob-2.2.0/js/canvas-to-blob.min.js");
        $doc->addScript(JUri::root(true) . "/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/js/jquery.fileupload.js");
        $doc->addScript(JUri::root(true) . "/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/js/jquery.fileupload-process.js");
        $doc->addScript(JUri::root(true) . "/media/com_etdgallery/vendor/jQuery-File-Upload-9.11.2/js/jquery.fileupload-image.js");
        $doc->addScript(JUri::root(true) . "/media/com_etdgallery/vendor/Jcrop-2.0.3/js/Jcrop.min.js");

        $options = array(
            'dataType'           => 'json',
            'acceptFileTypes'    => '/(\\.|\\/)(gif|jpe?g|png)$/i',
            'maxFileSize'        => $config->get('max_upload_size', 100000),
            'previewMaxWidth'    => $admin_size->width,
            'previewMaxHeight'   => $admin_size->height,
            'previewCrop'        => $admin_size->crop,
            'disableImageResize' => '/Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent)',
            'replaceFileInput'   => false
        );

        $doc->addScriptDeclaration("jQuery(function ($) {

    var sizes         = " . $config->get('sizes', '[]') . ",
        coords        = {},
        currentCoords = {},
        imgDataUri    = null,
        currentName   = '',
        Jcrop         = null,
        cropping      = false;

    function _blobToBase64(blob, cb) {

        var reader = new FileReader();
        reader.onload = function(evt) {
            cb(evt.target.result);
        };
        reader.readAsDataURL(blob);

    }

    $(document).ready(function() {
        $('#" . $this->id . "')
            .fileupload(" . json_encode($options) . ")
            .on('fileuploadadd', function (e, data) {
                $('#preview').find('img, canvas').remove();
                coords[data.files[0].name] = {};
                _blobToBase64(data.files[0], function(dataUri) {
                    imgDataUri = dataUri;
                });
                \$('#" . $this->id . "_crop').removeClass('hide');
            }).on('fileuploadprocessalways', function (e, data) {
                 var file = data.files[0];
                 if (file.preview) {
                    $('#preview').append(file.preview);
                 }
            });

        var \$modal  = \$('<div class=\"modal hide fade\" style=\"max-height:none;width:800px;margin-left:-400px\"><div class=\"modal-header\"><button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button><h3 style=\"display:inline-block;margin-right:10px\">Retailler l\'image</h3> <select class=\"form-control\" name=\"size\" style=\"margin:0\"></select> <a href=\"#\" class=\"btn btn-primary\"><i class=\"icon-white icon-save\"></i> Appliquer</a></div><div class=\"modal-body\" style=\"max-height:none;width:800px;padding:0\"><div class=\"img-container\" style=\"position:relative\"></div></div></div>');
        var \$select = \$modal.find('select');

        \$.each(sizes, function() {
            if (this.crop == \"1\") {
                \$select.append('<option value=\"' + this.name + '\">' + this.name + ' (' + this.width + 'x' + this.height + ')</option>')
            }
        });

        \$modal.appendTo('body');

        \$modal.modal({
            show: false
        });

        \$('#" . $this->id . "_crop').on('click', function() {

            cropping = false;
            currentCoords = {};
            \$modal.find('.img-container').empty();

            var \$img = \$('<img />');
            currentName = \$(this).data('name');

            if (coords[currentName] && !\$.isEmptyObject(coords[currentName])) {
                currentCoords = coords[currentName];
            }

            \$img.on('load', function() {

                \$img.attr('width', this.width);
                \$img.attr('height', this.height);
                \$modal.find('.img-container').append(\$img);

                \$img.Jcrop({
                    canDelete: false,
                    canResize: false,
                    canDrag: true,
                    canSelect: true
                });

                Jcrop = \$img.Jcrop('api');

                Jcrop.container.on('cropmove', function() {
                    if (cropping && Jcrop.ui && Jcrop.ui.selection && Jcrop.ui.selection.last) {
                        currentCoords[\$select.val()] = Jcrop.ui.selection.last;
                    }
                });

                var img  = Jcrop.opt.imgsrc;

                var w = img.naturalWidth || img.width,
                    h = img.naturalHeight || img.height;

                // On désactive les formats impossibles à rogner.
                \$select.find('option').each(function() {
                    var \$option = \$(this);
                    var size = sizes[\$option.attr('value')];
                    \$option.prop('disabled', (w < size.width && h < size.height));
                });

                \$select.trigger('change');

            }).attr('src', imgDataUri);

            \$modal.modal('show');

        });

        \$modal.on('shown', function() {
            cropping = true;
        });

        \$modal.on('hidden', function() {
            cropping = false;
        });

        \$select.on('change', function() {

            var sizeName = \$select.val();

            if (sizeName) {

                var size = sizes[sizeName],
                    img  = Jcrop.opt.imgsrc;

                var w = img.naturalWidth || img.width,
                    h = img.naturalHeight || img.height;

                var sizeW = parseInt(size.width),
                    sizeH = parseInt(size.height),
                    imgRatio  = w / h;

                var options = {
                    canSelect: true,
                    allowSelect: true,
                    minSize: [sizeW, sizeH],
                    maxSize: [sizeW, sizeH],
                    setSelect: [0, 0, sizeW, sizeH]
                };

                if (currentCoords[size.name]) {
                    var c = currentCoords[size.name];
                    options['setSelect'] = [c.x, c.y, sizeW, sizeH];
                }

                if (w < h) {
                    if (sizeW < sizeH) {
                        options['boxHeight'] = sizeH;
                        options['boxWidth']  = sizeH + sizeW * imgRatio;
                    } else if (sizeW > sizeH) {
                        options['boxWidth']  = sizeW;
                        options['boxHeight'] = sizeW + sizeH / imgRatio;
                    } else {
                        options['boxWidth']  = sizeW;
                        options['boxHeight'] = sizeH / imgRatio;
                    }
                } else if (w > h) {
                    if (sizeW > sizeH) {
                        options['boxWidth']  = sizeW;
                        options['boxHeight'] = sizeW + sizeH / imgRatio;
                    } else if (sizeW < sizeH) {
                        options['boxHeight'] = sizeH;
                        options['boxWidth']  = sizeH + sizeW * imgRatio;
                    } else {
                        options['boxWidth']  = sizeW * imgRatio;
                        options['boxHeight'] = sizeH;
                    }
                } else {
                    if (sizeW != sizeH) {
                        options['boxWidth']  = sizeW;
                        options['boxHeight'] = sizeW + sizeH / imgRatio;
                    } else {
                        options['boxWidth']  = sizeW;
                        options['boxHeight'] = sizeH;
                    }
                }

                Jcrop.setOptions(options);
                Jcrop.applySizeConstraints();

            }

        });

        \$modal.find('.btn-primary').on('click', function() {

            if (!\$.isEmptyObject(currentCoords)) {
                \$('#" . $this->id . "_crop_hidden').val(JSON.stringify(currentCoords));
            }

            \$modal.modal(\"hide\");
        });


    });
});");

        if ($filename && $dirname && $id) {
            $html[] = '<div id="preview"><img width="' . $admin_size->width . '" height="' . $admin_size->height . '" src="' . JUri::root() . $dirname . '/' . $id . '_' . $config->get('admin_size') . '_' . $filename . '" alt=""></div>';
        } else {
            $html[] = '<div id="preview"><img width="' . $admin_size->width . '" height="' . $admin_size->height . '" src="" alt=""></div>';
        }
        $html[] = '<br>';
        $html[] = '<span class="btn btn-success fileinput-button">';
        $html[] = '<i class="icon-plus"></i>';
        $html[] = '<span>' . JText::_('COM_ETDGALLERY_UPLOAD_SELECT_FILE') . '</span>';
        $html[] = '<input id="' . $this->id . '" type="file" name="' . $this->name . '">';
        $html[] = '</span>';
        $html[] = '&nbsp;<span class="btn btn-default crop hide" id="' . $this->id . '_crop">';
        $html[] = '<i class="icon-expand"></i>';
        $html[] = '<span>' . JText::_('COM_ETDGALLERY_UPLOAD_CROP') . '</span>';
        $html[] = '</span>';
        $html[] = '<input id="' . $this->id . '_crop_hidden" type="hidden" name="' . $this->getName('crop'). '">';

        return implode($html);

    }

}