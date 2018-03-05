<?php
/**
 * @package     ETD Gallery
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     Apache License 2.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class Pkg_EtdGalleryInstallerScript {

    /**
     * The name of our package, e.g. pkg_example. Used for dependency tracking.
     *
     * @var  string
     */
    protected $packageName = 'pkg_etdgallery';

    /**
     * The name of our component, e.g. com_example. Used for dependency tracking.
     *
     * @var  string
     */
    protected $componentName = 'com_etdgallery';

    /**
     * The minimum PHP version required to install this extension
     *
     * @var   string
     */
    protected $minimumPHPVersion = '5.4.0';

    /**
     * The minimum Joomla! version required to install this extension
     *
     * @var   string
     */
    protected $minimumJoomlaVersion = '3.5.0';

    /**
     * The maximum Joomla! version this extension can be installed on
     *
     * @var   string
     */
    protected $maximumJoomlaVersion = '4.0.0';

    /**
     * A list of extensions (modules, plugins) to enable after installation. Each item has four values, in this order:
     * type (plugin, module, ...), name (of the extension), client (0=site, 1=admin), group (for plugins).
     *
     * @var array
     */
    protected $extensionsToEnable = array(
        array('plugin', 'etdgallery', 1, 'content'),
        array('plugin', 'etdgalleryrender', 1, 'system')
    );

    /**
     * =================================================================================================================
     * DO NOT EDIT BELOW THIS LINE
     * =================================================================================================================
     */

    /**
     * Joomla! pre-flight event. This runs before Joomla! installs or updates the package. This is our last chance to
     * tell Joomla! if it should abort the installation.
     *
     * @param   string                     $type    Installation type (install, update, discover_install)
     * @param   \JInstallerAdapterPackage  $parent  Parent object
     *
     * @return  boolean  True to let the installation proceed, false to halt the installation
     */
    public function preflight($type, $parent) {

        // Check the minimum PHP version
        if (!version_compare(PHP_VERSION, $this->minimumPHPVersion, 'ge')) {
            $msg = "<p>You need PHP $this->minimumPHPVersion or later to install this package</p>";
            JLog::add($msg, JLog::WARNING, 'jerror');

            return false;
        }

        // Check the minimum Joomla! version
        if (!version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge')) {
            $msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";
            JLog::add($msg, JLog::WARNING, 'jerror');

            return false;
        }

        // Check the maximum Joomla! version
        if (!version_compare(JVERSION, $this->maximumJoomlaVersion, 'le')) {
            $msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this component</p>";
            JLog::add($msg, JLog::WARNING, 'jerror');

            return false;
        }

        // HHVM made sense in 2013, now PHP 7 is a way better solution than an hybrid PHP interpreter
        if (defined('HHVM_VERSION')) {
            $msg = "<p>We have detected that you are running HHVM instead of PHP. This software WILL NOT WORK properly on HHVM. Please switch to PHP 7 instead.</p>";
            JLog::add($msg, JLog::WARNING, 'jerror');

            return false;
        }

        return true;
    }

    /**
     * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
     * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
     * database updates and similar housekeeping functions.
     *
     * @param   string                       $type   install, update or discover_update
     * @param   \JInstallerAdapterComponent  $parent Parent object
     */
    public function postflight($type, $parent) {

        /**
         * Clean the cache after installing the package.
         *
         * See bug report https://github.com/joomla/joomla-cms/issues/16147
         */
        $conf = \JFactory::getConfig();
        $clearGroups = array('_system', 'com_modules', 'mod_menu', 'com_plugins', 'com_modules');
        $cacheClients = array(0, 1);

        foreach ($clearGroups as $group) {

            foreach ($cacheClients as $client_id) {

                try {
                    $options = array(
                        'defaultgroup' => $group,
                        'cachebase' => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache')
                    );

                    /** @var JCache $cache */
                    $cache = \JCache::getInstance('callback', $options);
                    $cache->clean();

                } catch (Exception $exception) {
                    $options['result'] = false;
                }

                // Trigger the onContentCleanCache event.
                try {
                    JFactory::getApplication()->triggerEvent('onContentCleanCache', $options);
                } catch (Exception $e) {
                    // Suck it up
                }
            }
        }
    }

    /**
     * Tuns on installation (but not on upgrade). This happens in install and discover_install installation routes.
     *
     * @param   \JInstallerAdapterPackage  $parent  Parent object
     *
     * @return  bool
     */
    public function install($parent) {
        // Enable the extensions we need to install
        $this->enableExtensions();

        return true;
    }

    /**
     * Runs on uninstallation
     *
     * @param   \JInstallerAdapterPackage  $parent  Parent object
     *
     * @return  bool
     */
    public function uninstall($parent) {
        return true;
    }

    /**
     * Enable modules and plugins after installing them
     */
    private function enableExtensions() {

        foreach ($this->extensionsToEnable as $ext) {
            $this->enableExtension($ext[0], $ext[1], $ext[2], $ext[3]);
        }
    }

    /**
     * Enable an extension
     *
     * @param   string   $type    The extension type.
     * @param   string   $name    The name of the extension (the element field).
     * @param   integer  $client  The application id (0: Joomla CMS site; 1: Joomla CMS administrator).
     * @param   string   $group   The extension group (for plugins).
     */
    private function enableExtension($type, $name, $client = 1, $group = null) {

        try {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->update('#__extensions')
                ->set($db->qn('enabled') . ' = ' . $db->q(1))
                ->where('type = ' . $db->quote($type))
                ->where('element = ' . $db->quote($name));
        } catch (\Exception $e) {
            return;
        }


        switch ($type) {
            case 'plugin':
                // Plugins have a folder but not a client
                $query->where('folder = ' . $db->quote($group));
                break;

            case 'language':
            case 'module':
            case 'template':
                // Languages, modules and templates have a client but not a folder
                $client = JApplicationHelper::getClientInfo($client, true);
                $query->where('client_id = ' . (int) $client->id);
                break;

            default:
            case 'library':
            case 'package':
            case 'component':
                // Components, packages and libraries don't have a folder or client.
                // Included for completeness.
                break;
        }

        try {
            $db->setQuery($query);
            $db->execute();

        } catch (\Exception $e) {
        }
    }
}
