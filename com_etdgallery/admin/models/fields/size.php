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

JFormHelper::loadFieldType('List');

class JFormFieldSize extends JFormFieldList {

    /**
     * The form field type.
     *
     * @var        string
     * @since   1.6
     */
    protected $type = 'Size';

    protected function getOptions() {

        $options = array();
        $config  = JComponentHelper::getParams('com_etdgallery');

        if ($config->exists('sizes')) {

            $sizes = json_decode($config->get('sizes', '[]'));

            foreach ($sizes as $size) {
                $options[] = JHtml::_('select.option', $size->name, $size->name, 'value', 'text');
            }

        }

        return array_merge(parent::getOptions(), $options);
    }

}
