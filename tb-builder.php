<?php
/*
Plugin Name: Theme Blvd Layout Builder
Plugin URI: 
Description: This plugins gives you a slick interface that ties int the Theme Blvd framework to create custom layouts for your WordPress pages.
Version: 1.0.0
Author: Jason Bobich
Author URI: http://jasonbobich.com
License: GPL2

    Copyright 2012  Jason Bobich

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

define( 'TB_BUILDER_PLUGIN_VERSION', '1.0.0' );
define( 'TB_BUILDER_PLUGIN_DIR', dirname( __FILE__ ) ); 
define( 'TB_BUILDER_PLUGIN_URI', plugins_url( '' , __FILE__ ) );

/**
 * Run Layout Builder plugin
 *
 * @since 1.0.0
 */

function themeblvd_builder_init() {
	
	global $_themeblvd_layout_builder;
	
	// Check to make sure Theme Blvd Framework 2.2+ is running
	if( ! defined( 'TB_FRAMEWORK_VERSION' ) || version_compare( TB_FRAMEWORK_VERSION, '2.2.0', '<' ) ) {
		add_action( 'admin_notices', 'themeblvd_builder_warning' );
		return;
	}
	
	// Frontend actions -- These work in conjuction with framework theme files, 
	// header.php, template_builder.php, and footer.php
	add_action( 'themeblvd_builder_content', 'themeblvd_builder_content' );
	add_action( 'themeblvd_featured', 'themeblvd_builder_featured' );
	add_action( 'themeblvd_featured_below', 'themeblvd_builder_featured_below' );
	
	// Get custom layouts
	$custom_layouts = array();
	$custom_layout_posts = get_posts('post_type=tb_layout&numberposts=-1');
	if( ! empty( $custom_layout_posts ) ) {
		foreach( $custom_layout_posts as $layout )
			$custom_layouts[$layout->post_name] = $layout->post_title;
	} else {
		$custom_layouts['null'] = __( 'You haven\'t created any custom layouts yet.', 'themeblvd' );
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
	add_filter( 'themeblvd_homepage_content', 'themeblvd_builder_homepage_content' );
	
	// Trigger customizer support for custom homepage options.
	add_filter( 'themeblvd_customizer_modify_sections', 'themeblvd_modify_customizer_homepage' );
	
	// Admin Layout Builder
	if( is_admin() ){
		// Check to make sure admin interface isn't set to be 
		// hidden and for the appropriate user capability
		if ( themeblvd_supports( 'admin', 'builder' ) && current_user_can( themeblvd_admin_module_cap( 'builder' ) ) ) {
			include_once( TB_BUILDER_PLUGIN_DIR . '/admin/builder-samples.php' );
			include_once( TB_BUILDER_PLUGIN_DIR . '/admin/class-tb-layout-builder.php' );
			$_themeblvd_layout_builder = new Theme_Blvd_Layout_Builder();
		}
	}
	
}
add_action( 'after_setup_theme', 'themeblvd_builder_init' );

/**
 * Register text domain for localization.
 *
 * @since 1.0.0
 */

function themeblvd_builder_textdomain() {
	load_plugin_textdomain( 'themeblvd_builder', false, TB_SIDEBARS_PLUGIN_DIR . '/lang' );
}
add_action( 'plugins_loaded', 'themeblvd_builder_textdomain' );

/**
 * Display warning telling the user they must have a 
 * theme with Theme Blvd framework v2.2+ installed in 
 * order to run this plugin.
 *
 * @since 1.0.0
 */

function themeblvd_builder_warning() {
	echo '<div class="updated">';
	echo '<p>'.__( 'You currently have the "Theme Blvd Layout Builder" plugin activated, however you are not using a theme with Theme Blvd Framework v2.2+, and so this plugin will not do anything.', 'themeblvd_builder' ).'</p>';
	echo '</div>';
}

/**
 * Filter homepage content var for when index.php of 
 * the theme runs.
 *
 * @since 1.0.0
 */

function themeblvd_builder_homepage_content( $content ) {
	$content = themeblvd_get_option( 'homepage_content', null, 'posts' );
	return $content;
}

/**
 * Add custom homepage options to customizer framework.
 *
 * @since 1.0.0
 */

function themeblvd_modify_customizer_homepage( $sections ) {
	$sections[] = 'static_front_page';
	return $sections;
}

/**
 * Display custom layout within template_builder.php 
 * page template.
 *
 * When each element is displayed, it is done so with 
 * an external function. This will allow some elements 
 * to be used for other things such as shortcodes. 
 * However, even elements that shouldn't have an external 
 * function do to allow those elements to be indidivually 
 * edited from a child theme.
 *
 * @since 2.0.0
 *
 * @param string $layout Post slug for layout
 * @param string $location Location of elements, featured or primary
 */
 
function themeblvd_builder_elements( $layout, $location ) {
	
	// Setup
	$counter = 0;
	$primary_query = false;
	$layout_id = themeblvd_post_id_by_name( $layout, 'tb_layout' );
	if( ! $layout_id ) {
		// This should rarely happen. A common scenario might 
		// be the user setup a page with a layout, but then 
		// deleted the layout after page was already published.
		echo themeblvd_get_local( 'invalid_layout' );
		return;
	}
	// Gather elements and only move forward if we have elements to show.
	$elements = get_post_meta( $layout_id, 'elements', true );
	if( ! empty( $elements ) && ! empty( $elements[$location] ) ) {
		$elements = $elements[$location];
		$num_elements = count($elements);
	} else {
		// If there are no elements in this location, 
		// get us out of here!
		return;
	}

	// Loop through elements
	foreach( $elements as $id => $element ) {
		
		// Skip element if its type isn't registered
		if( ! themeblvd_is_element( $element['type'] ) )
			continue;
		
		// Increase counter
		$counter++;
		
		// CSS classes for element
		$classes = 'element '.$location.'-element-'.$counter.' element-'.$element['type'];
		if( $counter == 1 )
			$classes .= ' first-element';
		if( $num_elements == $counter )
			$classes .= ' last-element';
		if( $element['type'] == 'slider' ) {
			if( isset( $element['options']['slider_id'] ) ) {
				$slider_id = themeblvd_post_id_by_name( $element['options']['slider_id'], 'tb_slider' );
				$type = get_post_meta( $slider_id, 'type', true );
				$classes .= ' element-slider-'.$type;
			}
		}
		if( $element['type'] == 'paginated_post_lst' || $element['type'] == 'paginated_post_grid' )
			$classes .= $element['type'];
		if( isset( $element['options']['visibility'] ) )
			$classes .= themeblvd_responsive_visibility_class( $element['options']['visibility'], true );
		$classes .= themeblvd_get_classes( 'element_'.$element['type'], true, false, $element['type'], $element['options'] );
		
		// Start ouput
		do_action( 'themeblvd_element_'.$element['type'].'_before', $id, $element['options'], $location ); // Before element: themeblvd_element_{type}_before
		do_action( 'themeblvd_element_open', $element['type'], $location, $classes );
		do_action( 'themeblvd_element_'.$element['type'].'_top', $id, $element['options'], $location ); // Top of element: themeblvd_element_{type}_top
		echo '<div class="grid-protection">';
		
		switch( $element['type'] ) {
			
			/*------------------------------------------------------*/
			/* Columns
			/*------------------------------------------------------*/
			
			case 'columns' :
				$i = 1;
				$columns = array();
				$num = $element['options']['setup']['num'];
				while( $i <= $num ) {
					$columns[] = $element['options']['col_'.$i];
					$i++;
				}
				themeblvd_columns( $num, $element['options']['setup']['width'][$num], $columns );
				break;
			
			/*------------------------------------------------------*/
			/* Content
			/*------------------------------------------------------*/
			
			case 'content' :
				echo themeblvd_content( $element['options'] );
				break;
			
			/*------------------------------------------------------*/
			/* Divider
			/*------------------------------------------------------*/
			
			case 'divider' :
				echo themeblvd_divider( $element['options']['type'] );
				break;
				
			/*------------------------------------------------------*/
			/* Headline
			/*------------------------------------------------------*/
			
			case 'headline' :
				echo themeblvd_headline( $element['options'] );
				break;
				
			/*------------------------------------------------------*/
			/* Post Grid
			/*------------------------------------------------------*/
			
			case 'post_grid' :
				themeblvd_posts( $element['options'], 'grid', $location, 'secondary' );
				break;
				
			/*------------------------------------------------------*/
			/* Post Grid (paginated)
			/*------------------------------------------------------*/
			
			case 'post_grid_paginated' :
				if( ! $primary_query ) {
					themeblvd_posts_paginated( $element['options'], 'grid', $location );
					$primary_query = true;
				}
				break;
				
			/*------------------------------------------------------*/
			/* Post Grid Slider
			/*------------------------------------------------------*/
			
			case 'post_grid_slider' :
				themeblvd_post_slider( $id, $element['options'], 'grid', $location );
				break;
				
			/*------------------------------------------------------*/
			/* Post List
			/*------------------------------------------------------*/
			
			case 'post_list' :
				themeblvd_posts( $element['options'], 'list', $location, 'secondary' );
				break;
				
			/*------------------------------------------------------*/
			/* Post List (paginated)
			/*------------------------------------------------------*/
			
			case 'post_list_paginated' :
				if( ! $primary_query ) {
					themeblvd_posts_paginated( $element['options'], 'list', $location );
					$primary_query = true;
				}
				break;
				
			/*------------------------------------------------------*/
			/* Post List Slider
			/*------------------------------------------------------*/
			
			case 'post_list_slider' :
				themeblvd_post_slider( $id, $element['options'], 'list', $location );
				break;
				
			/*------------------------------------------------------*/
			/* Slider
			/*------------------------------------------------------*/
			
			case 'slider' :
				themeblvd_slider( $element['options']['slider_id'] );
				break;
				
			/*------------------------------------------------------*/
			/* Slogan
			/*------------------------------------------------------*/
			
			case 'slogan' :
				echo themeblvd_slogan( $element['options'] );
				break;
				
			/*------------------------------------------------------*/
			/* Tabs
			/*------------------------------------------------------*/
			
			case 'tabs' :
				echo themeblvd_tabs( $id, $element['options'] );
				break;
				
			/*------------------------------------------------------*/
			/* Recent Tweet
			/*------------------------------------------------------*/
			
			case 'tweet' :
				echo themeblvd_tweet( $element['options'] );
				break;
			
		} // End switch
		
		// Allow to add on custom element that's
		// not in the framework
		do_action( 'themeblvd_'.$element['type'], $id, $element['options'], $location );
		
		// End output
		echo '<div class="clear"></div>';
		echo '</div><!-- .grid-protection (end) -->';
		do_action( 'themeblvd_element_'.$element['type'].'_bottom', $id, $element['options'], $location ); // Bottom of element: themeblvd_element_{type}_bottom
		do_action( 'themeblvd_element_close', $element['type'], $location, $classes );
		do_action( 'themeblvd_element_'.$element['type'].'_after', $id, $element['options'], $location ); // Below element: themeblvd_element_{type}_bottom
		
	} // End foreach
			
}

/**
 * Display builder elements above the primary area.
 *
 * @since 1.0.0
 */

function themeblvd_builder_content() {
	if( themeblvd_config( 'builder' ) )
		themeblvd_builder_elements( themeblvd_config( 'builder' ), 'primary' );	
}

/**
 * Display builder elements above the primary area.
 *
 * @since 1.0.0
 */

function themeblvd_builder_featured() {
	if( themeblvd_config( 'builder' ) )
		themeblvd_builder_elements( themeblvd_config( 'builder' ), 'featured' );	
}

/**
 * Display builder elements below the primary area.
 *
 * @since 1.0.0
 */
 
function themeblvd_builder_featured_below() {
	if( themeblvd_config( 'builder' ) )
		themeblvd_builder_elements( themeblvd_config( 'builder' ), 'featured_below' );	
}