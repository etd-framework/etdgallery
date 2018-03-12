<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_etdgallery
 *
 * @version     1.1.12
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

abstract class ModEtdGalleryHelper {

    private static $cats = array();

    /**
     * Retrieve a list of article
     *
     * @param   \Joomla\Registry\Registry &$params module parameters
     *
     * @return  mixed
     */
    public static function getList(&$params) {

        // Get the dbo
        $db = JFactory::getDbo();

        // Get an instance of the generic articles model
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_etdgallery/models', 'EtdGalleryModel');
        $model = JModelLegacy::getInstance('Images', 'EtdGalleryModel', array('ignore_request' => true));

        // Set application parameters in model
        $app       = JFactory::getApplication();
        $appParams = $app->getParams();
        $model->setState('params', $appParams);

        // Set the filters based on the module params
        $model->setState('list.start', 0);
        $model->setState('list.limit', (int)$params->get('count', 10));
        $model->setState('filter.state', 1);

        // Filtre par tag
        $tag_id = $params->get('tag_id', array(), 'array');
        if (!empty($tag_id)) {
            $model->setState('filter.tag_id', $tag_id);
        }

        // Filtre par type
        $type = $params->get('type');
        if (!empty($type)) {
            $model->setState('filter.type', $type);
        }

        // Filtre par article
        if ($params->get('article_only', '0') && $app->input->getCmd('option') == 'com_content' && $app->input->getCmd('view') == 'article') {
            $model->setState('filter.article_id', $app->input->getInt('id'));
        }

        // Set ordering
        $order_map = array(
            'ordering'  => 'a.ordering',
            'm_dsc'     => 'a.modified DESC, a.created',
            'mc_dsc'    => 'CASE WHEN (a.modified = ' . $db->quote($db->getNullDate()) . ') THEN a.created ELSE a.modified END',
            'c_dsc'     => 'a.created',
            'p_dsc'     => 'a.publish_up',
            'pdown_asc' => 'a.publish_down',
            'random'    => 'RAND()',
        );
        $ordering  = \Joomla\Utilities\ArrayHelper::getValue($order_map, $params->get('ordering'), 'a.publish_up');
        $dir       = 'DESC';

        if (in_array($params->get('ordering'), array("ordering", "pdown_asc"))) {
            $dir = "ASC";
        }

        $model->setState('list.ordering', $ordering);
        $model->setState('list.direction', $dir);

        $model->setState('list.start', 0);
        $model->setState('list.limit', $params->get('count', '10'));

        $items = $model->getItems();

        return $items;

    }
}
