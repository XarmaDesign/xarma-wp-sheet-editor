<?php

class Xarma_Rollback {

	public static function init() {
		add_action( 'save_post', [ __CLASS__, 'backup_post' ], 10, 2 );
		add_action( 'admin_post_xarma_restore_backup', [ __CLASS__, 'restore_backup' ] );
	}

	public static function backup_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$data = [
			'title' => $post->post_title,
			'content' => $post->post_content,
			'status' => $post->post_status,
			'type' => $post->post_type,
			'meta' => get_post_meta( $post_id ),
		];

		$dir = plugin_dir_path( __DIR__ ) . '../backups/';
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$timestamp = current_time( 'Ymd_His' );
		$file = "{$dir}post_{$post_id}_{$timestamp}.json";

		file_put_contents( $file, wp_json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	public static function restore_backup() {
		if ( ! current_user_can( 'edit_posts' ) || ! isset( $_GET['file'] ) ) {
			wp_die( 'Accesso negato' );
		}

		$file = sanitize_file_name( $_GET['file'] );
		$path = plugin_dir_path( __DIR__ ) . '../backups/' . $file;

		if ( ! file_exists( $path ) ) {
			wp_die( 'Backup non trovato' );
		}

		$json = file_get_contents( $path );
		$data = json_decode( $json, true );

		if ( ! $data || ! isset( $file ) ) {
			wp_die( 'Backup non valido' );
		}

		preg_match( '/post_(\d+)_/', $file, $match );
		$post_id = intval( $match[1] ?? 0 );

		if ( $post_id ) {
			wp_update_post( [
				'ID' => $post_id,
				'post_title' => $data['title'] ?? '',
				'post_content' => $data['content'] ?? '',
				'post_status' => $data['status'] ?? 'draft',
			] );

			if ( ! empty( $data['meta'] ) ) {
				foreach ( $data['meta'] as $key => $values ) {
					delete_post_meta( $post_id, $key );
					foreach ( $values as $value ) {
						add_post_meta( $post_id, $key, maybe_unserialize( $value ) );
					}
				}
			}
		}

		wp_redirect( admin_url( 'admin.php?page=xarma-sheet&restored=1' ) );
		exit;
	}
}