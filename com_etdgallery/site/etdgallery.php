<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_etdgallery
 *
 * @version     1.1.12
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

JLoader::register('EtdGalleryHelperRoute', JPATH_SITE . '/components/com_etdgallery/helpers/route.php');

$controller	= JControllerLegacy::getInstance('EtdGallery');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
