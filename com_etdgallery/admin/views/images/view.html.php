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

class EtdGalleryViewImages extends JViewLegacy {

    protected $items;

    protected $pagination;

    protected $state;

    /**
     * Method to display the view.
     *
     * @param   string $tpl A template file to load. [optional]
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     *
     * @since   1.6
     */
    public function display($tpl = null) {

        require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/etdgallery.php';

        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        EtdGalleryHelper::addSubmenu('images');

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar() {

        $canDo = JHelperContent::getActions('com_etdgallery');
        $user  = JFactory::getUser();

        // Get the toolbar object instance
        $bar = JToolBar::getInstance('toolbar');

        JToolbarHelper::title(JText::_('COM_ETDGALLERY_MANAGER_IMAGES'), 'images');

        if ($canDo->get('core.create')) {
            JToolbarHelper::addNew('image.add');
        }

        if ($canDo->get('core.edit')) {
            JToolbarHelper::editList('image.edit');
        }

        if ($canDo->get('core.edit.state')) {
            if ($this->state->get('filter.state') != 2) {
                JToolbarHelper::publish('images.publish', 'JTOOLBAR_PUBLISH', true);
                JToolbarHelper::unpublish('images.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            }

            if ($this->state->get('filter.state') != -1) {
                if ($this->state->get('filter.state') != 2) {
                    JToolbarHelper::archiveList('images.archive');
                } elseif ($this->state->get('filter.state') == 2) {
                    JToolbarHelper::unarchiveList('images.publish');
                }
            }
        }

        // Add a batch button
        if ($user->authorise('core.create', 'com_etdgallery') && $user->authorise('core.edit', 'com_etdgallery') && $user->authorise('core.edit.state', 'com_etdgallery')) {
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');

            // Instantiate a new JLayoutFile instance and render the batch button
            $layout = new JLayoutFile('joomla.toolbar.batch');

            $dhtml = $layout->render(array('title' => $title));
            $bar->appendButton('Custom', $dhtml, 'batch');
        }

        if ($canDo->get('core.delete')) {
            JToolbarHelper::deleteList('', 'images.delete');
        }

        if ($user->authorise('core.admin', 'com_etdgallery')) {
            JToolbarHelper::preferences('com_etdgallery');
        }
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     *
     * @since   3.0
     */
    protected function getSortFields() {

        return array(
            'ordering'     => JText::_('JGRID_HEADING_ORDERING'),
            'a.state'      => JText::_('JSTATUS'),
            'a.title'      => JText::_('COM_ETDGALLERY_HEADING_TITLE'),
            'a.type'       => JText::_('COM_ETDGALLERY_HEADING_TYPE'),
            'article_name' => JText::_('COM_ETDGALLERY_HEADING_ARTICLE_NAME'),
            'a.id'         => JText::_('JGRID_HEADING_ID')
        );
    }
}
