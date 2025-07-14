<?php

defined( 'ABSPATH' ) || exit;

class Xarma_Loader {

	public static function init() {
		require_once XARMA_PATH . 'includes/admin/class-xarma-admin-page.php';
		require_once XARMA_PATH . 'includes/ajax/class-xarma-ajax-save.php';

		Xarma_Admin_Page::init();
		Xarma_Ajax_Save::init();
	}
}