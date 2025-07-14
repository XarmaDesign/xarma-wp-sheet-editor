<?php

class Xarma_Backup {

	public static function backup_post( $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$original = get_post( $post_id );
		if ( ! $original ) return;

		$meta = get_post_meta( $post_id );
		$backup = [
			'post' => $original,
			'meta' => $meta,
			'date' => current_time( 'mysql' )
		];

		$dir = WP_CONTENT_DIR . '/xarma-backups/';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		file_put_contents( $dir . $post_id . '-' . time() . '.json', wp_json_encode( $backup ) );
	}
}