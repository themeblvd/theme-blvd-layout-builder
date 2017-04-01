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
 *
 * @param string $context Where the custom layout is being outputted, main or footer
 */
function themeblvd_builder_layout( $context ) {

	global $post;

	// Check to make sure theme is up to date for this process.
	if ( ! function_exists( 'themeblvd_elements' ) ) {
		return;
	}

	// Where to pull custom layout data from. Will either be
	// current page or synced template.
	if ( $context == 'footer' ) {
		$post_id = themeblvd_set_att('footer_sync', themeblvd_config('bottom_builder_post_id') );
		$layout_name = themeblvd_config('bottom_builder');
	} else {
		$post_id = themeblvd_config('builder_post_id');
		$layout_name = themeblvd_config('builder');
	}

	// Get section data
	$section_data = get_post_meta( $post_id, '_tb_builder_sections', true );

	if ( ! $section_data ) {
		echo '<section class="element-section">';
		printf('<div class="element"><div class="alert alert-warning">%s</div></div>', esc_html__('The template has not been configured yet.', 'theme-blvd-layout-builder'));
		echo '</section>';
		return;
	}

	// Get elements for layout, which are organized within sections
	$sections = get_post_meta( $post_id, '_tb_builder_elements', true );

	// Loop through sections of elements
	if ( $sections ) {

		$counter = apply_filters('themeblvd_builder_section_start_count', 1);

		// Check for pagination handling
		$sections = themeblvd_builder_paginated_layout( $post_id, $sections );

		// Display sections of elements
		foreach ( $sections as $section_id => $elements ) {

			// Section classes
			$class = implode( ' ', themeblvd_get_section_class( $section_id, $section_data[$section_id], count($elements) ) );

			// Display settings for section
			$display = array();

			if ( isset( $section_data[$section_id]['display'] ) ) {
				$display = $section_data[$section_id]['display'];
			}

			// Open section
			do_action( 'themeblvd_section_before', $section_id, $layout_name, $section_data[$section_id], $counter );

			// Section ID
			$html_id = apply_filters( 'themeblvd_section_html_id', sprintf('%s-section-%s', $layout_name, $counter), $section_id, $layout_name, $section_data[$section_id], $counter );

			// Output section
			printf( '<section id="%s" class="%s">', esc_attr($html_id), esc_attr($class) );

			if ( $display ) {

				if ( in_array($display['bg_type'], array('image', 'slideshow', 'video')) && ! empty($display['apply_bg_shade']) ) {
					printf( '<div class="bg-shade" style="background-color: %s;"></div>', esc_attr( themeblvd_get_rgb( $display['bg_shade_color'], $display['bg_shade_opacity'] ) ) );
				}

				if ( function_exists('themeblvd_do_parallax') && themeblvd_do_parallax($display) ) { // framework 2.5.1+
					themeblvd_bg_parallax( $display );
				}

				if ( $display['bg_type'] == 'video' && ! empty($display['bg_video']) ) {
					themeblvd_bg_video( $display['bg_video'] );
				}

				if ( $display['bg_type'] == 'slideshow' && ! empty($display['bg_slideshow']) ) {

					$parallax = false;

					if ( ! empty($display['apply_bg_slideshow_parallax']) ) {
						$parallax = true;
					}

					themeblvd_bg_slideshow( $section_id, $display['bg_slideshow'], $parallax );
				}

				if ( ! empty( $display['blend_up'] ) || ! empty( $display['blend_down'] ) ) {

					$bg_color = apply_filters('themeblvd_blend_section_color_default', '#ffffff');

					if ( ! empty( $display['bg_color'] ) ) {
						$bg_color = $display['bg_color'];
					}

					if ( ! empty( $display['blend_up'] ) ) {
						printf('<span class="tb-blend up"><span class="blend-outer"><span class="blend-inner" style="background-color:%s"></span></span></span>', esc_attr($bg_color));
					}

					if ( ! empty( $display['blend_down'] ) ) {
						printf('<span class="tb-blend down"><span class="blend-outer"><span class="blend-inner" style="background-color:%s"></span></span></span>', esc_attr($bg_color));
					}

				}
			}

			do_action( 'themeblvd_section_top', $section_id, $layout_name, $section_data[$section_id], $counter );

			// Display elements
			themeblvd_elements( $section_id, $elements );

			// Close section
			do_action( 'themeblvd_section_bottom', $section_id, $layout_name, $section_data[$section_id], $counter );
			printf( '</section><!-- #%s (end) -->', esc_attr($section_id) );
			do_action( 'themeblvd_section_after', $section_id, $layout_name, $section_data[$section_id], $counter );

			// End section
			do_action( 'themeblvd_section_close', $section_id, $layout_name, $section_data[$section_id], $counter );

			$counter++;

		}

	} else {

		echo '<section class="element-section">';
		printf('<div class="element"><div class="alert alert-warning">%s</div></div>', esc_html__('No element data could be found for this custom layout.', 'theme-blvd-layout-builder'));
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
 * If we're on the second page of a paginated query,
 * we'll find the paginated element and see if all other
 * elements should be hidden. If so, we'll modify the sections
 * of elements to display.
 *
 * @since 2.0.0
 *
 * @param string $var Description
 * @return string $var Description
 */
function themeblvd_builder_paginated_layout( $post_id, $sections ) {

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

					if ( $element['type'] == 'blog' || $element['type'] == 'post_list' || $element['type'] == 'post_grid' ) {

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

/**
 * Add external styles for Builder sections
 *
 * @since 2.0.0
 */
function themeblvd_builder_styles() {

	if ( ! function_exists('themeblvd_config') ) {
		return;
	}

	$layouts = array();

	if ( themeblvd_config('builder_post_id') ) {
		$layouts['main'] = themeblvd_config('builder_post_id');
	}

	if ( themeblvd_config('bottom_builder_post_id') ) {
		$layouts['bottom'] = themeblvd_config('bottom_builder_post_id');
	}

	if ( ! $layouts ) {
		return;
	}

	$print = '';

	foreach ( $layouts as $location => $post_id ) {

		$sections = get_post_meta( $post_id, '_tb_builder_sections', true );
		$count = apply_filters('themeblvd_builder_section_start_count', 1);

		if ( $sections ) {
			foreach ( $sections as $section_id => $section ) {

				$section_print = '';

				$styles = themeblvd_get_display_inline_style( $section['display'], 'external' );

				if ( $styles ) {
					foreach ( $styles as $type => $params ) {

						// Add extra top padding for transparent "suck up" header, if first section
						if ( $location == 'main' && $count == 1 && themeblvd_config('suck_up') ) {

							if ( $type == 'desktop' || $type == 'tablet' ) {

								if ( $type == 'tablet' ) {
									$top = intval(themeblvd_config('top_height_tablet'));
								} else {
									$top = intval(themeblvd_config('top_height'));
								}

								if ( empty( $params['padding-top'] ) ) { // user didn't set custom padding
									$top = $top + 60; // themelvd.css default top padding for section is 60
								} else {
									$top = $top + intval($params['padding-top']);
								}

								$params['padding-top'] = strval($top).'px';

							}

						}

						if ( ! $params ) {
							continue;
						}

						$indent = '';

						if ( $type != 'general' ) {
							$indent = "\t";
						}

						switch ( $type ) {
							case 'desktop' :
								$section_print .= "@media (min-width: 992px) {\n";
								break;
							case 'tablet' :
								$section_print .= "@media (max-width: 991px) and (min-width: 768px) {\n";
								break;
							case 'mobile' :
								$section_print .= "@media (max-width: 767px) {\n";
						}

						if ( strpos($section_id, 'section_') === false ) {
							$section_id = 'section_'.$section_id;
						}

						$section_print .= $indent.sprintf("#custom-%s > .%s {\n", $location, $section_id);

						foreach ( $params as $prop => $value ) {
							$prop = str_replace('-2', '', $prop);
							$section_print .= $indent.sprintf("\t%s: %s;\n", $prop, $value);
						}

						$section_print .= $indent."}\n";

						// Add modified styles for any popout elements when section has custom padding
						if ( ! empty($params['padding-right']) || ! empty($params['padding-left']) ) {

							$section_print .= $indent.sprintf("#custom-%s > .%s > .element.popout {\n", $location, $section_id);

							if ( ! empty($params['padding-right']) ) {
								if ( $params['padding-right'] == '0px' ) {
									$section_print .= $indent."\tmargin-right: 0;\n";
								} else {
									$section_print .= $indent.sprintf("\tmargin-right: -%s;\n", $params['padding-right']);
								}

							}

							if ( ! empty($params['padding-left']) ) {
								if ( $params['padding-left'] == '0px' ) {
									$section_print .= $indent."\tmargin-left: 0;\n";
								} else {
									$section_print .= $indent.sprintf("\tmargin-left: -%s;\n", $params['padding-left']);
								}
							}

							$section_print .= $indent."}\n";
						}

						if ( ! empty($params['padding-top']) && $params['padding-top'] != '0px' ) {
							$section_print .= $indent.sprintf("#custom-%s > .%s > .element.popout.first {\n", $location, $section_id);
							$section_print .= $indent.sprintf("\tmargin-top: -%s;\n", $params['padding-top']);
							$section_print .= $indent."}\n";
						}

						if ( ! empty($params['padding-bottom']) && $params['padding-bottom'] != '0px' ) {
							$section_print .= $indent.sprintf("#custom-%s > .%s > .element.popout.last {\n", $location, $section_id);
							$section_print .= $indent.sprintf("\tmargin-bottom: -%s;\n", $params['padding-bottom']);
							$section_print .= $indent."}\n";
						}

						if ( $type != 'general' ) {
							$section_print .= "}\n";
						}

					}
				}

				// If this is the first section of a page's layout and using transparent,
				// header, look to see if first element uses the match to viewport height
				// feature. If yes, then we'll zero out the padding when the viewport is
				// larger than 992 x 500.
				if ( $count == 1 && $location == 'main' && themeblvd_config('suck_up') ) {

					$elements = get_post_meta( $post_id, '_tb_builder_elements', true );

					$id = $section_id;

					if ( $id == 'section_primary' ) {
						$id = 'primary';
					}

				    if ( ! empty($elements[$id]) ) {

						$first = current($elements[$id]);

						if ( $first['type'] == 'jumbotron' || $first['type'] == 'jumbotron_slider' && ! empty($first['display']['apply_popout']) ) {

							$section_print .= "@media (min-width: 992px) {\n";
							$section_print .= "\t#custom-main > .section_primary > .element.popout.first .jumbotron-outer {\n";
							$section_print .= sprintf("\t\tpadding-top: %spx;\n", intval(themeblvd_config('top_height')) + 60);
							$section_print .= "\t}\n";
							$section_print .= "}\n";

							$section_print .= "@media (max-width: 991px) and (min-width: 768px) {\n";
							$section_print .= "\t#custom-main > .section_primary > .element.popout.first .jumbotron-outer {\n";
							$section_print .= sprintf("\t\tpadding-top: %spx;\n", intval(themeblvd_config('top_height_tablet')) + 60);
							$section_print .= "\t}\n";
							$section_print .= "}\n";

						}

						if ( in_array('height-100vh', themeblvd_get_element_class($first) ) ) {
							$section_print .= "@media (min-width: 992px) and (min-height: 500px) {\n";
							$section_print .= sprintf("\t#custom-%s > .%s { /* First element is set to match viewport height w/suck up header */\n", $location, $section_id);
							$section_print .= "\t\tpadding-top: 0;\n";
							$section_print .= "\t\tpadding-bottom: 0;\n";
							$section_print .= "\t}\n";
							$section_print .= "}\n";
						}
				    }

				}

				if ( $section_print ) {
					$print .= sprintf("\n/* %s */\n", $section['label']);
					$print .= $section_print;
				}

				$count++;
			}

		}

		// Custom CSS
		if ( $custom = get_post_meta( $post_id, '_tb_builder_styles', true ) ) {
			$print .= "\n/* Layout Custom CSS */\n";
			$print .= $custom;
		}

	}

	// Sanitize
	$print = trim($print);
	$print = wp_kses( $print, array() );
	$print = htmlspecialchars_decode( $print );

	// Print after style.css
	if ( $print ) {
		wp_add_inline_style( 'themeblvd-theme', $print );
	}

}
