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

class EtdGalleryModelImage extends JModelAdmin {

    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'COM_ETDGALLERY_IMAGE';

    /**
     * The type alias for this content type.
     *
     * @var      string
     * @since    3.2
     */
    public $typeAlias = 'com_etdgallery.image';

    /**
     * Batch copy/move command. If set to false,
     * the batch copy/move command is not supported
     *
     * @var string
     */
    protected $batch_copymove = false;

    /**
     * Allowed batch commands
     *
     * @var array
     */
    protected $batch_commands = array('tag' => 'batchTag');

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     */
    public function getItem($pk = null) {

        if ($item = parent::getItem($pk)) {

            if (!empty($item->id)) {
                $item->tags = new JHelperTags;
                $item->tags->getTagIds($item->id, 'com_etdgallery.image');
            }

            if ($item->type == "video") {
                $item->url = $item->filename;
            }

            if($item->catid > 0) {
                $item->cat_alias = $this->getCategory($item->catid)->alias;
            }
        }

        return $item;
    }

    /**
     * Batch copy items to a new category or current.
     *
     * @param   integer $value The new category.
     * @param   array $pks An array of row IDs.
     * @param   array $contexts An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     *
     * @since    2.5
     */
    protected function batchCopy($value, $pks, $contexts) {

        $categoryId = (int)$value;

        $table = $this->getTable();
        $newIds = array();

        // Check that the category exists
        if ($categoryId) {
            $categoryTable = JTable::getInstance('Category');

            if (!$categoryTable->load($categoryId)) {
                if ($error = $categoryTable->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));

                    return false;
                }
            }
        }

        if (empty($categoryId)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));

            return false;
        }

        // Check that the user has create permission for the component
        $user = JFactory::getUser();

        if (!$user->authorise('core.create', 'com_banners.category.' . $categoryId)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

            return false;
        }

        // Parent exists so we let's proceed
        while (!empty($pks)) {
            // Pop the first ID off the stack
            $pk = array_shift($pks);

            $table->reset();

            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Alter the title
            $table->title = \Joomla\String\String::increment($table->title);

            // Reset the ID because we are making a copy
            $table->id = 0;

            // Unpublish because we are making a copy
            $table->state = 0;

            // TODO: Deal with ordering?
            // $table->ordering	= 1;

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$pk] = $newId;
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
    }

    /**
     * Returns a JTable object, always creating it.
     *
     * @param   string $type The table type to instantiate. [optional]
     * @param   string $prefix A prefix for the table class name. [optional]
     * @param   array $config Configuration array for model. [optional]
     *
     * @return  JTable  A database object
     *
     * @since   1.6
     */
    public function getTable($type = 'Image', $prefix = 'EtdGalleryTable', $config = array()) {

        JTable::addIncludePath(JPATH_ADMINISTRATOR."/components/com_etdgallery/tables");
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array $data Data for the form. [optional]
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not. [optional]
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true) {

        // Get the form.
        $form = $this->loadForm('com_etdgallery.image', 'image', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object)$data)) {

            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData() {

        // Check the session for previously entered form data.
        $app = JFactory::getApplication();
        $data = $app->getUserState('com_etdgallery.edit.image.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_etdgallery.image', $data);

        return $data;
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   JTable $table A record object.
     *
     * @return  array  An array of conditions to add to add to ordering queries.
     *
     * @since   1.6
     */
    protected function getReorderConditions($table) {

        $condition = array();
        $condition[] = 'catid = ' . (int)$table->catid;
        $condition[] = 'article_id = ' . (int)$table->article_id;
        $condition[] = 'state >= 0';

        return $condition;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   JTable $table A JTable object.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareTable($table) {

        $date = JFactory::getDate();

        if (empty($table->id)) {

            // Set the values
            $table->created = $date->toSql();

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true)
                    ->select('MAX(ordering)')
                    ->from('#__etdgallery')
                    ->where($this->getReorderConditions($table));

                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        }
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data) {

        if ($data["type"] == "image") {

            // On récupère le nom du fichier si nécessaire.
            $jform = JFactory::getApplication()->input->files->get('jform');
            $image = $jform['image'];

            if ($image['error'] == '0') {
                $data["filename"] = $image['name'];
            }

            // On récupère les dossiers de destination.
            $config    = JComponentHelper::getParams('com_etdgallery');
            $imagesDir = JPATH_ROOT . "/images/" . $config->get('images_dir', 'etdgallery');
            $dirname   = "images/" . $config->get('images_dir', 'etdgallery');

            // Si l'image appartient à une catégorie.
            if ($data["catid"] > 0) {

                // On récupère l'alias de la catégorie.
                $category  = $this->getCategory($data["catid"]);
                $cat_alias = $category->alias;

                if ($cat_alias) {

                    $imagesDir .= '/' . $cat_alias;
                    $dirname .= '/' . $cat_alias;
                }
            }

            // On crée le dossier de destination.
            if (!is_dir($imagesDir)) {
                JFolder::create($imagesDir);
            }

            $data['dirname'] = $dirname;

            // Si l'image existe déjà et a donc déjà été uploadée.
            // Et si la catégorie a changée.
            // Il faut donc changer les images de dossier.
            if ($data['id']) {

                $item  = $this->getItem($data["id"]);
                $sizes = json_decode($config->get('sizes', '[]'));

                if ($item->catid != $data['catid']) {

                    // On renome le fichier original pour faire apparaitre l'id de l'image.
                    $old_path      = JPATH_ROOT . "/" . $item->dirname . "/" . $item->id . "_" . $item->filename;
                    $original_path = $imagesDir . "/" . $data["id"] . "_" . $item->filename;

                    JFile::move($old_path, $original_path);

                    foreach ($sizes as $size) {

                        // On renome le fichier original pour faire apparaitre l'id de l'image.
                        $old_path      = JPATH_ROOT . "/" . $item->dirname . "/" . $item->id . "_" . $size->name . "_" . $item->filename;
                        $original_path = $imagesDir . "/" . $data["id"] . "_" . $size->name . "_" . $item->filename;

                        JFile::move($old_path, $original_path);
                    }
                }
            }

        } elseif ($data["type"] == "video") {

            // Le nom de fichier est l'url.
            $data["filename"] = $data["url"];

        } else {
            return false;
        }

        if (parent::save($data)) {

            // Si on veut mettre cette image en vedette dans un article.
            if ((isset($data['featured']) && $data['featured'] == '1') && isset($data['article_id']) && $data['article_id'] > 0) {

                // On s'assure qu'il n'y ait pas d'autres images en vedette dans le même article.
                $db    = $this->getDbo();
                $query = $db->getQuery(true)
                            ->update($db->quoteName('#__etdgallery'))
                            ->set('featured = 0')
                            ->where('article_id = ' . (int) $data['article_id'])
                            ->where('id <> ' . (int) $this->getState('image.id'));

                $db->setQuery($query);
                $db->execute();

            }

            return true;
        }

        return false;
    }

    public function delete(&$pks) {

        $directories = [];

        foreach ($pks as $i => $pk) {

            $item =            $this->getItem($pk);
            $directories[$i] = $item->dirname;
        }

        $ret = parent::delete($pks);

        // Si la suppression est OK, on supprime les fichiers aussi.
        if ($ret) {

            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');

            foreach ($pks as $i => $pk) {

                // On supprime tous les fichiers commençant par l'id.
                $files = JFolder::files(JPATH_ROOT . "/" . $directories[$i], '^' . $pk . '_.', false, true);

                if (!empty($files)) {
                    JFile::delete($files);
                }
            }
        }

        return $ret;
    }

    public function featured($id, $value = 0, $article_id = null) {

        $article_id = (int) $article_id;

        try {

            $db = $this->getDbo();

            // On vire les autres "en vedette".
            if ((int) $value == 1) {

                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__etdgallery'))
                    ->set('featured = 0');

                if (!empty($article_id)) {
                    $query->where('article_id = ' . $article_id);
                }

                $db->setQuery($query);
                $db->execute();

            }

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__etdgallery'))
                ->set('featured = ' . (int) $value)
                ->where('id = ' . (int) $id);
            $db->setQuery($query);
            $db->execute();

        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $this->cleanCache();

        return true;
    }

    /**
     * Récupère les informations d'une catégorie en fonction de son id.
     *
     * @param null $catid
     * @return bool
     */
    public function getCategory($catid = null) {

        $catid = (empty($catid)) ? $this->getItem()->catid : (int) $catid;

        if ($catid > 0) {
            $query = $this->_db->getQuery(true);

            $query->select('*')
                ->from($this->_db->quoteName('#__categories'))
                ->where('id = ' . (int) $catid);

            $this->_db->setQuery($query)
                ->execute();

            return $this->_db->loadObject();
        }

        return false;
    }
}
