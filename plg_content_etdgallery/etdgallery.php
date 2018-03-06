<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Etdgallery
 *
 * @version     1.1.1
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

jimport('joomla.filesystem.file');

class PlgContentEtdGallery extends JPlugin {

    /**
     * Update images properties after content is saved.
     *
     * @param $context
     * @param $article
     * @param $isNew
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
                $config = JComponentHelper::getParams('com_etdgallery');
                $sizes  = json_decode($config->get('sizes', '[]'));

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
                    $original_path = $image->dirname . "/" . $image_id . "_" . $image->filename;
                    $new_path      = $image->dirname . "/" . $new_image_id . "_" . $image->filename;

                    if (file_exists($original_path)) {
                        JFile::copy($original_path, $new_path);
                    }

                    // On copie les fichiers des tailles.
                    foreach ($sizes as $size) {

                        $original_path = $image->dirname . "/" . $image_id . "_" . $size->name . "_" . $image->filename;
                        $new_path      = $image->dirname . "/" . $new_image_id . "_" . $size->name . "_" . $image->filename;

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

    /**
     * Delete images when content is deleted.
     *
     * @param $context
     * @param $article
     */
    public function onContentBeforeDelete($context, $article) {

        $config = JComponentHelper::getParams('com_etdgallery');

        if ($context == "com_content.article" && $config->get('delete_on_delete', false)) {

            $db = JFactory::getDbo();

            // On charge les images associées à l'article
            $db->setQuery(
                $db->getQuery(true)
                ->select('a.id, a.filename, a.dirname')
                ->from('#__etdgallery AS a')
                ->where('a.article_id = ' . $article->id)
            );

            $images = $db->loadObjectList();

            // Si on a des images, on les supprime.
            if (!empty($images)) {

                $sizes = json_decode($config->get('sizes', '[]'));

                foreach ($images as $image) {

                    $path = $image->dirname . "/" . $image->id . "_" . $image->filename;

                    if (file_exists($path)) {
                        JFile::delete($path);
                    }

                    foreach ($sizes as $size) {

                        $path = $image->dirname . "/" . $image->id . "_" .  $size->name . "_" . $image->filename;

                        if (file_exists($path)) {
                            JFile::delete($path);
                        }
                    }
                }
            }
        }

        return;
    }

    /**
     *
     * @param   string   $context   The context of the content being passed to the plugin.
     * @param   object   &$article  The article object.  Note $article->text is also available
     * @param   mixed    &$params   The article params
     * @param   integer  $page      The 'page' number
     *
     * @return  mixed   true if there is an error. Void otherwise.
     *
     * @since   1.6
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0) {

        $app = JFactory::getApplication();

        if ($context == "com_content.article" && $app->isClient('site')) {

            $db = JFactory::getDbo();

            // On charge les images associées à l'article.
            $db->setQuery(
                $db->getQuery(true)
                    ->select('a.id, a.type, a.filename, a.dirname')
                    ->from('#__etdgallery AS a')
                    ->where('a.article_id = ' . $article->id)
            );

            $images = $db->loadObjectList();

            // Si on a des images, on affiche la galerie.
            if (!empty($images)) {

                $config = JComponentHelper::getParams('com_etdgallery');
                $sizes  = json_decode($config->get('sizes', '[]'));

                foreach ($images as &$image) {

                    if ($image->type == "image") {
                        $image->src = new stdClass();

                        foreach($sizes as $size) {
                            $image->src->{$size->name} = $image->dirname . "/" . $image->id . "_" . $size->name . "_" . $image->filename;
                        }
                    }
                }

                ob_start();
                require_once JPATH_PLUGINS . "/content/etdgallery/layouts/default.php";

                // On récupère notre code html.
                $article->text .= ob_get_clean();
            }
        }

        return;
    }
}