<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_etdgallery
 *
 * @version     1.1.4
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class EtdGalleryController extends JControllerLegacy {

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  12.2
     */
    protected $default_view = 'images';

    public function display($cachable = false, $urlparams = false) {

        require_once JPATH_COMPONENT . '/helpers/etdgallery.php';

        $view   = $this->input->get('view', 'images');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');

        // Check for edit form.
        if ($view == 'image' && $layout == 'edit' && !$this->checkEditId('com_etdgallery.edit.image', $id)) {

            // Somehow the person just went to the form - we don't allow that.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_etdgallery&view=images', false));

            return false;
        }

        parent::display();

        return $this;
    }
}
