<?php

defined( 'ABSPATH' ) || exit;

class Xarma_Admin_Page {

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	public static function add_menu() {
		add_menu_page(
			'Xarma Sheet Editor',
			'WP Sheet Editor',
			'edit_posts',
			'xarma-sheet-editor',
			[ __CLASS__, 'render_page' ],
			'dashicons-edit-page',
			55
		);
	}

	public static function enqueue_assets( $hook ) {
		if ( $hook !== 'toplevel_page_xarma-sheet-editor' ) {
			return;
		}

		wp_enqueue_style( 'xarma-admin-css', XARMA_URL . 'assets/css/admin.css', [], XARMA_VER );
		wp_enqueue_script( 'xarma-admin-js', XARMA_URL . 'assets/js/admin.js', [ 'jquery' ], XARMA_VER, true );

		wp_localize_script( 'xarma-admin-js', 'xarmaData', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'xarma_nonce' ),
		] );
	}

	public static function render_page() {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$languages = function_exists( 'pll_get_languages' ) ? pll_get_languages() : [];

		?>
		<div class="wrap">
			<h1>Xarma WP Sheet Editor</h1>

			<div class="xarma-toolbar">
				<select id="xarma-post-type">
					<?php foreach ( $post_types as $pt ) : ?>
						<option value="<?php echo esc_attr( $pt->name ); ?>"><?php echo esc_html( $pt->label ); ?></option>
					<?php endforeach; ?>
				</select>

				<?php if ( $languages ) : ?>
					<select id="xarma-lang-filter">
						<option value=""><?php _e( 'All Languages', 'xarma' ); ?></option>
						<?php foreach ( $languages as $lang ) : ?>
							<option value="<?php echo esc_attr( $lang['slug'] ); ?>"><?php echo esc_html( $lang['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>

				<input type="text" id="xarma-filter-input" placeholder="Filtra contenuti..." />
				<button id="new-post-btn" class="button button-primary">+ Nuovo</button>
			</div>

			<table id="xarma-sheet-table" class="widefat fixed striped">
				<thead>
					<tr>
						<th><input type="checkbox"></th>
						<th>Titolo</th>
						<th>Stato</th>
						<th>Data</th>
						<th>Colore</th>
						<th>Slug</th>
						<th>Excerpt</th>
						<th>Autore</th>
						<th>Contenuto</th>
						<th>Lingua</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>

			<div id="xarma-toast"></div>
		</div>
		<?php
	}
}