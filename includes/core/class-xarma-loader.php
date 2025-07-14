<?php
class Xarma_Loader {

	public static function init() {
		self::load_dependencies();
		self::load_textdomain();

		// Init core components
		Xarma_Admin_Page::init();
		Xarma_Ajax_Handler::init();
	}

	private static function load_dependencies() {
		require_once XARMA_PATH . 'includes/admin/class-xarma-admin-page.php';
		require_once XARMA_PATH . 'includes/ajax/class-xarma-ajax-handler.php';
		require_once XARMA_PATH . 'includes/utils/class-xarma-helper.php';
	}

	private static function load_textdomain() {
		load_plugin_textdomain( 'xarma-sheet', false, dirname( plugin_basename( __FILE__ ), 2 ) . '/languages' );
	}
}