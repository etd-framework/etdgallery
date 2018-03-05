<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Etdgallery
 *
 * @version		1.1.0
 * @copyright	Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license		http://www.etd-solutions.com/licence
 * @author		ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

jimport('joomla.filesystem.file');

class PlgContentEtdGallery extends JPlugin {

    /*
    * update images properties after content is saved
    */
    public function onContentAfterSave($context, $article, $isNew) {

        if ($context == 'com_content.article') {

            // App
            $app = JFactory::getApplication();

            // Objet DB
            $db = JFactory::getDbo();

            // On récupère les images.
            $images = $app->input->get('etdgallery', array(), 'array');

            // On s'assure que c'est un tableau d'integer.
            \Joomla\Utilities\ArrayHelper::toInteger($images);

            // On copie un article et donc ses images.
            if ($app->input->get('task') == 'save2copy') {

                // Config du composant
                $config    = JComponentHelper::getParams('com_etdgallery');
                $sizes     = json_decode($config->get('sizes', '[]'));
                $imagesDir = JPATH_ROOT . "/images/" . $config->get('images_dir', 'di');

                // On va appeler le modèle.
                JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR."/components/com_etdgallery/models");
                $model = JModelLegacy::getInstance('Image', 'EtdGalleryModel', array('ignore_request' => true));

                foreach ($images as $image_id) {

                    // On charge l'image.
                    $image = $model->getItem($image_id);

                    // On reset certains champs car on crée une copie.
                    $image->id         = 0;
                    $image->article_id = $article->id;
                    $image->created    = null;

                    // On engistre.
                    $model->setState('image.id', null);
                    $model->save(\Joomla\Utilities\ArrayHelper::fromObject($image));

                    $new_image_id = $model->getState('image.id');

                    // On copie le fichier d'origine.
                    $original_path = $imagesDir . "/" . $image_id . "_" . $image->filename;
                    $new_path      = $imagesDir . "/" . $new_image_id . "_" . $image->filename;

                    if (file_exists($original_path)) {
                        JFile::copy($original_path, $new_path);
                    }

                    // On copie les fichiers des tailles.
                    foreach ($sizes as $size) {

                        $original_path = $imagesDir . "/" . $image_id . "_" . $size->name . "_" . $image->filename;
                        $new_path      = $imagesDir . "/" . $new_image_id . "_" . $size->name . "_" . $image->filename;

                        if (file_exists($original_path)) {
                            JFile::copy($original_path, $new_path);
                        }

                    }


                }

            } elseif ($isNew) { // Si c'est un nouvel article, on doit associer les images avec.

                // On prépare la requête de mise à jour.
                $query = $db->getQuery(true)
                            ->update('#__etdgallery');

                // On met à jour l'identifiant de l'article pour associer l'image.
                foreach ($images as $i => $image_id) {

                    $query->clear('where')
                          ->clear('set')
                          ->set('article_id = ' . $article->id)
                          ->set('ordering = ' . ($i+1))
                          ->where('id = ' . $image_id);

                    $db->setQuery($query)->execute();

                }

            }

            // On nettoie le cache pour le module.
            $cache = JCache::getInstance('callback', array(
                'defaultgroup' => "mod_etdgallery",
                'cachebase'    => JPATH_SITE . '/cache'
            ));
            $cache->clean();

        }
    }

    /*
     * delete images when content is deleted
     */
    public function onContentBeforeDelete($context, $article) {

        $config = JComponentHelper::getParams('com_etdgallery');

        if ($context == "com_content.article" && $config->get('delete_on_delete', false)) {

            $db = JFactory::getDbo();

            // On charge les images associées à l'article
            $db->setQuery(
                $db->getQuery(true)
                ->select('a.id, a.filename')
                ->from('#__etdgallery AS a')
                ->where('a.article_id = ' . $article->id)
            );

            $images = $db->loadObjectList();

            // Si on a des images, on les supprime.
            if (!empty($images)) {

                $sizes     = json_decode($config->get('sizes', '[]'));
                $imagesDir = JPATH_ROOT . "/images/" . $config->get('images_dir', 'di');

                foreach ($images as $image) {

                    $path = $imagesDir . "/" . $image->id . "_" . $image->filename;

                    if (file_exists($path)) {
                        JFile::delete($path);
                    }

                    foreach ($sizes as $size) {

                        $path = $imagesDir . "/" . $image->id . "_" .  $size->name . "_" . $image->filename;

                        if (file_exists($path)) {
                            JFile::delete($path);
                        }

                    }

                }

            }

        }

    }

}