<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_etdgallery
 *
 * @version     1.1.1
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class EtdGalleryModelCategory extends JModelList {

    public function __construct($config = array()) {

        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id',
                'a.id',
                'title',
                'a.title',
                'state',
                'a.state',
                'ordering',
                'a.ordering',
                'a.publish_up',
                'publish_up',
                'a.publish_down',
                'publish_down',
                'a.created',
                'created',
                'hits',
                'a.hits'
            );
        }

        parent::__construct($config);
    }

    public function getItems() {

        $items = parent::getItems();

        if ($items) {

            $config = JComponentHelper::getParams('com_etdgallery');
            $sizes  = json_decode($config->get('sizes', '[]'));

            foreach ($items as $item) {

                if ($item->type == "image") {
                    $item->src = new stdClass();

                    foreach($sizes as $size) {
                        $item->src->{$size->name} = $item->dirname . "/" . $item->id . "_" . $size->name . "_" . $item->filename;
                    }
                }

            }
        }

        return $items;
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  string    An SQL query
     *
     * @since   1.6
     */
    protected function getListQuery() {

        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select required fields from the categories.
        $query->select($this->getState('list.select', 'a.*, c.alias AS cat_alias'))
            ->from($db->quoteName('#__etdgallery') . ' AS a')
            ->leftJoin($db->quoteName('#__categories') . ' AS C ON c.id = a.catid')
            ->where('a.catid = ' . $db->quote($this->getState('category.id')));

        // Filter by state
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.state = ' . (int)$state);
        }

        // Filter by type
        $type = $this->getState('filter.type');
        if (in_array($type, array('image', 'video'))) {
            $query->where('a.type = ' . $db->quote($type));
        }

        // Filtre par article
        $article_id = $this->getState('filter.article_id');
        if (is_numeric($article_id)) {
            $query->where('a.article_id = ' . (int)$article_id);
        } elseif (is_array($article_id)) {
            \Joomla\Utilities\ArrayHelper::toInteger($article_id);
            $query->where('a.article_id IN (' . implode(",", $article_id) . ')');
        }

        // Filter by tag
        $tag_id = $this->getState('filter.tag_id');
        if (isset($tag_id)) {
            $query->leftJoin('#__contentitem_tag_map AS b ON b.content_item_id = a.id AND b.type_alias = ' . $db->quote('com_etdgallery.image'));

            if (is_numeric($tag_id)) {
                $query->where('b.tag_id = ' . (int)$tag_id);
            } elseif (is_array($tag_id)) {
                \Joomla\Utilities\ArrayHelper::toInteger($tag_id);
                $query->where('b.tag_id IN (' . implode(",", $tag_id) . ')');
            }

        }

        // Define null and now dates
        $nullDate	= $db->quote($db->getNullDate());
        $nowDate	= $db->quote(JFactory::getDate()->toSql());

        // Filter by start and end dates.
        $query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
              ->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string $ordering  An optional ordering field.
     * @param   string $direction An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null) {

        $app    = JFactory::getApplication();
        $params = $app->getParams();

        $pk = $app->input->getInt('id');
        $this->setState('category.id', $pk);

        // Load state from the request.
        $tid = $app->input->get('tag_id', '', 'raw');
        $this->setState('filter.tag_id', $tid);

        // Load state from the request.
        $aid = $app->input->get('article_id');
        $this->setState('filter.article_id', $aid);

        // Load state from the request.
        $type = $app->input->getCmd('type');
        $this->setState('filter.type', $type);

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $params->get('list_limit', $app->get('list_limit')), 'uint');
        $this->setState('list.limit', $limit);

        $limitstart = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        $orderCol = $app->input->get('filter_order', $params->get('list_ordering', 'ordering'));
        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'ordering';
        }
        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->input->get('filter_order_Dir', $params->get('list_direction', 'ASC'));
        if (!in_array(strtoupper($listOrder), array(
            'ASC',
            'DESC',
            ''
        ))
        ) {
            $listOrder = 'ASC';
        }
        $this->setState('list.direction', $listOrder);

        $user = JFactory::getUser();
        if ((!$user->authorise('core.edit.state', 'com_etdgallery')) && (!$user->authorise('core.edit', 'com_etdgallery'))) {
            // Limit to published for people who can't edit or edit.state.
            $this->setState('filter.state', 1);

            // Filter by start and end dates.
            $this->setState('filter.publish_date', true);
        }

        // Load the parameters
        $this->setState('params', $params);
    }
}
