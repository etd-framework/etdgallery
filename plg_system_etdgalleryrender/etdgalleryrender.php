<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Etdgalleryrender
 *
 * @version     1.1.4
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class PlgSystemEtdgalleryrender extends JPlugin {

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Est appelée avant le rendu du document.
     *
     * @throws Exception
     */
    public function onBeforeRender() {

        // On initialise les variables.
        $app   = JFactory::getApplication();
        $input = $app->input;

        // Si on est dans l'administration
        if ($app->isAdmin()) {

            // Si on est dans la vue d'ajout/édition d'un article.
            if (
                $input->get('option', '', 'cmd') == 'com_content' &&
                $input->get('view', '', 'cmd') == 'article' &&
                $input->get('layout', '', 'cmd' == 'edit')
            ) {

                // On récupère le document.
                $doc       = JFactory::getDocument();
                $component = $doc->getBuffer('component');

                // On récupère notre code html.
                $content = $this->renderLayout('article_tab');

                // On l'ajoute au rendu du composant.
                $myTabContent = JLayoutHelper::render('libraries.cms.html.bootstrap.starttabset', array('selector' => 'myTab'));
                $component    = str_replace($myTabContent, $myTabContent . $content, $component);

                // On remplace dans le document.
                $doc->setBuffer($component, array('type'  => 'component', 'name'  => '', 'title' => ''));
            }
        }
    }

    protected function renderLayout($layout) {

        ob_start();
        require_once JPATH_PLUGINS."/system/etdgalleryrender/layouts/$layout.php";
        return ob_get_clean();
    }
}