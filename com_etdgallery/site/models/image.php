<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_etdgallery
 *
 * @version		1.1.0
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class EtdGalleryModelImage extends JModelItem {

    /**
     * Increment the hit counter for the image.
     *
     * @param   integer  $pk  Optional primary key of the image to increment.
     *
     * @return  boolean  True if successful; false otherwise and internal error set.
     */
    public function hit($pk) {

        JTable::addIncludePath(JPATH_ADMINISTRATOR."/components/com_etdgallery/tables");
        $table = JTable::getInstance('Image', 'EtdGalleryTable');
        $table->load($pk);
        $table->hit($pk);

        return $table->hits;
    }

}