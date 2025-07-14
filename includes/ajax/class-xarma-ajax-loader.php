<?php

class Xarma_Ajax_Loader {

	public static function init() {
		add_action( 'wp_ajax_xarma_get_posts', [ __CLASS__, 'get_posts' ] );
	}

	public static function get_posts() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		$type = sanitize_text_field( $_POST['post_type'] ?? 'post' );
		$lang = sanitize_text_field( $_POST['lang'] ?? '' );

		$args = [
			'post_type'      => $type,
			'posts_per_page' => -1,
			'post_status'    => [ 'publish', 'draft', 'trash' ],
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		];

		if ( function_exists( 'pll_get_post' ) && $lang ) {
			$args['lang'] = $lang;
		}

		$posts = get_posts( $args );
		$data  = [];

		foreach ( $posts as $post ) {
			$data[] = [
				'ID'         => $post->ID,
				'title'      => $post->post_title,
				'status'     => $post->post_status,
				'date'       => get_the_date( 'Y-m-d', $post ),
				'meta_color' => get_post_meta( $post->ID, 'color', true ),
				'lang'       => function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post->ID ) : '',
			];
		}

		wp_send_json_success( $data );
	}
}