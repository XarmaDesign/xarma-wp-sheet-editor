<?php

class Xarma_Ajax_Export {

	public static function init() {
		add_action( 'wp_ajax_xarma_export_excel', [ __CLASS__, 'export_excel' ] );
	}

	public static function export_excel() {
		check_ajax_referer( 'xarma_nonce', 'nonce' );

		$type = sanitize_text_field( $_POST['post_type'] ?? 'post' );

		$posts = get_posts( [
			'post_type'      => $type,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		] );

		if ( empty( $posts ) ) {
			wp_die( 'Nessun contenuto da esportare.' );
		}

		require_once XARMA_PATH . 'vendor/autoload.php';

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$sheet->fromArray( [ 'ID', 'Titolo', 'Status', 'Data', 'Color' ], NULL, 'A1' );

		$row = 2;
		foreach ( $posts as $post ) {
			$sheet->setCellValue( "A{$row}", $post->ID );
			$sheet->setCellValue( "B{$row}", $post->post_title );
			$sheet->setCellValue( "C{$row}", $post->post_status );
			$sheet->setCellValue( "D{$row}", get_the_date( 'Y-m-d', $post ) );
			$sheet->setCellValue( "E{$row}", get_post_meta( $post->ID, 'color', true ) );
			$row++;
		}

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );

		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment;filename="xarma_export.xlsx"' );
		header( 'Cache-Control: max-age=0' );

		$writer->save( 'php://output' );
		exit;
	}
}