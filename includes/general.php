<?php
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
 * @param string $location Location of elements, featured or primary -- @deprecated
 */
function themeblvd_builder_elements( $layout_id, $location = '' ) {

	$api = Theme_Blvd_Builder_API::get_instance();

	// Setup
	$counter = 0;
	$primary_query = false;
	$previous_popout = false;

	if ( ! $layout_id ) {
		// This should rarely happen. A common scenario might
		// be the user setup a page with a layout, but then
		// deleted the layout after page was already published.
		echo themeblvd_get_local( 'invalid_layout' );
		return;
	}

	// Gather elements and only move forward if we have elements to show.
	$elements = get_post_meta( $layout_id, 'elements', true );

	// @deprecated -- Grab elements by location. Starting with
	// v2.0 of the builder and and theme framework 2.5, we no
	// longer have locations for elements.
	if ( $location && isset( $elements[$location] ) ) {
		$elements = $elements[$location];
	}

	// If there are no elements get us out of here!
	if ( ! is_array($elements) || count($elements) == 0 ) {
		return;
	}

	// Number of elements
	$total = count($elements);

	// Loop through elements
	foreach ( $elements as $id => $element ) {

		// Skip element if its type isn't registered
		if ( ! $api->is_element( $element['type'] ) ) {
			continue;
		}

		// Increase counter
		$counter++;

		// Allow things to jive with old theme
		if ( ! isset( $element['display'] ) ) {
			$element['display'] = array();
		}

		// Handle sectioning
		if ( $counter == 1 ) {
			do_action( 'themeblvd_section_open', $element['display'] );
		}

		if ( $counter >= 2 && themeblvd_builder_element_popout( $element['display'] ) && ! $previous_popout ) {
			do_action( 'themeblvd_section_close', $element['display'] );
		}

		if ( $counter >= 2 && ( themeblvd_builder_element_popout( $element['display'] ) || $previous_popout ) ) {
			do_action( 'themeblvd_section_open', $element['display'] );
		}

		// CSS classes for element
		$class = implode( ' ', themeblvd_builder_get_element_class( $element, $counter, $total ) );

		// Any added classes by the theme
		$add_class = themeblvd_get_classes( 'element_'.$element['type'], true, false, $element['type'], $element['options'], $location );

		if ( $add_class ) {
			$class .= ' '.$add_class;
		}

		// Start ouput
		do_action( 'themeblvd_element_'.$element['type'].'_before', $id, $element['options'], $location, $element['display']  ); // Before element: themeblvd_element_{type}_before
		do_action( 'themeblvd_element_open', $element['type'], $location, $class, $element['display'] );
		do_action( 'themeblvd_element_'.$element['type'].'_top', $id, $element['options'], $location, $element['display']  ); // Top of element: themeblvd_element_{type}_top
		echo '<div class="grid-protection">';

		switch( $element['type'] ) {

			/*------------------------------------------------------*/
			/* Columns
			/*------------------------------------------------------*/

			case 'columns' :

				if ( function_exists('themeblvd_blocks') ) { // theme framework 2.5+

					$num = 1;
					if ( is_string( $element['options']['setup'] ) ) {
						$num = count( explode( '-', $element['options']['setup'] ) );
					}

					$args = array(
						'layout_id'		=> $layout_id,
						'element_id' 	=> $id,
						'num'			=> $num,
						'widths'		=> $element['options']['setup'],
						'height'		=> $element['options']['height'],
						'align'			=> $element['options']['align']
					);
					themeblvd_columns( $args );

				} else {

					// @deprecated

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

				}
				break;

			/*------------------------------------------------------*/
			/* Content
			/*------------------------------------------------------*/

			case 'content' :

				if ( function_exists('themeblvd_blocks') ) { // theme framework 2.5+

					$blocks = array();
					$column = get_post_meta( $layout_id, $id.'_col_1', true );

					if ( ! empty( $column['blocks'] ) ) {
						$blocks = $column['blocks'];
					}

					themeblvd_blocks( $blocks );

				} else {

					// @deprecated

					if ( ! function_exists( 'themeblvd_content' ) ) {
						_e('Content element not supported.', 'themeblvd_builder');
						break;
					}

					echo themeblvd_content( $element['options'] );

				}

				break;

			/*------------------------------------------------------*/
			/* Divider
			/*------------------------------------------------------*/

			case 'divider' :

				if ( ! function_exists( 'themeblvd_divider' ) ) {
					_e('Divider element not supported.', 'themeblvd_builder');
					break;
				}

				if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) {
					echo themeblvd_divider( $element['options'] );
				} else {
					echo themeblvd_divider( $element['options']['type'] );
				}

				break;

			/*------------------------------------------------------*/
			/* Google Map
			/*------------------------------------------------------*/

			case 'map' :

				if ( ! function_exists( 'themeblvd_map' ) ) {
					_e('Google Map element not supported.', 'themeblvd_builder');
					break;
				}

				themeblvd_map( $element['options'] );

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
			/* HTML
			/*------------------------------------------------------*/

			case 'html' :
				echo $element['options']['html'];
				break;

			/*------------------------------------------------------*/
			/* Image
			/*------------------------------------------------------*/

			case 'image' :

				if ( ! function_exists( 'themeblvd_image' ) ) {
					_e('Image element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_image( $element['options'] );

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
			/* Milestones
			/*------------------------------------------------------*/

			case 'milestones' :

				if ( ! function_exists( 'themeblvd_milestones' ) ) {
					_e('Milestones element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_milestones( $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Post Grid
			/*------------------------------------------------------*/

			case 'post_grid' :

				if ( ! function_exists( 'themeblvd_posts' ) ) {
					_e('Post Grid element not supported.', 'themeblvd_builder');
					break;
				}

				themeblvd_posts( $element['options'], 'grid', $location );

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

				themeblvd_posts( $element['options'], 'list', $location );

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
			/* Simple Slider
			/*------------------------------------------------------*/

			case 'simple_slider' :
			case 'simple_slider_popout' :

				if ( ! function_exists( 'themeblvd_simple_slider' ) ) {
					_e('Simple Slider element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_simple_slider( $element['options'] );

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
			/* Tabs -- @deprecated as of 2.0.0
			/*------------------------------------------------------*/

			case 'tabs' :

				if ( ! function_exists( 'themeblvd_tabs' ) ) {
					_e('Tabs element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_tabs( $id, $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Video
			/*------------------------------------------------------*/

			case 'video' :

				if ( ! function_exists( 'themeblvd_video' ) ) {
					_e('Video element not supported.', 'themeblvd_builder');
					break;
				}

				echo themeblvd_video( $element['options']['video'] );

				break;

		} // End switch

		// Allow to add on custom element that's
		// not in the framework
		do_action( 'themeblvd_'.$element['type'], $id, $element['options'], $location );

		// End output
		echo '<div class="clear"></div>';
		echo '</div><!-- .grid-protection (end) -->';
		do_action( 'themeblvd_element_'.$element['type'].'_bottom', $id, $element['options'], $location, $element['display']  ); // Bottom of element: themeblvd_element_{type}_bottom
		do_action( 'themeblvd_element_close', $element['type'], $location, $class, $element['display']  );
		do_action( 'themeblvd_element_'.$element['type'].'_after', $id, $element['options'], $location, $element['display']  ); // Below element: themeblvd_element_{type}_bottom

		if ( themeblvd_builder_element_popout( $element['display'] ) ) {
			$previous_popout = true;
			do_action( 'themeblvd_section_close', $element['display'] );
		} else {
			$previous_popout = false;
		}

	} // End foreach

	// End final section
	do_action( 'themeblvd_section_close', $element['display'] );
}

/**
 * Verify data from current custom layout is saved
 * properly with the current version of the Layout
 * Builder plugin.
 *
 * @since 2.0.0
 */
function themeblvd_builder_verify_data() {
	if ( themeblvd_config( 'builder' ) && themeblvd_config( 'builder_post_id' ) ) {
		$data = new Theme_Blvd_Layout_Builder_Data( themeblvd_config( 'builder_post_id' ) );
		$data->verify('elements');
		$data->verify('info');
		$data->finalize();
	}
}

/**
 * Display builder elements above the primary area.
 *
 * @since 1.0.0
 */
function themeblvd_builder_content() {
	if ( themeblvd_config( 'builder' ) ) {
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
			themeblvd_builder_elements( themeblvd_config( 'builder_post_id' ) );
		} else {
			themeblvd_builder_elements( themeblvd_config( 'builder_post_id' ), 'primary' ); // @deprecated
		}
	}
}

/**
 * Display builder elements above the primary area.
 *
 * @since 1.0.0
 */
function themeblvd_builder_featured() {
	if ( themeblvd_config( 'builder' ) ) {
		themeblvd_builder_elements( themeblvd_config( 'builder_post_id' ), 'featured' ); // @deprecated
	}
}

/**
 * Display builder elements below the primary area.
 *
 * @since 1.0.0
 */
function themeblvd_builder_featured_below() {
	if ( themeblvd_config( 'builder' ) ) {
		themeblvd_builder_elements( themeblvd_config( 'builder_post_id' ), 'featured_below' ); // @deprecated
	}
}

/**
 * Return true if the current builder element should be
 * popped out into its own section.
 *
 * @since 2.0.0
 *
 * @param array $display The element's display options
 * @return bool Whether the builder element has a custom background
 */
function themeblvd_builder_element_popout( $display ) {

	if ( ! empty( $display['bg_type'] ) && $display['bg_type'] != 'none' ) {
		return true;
	}

	if ( ! empty( $display['apply_popout'] ) ) {
		return true;
	}

	return false;
}

/**
 * Get CSS classes needed for individual element
 *
 * @since 2.0.0
 *
 * @param array $element The element array, with all options and information
 * @param int $num Current element's number in the stack
 * @param int $total Total number of elements in the stack
 * @return string $class CSS class to return
 */
function themeblvd_builder_get_element_class( $element, $num, $total ){

	$class = array( 'element', 'element-'.$num, 'element-'.$element['type'] );

	// First and last elements
	if ( $num == 1 ) {
		$class[] = 'first-element';
	}
	if ( $total == $num ) {
		$class[] = 'last-element';
	}

	// Display classes
	if ( ! empty( $element['display'] ) ) {

		$bg_type = $element['display']['bg_type'];

		// Does the element have custom padding?
		if ( ! empty( $element['display']['apply_padding'] ) ) {
			$class[] = 'has-custom-padding';
		}

	}

	// For custom sliders, we can specify the type of slider
	if ( $element['type'] == 'slider' ) {
		if ( isset( $element['options']['slider_id'] ) ) {
			$slider_id = themeblvd_post_id_by_name( $element['options']['slider_id'], 'tb_slider' );
			$type = get_post_meta( $slider_id, 'type', true );
			$class[] = 'element-slider-'.$type;
		}
	}

	// For paginated post list/grid we want to output the shared
	// class that Post List/Grid page templates, and main index.php
	// are using.
	if ( $element['type'] == 'paginated_post_list' || $element['type'] == 'paginated_post_grid' ) {
		$class[] = $element['type'];
	}

	// Responsive visibility
	if ( ! empty( $element['display']['visibility'] ) ) {
		$class[] = themeblvd_responsive_visibility_class( $element['display']['visibility'] );
	} else if ( ! empty( $element['options']['visibility'] ) ) { // @deprecated
		$class[] = themeblvd_responsive_visibility_class( $element['options']['visibility'] );
	}

	// User-added CSS classes
	if ( ! empty( $element['display']['classes'] ) ) {
		$class[] = $element['display']['classes'];
	} else if ( ! empty( $element['options']['classes'] ) ) { // @deprecated
		$class[] = $element['options']['classes'];
	}

	return apply_filters( 'themeblvd_builder_element_class', $class, $element );
}