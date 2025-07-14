<?php
/**
 * Plugin Name:       Xarma WP Sheet Editor
 * Description:       Editor a foglio di calcolo per modificare contenuti WordPress in tempo reale.
 * Version:           1.2.0
 * Author:            XarmaDesign
 * License:           GPL-2.0+
 * Text Domain:       xarma-sheet
 */

defined( 'ABSPATH' ) || exit;

define( 'XARMA_PATH', plugin_dir_path( __FILE__ ) );
define( 'XARMA_URL', plugin_dir_url( __FILE__ ) );

require_once XARMA_PATH . 'includes/core/class-xarma-loader.php';

Xarma_Loader::init();