<?php

class Xarma_Admin_Page {

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	public static function add_menu() {
		add_menu_page(
			'Foglio Xarma',
			'Xarma Sheet',
			'manage_options',
			'xarma-sheet',
			[ __CLASS__, 'render_page' ],
			'dashicons-edit-page',
			25
		);
	}

	public static function enqueue_assets( $hook ) {
		if ( $hook !== 'toplevel_page_xarma-sheet' ) {
			return;
		}
		wp_enqueue_style( 'xarma-admin-css', XARMA_URL . 'assets/css/admin.css', [], '1.2.0' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'xarma-admin-js', XARMA_URL . 'assets/js/admin.js', [ 'jquery' ], '1.2.0', true );
		wp_localize_script( 'xarma-admin-js', 'xarmaData', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'xarma_nonce' ),
		] );
	}

	public static function render_page() {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		echo '<div class="wrap"><h1>Xarma WP Sheet Editor</h1>';

		echo '<div class="xarma-toolbar" style="margin-bottom:15px;">';
		echo '<label>Tipo:</label> ';
		echo '<select id="xarma-post-type">';
		foreach ( $post_types as $type ) {
			printf(
				'<option value="%1$s">%2$s</option>',
				esc_attr( $type->name ),
				esc_html( $type->labels->singular_name )
			);
		}
		echo '</select> ';

		if ( function_exists( 'pll_languages_list' ) ) {
			$langs = pll_languages_list();
			echo '<label>Lingua:</label> ';
			echo '<select id="xarma-lang-filter">';
			echo '<option value="">Tutte</option>';
			foreach ( $langs as $code ) {
				echo '<option value="' . esc_attr( $code ) . '">' . esc_html( strtoupper( $code ) ) . '</option>';
			}
			echo '</select> ';
		}

		echo '<input type="text" id="xarma-filter-input" placeholder="🔍 Cerca..." /> ';
		echo '<button id="new-post-btn" class="button button-primary">+ Nuovo post</button>';
		echo '</div>';

		echo '<div id="xarma-sheet-table-wrapper">';
		echo '<table id="xarma-sheet-table">
			<thead>
				<tr>
					<th class="handle"></th>
					<th>✓</th>
					<th data-col="title">Titolo</th>
					<th data-col="status">Status</th>
					<th data-col="date">Data</th>
					<th data-col="color">Color</th>
					<th>Lingua</th>
					<th>Azioni</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>';
		echo '</div>';

		echo '<form method="post" action="' . admin_url( 'admin-ajax.php' ) . '" target="_blank" style="margin-top:20px;">
			<input type="hidden" name="action" value="xarma_export_excel">
			<input type="hidden" name="nonce" value="' . wp_create_nonce( 'xarma_nonce' ) . '">
			<input type="hidden" name="post_type" value="post">
			<button type="submit" class="button">Esporta Excel (.xlsx)</button>
		</form>';

		echo '</div>';
	}
}