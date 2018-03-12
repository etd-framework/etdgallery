<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_etdgallery
 *
 * @version     1.1.12
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class EtdGalleryHelper extends JHelperContent {

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName) {

		JHtmlSidebar::addEntry(
			JText::_('COM_ETDGALLERY_SUBMENU_IMAGES'),
			'index.php?option=com_etdgallery&view=images',
			$vName == 'images'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_ETDGALLERY_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_etdgallery',
			$vName == 'categories'
		);
	}

    /**
     * Adds Count Items for Category Manager.
     *
     * @param   stdClass[]  &$items  The banner category objects
     *
     * @return  stdClass[]
     *
     * @since   3.5
     */
    public static function countItems(&$items) {

        $db = JFactory::getDbo();

        foreach ($items as $item) {

            $item->count_trashed = 0;
            $item->count_archived = 0;
            $item->count_unpublished = 0;
            $item->count_published = 0;

            $query = $db->getQuery(true);
            $query->select('state, count(*) AS count')
                ->from($db->qn('#__etdgallery'))
                ->where('catid = ' . (int) $item->id)
                ->group('state');
            $db->setQuery($query);
            $images = $db->loadObjectList();

            foreach ($images as $image) {

                if ($image->state == 1) {
                    $item->count_published = $image->count;
                }

                if ($image->state == 0) {
                    $item->count_unpublished = $image->count;
                }

                if ($image->state == 2) {
                    $item->count_archived = $image->count;
                }

                if ($image->state == -2) {
                    $item->count_trashed = $image->count;
                }
            }
        }

        return $items;
    }
}
