<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_etdgallery
 *
 * @version     1.1.12
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$app = JFactory::getApplication();

$cacheparams               = new stdClass;
$cacheparams->cachemode    = 'id';
$cacheparams->class        = 'ModEtdGalleryHelper';
$cacheparams->methodparams = $params;

$storeid                 = $module->id.':getList';
if ($params->get('article_only', '0') && $app->input->getCmd('option') == 'com_content' && $app->input->getCmd('view') == 'article') {
    $storeid .= 'article_'.$app->input->getInt('id');
}
$cacheparams->method     = 'getList';
$cacheparams->modeparams = md5($storeid);
$list = JModuleHelper::moduleCache($module, $params, $cacheparams);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_etdgallery', $params->get('layout', 'default'));
