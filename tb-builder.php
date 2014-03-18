<?php
/*
Plugin Name: Theme Blvd Layout Builder
Description: This plugins gives you a slick interface that ties int the Theme Blvd framework to create custom layouts for your WordPress pages.
Version: 1.2.3
Author: Theme Blvd
Author URI: http://themeblvd.com
License: GPL2

    Copyright 2013  Theme Blvd

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

define( 'TB_BUILDER_PLUGIN_VERSION', '1.2.3' );
define( 'TB_BUILDER_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_BUILDER_PLUGIN_URI', plugins_url( '' , __FILE__ ) );

/**
 * Run Layout Builder plugin
 *
 * @since 1.0.0
 */
function themeblvd_builder_init() {

	global $_themeblvd_layout_builder;

	// Include general functions
	include_once( TB_BUILDER_PLUGIN_DIR . '/includes/general.php' );

	// Check to make sure Theme Blvd Framework 2.2+ is running
	if ( ! defined( 'TB_FRAMEWORK_VERSION' ) || version_compare( TB_FRAMEWORK_VERSION, '2.2.0', '<' ) ) {
		add_action( 'admin_notices', 'themeblvd_builder_warning' );
		add_action( 'admin_init', 'themeblvd_builder_disable_nag' );
		return;
	}

	// If using framework v2.2.0, tell them they should now update to 2.2.1
	if ( version_compare( TB_FRAMEWORK_VERSION, '2.2.0', '=' ) ) {
		add_action( 'admin_notices', 'themeblvd_builder_warning_2' );
	}

	// If using framework version prior to v2.3, tell them API functions won't work.
	if ( version_compare( TB_FRAMEWORK_VERSION, '2.3.0', '<' ) ) {
		add_action( 'admin_notices', 'themeblvd_builder_warning_3' );
	}

	// Hook in check for nag to dismiss.
	add_action( 'admin_init', 'themeblvd_builder_disable_nag' );

	// Register custom layout hidden post type
	add_action( 'init', 'themeblvd_builder_register_post_type' );

	// Frontend actions -- These work in conjuction with framework theme files,
	// header.php, template_builder.php, and footer.php
	add_action( 'themeblvd_builder_content', 'themeblvd_builder_content' );
	add_action( 'themeblvd_featured', 'themeblvd_builder_featured' );
	add_action( 'themeblvd_featured_below', 'themeblvd_builder_featured_below' );

	// Get custom layouts
	$custom_layouts = array();
	if ( is_admin() ) {
		$custom_layout_posts = get_posts('post_type=tb_layout&orderby=title&order=ASC&numberposts=-1');
		if ( ! empty( $custom_layout_posts ) ) {
			foreach( $custom_layout_posts as $layout ) {
				$custom_layouts[$layout->post_name] = $layout->post_title;
			}
		} else {
			$custom_layouts['null'] = __( 'You haven\'t created any custom layouts yet.', 'themeblvd' );
		}
	}

	// Add option to theme options page allowing user to
	// select custom layout for their homepage.
	$options = array(
		'homepage_content' => array(
			'name' 		=> __( 'Homepage Content', 'themeblvd_builder' ),
			'desc' 		=> __( 'Select the content you\'d like to show on your homepage. Note that for this setting to take effect, you must go to Settings > Reading > Frontpage displays, and select "your latest posts."', 'themeblvd_builder' ),
			'id' 		=> 'homepage_content',
			'std' 		=> 'posts',
			'type' 		=> 'radio',
			'options' 	=> array(
				'posts'			=> __( 'Posts', 'themeblvd_builder' ),
				'custom_layout' => __( 'Custom Layout', 'themeblvd_builder' )
			)
		),
		'homepage_custom_layout' => array(
			'name' 		=> __( 'Select Custom Layout', 'themeblvd_builder' ),
			'desc' 		=> __( 'Select from the custom layouts you\'ve built under the <a href="admin.php?page=themeblvd_builder">Builder</a> section.', 'themeblvd_builder' ),
			'id' 		=> 'homepage_custom_layout',
			'std' 		=> '',
			'type' 		=> 'select',
			'options' 	=> $custom_layouts
		)
	);
	themeblvd_add_option_section( 'content', 'homepage', __( 'Homepage', 'themeblvd_builder' ), null, $options, true );

	// Filter homepage content according to options section
	// we added above.
	add_filter( 'template_include', 'themeblvd_builder_homepage' );

	// Trigger customizer support for custom homepage options.
	add_filter( 'themeblvd_customizer_modify_sections', 'themeblvd_modify_customizer_homepage' );

	// Admin Layout Builder
	if ( is_admin() ){
		// Check to make sure admin interface isn't set to be
		// hidden and for the appropriate user capability
		if ( themeblvd_supports( 'admin', 'builder' ) && current_user_can( themeblvd_admin_module_cap( 'builder' ) ) ) {
			include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/builder-samples.php' );
			include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/class-tb-layout-builder-ajax.php' );
			include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/class-tb-layout-builder.php' );
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

	// Include screen options class (used in API)
	include_once( TB_BUILDER_PLUGIN_DIR . '/includes/admin/class-tb-layout-builder-screen.php' );

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