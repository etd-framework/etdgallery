var index;

function initSizes(id, currentIndex) {

    var $accordion = jQuery('#' + id);
    index = currentIndex;

    jQuery('#' + id +'_add').on('click', function(e) {
        index++;
        var html = '<div class="size" id="' + id +'_' + index + '">';
        html += '<div class="inner">';
        html += '<div class="row-fluid">';
        html += '<input type="text" name="tmp_sizes[' + index + '][name]" class="span3" value="" placeholder="Nom"> ';
        html += '<input type="text" name="tmp_sizes[' + index + '][height]" class="span3" value="" placeholder="Hauteur max"> ';
        html += '<input type="text" name="tmp_sizes[' + index + '][width]" class="span3" value="" placeholder="Largeur max"> ';
        html += '<label class="checkbox span2"><input type="checkbox" value="1" name="tmp_sizes[' + index + '][crop]"> Rogner</label>';
        html += '<label class="span1"><span class="delete"><span class="icon-trash"></span></span></label>';
        html += '</div>';
        html += '</div>';

        $accordion.append(html);

    });

    jQuery(document).on('change', '.sizes input', function() {
        updateValue(id);
    });

    jQuery(document).on('click', '.sizes .delete', function() {
        if (confirm('Êtes-vous sûr ?')) {
            jQuery(this).parents('.size').remove();
            updateValue(id);
        }
    });
}

function updateValue(id) {
    var data = {},
        inputs = jQuery('.sizes > div').serializeForm();
    if (inputs) {
        jQuery.each(inputs.tmp_sizes, function() {
            if (!this.crop) {
                this.crop = '0';
            }
            data[this.name] = this;
        });
    }
    console.log(JSON.stringify(data));
    jQuery('#' + id +'_hidden').val(JSON.stringify(data));
}