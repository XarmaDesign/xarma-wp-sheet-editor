<?php

defined( 'ABSPATH' ) || exit;

class Xarma_Ajax_Save {

	public static function init() {
		add_action( 'wp_ajax_xarma_get_posts', [ __CLASS__, 'get_posts' ] );
		add_action( 'wp_ajax_xarma_save_post', [ __CLASS__, 'save_post' ] );
		add_action( 'wp_ajax_xarma_new_post', [ __CLASS__, 'new_post' ] );
	}

	public static function get_posts() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		$post_type = sanitize_text_field( $_POST['post_type'] ?? 'post' );
		$lang      = sanitize_text_field( $_POST['lang'] ?? '' );

		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'any',
		];

		if ( $lang ) {
			$args['lang'] = $lang;
		}

		$query  = new WP_Query( $args );
		$posts  = [];

		foreach ( $query->posts as $post ) {
			$posts[] = [
				'ID'         => $post->ID,
				'title'      => $post->post_title,
				'status'     => $post->post_status,
				'date'       => substr( $post->post_date, 0, 10 ),
				'slug'       => $post->post_name,
				'excerpt'    => $post->post_excerpt,
				'content'    => $post->post_content,
				'author'     => $post->post_author,
				'meta_color' => get_post_meta( $post->ID, 'meta_color', true ),
				'lang'       => apply_filters( 'wpml_post_language_details', '', $post->ID )['language_code'] ?? '',
			];
		}

		$authors = get_users( [ 'who' => 'authors' ] );

		wp_send_json_success([
			'posts'   => $posts,
			'authors' => array_map( function ( $user ) {
				return [
					'ID'           => $user->ID,
					'display_name' => $user->display_name
				];
			}, $authors )
		]);
	}

	public static function save_post() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		$post_id = intval( $_POST['post_id'] );
		$field   = sanitize_key( $_POST['field'] );
		$value   = $_POST['value'];

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error();
		}

		switch ( $field ) {
			case 'title':
				wp_update_post( [ 'ID' => $post_id, 'post_title' => sanitize_text_field( $value ) ] );
				break;
			case 'slug':
				wp_update_post( [ 'ID' => $post_id, 'post_name' => sanitize_title( $value ) ] );
				break;
			case 'excerpt':
				wp_update_post( [ 'ID' => $post_id, 'post_excerpt' => sanitize_text_field( $value ) ] );
				break;
			case 'content':
				wp_update_post( [ 'ID' => $post_id, 'post_content' => wp_kses_post( $value ) ] );
				break;
			case 'date':
				wp_update_post( [ 'ID' => $post_id, 'post_date' => $value . ' 00:00:00' ] );
				break;
			case 'status':
				wp_update_post( [ 'ID' => $post_id, 'post_status' => $value ] );
				break;
			case 'author':
				wp_update_post( [ 'ID' => $post_id, 'post_author' => intval( $value ) ] );
				break;
			case 'color':
				update_post_meta( $post_id, 'meta_color', sanitize_hex_color( $value ) );
				break;
		}

		wp_send_json_success();
	}

	public static function new_post() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );
		$post_type = sanitize_text_field( $_POST['post_type'] ?? 'post' );

		$new_id = wp_insert_post( [
			'post_type'   => $post_type,
			'post_status' => 'draft',
			'post_title'  => '(Nuovo)'
		] );

		if ( $new_id ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}
}