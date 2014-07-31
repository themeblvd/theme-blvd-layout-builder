<?php
/*
Plugin Name: Theme Blvd Layout Builder
Description: This plugins gives you a slick interface that ties int the Theme Blvd framework to create custom layouts for your WordPress pages.
Version: 2.0.0
Author: Theme Blvd
Author URI: http://themeblvd.com
License: GPL2

    Copyright 2014  Theme Blvd

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

define( 'TB_BUILDER_PLUGIN_VERSION', '2.0.0' );
define( 'TB_BUILDER_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_BUILDER_PLUGIN_URI', plugins_url( '' , __FILE__ ) );

/**
 * Run Layout Builder plugin
 *
 * @since 1.0.0
 */
function themeblvd_builder_init() {

	global $_themeblvd_export_layouts;
	global $_themeblvd_layout_builder;

	// Include general items
	include_once( TB_BUILDER_PLUGIN_DIR . '/includes/class-tb-layout-builder-data.php' );
	include_once( TB_BUILDER_PLUGIN_DIR . '/includes/class-tb-layout-builder-notices.php' );
	include_once( TB_BUILDER_PLUGIN_DIR . '/includes/general.php' );
	include_once( TB_BUILDER_PLUGIN_DIR . '/includes/legacy.php' ); // @deprecated functions used by older themes

	// DEBUG/DEV Mode
	if ( ! defined( 'TB_BUILDER_DEBUG' ) ) {
		define( 'TB_BUILDER_DEBUG', false );
	}

	// Error handling
	$notices = Theme_Blvd_Layout_Builder_Notices::get_instance();

	if ( $notices->do_stop() ) {
		// Stop plugin from running
		return;
	}

	// Register custom layout hidden post type
	add_action( 'init', 'themeblvd_builder_register_post_type' );

	// Verify custom layout's data
	add_action( 'template_redirect', 'themeblvd_builder_verify_data' );

	// Frontend actions
	if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) {

		/**
		 * Hooks to action in the theme's template_builder.php page
		 * template in order to display the custom layout.
		 * Requires that the theme has the function themeblvd_elements(),
		 * Theme Blvd Framework 2.5+.
		 */
		add_action( 'themeblvd_builder_content', 'themeblvd_builder_layout' );

	} else {
		// @deprecated
		add_action( 'themeblvd_builder_content', 'themeblvd_builder_content' );
		add_action( 'themeblvd_featured', 'themeblvd_builder_featured' );
		add_action( 'themeblvd_featured_below', 'themeblvd_builder_featured_below' );
	}

	// Homepage layout (@deprecated -- With current themes, user must set a static frontpage with the template applied)
	if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
		themeblvd_builder_legacy_homepage();
	}

	// Admin Layout Builder
	if ( is_admin() ){

		// Check to make sure admin interface isn't set to be
		// hidden and for the appropriate user capability
		if ( themeblvd_supports( 'admin', 'builder' ) && current_user_can( themeblvd_admin_module_cap( 'builder' ) ) ) {

			// Setup exporting capabilities
			if ( class_exists( 'Theme_Blvd_Export' ) ) { // Theme Blvd framework 2.5+

				include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/class-tb-export-layout.php' );

				$args = array(
					'filename'	=> 'layout-{name}.xml', // string {name} will be dynamically replaced with each export
					'base_url'	=> admin_url('admin.php?page=themeblvd_builder')
				);
				$_themeblvd_export_layout = new Theme_Blvd_Export_Layout( 'layout', $args ); // Extends class Theme_Blvd_Export
			}

			include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/builder-samples.php' );
			include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/class-tb-import-layout.php' );
			include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/class-tb-layout-builder-ajax.php' );
			include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/class-tb-layout-builder.php' );

			// Setup Builder interface
			$_themeblvd_layout_builder = new Theme_Blvd_Layout_Builder();

		}
	}

}
add_action( 'after_setup_theme', 'themeblvd_builder_init' );

/**
 * Setup Layout Builder API
 *
 * @since 1.2.0
 */
function themeblvd_builder_api_init() {

	// Include screen options class (not currently
	// used in API, but could potentially be later on)
	// include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/class-tb-layout-builder-screen.php' );

	// Instantiate single object for Builder "Screen Options" tab.
	// Theme_Blvd_Layout_Builder_Screen::get_instance();

	// Include Theme_Blvd_Builder_API class.
	include_once( TB_BUILDER_PLUGIN_DIR . '/includes/api/class-tb-builder-api.php' );

	// Instantiate single object for Builder API.
	// Helper functions are located within theme
	// framework.
	Theme_Blvd_Builder_API::get_instance();

}
add_action( 'themeblvd_api', 'themeblvd_builder_api_init' );

/**
 * Register text domain for localization.
 *
 * @since 1.0.0
 */
function themeblvd_builder_textdomain() {
	load_plugin_textdomain( 'themeblvd_builder', false, TB_BUILDER_PLUGIN_DIR . '/lang' );
}
add_action( 'plugins_loaded', 'themeblvd_builder_textdomain' );