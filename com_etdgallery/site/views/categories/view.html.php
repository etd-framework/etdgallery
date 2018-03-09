<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_etdgallery
 *
 * @version     1.1.4
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class EtdGalleryViewCategories extends JViewCategories {

	/**
	 * Language key for default page heading
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $pageHeading = 'COM_ETDGALLERY_CATEGORIES';

	/**
	 * @var    string  The name of the extension for the category
	 * @since  3.2
	 */
	protected $extension = 'com_etdgallery';
}
