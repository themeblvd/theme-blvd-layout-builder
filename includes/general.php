<?php
/**
 * Disable a nag message.
 *
 * @since 1.1.0
 */
function themeblvd_builder_disable_nag() {
	global $current_user;
    if ( isset( $_GET['tb_nag_ignore'] ) ) {
         add_user_meta( $current_user->ID, $_GET['tb_nag_ignore'], 'true', true );
	}
}

/**
 * Disable a nag message URL.
 *
 * @since 1.2.0
 */
function themeblvd_builder_disable_url( $id ) {

	global $pagenow;

	$url = admin_url( $pagenow );

	if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
		$url .= sprintf( '?%s&tb_nag_ignore=%s', $_SERVER['QUERY_STRING'], $id );
	} else {
		$url .= sprintf( '?tb_nag_ignore=%s', $id );
	}

	return $url;
}

/**
 * Display warning telling the user they must have a
 * theme with Theme Blvd framework v2.2+ installed in
 * order to run this plugin.
 *
 * @since 1.0.0
 */
function themeblvd_builder_warning() {
	global $current_user;
    if ( ! get_user_meta( $current_user->ID, 'tb_builder_warning' ) ) {
		echo '<div class="updated">';
		echo '<p>'.__( 'You currently have the "Theme Blvd Layout Builder" plugin activated, however you are not using a theme with Theme Blvd Framework v2.2+, and so this plugin will not do anything.', 'themeblvd_builder' ).'</p>';
		echo '<p><a href="'.themeblvd_builder_disable_url('tb_builder_warning').'">'.__('Dismiss this notice', 'themeblvd_builder').'</a> | <a href="http://www.themeblvd.com" target="_blank">'.__('Visit ThemeBlvd.com', 'themeblvd_builder').'</a></p>';
		echo '</div>';
	}
}

/**
 * Display warning telling the user they should be using
 * theme with Theme Blvd framework v2.2.1+.
 *
 * @since 1.1.0
 */
function themeblvd_builder_warning_2() {
	global $current_user;
    if ( ! get_user_meta( $current_user->ID, 'tb_builder_warning_2' ) ) {
        echo '<div class="updated">';
        echo '<p>'.__( 'You are currently running a theme with Theme Blvd framework v2.2.0. To get the best results from this version of the Theme Blvd Layout Builder, you should update your current theme to its latest version, which will contain framework v2.2.1+.', 'themeblvd_builder' ).'</p>';
        echo '<p><a href="'.themeblvd_builder_disable_url('tb_builder_warning_2').'">'.__('Dismiss this notice', 'themeblvd_builder').'</a></p>';
        echo '</div>';
    }
}

/**
 * Display warning telling the user they should be using
 * theme with Theme Blvd framework v2.2.1+.
 *
 * @since 1.2.0
 */
function themeblvd_builder_warning_3() {
	global $current_user;
    if ( ! get_user_meta( $current_user->ID, 'tb_builder_warning_3' ) ) {
        echo '<div class="updated">';
        echo '<p>'.__( 'If you\'re using any Layout Builder API functions to <a href="http://dev.themeblvd.com/tutorial/add-remove-builder-elements/" target="_blank">modify elements</a> or <a href="http://dev.themeblvd.com/tutorial/add-remove-sample-layout/" target="_blank">modify sample layouts</a>, you need to update your current theme to its latest version, which will contain framework v2.3+, in order for these API functions to continue working properly.', 'themeblvd_builder' ).'</p>';
        echo '<p><a href="'.themeblvd_builder_disable_url('tb_builder_warning_3').'">'.__('Dismiss this notice', 'themeblvd_builder').'</a></p>';
        echo '</div>';
    }
}

/**
 * Register hidden custom post type for layouts.
 *
 * @since 1.2.0
 */
function themeblvd_builder_register_post_type() {
	$args = apply_filters( 'themeblvd_builder_post_type_args', array(
		'labels' 			=> array( 'name' => 'Layouts', 'singular_name' => 'Layout' ),
		'public'			=> false,
		//'show_ui' 		=> true,	// Can uncomment for debugging
		'query_var' 		=> true,
		'capability_type' 	=> 'post',
		'hierarchical' 		=> false,
		'rewrite' 			=> false,
		'supports' 			=> array( 'title', 'custom-fields' ),
		'can_export'		=> true
	));
	register_post_type( 'tb_layout', $args );
}
/**
 * Redirect homepage to index.php to the custom
 * layout template if option is set. This is
 * filtered to template_include.
 *
 * @since 1.0.1
 */
function themeblvd_builder_homepage( $template ) {

	// If this is the homepage (but NOT the "posts page")
	// and the user has selected to show a custom layout,
	// redirect index.php to template_builder.php
	if ( is_home() && 'posts' == get_option('show_on_front') && 'custom_layout' == themeblvd_get_option( 'homepage_content', null, 'posts' ) ) {
		$template = locate_template( 'template_builder.php' );
	}

	return $template;
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
 * @since 1.0.0
 *
 * @param string $layout_id Post ID for custom layout
 * @param string $location Location of elements, featured or primary
 */
function themeblvd_builder_elements( $layout_id, $location ) {

	$api = Theme_Blvd_Builder_API::get_instance();

	// Setup
	$counter = 0;
	$primary_query = false;
	if ( ! $layout_id ) {
		// This should rarely happen. A common scenario might
		// be the user setup a page with a layout, but then
		// deleted the layout after page was already published.
		echo themeblvd_get_local( 'invalid_layout' );
		return;
	}
	// Gather elements and only move forward if we have elements to show.
	$elements = get_post_meta( $layout_id, 'elements', true );
	if ( ! empty( $elements ) && ! empty( $elements[$location] ) ) {
		$elements = $elements[$location];
		$num_elements = count($elements);
	} else {
		// If there are no elements in this location,
		// get us out of here!
		return;
	}

	// Loop through elements
	foreach ( $elements as $id => $element ) {

		// Skip element if its type isn't registered
		if ( ! $api->is_element( $element['type'] ) ) {
			continue;
		}

		// Increase counter
		$counter++;

		// CSS classes for element
		$classes = 'element '.$location.'-element-'.$counter.' element-'.$element['type'];
		if ( $counter == 1 ) {
			$classes .= ' first-element';
		}
		if ( $num_elements == $counter ) {
			$classes .= ' last-element';
		}
		if ( $element['type'] == 'slider' ) {
			if ( isset( $element['options']['slider_id'] ) ) {
				$slider_id = themeblvd_post_id_by_name( $element['options']['slider_id'], 'tb_slider' );
				$type = get_post_meta( $slider_id, 'type', true );
				$classes .= ' element-slider-'.$type;
			}
		}
		if ( $element['type'] == 'paginated_post_lst' || $element['type'] == 'paginated_post_grid' ) {
			$classes .= $element['type'];
		}
		if ( ! empty( $element['options']['classes'] ) ) {
			$classes .= ' '.$element['options']['classes'];
		}
		if ( isset( $element['options']['visibility'] ) ) {
			$classes .= themeblvd_responsive_visibility_class( $element['options']['visibility'], true );
		}
		$classes .= themeblvd_get_classes( 'element_'.$element['type'], true, false, $element['type'], $element['options'], $location );

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

				if ( ! function_exists( 'themeblvd_columns' ) ) {
					_e('Columns element not supported.', 'themeblvd_builder');
					break;
				}

				$i = 1;
				$columns = array();
				$num = $element['options']['setup']['num'];
				while ( $i <= $num ) {
					$columns[] = $element['options']['col_'.$i];
					$i++;
				}
				themeblvd_columns( $num, $element['options']['setup']['width'][$num], $columns );
				break;

			/*------------------------------------------------------*/
			/* Content
			/*------------------------------------------------------*/

			case 'content' :

				if ( ! function_exists( 'themeblvd_content' ) ) {
					_e('Content element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_content( $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Divider
			/*------------------------------------------------------*/

			case 'divider' :

				if ( ! function_exists( 'themeblvd_divider' ) ) {
					_e('Divider element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_divider( $element['options']['type'] );

				break;

			/*------------------------------------------------------*/
			/* Headline
			/*------------------------------------------------------*/

			case 'headline' :

				if ( ! function_exists( 'themeblvd_headline' ) ) {
					_e('Headline element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_headline( $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Jumbotron
			/*------------------------------------------------------*/

			case 'jumbotron' :

				if ( ! function_exists( 'themeblvd_jumbotron' ) ) {
					_e('Jumbotron element not supported.', 'themeblvd_builder');
					break;
				}

				themeblvd_jumbotron( $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Post Grid
			/*------------------------------------------------------*/

			case 'post_grid' :

				if ( ! function_exists( 'themeblvd_posts' ) ) {
					_e('Post Grid element not supported.', 'themeblvd_builder');
					break;
				}

				themeblvd_posts( $element['options'], 'grid', $location, 'secondary' );

				break;

			/*------------------------------------------------------*/
			/* Post Grid (paginated)
			/*------------------------------------------------------*/

			case 'post_grid_paginated' :

				if ( ! function_exists( 'themeblvd_posts_paginated' ) ) {
					_e('Paginated Post Grid element not supported.', 'themeblvd_builder');
					break;
				}

				if ( ! $primary_query ) {
					themeblvd_posts_paginated( $element['options'], 'grid', $location );
					$primary_query = true;
				}

				break;

			/*------------------------------------------------------*/
			/* Post Grid Slider
			/*------------------------------------------------------*/

			case 'post_grid_slider' :

				if ( ! function_exists( 'themeblvd_post_slider' ) ) {
					_e('Post Grid Slider element not supported.', 'themeblvd_builder');
					break;
				}

				themeblvd_post_slider( $id, $element['options'], 'grid', $location );

				break;

			/*------------------------------------------------------*/
			/* Post List
			/*------------------------------------------------------*/

			case 'post_list' :

				if ( ! function_exists( 'themeblvd_posts' ) ) {
					_e('Post List element not supported.', 'themeblvd_builder');
					break;
				}

				themeblvd_posts( $element['options'], 'list', $location, 'secondary' );

				break;

			/*------------------------------------------------------*/
			/* Post List (paginated)
			/*------------------------------------------------------*/

			case 'post_list_paginated' :

				if ( ! function_exists( 'themeblvd_posts_paginated' ) ) {
					_e('Paginated Post List element not supported.', 'themeblvd_builder');
					break;
				}

				if ( ! $primary_query ) {
					themeblvd_posts_paginated( $element['options'], 'list', $location );
					$primary_query = true;
				}

				break;

			/*------------------------------------------------------*/
			/* Post List Slider
			/*------------------------------------------------------*/

			case 'post_list_slider' :

				if ( ! function_exists( 'themeblvd_post_slider' ) ) {
					_e('Post List Slider element not supported.', 'themeblvd_builder');
					break;
				}

				themeblvd_post_slider( $id, $element['options'], 'list', $location );

				break;

			/*------------------------------------------------------*/
			/* Post Slider (mimics standard slider)
			/*------------------------------------------------------*/

			case 'post_slider' :

				if ( ! function_exists( 'themeblvd_slider_auto' ) ) {
					_e('Post Slider element not supported.', 'themeblvd_builder');
					break;
				}

				themeblvd_slider_auto( $id, $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Slider
			/*------------------------------------------------------*/

			case 'slider' :

				if ( ! function_exists( 'themeblvd_slider' ) ) {
					_e('Slider element not supported.', 'themeblvd_builder');
					break;
				}

				themeblvd_slider( $element['options']['slider_id'] );

				break;

			/*------------------------------------------------------*/
			/* Slogan
			/*------------------------------------------------------*/

			case 'slogan' :

				if ( ! function_exists( 'themeblvd_slogan' ) ) {
					_e('Slogan element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_slogan( $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Tabs
			/*------------------------------------------------------*/

			case 'tabs' :

				if ( ! function_exists( 'themeblvd_tabs' ) ) {
					_e('Tabs element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_tabs( $id, $element['options'] );

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
	if ( themeblvd_config( 'builder' ) ) {
		themeblvd_builder_elements( themeblvd_config( 'builder_post_id' ), 'primary' );
	}
}
/**
 * Display builder elements above the primary area.
 *
 * @since 1.0.0
 */
function themeblvd_builder_featured() {
	if ( themeblvd_config( 'builder' ) ) {
		themeblvd_builder_elements( themeblvd_config( 'builder_post_id' ), 'featured' );
	}
}

/**
 * Display builder elements below the primary area.
 *
 * @since 1.0.0
 */
function themeblvd_builder_featured_below() {
	if ( themeblvd_config( 'builder' ) ) {
		themeblvd_builder_elements( themeblvd_config( 'builder_post_id' ), 'featured_below' );
	}
}