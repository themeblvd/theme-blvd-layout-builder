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
 * Display custom layout for themes with framework v2.5+
 *
 * @since 2.0.0
 */
function themeblvd_builder_layout() {

	global $post;

	// Check to make sure theme is up to date for this process.
	if ( ! function_exists( 'themeblvd_elements' ) ) {
		return;
	}

	// Where to pull custom layout data from. Will either be
	// current page or synced template.
	$post_id = themeblvd_config( 'builder_post_id' );

	// Get section data
	$section_data = get_post_meta( $post_id, '_tb_builder_sections', true );

	if ( ! $section_data ) {
		echo '<section class="element-section">';
		printf('<div class="alert alert-warning">%s</div>', __('The template has not been configured yet.', 'themeblvd_builder'));
		echo '</section>';
		return;
	}

	// Get elements for layout, which are organized within sections
	$sections = get_post_meta( $post_id, '_tb_builder_elements', true );

	// Loop through sections of elements
	if ( $sections ) {

		// Check for pagination handling
		$sections = themeblvd_builder_paginated_layout( $post_id, $sections );

		// Display sections of elements
		foreach ( $sections as $section_id => $elements ) {

			// Section classes
			$class = implode( ' ', themeblvd_get_section_class( $section_data[$section_id], count($elements) ) );

			// Display settings for section
			$display = array();

			if ( isset( $section_data[$section_id]['display'] ) ) {
				$display = $section_data[$section_id]['display'];
			}

			// Open section
			do_action( 'themeblvd_section_before', $section_id, $section_data[$section_id] );

			printf( '<section class="%s" style="%s" data-parallax="%s">', $class, themeblvd_get_display_inline_style($display), themeblvd_get_parallax_intensity($display) );

			if ( $display ) {
				if ( $display['bg_type'] == 'image' && $display['apply_bg_shade'] ) {
					printf( '<div class="bg-shade" style="background-color: %s; background-color: %s;"></div>', $display['bg_shade_color'], themeblvd_get_rgb( $display['bg_shade_color'], $display['bg_shade_opacity'] ) );
				}
			}

			do_action( 'themeblvd_section_top', $section_id, $section_data[$section_id] );

			// Display elements
			themeblvd_elements( $section_id, $elements );

			// Close section
			do_action( 'themeblvd_section_bottom', $section_id, $section_data[$section_id] );
			printf( '</section><!-- #%s (end) -->', $section_id );
			do_action( 'themeblvd_section_after', $section_id, $section_data[$section_id] );

			// End section
			do_action( 'themeblvd_section_close', $section_data[$section_id]['display'] );

		}

	} else {

		echo '<section class="element-section">';
		printf('<div class="alert alert-warning">%s</div>', __('No element data could be found for this custom layout.', 'themeblvd_builder'));
		echo '</section>';

	}

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
 * If we're on the second page of a paginated query,
 * we'll find the paginated element and see if all other
 * elements should be hidden. If so, we'll modify the sections
 * of elements to display;
 *
 * @since 2.0.0
 *
 * @param string $var Description
 * @return string $var Description
 */
function themeblvd_builder_paginated_layout( $post_id, $sections ){

	if ( is_paged() ) {

		$show_section_id = '';
		$show_element_id = '';

		// Hunt for the actual ID's of section and element we're going to keep.
		foreach ( $sections as $section_id => $elements ) {
			if ( $elements ) {
				foreach ( $elements as $element_id => $element ) {

					if ( ! isset( $element['type'] ) ) {
						continue;
					}

					if ( $element['type'] == 'post_list' || $element['type'] == 'post_grid' ) {

						if ( ! empty( $element['options']['paginated_hide'] ) ) {
							$show_section_id = $section_id;
							$show_element_id = $element_id;
						}

					} else if ( $element['type'] == 'columns' ) {

						$num = count( explode( '-', $element['options']['setup'] ) );

						for ( $i = 1; $i <= $num; $i++ ) {

							$blocks = get_post_meta( $post_id, '_tb_builder_'.$element_id.'_col_'.$i, true );

							if ( ! empty( $blocks['elements'] ) ) {
								foreach ( $blocks['elements'] as $block_id => $block ) {
									if ( ! empty( $block['options']['paginated_hide'] ) ) {
										$show_section_id = $section_id;
										$show_element_id = $element_id;
									}
								}
							}
						} // end for $i
					}
				} // end foreach $elements
			}
		} // end foreach $sections

		// Now remove everything that isn't part of what we want to keep.
		if ( $show_section_id && $show_element_id ) {
			foreach ( $sections as $section_id => $elements ) {

				if ( $section_id != $show_section_id ) {
					unset( $sections[$section_id] );
					continue;
				}

				if ( $elements ) {
					foreach ( $elements as $element_id => $element ) {
						if ( $element_id != $show_element_id ) {
							unset( $sections[$section_id][$element_id] );
						}
					}
				}
			}
		}

	} // end if ( is_paged() )

	return $sections;
}