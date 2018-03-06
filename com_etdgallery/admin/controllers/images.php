<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_etdgallery
 *
 * @version		1.1.0
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class EtdGalleryControllerImages extends JControllerAdmin {

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  JModelLegacy
     *
     * @since   1.6
     */
    public function getModel($name = 'Image', $prefix = 'EtdGalleryModel', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, $config);
    }
}
