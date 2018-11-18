<?php
/**
 * Integrate into WordPress 5+ editor.
 */
class Theme_Blvd_Layout_Builder_Editor {

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {

		add_action( 'enqueue_block_editor_assets', array( $this, 'assets' ) );

	}

	/**
	 * Enqueue assets for WordPress 5+ editor.
	 *
	 * @since 2.3.0
	 */
	public function assets() {

		global $current_screen;

		if ( 'page' !== $current_screen->post_type ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script(
			'theme-blvd-layout-builder-editor',
			esc_url( TB_BUILDER_PLUGIN_URI . "/inc/admin/assets/js/editor{$suffix}.js" ),
			array( 'wp-element', 'wp-components', 'wp-hooks' ),
			// array( 'wp-plugins', 'wp-element', 'wp-edit-post', 'wp-api-request', 'wp-data', 'wp-components', 'wp-blocks', 'wp-editor' ),
			TB_BUILDER_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'theme-blvd-layout-builder-editor',
			'themeblvdLayoutBuilderEditorL10n',
			array(
				'editText'     => __( 'Edit Custom Layout', 'theme-blvd-layout-builder' ),
				'editLink'     => esc_url( admin_url( 'admin.php?page=tb-edit-layout&post_type=page&referer=editor' ) ),
				'redirectText' => __( 'Redirecting...', 'theme-blvd-layout-builder' ),
			)
		);

	}
}
