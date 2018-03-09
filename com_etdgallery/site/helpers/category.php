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

class EtdGalleryCategories extends JCategories {

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   11.1
	 */
	public function __construct($options = array()) {

		$options['table'] = '#__etdgallery';
		$options['extension'] = 'com_etdgallery';

		parent::__construct($options);
	}
}
