<?php

class Xarma_Ajax_Save {

	public static function init() {
		add_action( 'wp_ajax_xarma_save_post', [ __CLASS__, 'save_post' ] );
	}

	public static function save_post() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		$post_id = intval( $_POST['post_id'] );
		$field   = sanitize_text_field( $_POST['field'] );
		$value   = sanitize_text_field( $_POST['value'] );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error();
		}

		if ( $field === 'title' ) {
			wp_update_post( [ 'ID' => $post_id, 'post_title' => $value ] );
		} elseif ( $field === 'status' ) {
			wp_update_post( [ 'ID' => $post_id, 'post_status' => $value ] );
		} elseif ( $field === 'date' ) {
			wp_update_post( [ 'ID' => $post_id, 'post_date' => $value ] );
		} elseif ( $field === 'color' ) {
			update_post_meta( $post_id, 'color', $value );
		}

		wp_send_json_success();
	}
}