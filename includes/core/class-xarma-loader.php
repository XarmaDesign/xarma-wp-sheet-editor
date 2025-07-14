<?php

class Xarma_Loader {

	public static function init() {
		require_once XARMA_PATH . 'includes/admin/class-xarma-admin-page.php';
		require_once XARMA_PATH . 'includes/ajax/class-xarma-ajax-loader.php';
		require_once XARMA_PATH . 'includes/ajax/class-xarma-ajax-save.php';
		require_once XARMA_PATH . 'includes/ajax/class-xarma-ajax-export.php';
		require_once XARMA_PATH . 'includes/utils/class-xarma-backup.php';

		Xarma_Admin_Page::init();
		Xarma_Ajax_Loader::init();
		Xarma_Ajax_Save::init();
		Xarma_Ajax_Export::init();
	}
}