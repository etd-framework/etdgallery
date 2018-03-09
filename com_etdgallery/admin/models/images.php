<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_etdgallery
 *
 * @version		1.1.3
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class EtdGalleryModelImages extends JModelList {

    /**
     * Constructor.
     *
     * @param   array $config An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array()) {

        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'type', 'a.type',
                'filename', 'a.filename',
                'dirname', 'a.dirname',
                'title', 'a.title',
                'state', 'a.state',
                'featured', 'a.featured',
                'ordering', 'a.ordering',
                'article_id', 'a.article_id',
                'article_title',
                'created',
                'a.created'
            );
        }

        parent::__construct($config);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery() {

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState('list.select', 'a.id AS id, a.type as type, a.catid AS catid, a.title AS title, a.filename as filename, a.dirname as dirname, a.article_id AS article_id, a.state AS state,
            a.ordering AS ordering, a.created AS created, a.publish_up as publish_up, a.publish_down as publish_down, b.title AS article_title, d.alias as cat_alias'));
        $query->from($db->quoteName('#__etdgallery') . ' AS a');

        // Join over the articles.
        $query->join('LEFT', '#__content AS b ON b.id = a.article_id');

        // Join over the categories.
        $query->join('LEFT', '#__categories AS d ON d.id = a.catid');

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('a.state = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(a.state IN (0, 1))');
        }

        // Filter by category.
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId) && $categoryId > 0) {
            $query->where('a.catid = ' . (int) $categoryId);
        }

        // Filter by article.
        $articleId = $this->getState('filter.article_id');

        if (is_numeric($articleId)) {
            $query->where('a.article_id = ' . (int) $articleId);
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(a.title LIKE ' . $search . ')');
            }
        }

        // Filter by tag
        $tag = $this->getState('filter.tag');
        if (isset($tag)) {
            $query->leftJoin('#__contentitem_tag_map AS c ON c.content_item_id = a.id AND c.type_alias = ' . $db->quote('com_etdgallery.image'));

            if (is_numeric($tag)) {
                $query->where('c.tag_id = ' . (int)$tag);
            } elseif (is_array($tag)) {
                \Joomla\Utilities\ArrayHelper::toInteger($tag);
                $query->where('c.tag_id IN (' . implode(",", $tag) . ')');
            }

        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'ordering');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        if ($orderCol == 'ordering' || $orderCol == 'article_title') {
            $orderCol = 'b.title ' . $orderDirn . ', a.ordering';
        }

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string $id A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '') {

        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.article_id');
        $id .= ':' . $this->getState('filter.catid');
        $id .= ':' . $this->getState('filter.tag');

        return parent::getStoreId($id);
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable    A database object
     *
     * @since   1.6
     */
    public function getTable($type = 'Image', $prefix = 'EtdGalleryTable', $config = array()) {

        return JTable::getInstance($type, $prefix, $config);
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

        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
        $this->setState('filter.published', $published);

        $tag = $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag');
        $this->setState('filter.tags', $tag);

        $categoryId = $this->getUserStateFromRequest($this->context . '.filter.article_id', 'filter_article_id', '');
        $this->setState('filter.article_id', $categoryId);

        $categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
        $this->setState('filter.category_id', $categoryId);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_etdgallery');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.id', 'desc');
    }
}
