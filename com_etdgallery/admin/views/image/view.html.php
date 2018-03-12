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

class EtdGalleryViewImage extends JViewLegacy {

    protected $form;
    protected $item;
    protected $state;

    /**
     * Display the view
     *
     * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
     * @return bool
     */
    public function display($tpl = null) {

        // Initialiase variables.
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        $this->addToolbar();

        return parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar() {

        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user = JFactory::getUser();
        $isNew = ($this->item->id == 0);

        // Since we don't track these assets at the item level, use the category id.
        $canDo = JHelperContent::getActions('com_etdgallery');

        JToolbarHelper::title($isNew ? JText::_('COM_ETDGALLERY_MANAGER_IMAGE_NEW') : JText::_('COM_ETDGALLERY_MANAGER_IMAGE_EDIT'), 'image');

        // If not checked out, can save the item.
        if ($canDo->get('core.edit')) {
            JToolbarHelper::apply('image.apply');
            JToolbarHelper::save('image.save');

            if ($canDo->get('core.create')) {
                JToolbarHelper::save2new('image.save2new');
            }
        }

        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolbarHelper::save2copy('image.save2copy');
        }

        if (empty($this->item->id)) {
            JToolbarHelper::cancel('image.cancel');
        } else {

            JToolbarHelper::cancel('image.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
