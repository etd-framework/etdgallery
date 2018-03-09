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

class EtdGalleryTableImage extends JTable {

    /**
     * Constructor
     *
     * @param   JDatabaseDriver &$_db Database connector object
     *
     * @since   1.5
     */
    public function __construct(&$_db) {

        parent::__construct('#__etdgallery', 'id', $_db);

        JTableObserverTags::createObserver($this, array('typeAlias' => 'com_etdgallery.image'));

        $date = JFactory::getDate();
        $this->created = $date->toSql();
        $this->setColumnAlias('published', 'state');
    }

    /**
     * Overloaded check function
     *
     * @return  boolean
     *
     * @see     JTable::check
     * @since   1.5
     */
    public function check() {

        // Set name
        $this->title = htmlspecialchars_decode($this->title, ENT_QUOTES);

        // Set ordering
        if ($this->state < 0) {
            // Set ordering to 0 if state is archived or trashed
            $this->ordering = 0;
        } elseif (empty($this->ordering)) {
            // Set ordering to last if ordering was 0
            $this->ordering = self::getNextOrder($this->_db->quoteName('catid') . '=' . $this->_db->quote($this->catid) . ' AND '. $this->_db->quoteName('article_id') . '=' . $this->_db->quote($this->article_id) . ' AND state >= 0');
        }

        return true;
    }

}
