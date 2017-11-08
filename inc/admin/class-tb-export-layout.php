<?php
/**
 * Extend the Theme_Blvd_Export, which holds the
 * basic structure for exporting.
 *
 * See Theme_Blvd_Export class documentation at
 * /framework/tools/tb-class-export.php
 *
 * @author		Jason Bobich
 * @copyright	Copyright (c) Jason Bobich
 * @link		http://jasonbobich.com
 * @link		http://themeblvd.com
 * @package 	Theme Blvd WordPress Framework
 */
class Theme_Blvd_Export_Layout extends Theme_Blvd_Export {

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id A unique ID for this exporter
	 */
	public function __construct( $id, $args = array() ) {
		parent::__construct( $id, $args );
	}

	/**
	 * Set headers.
	 *
	 * @since 2.0.0
	 */
	protected function headers() {

		// Filename
		$layout = get_post( $_GET['layout'] );
		$filename = str_replace('{name}', $layout->post_name, $this->args['filename'] );

		// Set headers
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename='.$filename );
		header( 'Content-Type: text/xml; charset='.get_option( 'blog_charset' ), true );

	}

	/**
	 * Output content to be exported.
	 *
	 * @since 2.0.0
	 */
	public function export() {

		$layout_id = $_GET['layout'];
		$layout = get_post($layout_id);

		// Meta data
		$meta = get_post_meta($layout_id);

		// Run export
		include_once( TB_BUILDER_PLUGIN_DIR . '/inc/admin/export-layout.php' );

	}

}
