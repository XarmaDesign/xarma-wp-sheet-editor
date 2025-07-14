<?php

class Xarma_Ajax_Handler {

	public static function init() {
		add_action( 'wp_ajax_xarma_get_posts', [ __CLASS__, 'get_posts' ] );
		add_action( 'wp_ajax_xarma_update_post', [ __CLASS__, 'update_post' ] );
		add_action( 'wp_ajax_xarma_update_order', [ __CLASS__, 'update_order' ] );
		add_action( 'wp_ajax_xarma_clone_post', [ __CLASS__, 'clone_post' ] );
		add_action( 'wp_ajax_xarma_new_post', [ __CLASS__, 'new_post' ] );
		add_action( 'wp_ajax_xarma_trash_post', [ __CLASS__, 'trash_post' ] );
		add_action( 'wp_ajax_xarma_export_excel', [ __CLASS__, 'export_excel' ] );
	}

	public static function get_posts() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		$post_type = sanitize_key( $_POST['post_type'] ?? 'post' );
		$lang      = sanitize_text_field( $_POST['lang'] ?? '' );

		$args = [
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'posts_per_page' => 500,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		];

		if ( $lang ) {
			if ( function_exists( 'pll_get_post' ) ) {
				$args['lang'] = $lang;
			} elseif ( function_exists( 'wpml_get_language_information' ) && isset( $GLOBALS['sitepress'] ) ) {
				$GLOBALS['sitepress']->switch_lang( $lang );
			}
		}

		$query = new WP_Query( $args );
		$data  = [];

		foreach ( $query->posts as $post ) {
			$lang_code = '';
			if ( function_exists( 'pll_get_post_language' ) ) {
				$lang_code = pll_get_post_language( $post->ID );
			} elseif ( function_exists( 'wpml_get_language_information' ) ) {
				$lang_data = wpml_get_language_information( null, $post->ID );
				$lang_code = $lang_data['language_code'] ?? '';
			}

			$data[] = [
				'ID'         => $post->ID,
				'title'      => $post->post_title,
				'status'     => $post->post_status,
				'date'       => get_the_date( 'Y-m-d', $post ),
				'meta_color' => get_post_meta( $post->ID, 'color', true ),
				'lang'       => $lang_code,
			];
		}

		wp_send_json_success( $data );
	}

	public static function update_post() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		$post_id = absint( $_POST['post_id'] ?? 0 );
		$field   = sanitize_key( $_POST['field'] ?? '' );
		$value   = sanitize_text_field( $_POST['value'] ?? '' );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$args = [ 'ID' => $post_id ];
		switch ( $field ) {
			case 'title':
				$args['post_title'] = $value;
				break;
			case 'status':
				$args['post_status'] = $value;
				break;
			case 'date':
				$args['post_date'] = $value . ' 00:00:00';
				break;
			case 'color':
				update_post_meta( $post_id, 'color', $value );
				wp_send_json_success();
				return;
			default:
				wp_send_json_error( 'Invalid field' );
		}

		$result = wp_update_post( $args, true );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( [ 'updated' => $result ] );
	}

	public static function update_order() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		foreach ( $_POST['order'] as $row ) {
			wp_update_post( [
				'ID'         => intval( $row['id'] ),
				'menu_order' => intval( $row['order'] )
			] );
		}
		wp_send_json_success();
	}

	public static function clone_post() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );
		$post_id = absint( $_POST['post_id'] ?? 0 );

		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( 'Post non trovato' );
		}

		$new_post = [
			'post_title'   => $post->post_title . ' (Copy)',
			'post_content' => $post->post_content,
			'post_status'  => 'draft',
			'post_type'    => $post->post_type,
		];

		$new_id = wp_insert_post( $new_post );

		$meta = get_post_meta( $post_id );
		foreach ( $meta as $key => $values ) {
			foreach ( $values as $value ) {
				add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
			}
		}

		wp_send_json_success( [ 'new_id' => $new_id ] );
	}

	public static function new_post() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		$title     = sanitize_text_field( $_POST['title'] ?? 'Nuovo post' );
		$post_type = sanitize_key( $_POST['post_type'] ?? 'post' );

		$post_id = wp_insert_post( [
			'post_title'  => $title,
			'post_status' => 'draft',
			'post_type'   => $post_type,
		] );

		wp_send_json_success( [ 'ID' => $post_id ] );
	}

	public static function trash_post() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );
		$post_id = absint( $_POST['post_id'] ?? 0 );
		$do      = sanitize_text_field( $_POST['do'] ?? 'trash' );

		if ( $do === 'trash' ) {
			wp_trash_post( $post_id );
		} elseif ( $do === 'restore' ) {
			wp_untrash_post( $post_id );
		}

		wp_send_json_success();
	}

	public static function export_excel() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		if ( ! class_exists( \PhpOffice\PhpSpreadsheet\Spreadsheet::class ) ) {
			require_once plugin_dir_path( __DIR__, 2 ) . 'vendor/autoload.php';
		}

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet       = $spreadsheet->getActiveSheet();

		$sheet->setCellValue('A1', 'ID');
		$sheet->setCellValue('B1', 'Titolo');
		$sheet->setCellValue('C1', 'Status');
		$sheet->setCellValue('D1', 'Data');
		$sheet->setCellValue('E1', 'Color');

		$args = [
			'post_type'      => sanitize_key( $_POST['post_type'] ?? 'post' ),
			'post_status'    => 'any',
			'posts_per_page' => 500,
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
		];

		$posts = get_posts( $args );
		$row   = 2;

		foreach ( $posts as $post ) {
			$sheet->setCellValue("A{$row}", $post->ID);
			$sheet->setCellValue("B{$row}", $post->post_title);
			$sheet->setCellValue("C{$row}", $post->post_status);
			$sheet->setCellValue("D{$row}", $post->post_date);
			$sheet->setCellValue("E{$row}", get_post_meta( $post->ID, 'color', true ) );
			$row++;
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="xarma_export.xlsx"');
		header('Cache-Control: max-age=0');

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
		$writer->save('php://output');
		exit;
	}
}