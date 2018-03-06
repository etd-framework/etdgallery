<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_etdgallery
 *
 * @version		1.1.1
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class JFormFieldSizes extends JFormField {

    /**
     * The form field type.
     *
     * @var        string
     * @since   1.6
     */
    protected $type = 'Sizes';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput() {

        JHtml::_('jquery.framework');
        JHtml::_('bootstrap.framework');

        $doc   = JFactory::getDocument();
        $html  = array();
        $value = $this->value;

        if (is_string($value) && !empty($value)) {
            $value = json_decode($value);
        }

        $doc->addStyleSheet(JUri::root(true)."/media/com_etdgallery/css/sizes.css");
        $doc->addScript(JUri::root(true)."/media/com_etdgallery/js/jquery-serializeForm.min.js");
        $doc->addScript(JUri::root(true)."/media/com_etdgallery/js/sizes.js");
        $doc->addScriptDeclaration("jQuery(document).ready(function() {
    initSizes('" . $this->id . "', " . (!empty($value) ? count($value) : '0') . ");
});");

        $html[] = '<div class="sizes" id="' . $this->id . '">';

        if (!empty($value)) {
            foreach ($value as $i => $size) {

                $html[] = '<div class="size" id="' . $this->id .'_' . $i . '">';
                $html[] = '<div class="inner">';
                $html[] = '<input type="text" name="tmp_sizes[' . $i . '][name]" class="span3" value="' . $size->name . '" placeholder="Nom"> ';
                $html[] = '<input type="text" name="tmp_sizes[' . $i . '][height]" class="span3" value="' . $size->height . '" placeholder="Hauteur max"> ';
                $html[] = '<input type="text" name="tmp_sizes[' . $i . '][width]" class="span3" value="' . $size->width . '" placeholder="Largeur max"> ';
                $html[] = '<label class="checkbox span2"><input type="checkbox" value="1" name="tmp_sizes[' . $i . '][crop]"' . ($size->crop ? ' checked' : '') . '> Rogner</label> ';
                $html[] = '<label class="span1 text-right"><span class="delete"><span class="icon-trash"></span></span></label>';
                $html[] = '</div>';
                $html[] = '</div>';

            }
        }

        $html[] = '</div>';
        $html[] = '<button type="button" class="btn" id="' . $this->id . '_add"><span class="icon-plus"></span> Ajouter une taille</button>';
        $html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value) . '" id="' . $this->id . '_hidden">';

        return implode($html);

    }

}
