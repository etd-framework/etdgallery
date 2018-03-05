(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {

    // PRIVATE METHODS
    // =========================

    function _updateArticle(intro, full) {
        $('#jform_images_image_intro').val(intro);
        $('#jform_images_image_fulltext').val(full);
    }

    function _blobToBase64(blob, cb) {

        var reader = new FileReader();
        reader.onload = function(evt) {
            cb(evt.target.result);
        };
        reader.readAsDataURL(blob);

    }

    // CLASS DEFINITION
    // =========================

    var EtdGallery = function (element, options) {

        this.$element      = $(element);
        this.$files        = this.$element.find('.files');
        this.$buttonsBar   = this.$element.find('.fileupload-buttonbar');
        this.options       = options;
        this.$modal        = null;
        this.coords        = {};
        this.currentCoords = {};
        this.uris          = {};

        this.initFileUpload()
            .makeSortable()
            .initFeatured()
            .initDelete()
            .initCrop()
            .loadImages()

    };

    EtdGallery.DEFAULTS = {
        maxFileSize: 10000,
        previewMaxWidth: 190,
        previewMaxHeight: 190,
        previewCrop: true,
        token: '',
        articleId: 0,
        sortableOpacity: 0.5,
        urls: {
            upload: '',
            order: '',
            featured: '',
            images: ''
        }
    };

    EtdGallery.prototype.initFileUpload = function() {

        var self = this;

        this.$element.fileupload({
            url: this.options.urls.upload,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            maxFileSize: this.options.maxFileSize,
            previewMaxWidth: this.options.previewMaxWidth,
            previewMaxHeight: this.options.previewMaxHeight,
            previewCrop: this.options.previewCrop,
            formData: function() {

                var $context = $(this.context);

                console.log($context);

                var data = [
                    {
                        name: self.options.token,
                        value: '1'
                    },
                    {
                        name: 'article_id',
                        value: self.options.articleId
                    }
                ];

                if (self.coords[$context.data('name')]) {
                    data.push({
                        name: 'crop',
                        value: JSON.stringify(self.coords[$context.data('name')])
                    });
                }

                return $.merge(data, $context.find('input, textarea').serializeArray());

            }
        }).on('fileuploaddone', function (e, data) {

            if (data.error) {
                alert(data.message);
            } else {
                var file = data.result.files[0];
                if (file.featured) {
                    var $btns = $('button.featured').not('[data-id="' + file.id + '"]');
                    $btns.find('.icon-star').addClass('hide');
                    $btns.find('.icon-star-empty').removeClass('hide');
                    $btns.find('input').prop('checked', false);
                    _updateArticle(file.introUrl, file.fullUrl);
                }
            }

        });

        return this;

    };

    EtdGallery.prototype.initFeatured = function() {
        
        var self = this;

        $(document).on('click', 'button.featured', function() {
            var $btn  = $(this),
                $btns = $('button.featured'),
                $chk  = $btn.find('input');

            if ($chk.is(':checked')) {

            $chk.prop('checked', false);
                $btn.removeClass('active');
                $btn.find('.icon-star').addClass('hide');
                $btn.find('.icon-star-empty').removeClass('hide');

            } else {

            $btns.removeClass('active')
                $btns.find('.icon-star').addClass('hide');
                $btns.find('.icon-star-empty').removeClass('hide');
                $btns.find('input').prop('checked', false);

                $btn.find('.icon-star-empty').addClass('hide');
                $btn.find('.icon-star').removeClass('hide');
                $btn.addClass('active');
                $chk.prop('checked', true);

            }

            var data = {
                id: $btn.data('id'),
                featured: $chk.is(':checked') ? '1': '0',
                article_id: self.options.articleId
            };

            data[self.options.token] = '1';

            $.ajax({
                url: self.options.urls.featured,
                dataType: 'json',
                context: this,
                method: 'POST',
                data: data
            }).done(function (result) {

                if (result.error) {
                    alert(result.message);
                } else {
                    _updateArticle(result.introUrl, result.fullUrl);
                }

            });

        });

        return this;

    };

    EtdGallery.prototype.initDelete = function() {

        var self = this,
            $deleteBtn = this.$buttonsBar.find('.delete'),
            $selectBtn = this.$buttonsBar.find('.select');

        // On supprime le handler donné par fileupload.
        $deleteBtn.off('click');

        $selectBtn.on('click', function() {

            var $btn = $(this),
                $chk = $btn.find('input');

            if ($chk.is(':checked')) {

                $btn.removeClass('active');
                $btn.find('.on').removeClass('hide');
                $btn.find('.off').addClass('hide');
                $chk.prop('checked', false);
                $deleteBtn.prop('disabled', true);
                self.$files.find('.select').removeClass('active').find('input').prop('checked', false);

            } else {

                $btn.addClass('active');
                $btn.find('.on').addClass('hide');
                $btn.find('.off').removeClass('hide');
                $chk.prop('checked', true);
                $deleteBtn.prop('disabled', false);
                self.$files.find('.select').addClass('active').find('input').prop('checked', true);

            }

        });

        $deleteBtn.on('click', function() {

            self.$files.find('.select input:checked')
                .closest('.template-download')
                .find('.delete').trigger('click');

        });

        $(document).on('click', '.files button.select', function() {

            var $btn  = $(this),
                $btns = self.$files.find('.select'),
                $chk  = $btn.find('input');

            if ($chk.is(':checked')) {

                $btn.removeClass('active');
                $chk.prop('checked', false);

            } else {

                $btn.addClass('active');
                $chk.prop('checked', true);

            }

            $deleteBtn.prop('disabled', ($btns.find('input:checked').length == 0));

        });

        return this;

    };

    EtdGallery.prototype.initCrop = function() {

        var self = this;

        var $modal  = $('<div class="modal hide fade" style="max-height:none;width:800px;margin-left:-400px"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3 style="display:inline-block;margin-right:10px">Retailler l\'image</h3> <select class="form-control" name="size" style="margin:0"></select> <a href="#" class="btn btn-primary"><i class="icon-white icon-save"></i> Appliquer</a></div><div class="modal-body" style="max-height:none;width:800px;padding:0"><div class="img-container" style="position:relative"></div></div></div>');
        var $select = $modal.find('select');

        $.each(this.options.sizes, function() {
            if (this.crop == "1") {
                $select.append('<option value="' + this.name + '">' + this.name + ' (' + this.width + 'x' + this.height + ')</option>')
            }
        });

        $modal.appendTo('body');

        $modal.modal({
            show: false
        });

        this.$modal = $modal;

        this.$element.on('fileuploadadd', function(e, data) {

            // On initialise le tableau des coordonnées.
            self.coords[data.files[0].name] = {};

            // On récupère l'image en base64.
            _blobToBase64(data.files[0], function(dataUri) {
                self.uris[data.files[0].name] = dataUri;
            });

        });

        $(document).on('click', 'button.crop', function() {

            self.cropping = false;
            self.currentCoords = {};
            self.$modal.find('.img-container').empty();

            var $img = $('<img />');
            self.currentName = $(this).data('name');
            var dataUri = self.uris[self.currentName];

            if (self.coords[self.currentName] && !$.isEmptyObject(self.coords[self.currentName])) {
                self.currentCoords = self.coords[self.currentName];
            }

            $img.on('load', function() {

                $img.attr('width', this.width);
                $img.attr('height', this.height);
                self.$modal.find('.img-container').append($img);

                $img.Jcrop({
                    canDelete: false,
                    canResize: false,
                    canDrag: true,
                    canSelect: true
                });

                self.Jcrop = $img.Jcrop('api');

                self.Jcrop.container.on('cropmove', function() {
                    if (self.cropping && self.Jcrop.ui && self.Jcrop.ui.selection && self.Jcrop.ui.selection.last) {
                        console.log('move', self.Jcrop.ui.selection.last);
                        self.currentCoords[$select.val()] = self.Jcrop.ui.selection.last;
                    }
                });

                var img  = self.Jcrop.opt.imgsrc;

                var w = img.naturalWidth || img.width,
                    h = img.naturalHeight || img.height;

                // On désactive les formats impossibles à rogner.
                $select.find('option').each(function() {
                    var $option = $(this);
                    var size = self.options.sizes[$option.attr('value')];
                    $option.prop('disabled', (w < size.width && h < size.height));
                });

                $select.trigger('change');

            }).attr('src', dataUri);

            self.$modal.modal('show');

        });

        $modal.on('shown', function() {
            self.cropping = true;
        });

        $modal.on('hidden', function() {
            self.cropping = false;
        });

        $select.on('change', function() {

            var sizeName = $select.val();

            if (sizeName) {

                var size = self.options.sizes[sizeName],
                    img  = self.Jcrop.opt.imgsrc;

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

                if (self.currentCoords[size.name]) {
                    var c = self.currentCoords[size.name];
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

                self.Jcrop.setOptions(options);
                self.Jcrop.applySizeConstraints();

            }

        });

        $modal.find('.btn-primary').on('click', function() {

            if (!$.isEmptyObject(self.currentCoords)) {
                console.log('primary', self.currentCoords);
                self.coords[self.currentName] = self.currentCoords;
            }

            $modal.modal("hide");
        });

        return this;

    };

    EtdGallery.prototype.makeSortable = function() {

        var self = this,
            data = {};

        data[this.options.token] = '1';

        this.$files.sortable({
            opacity: this.options.sortableOpacity,
            update: function() {

                var data = self.getOrdering();

                $.ajax({
                    url: self.options.urls.order,
                    dataType: 'text',
                    context: self.$element[0],
                    method: 'POST',
                    data: data
                }).always(function () {
                    $(this).removeClass('fileupload-processing');
                });

            }
        });

        return this;

    };

    EtdGallery.prototype.loadImages = function() {

        if (this.options.articleId) {

            var data = {};

            data['article_id']       = this.options.articleId;
            data[this.options.token] = '1';

            this.$element.addClass('fileupload-processing');

            $.ajax({
                url: this.options.urls.images,
                dataType: 'json',
                context: this.$element[0],
                method: 'POST',
                data: data
            }).always(function () {
                $(this).removeClass('fileupload-processing');
            }).done(function (result) {
                $(this).fileupload('option', 'done').call(this, $.Event('done'), {result: result});
            });

        }

        return this;

    };

    EtdGallery.prototype.getOrdering = function() {

        var data = {
            cid: [],
            order: []
        };

        $.each(this.$files.find('.template-download').not('.ui-sortable-placeholder'), function(index) {
            data.cid.push($(this).find('input[name=\"etdgallery[]\"]').val());
            data.order.push(index+1);
        });

        return data;

    };

    // PLUGIN DEFINITION
    // ==========================

    function Plugin(option) {
        return this.each(function () {

            var $this   = $(this);
            var data    = $this.data('etd.gallery');
            var options = $.extend({}, EtdGallery.DEFAULTS, $this.data(), typeof option == 'object' && option);
            var action  = typeof option == 'string' ? option : null;

            if (!data) {
                $this.data('etd.gallery', (data = new EtdGallery(this, options)));
            }

            if (action) {
                data[action]();
            }
        })
    }

    $.fn.etdgallery             = Plugin;
    $.fn.etdgallery.Constructor = EtdGallery;

}));