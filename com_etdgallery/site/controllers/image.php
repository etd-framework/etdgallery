<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_etdgallery
 *
 * @version     1.1.13
 * @copyright   Copyright (C) 2013 - 2018 ETD Solutions. All rights reserved.
 * @license     http://www.etd-solutions.com/licence
 * @author      ETD Solutions http://www.etd-solutions.com
 **/

// no direct access
defined('_JEXEC') or die('Restricted access to ETD Gallery');

class EtdGalleryControllerImage extends JControllerLegacy {

	public function hit() {

		$id = $this->input->get('id', 0, 'uint');

		if (empty($id)) {
			echo new JResponseJson(null, "bad id", true);
			die;
		}

		$model = $this->getModel('Image', 'EtdGalleryModel');
		$hits  = $model->hit($id);

		echo new JResponseJson(array(
			'hits' => $hits
		));
		die;
	}

}
