<?php
/**
 * Plugin Name: Xarma WP Sheet Editor
 * Description: Editor a foglio stile spreadsheet per modificare contenuti WordPress (post, CPT, meta).
 * Version: 1.0.0
 * Author: Xarma Dev
 * Requires PHP: 8.0
 * Requires at least: 6.5
 */

defined( 'ABSPATH' ) || exit;

define( 'XARMA_PATH', plugin_dir_path( __FILE__ ) );
define( 'XARMA_URL', plugin_dir_url( __FILE__ ) );

require_once XARMA_PATH . 'includes/admin/class-xarma-admin-page.php';
require_once XARMA_PATH . 'includes/ajax/class-xarma-ajax-handler.php';
require_once XARMA_PATH . 'includes/utils/class-xarma-rollback.php';

add_action( 'plugins_loaded', function () {
	Xarma_Admin_Page::init();
	Xarma_Ajax_Handler::init();
	Xarma_Rollback::init();
});