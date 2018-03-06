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
     * @var     string  The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_ETDGALLERY_IMAGES';

    public function __construct($config = array()) {

        parent::__construct($config);

        $this->registerTask('ajaxDelete', 'delete');
    }

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

    public function getImages() {

        // Init
        $app    = JFactory::getApplication();
        $input  = $app->input;
        $config = JComponentHelper::getParams('com_etdgallery');

        $result = (object) array(
            'error' => true,
            'message' => ''
        );

        if (!JSession::checkToken()) {
            $result->error   = true;
            $result->message = JText::_('JINVALID_TOKEN');
            echo json_encode($result);
            exit();
        }

        $id = $input->get('article_id', 0, 'uint');

        if (empty($id)) {
            $result->error   = true;
            $result->message = JText::_('COM_ETDGALLERY_INVALID_ARTICLE_ID');
            echo json_encode($result);
            exit();
        }

        // On sélectionne les enregistrements.
        $model = $this->getModel('Images', 'EtdGalleryModel', array('ignore_request' => true));
        $model->setState('filter.article_id', $id);
        $model->setState('list.select', 'a.id, a.filename, a.dirname, a.title, a.description, a.featured');
        $model->setState('list.start', 0);
        $model->setState('list.limit', 0);
        $model->setState('list.ordering', 'a.ordering');
        $model->setState('list.direction', 'ASC');

        $items = $model->getItems();

        $result->files = array();

        if ($items !== false) {

            $result->error = false;

            foreach ($items as $item) {

                $item->thumbnailUrl = JUri::root() . "/" . $item->dirname . "/" . $item->id . "_" . $config->get('admin_size', 'thumb') . "_" . $item->filename;
                $item->deleteUrl    = JRoute::_('index.php?option=com_etdgallery&task=images.ajaxDelete&cid[]=' . $item->id);
                $result->files[]    = $item;

            }
        } else {
            $result->message = $model->getError();
        }

        echo json_encode($result);
        exit();

    }

    protected function postDeleteHook(JModelLegacy $model, $id = null) {

        if ($this->task == 'ajaxDelete') {

            echo json_encode(array(
                'error'   => ($this->messageType == "error"),
                'message' => $this->message
            ));
            exit();
        }
    }
}
