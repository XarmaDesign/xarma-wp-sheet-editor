<?php
class Xarma_Sheet {

	public static function render_page() {
		echo '<div class="wrap"><h1>Xarma Sheet Editor</h1>';
		echo '<div id="xarma-sheet-root">';
		echo '<table class="wp-list-table widefat fixed striped" id="xarma-sheet-table">';
		echo '<thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Actions</th></tr></thead>';
		echo '<tbody></tbody>';
		echo '</table>';
		echo '</div>';
	}
}