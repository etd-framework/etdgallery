<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_etdgallery
 *
 * @version     1.1.5
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class EtdGalleryControllerImage extends JControllerForm {

    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'COM_ETDGALLERY_IMAGE';

    /**
     * Method to run batch operations.
     *
     * @param   string $model The model
     *
     * @return  boolean  True on success.
     *
     * @since    2.5
     */
    public function batch($model = null) {

        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Set the model
        $model = $this->getModel('Image', '', array());

        // Preset the redirect
        $this->setRedirect(JRoute::_('index.php?option=com_etdgallery&view=images' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }

    /**
     * Method to toggle the featured setting of an image.
     *
     * @return  void
     */
    public function featured() {

        // Init
        $app       = JFactory::getApplication();
        $input     = $app->input;
        $model     = $this->getModel();

        $result = (object) array(
            'error' => true,
            'message' => ''
        );

        if (!JSession::checkToken()) {
            $result->error   = true;
            $result->message = JText::_('JINVALID_TOKEN');
            echo json_encode($result);
            exit(403);
        }

        // On récupère les infos.
        $id         = $input->get('id', 0, 'uint');
        $featured   = $input->get('featured', 0, 'uint');
        $article_id = $input->get('article_id', 0, 'uint');
        $config     = JComponentHelper::getParams('com_etdgallery');
        $imagesUri  = JUri::root() . "images/" . $config->get('images_dir', 'di');

        // On contrôle les droits de modification.
        if (!$model->featured($id, $featured, $article_id)) {
            $result->error   = true;
            $result->message = $model->getError();
            echo json_encode($result);
            exit(403);
        }

        // On charge l'image.
        $item = $model->getItem($id);

        $result->error    = false;
        $result->introUrl = $imagesUri . "/" . $id . "_" . $config->get('intro_size') . "_" . $item->filename;
        $result->fullUrl  = $imagesUri . "/" . $id . "_" . $config->get('full_size') . "_" . $item->filename;

        echo json_encode($result);
        exit();

    }

    public function upload() {

        // Init
        $app       = JFactory::getApplication();
        $input     = $app->input;
        $image     = $input->files->get('image');
        $config    = JComponentHelper::getParams('com_etdgallery');
        $dirname   = "images/" . $config->get('images_dir', 'etdgallery');
        $imagesDir = JPATH_ROOT . "/images/" . $config->get('images_dir', 'etdgallery');
        $imagesUri = JUri::root() . "images/" . $config->get('images_dir', 'etdgallery');
        $sizes     = json_decode($config->get('sizes', '[]'));
        $model     = $this->getModel();

        // On crée le dossier de destination s'il n'existe pas.
        if (!is_dir($imagesDir)) {
            JFolder::create($imagesDir);
        }

        $result = (object) array(
            'error' => true,
            'message' => ''
        );

        if (!JSession::checkToken()) {
            $result->error   = true;
            $result->message = JText::_('JINVALID_TOKEN');
            echo json_encode($result);
            exit(403);
        }

        // On récupère les infos.
        $catid       = $input->get('catid', 0, 'uint');
        $article_id  = $input->get('article_id', 0, 'uint');
        $featured    = $input->get('featured', 0, 'uint');
        $title       = $input->get('title', '', 'string');
        $description = $input->get('description', '', 'string');
        $crop        = $input->get('crop', null, 'string');

        // Si l'image appartient à une catégorie.
        if ($catid > 0) {

            // On récupère l'alias de la catégorie.
            $category  = $model->getCategory($catid);
            $cat_alias = $category->alias;

            if ($cat_alias) {

                // On update les dossiers de destination
                $imagesDir .= '/' . $cat_alias;
                $imagesUri .= '/' . $cat_alias;
                $dirname   .= '/' . $cat_alias;

                // On crée le dossier de destination.
                if (!is_dir($imagesDir)) {
                    JFolder::create($imagesDir);
                }
            }
        }

        if (!empty($crop)) {
            $crop = json_decode($crop);
        }

        if (count($image)) {

            $isError  = false;
            $errorMsg = "";

            // On contrôle que c'est bien une image.
            if (!in_array($image['type'], array(
                'image/png',
                'image/gif',
                'image/jpeg'
            ))
            ) {
                $isError  = true;
                $errorMsg = 'JLIB_MEDIA_ERROR_WARNFILETYPE';
            }

            if ($image['error'] == 1 || $image['size'] > ($config->get('max_upload_size', 0))) {
                $isError  = true;
                $errorMsg = 'JLIB_MEDIA_ERROR_WARNFILETOOLARGE';
            }

            // Si une erreur s'est produite.
            if ($isError) {
                $result->message = JText::_($errorMsg);
                echo json_encode($result);
                exit();
            }

            // On normalise le nom du fichier
            $name = $this->normalizeFileName($image['name']);

            // Fichier d'origine
            $original_path = JPath::clean($imagesDir . "/" . $name);

            // On déplace le fichier dans le dossier.
            if (!JFile::upload($image['tmp_name'], $original_path)) {
                $result->message = JText::_($errorMsg);
                echo json_encode($result);
                exit();
            }

            // On enregistre l'image.
            $data  = array(
                'type'        => 'image',
                'article_id'  => $article_id,
                'state'       => 1,
                'filename'    => $name,
                'dirname'     => $dirname,
                'title'       => $title,
                'description' => $description,
                'featured'    => $featured
            );

            if ($model->save($data)) {

                $result->error = false;

                // On récupère l'identifiant de l'image.
                $image_id = $model->getState('image.id');

                // On génère toutes les images.
                $this->generateImageSizes($original_path, $image_id, $sizes, $crop);

                // On renome le fichier original pour faire apparaitre l'id de l'image.
                $old_path      = $original_path;
                $original_path = $imagesDir . "/" . $image_id . "_" . $name;
                JFile::move($old_path, $original_path);

                // On sauvegarde le nom dans la base.
                $result->files = array(
                    array(
                        'id'           => $image_id,
                        'name'         => $name,
                        'title'        => $title,
                        'description'  => $description,
                        'featured'     => $featured,
                        'url'          => $imagesUri . "/" . $image_id . "_" . $name,
                        'thumbnailUrl' => $imagesUri . "/" . $image_id . "_" . $config->get('admin_size', 'thumb') . "_" . $name,
                        'deleteUrl'    => JRoute::_('index.php?option=com_etdgallery&task=images.ajaxDelete&cid[]=' . $image_id),
                        'introUrl'     => $imagesUri . "/" . $image_id . "_" . $config->get('intro_size') . "_" . $name,
                        'fullUrl'      => $imagesUri . "/" . $image_id . "_" . $config->get('full_size') . "_" . $name
                    )
                );

            } else {

                $result->message = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError());
            }
        }

        echo json_encode($result);
        exit();

    }

    /**
     * Function that allows child controller access to model data
     * after the data has been saved.
     *
     * @param   \JModelLegacy  $model      The data model object.
     * @param   array          $validData  The validated data.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function postSaveHook(JModelLegacy $model, $validData = array()) {

        // Init
        $input = JFactory::getApplication()->input;
        $files = $input->files->get('jform');

        // Si une nouvelle image est uploadée.
        if (count($files)) {

            $config    = JComponentHelper::getParams('com_etdgallery');
            $imagesDir = JPATH_ROOT . "/images/" . $config->get('images_dir', 'di');
            $sizes     = json_decode($config->get('sizes', '[]'));
            $image_id  = $model->getState('image.id');
            $crop      = !empty($validData['crop']) ? json_decode($validData['crop']) : null;

            $image = $files['image'];

            // Pas d'image envoyée
            if ($image['error'] == 4) {
                return;
            }

            // On contrôle que c'est bien une image.
            if (!in_array($image['type'], array(
                'image/png',
                'image/gif',
                'image/jpeg'
            ))
            ) {
                throw new \InvalidArgumentException(JText::_('JLIB_MEDIA_ERROR_WARNFILETYPE'));
            }

            if ($image['error'] == 1 || $image['size'] > ($config->get('max_upload_size', 0))) {
                throw new \InvalidArgumentException(JText::_('JLIB_MEDIA_ERROR_WARNFILETOOLARGE'));
            }

            // On normalise le nom du fichier
            $name = $this->normalizeFileName($image['name']);

            // Fichier d'origine
            $original_path = JPath::clean($imagesDir . "/" . $name);

            // On déplace le fichier dans le dossier.
            if (!JFile::upload($image['tmp_name'], $original_path)) {
                throw new \InvalidArgumentException(JText::_('upload error'));
            }

            // On génère toutes les images.
            $this->generateImageSizes($original_path, $image_id, $sizes, $crop);

            // On renome le fichier original pour faire apparaitre l'id de l'image.
            JFile::move($original_path, $imagesDir . "/" . $image_id . "_" . $name);

            // On met à jour l'enregistrement avec le nom de fichier.
            $table = $model->getTable();
            if ($table->load($image_id)) {
                $table->filename = $name;
                $table->newTags = (array)(new JHelperTags)->getTagIds($image_id, 'com_etdgallery.image');

                $table->store();
            }
        }
    }

    protected function generateImageSizes($original_path, $image_id, $sizes, $crop = null) {

        jimport('image.image');

        $config = JComponentHelper::getParams('com_etdgallery');

        // On instancie le gestionnaire d'image.
        $image = new JImage($original_path);

        // On extrait le nom du fichier sans extension.
        $filename = pathinfo($original_path, PATHINFO_FILENAME);

        // On extrait le dossier.
        $path = pathinfo($original_path, PATHINFO_DIRNAME);

        // On extrait l'extension.
        $ext = strtolower(pathinfo($original_path, PATHINFO_EXTENSION));

        $options = array();

        switch ($ext) {
            case 'gif':
                $type = IMAGETYPE_GIF;
            break;

            case 'png':
                $type = IMAGETYPE_PNG;
                $options['quality'] = $config->get('quality') / 100;
            break;

            case 'jpg':
            case 'jpeg':
            default:
                $type = IMAGETYPE_JPEG;
            $options['quality'] = $config->get('quality');
            break;
        }

        // On change la couleur de fond.
        $image->filter('Backgroundfill', ['color' => '#FFFFFF']);

        // On crée les déclinaisons de taille pour l'image.
        foreach ($sizes as $size_name => $size) {

            // On crée le nouveau nom de fichier.
            $new_name = $image_id . "_" . $size_name . "_" . $filename;

            // On redimensionne l'image si besoin.
            if ($image->getWidth() > $size->width || $image->getHeight() > $size->height) {
                $newImage = $image->resize($size->width, $size->height, true, $size->crop ? JImage::SCALE_OUTSIDE : JImage::SCALE_INSIDE);
            } else {
                $newImage = new JImage($original_path);
            }

            // On rogne l'image.
            if ($size->crop) {

                $left = null;
                $top  = null;

                if (is_object($crop) && property_exists($crop, $size_name)) {
                    $left = $crop->$size_name->x;
                    $top  = $crop->$size_name->y;
                }

                $newImage->crop($size->width, $size->height, $left, $top, false);
            }

            // On sauvegarde l'image.
            if (!$newImage->toFile($path . "/" . $new_name . "." . $ext, $type, $options)) {
                throw new \InvalidArgumentException(JText::_('toFile error'));
            }

            // On libère la mémoire.
            $newImage->destroy();

        }

        // On libère la mémoire.
        $image->destroy();
    }

    protected function normalizeFileName($name) {

        // On extrait le nom du fichier sans extension.
        $filename = strtolower(pathinfo($name, PATHINFO_FILENAME));

        // On effectue une translitération.
        $filename = JApplicationHelper::stringURLSafe($filename);

        // On extrait l'extension.
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return $filename . "." . $ext;
    }
}
