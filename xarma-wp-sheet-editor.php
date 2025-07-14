<?php
/*
Plugin Name: Xarma WP Sheet Editor
Description: Editor a tabella per contenuti WP (post, pagine, CPT).
Version: 1.2
Author: XarmaDesign
*/

defined( 'ABSPATH' ) || exit;

define( 'XARMA_VER', '1.2' );
define( 'XARMA_PATH', plugin_dir_path( __FILE__ ) );
define( 'XARMA_URL', plugin_dir_url( __FILE__ ) );

// Caricamento classi principali
require_once XARMA_PATH . 'includes/core/class-xarma-loader.php';
Xarma_Loader::init();