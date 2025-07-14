<?php
// Sicurezza
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Nessuna cancellazione permanente per sicurezza
// Se vuoi rimuovere meta o file backup, scommenta queste righe:

// array_map( 'unlink', glob( plugin_dir_path( __FILE__ ) . 'backups/*.json' ) );
// rmdir( plugin_dir_path( __FILE__ ) . 'backups' );