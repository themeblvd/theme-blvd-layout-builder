<?php
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
	if ( is_home() && get_option('show_on_front') == 'posts' && themeblvd_get_option( 'homepage_content', null, 'posts' ) == 'custom_layout' ) {
		$template = locate_template( 'template_builder.php' );
	}

	return $template;
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
	$elements = get_post_meta( $layout_id, '_tb_builder_elements', true );

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
			$classes .= ' '.themeblvd_responsive_visibility_class( $element['options']['visibility'] );
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
					esc_html_e('Columns element not supported.', 'theme-blvd-layout-builder');
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
					esc_html_e('Content element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				if ( isset($element['options']['source']) && $element['options']['source'] == 'current' ) {
					wp_reset_query();
					the_content();
				} else {
					echo themeblvd_content( $element['options'] );
				}

				break;

			/*------------------------------------------------------*/
			/* Current Page
			/*------------------------------------------------------*/

			case 'current' :

				wp_reset_query();
				the_content();

				break;

			/*------------------------------------------------------*/
			/* Current Featurd Image
			/*------------------------------------------------------*/

			case 'featured_image' :

				wp_reset_query();
				themeblvd_the_post_thumbnail( $element['options']['crop'] );

				break;

			/*------------------------------------------------------*/
			/* Divider
			/*------------------------------------------------------*/

			case 'divider' :

				if ( ! function_exists( 'themeblvd_divider' ) ) {
					esc_html_e('Divider element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				echo themeblvd_divider( $element['options']['type'] );

				break;

			/*------------------------------------------------------*/
			/* Headline
			/*------------------------------------------------------*/

			case 'headline' :

				if ( ! function_exists( 'themeblvd_headline' ) ) {
					esc_html_e('Headline element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				echo themeblvd_headline( $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Jumbotron
			/*------------------------------------------------------*/

			case 'jumbotron' :

				if ( ! function_exists( 'themeblvd_jumbotron' ) ) {
					esc_html_e('Jumbotron element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				themeblvd_jumbotron( $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Post Grid
			/*------------------------------------------------------*/

			case 'post_grid' :

				if ( ! function_exists( 'themeblvd_posts' ) ) {
					esc_html_e('Post Grid element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				themeblvd_posts( $element['options'], 'grid', $location, 'secondary' );

				break;

			/*------------------------------------------------------*/
			/* Post Grid (paginated)
			/*------------------------------------------------------*/

			case 'post_grid_paginated' :

				if ( ! function_exists( 'themeblvd_posts_paginated' ) ) {
					esc_html_e('Paginated Post Grid element not supported.', 'theme-blvd-layout-builder');
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
					esc_html_e('Post Grid Slider element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				themeblvd_post_slider( $id, $element['options'], 'grid', $location );

				break;

			/*------------------------------------------------------*/
			/* Post List
			/*------------------------------------------------------*/

			case 'post_list' :

				if ( ! function_exists( 'themeblvd_posts' ) ) {
					esc_html_e('Post List element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				themeblvd_posts( $element['options'], 'list', $location, 'secondary' );

				break;

			/*------------------------------------------------------*/
			/* Post List (paginated)
			/*------------------------------------------------------*/

			case 'post_list_paginated' :

				if ( ! function_exists( 'themeblvd_posts_paginated' ) ) {
					esc_html_e('Paginated Post List element not supported.', 'theme-blvd-layout-builder');
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
					esc_html_e('Post List Slider element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				themeblvd_post_slider( $id, $element['options'], 'list', $location );

				break;

			/*------------------------------------------------------*/
			/* Post Slider (mimics standard slider)
			/*------------------------------------------------------*/

			case 'post_slider' :

				if ( ! function_exists( 'themeblvd_slider_auto' ) ) {
					esc_html_e('Post Slider element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				themeblvd_slider_auto( $id, $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Slider
			/*------------------------------------------------------*/

			case 'revslider' :

				echo apply_filters( 'the_content', sprintf('[rev_slider %s]', $element['options']['id']) );

				break;

			/*------------------------------------------------------*/
			/* Slider
			/*------------------------------------------------------*/

			case 'slider' :

				if ( ! function_exists( 'themeblvd_slider' ) ) {
					esc_html_e('Slider element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				themeblvd_slider( $element['options']['slider_id'] );

				break;

			/*------------------------------------------------------*/
			/* Slogan
			/*------------------------------------------------------*/

			case 'slogan' :

				if ( ! function_exists( 'themeblvd_slogan' ) ) {
					esc_html_e('Slogan element not supported.', 'theme-blvd-layout-builder');
					break;
				}

				echo themeblvd_slogan( $element['options'] );

				break;

			/*------------------------------------------------------*/
			/* Tabs
			/*------------------------------------------------------*/

			case 'tabs' :

				if ( ! function_exists( 'themeblvd_tabs' ) ) {
					esc_html_e('Tabs element not supported.', 'theme-blvd-layout-builder');
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
 * This function serves as a temporary bridge for plugin
 * 2.0+ to work with themes with a framework version prior to 2.5.
 *
 * @since 2.0.0
 */
function themeblvd_builder_legacy_config( $config ) {

	// Layout setup, taking into account elements attached to post
	if ( ( ! $config['builder'] || $config['builder'] == 'error' ) && is_page_template( 'template_builder.php' ) && ! is_search() && ! is_archive() ) {

		// Setup
		$config['builder'] = true;
		$config['builder_post_id'] = $config['id'];

		// Sidebar layout
		$layout_settings = get_post_meta( $config['builder_post_id'], 'settings', true );
		$config['sidebar_layout'] = $layout_settings['sidebar_layout'];

	}

	// Featured areas
	if ( $config['builder_post_id'] ) {
		$elements = get_post_meta( $config['builder_post_id'], '_tb_builder_elements', true );
		$config['featured'] = themeblvd_builder_legacy_featured_classes( $elements, 'featured' );
		$config['featured_below'] = themeblvd_builder_legacy_featured_classes( $elements, 'featured_below' );
	}

	// Sidebar Layout
	if ( ! $config['sidebar_layout'] || $config['sidebar_layout'] == 'default' ) {
		$config['sidebar_layout']= themeblvd_get_option( 'sidebar_layout' );
	}

	return $config;
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

/**
 * Add the homepage options and implement, if user is
 * using a theme prior to framework 2.5
 *
 * @since 2.0.0
 */
function themeblvd_builder_legacy_homepage() {

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
	$link = sprintf('<a href="%s">%s</a>', esc_url( admin_url('admin.php?page=themeblvd_builder') ), esc_html__('Templates', 'theme-blvd-layout-builder'));

	$options = array(
		'homepage_content' => array(
			'name' 		=> __( 'Homepage Content', 'theme-blvd-layout-builder' ),
			'desc' 		=> __( 'Select the content you\'d like to show on your homepage. Note that for this setting to take effect, you must go to Settings > Reading > Frontpage displays, and select "your latest posts."', 'theme-blvd-layout-builder' ),
			'id' 		=> 'homepage_content',
			'std' 		=> 'posts',
			'type' 		=> 'radio',
			'options' 	=> array(
				'posts'			=> __( 'Posts', 'theme-blvd-layout-builder' ),
				'custom_layout' => __( 'Custom Template', 'theme-blvd-layout-builder' )
			)
		),
		'homepage_custom_layout' => array(
			'name' 		=> __( 'Select Custom Template', 'theme-blvd-layout-builder' ),
			'desc' 		=> sprintf(__( 'Select from the custom templates you\'ve built under the %s section.', 'theme-blvd-layout-builder' ), $link ),
			'id' 		=> 'homepage_custom_layout',
			'std' 		=> '',
			'type' 		=> 'select',
			'options' 	=> $custom_layouts
		)
	);
	themeblvd_add_option_section( 'content', 'homepage', __( 'Homepage', 'theme-blvd-layout-builder' ), null, $options, true );

	// Filter homepage content according to options section
	// we added above.
	add_filter( 'template_include', 'themeblvd_builder_homepage' );
}

/**
 * Add sample layouts compatible with older themes.
 *
 * @since 2.0.0
 */
function themeblvd_builder_legacy_samples( $layouts ) {

	// Remove 2.0 defaults
	$api = Theme_Blvd_Builder_API::get_instance();

	foreach ( $api->core_layouts as $key => $value ) {
		unset( $layouts[$key] );
	}

	// Path to images used in sample layouts on frontend.
	$imgpath = TB_BUILDER_PLUGIN_URI . '/inc/admin/sample/legacy';

	/*--------------------------------------------*/
	/* (1) Business Homepage #1
	/*--------------------------------------------*/

	// Information
	$layouts['business_1'] = array(
		'name'				=> __('Business Homepage #1', 'theme-blvd-layout-builder'),
		'id'				=> 'business_1',
		'preview' 			=> $imgpath . '/sample-business_1.png',
		'sidebar_layout' 	=> 'full_width',
		'dir'				=> null,
		'uri'				=> null
	);

	// Featured Elements
	$layouts['business_1']['featured'] = array(
		'element_1' => array(
			'type'			=> 'slider',
			'query_type'	=> 'secondary',
			'options' 		=> array(
				'slider_id' => null
			)
		)
	);

	// Primary Elements
	$layouts['business_1']['primary'] = array(
		'element_2' => array(
			'type' 			=> 'slogan',
			'query_type' 	=> 'none',
			'options' 		=> array(
				'slogan'		=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.',
                'button'		=> 1,
                'button_text'	=> 'Get Started Today!',
                'button_color'	=> 'default',
                'button_url'	=> 'http://www.google.com',
                'button_target'	=> '_blank'
			)
		),
		'element_3' => array(
            'type'			=> 'columns',
            'query_type'	=> 'none',
            'options'		=> array(
                'setup' => array(
					'num' => '3',
					'width' => array(
						'2' => 'grid_6-grid_6',
						'3' => 'grid_4-grid_4-grid_4',
						'4' => 'grid_3-grid_3-grid_3-grid_3',
						'5' => 'grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1'
					)
				),
                'col_1' => array(
					'type'			=> 'raw',
					'page' 			=> null,
					'raw'			=> "<h3>Sample Headline #1</h3>\n\n<img src=\"$imgpath/business_1.jpg\" />\n\nLorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n[button link=\"http://google.com\"]Learn More[/button]",
					'raw_format'	=> 1
				),
                'col_2' => array(
					'type'			=> 'raw',
					'page'			=> null,
					'raw'			=> "<h3>Sample Headline #2</h3>\n\n<img src=\"$imgpath/business_2.jpg\" />\n\nLorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n[button link=\"http://google.com\"]Learn More[/button]",
					'raw_format'	=> 1
				),
                'col_3' => array(
					'type'			=> 'raw',
					'page'			=> null,
					'raw'			=> "<h3>Sample Headline #3</h3>\n\n<img src=\"$imgpath/business_3.jpg\" />\n\nLorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n[button link=\"http://google.com\"]Learn More[/button]",
					'raw_format'	=> 1
				),
                'col_4' => array(
					'type' 			=> null,
					'page'			=> null,
					'raw'			=> null,
					'raw_format' 	=> 1
				),
                'col_5' => array(
					'type' 			=> null,
					'page'			=> null,
					'raw'			=> null,
					'raw_format'	=> 1
				)
            )
		)
	);

	// Featured Below Elements
	$layouts['business_1']['featured_below'] = array();

	/*--------------------------------------------*/
	/* (2) Business Homepage #2
	/*--------------------------------------------*/

	// Information
	$layouts['business_2'] = array(
		'name'				=> __('Business Homepage #2', 'theme-blvd-layout-builder'),
		'id'				=> 'business_2',
		'preview'			=> $imgpath . '/sample-business_2.png',
		'sidebar_layout'	=> 'full_width',
		'dir'				=> null,
		'uri'				=> null
	);

	// Featured Elements
	$layouts['business_2']['featured'] = array(
		'element_1' => array(
			'type'			=> 'slider',
			'query_type' 	=> 'secondary',
			'options'		=> array(
				'slider_id' => null
			)
		)
	);

	// Main Elements
	$layouts['business_2']['primary'] = array(
		'element_2' => array(
			'type'			=> 'slogan',
			'query_type'	=> 'none',
			'options'		=> array(
				'slogan' 		=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.',
                'button' 		=> 1,
                'button_text' 	=> 'Get Started Today!',
                'button_color' 	=> 'default',
                'button_url' 	=> 'http://www.google.com',
                'button_target' => '_blank'
			)
		),
		'element_3' => array(
            'type'			=> 'columns',
            'query_type' 	=> 'none',
            'options' 		=> array(
                'setup' => array(
					'num' => '4',
					'width' => array(
						'2' => 'grid_6-grid_6',
						'3' => 'grid_4-grid_4-grid_4',
						'4' => 'grid_3-grid_3-grid_3-grid_3',
						'5' => 'grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1'
					)
				),
                'col_1' => array(
					'type'			=> 'raw',
					'page'			=> null,
					'raw'			=> '[icon image="clock" align="left"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
					'raw_format'	=> 1
				),
                'col_2' => array(
					'type'			=> 'raw',
					'page'			=> null,
					'raw' 			=> '[icon image="pie_chart" align="left"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
					'raw_format' 	=> 1
				),
                'col_3' => array(
					'type'			=> 'raw',
					'page'			=> null,
					'raw'			=> '[icon image="coffee_mug" align="left"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
					'raw_format'	=> 1
				),
                'col_4' => array(
					'type'			=> 'raw',
					'page'			=> null,
					'raw'			=> '[icon image="computer" align="left"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
					'raw_format'	=> 1
				),
                'col_5' => array(
					'type'			=> null,
					'page'			=> null,
					'raw'			=> null,
					'raw_format'	=> 1
				)
            )
		)
	);

	// Featured Below Elements
	$layouts['business_2']['featured_below'] = array();

	/*--------------------------------------------*/
	/* (3) Business Homepage #3
	/*--------------------------------------------*/

	// Information
	$layouts['business_3'] = array(
		'name'				=> __('Business Homepage #3', 'theme-blvd-layout-builder'),
		'id'				=> 'business_3',
		'preview'			=> $imgpath . '/sample-business_3.png',
		'sidebar_layout'	=> 'sidebar_right',
		'dir'				=> null,
		'uri'				=> null
	);

	// Featured Elements
	$layouts['business_3']['featured'] = array(
		'element_1' => array(
			'type'			=> 'slider',
			'query_type'	=> 'secondary',
			'options' 		=> array(
				'slider_id' => null
			)
		),
		'element_2' => array(
			'type' 			=> 'slogan',
			'query_type' 	=> 'none',
			'options' 		=> array(
				'slogan' 		=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.',
                'button' 		=> 1,
                'button_text' 	=> 'Get Started Today!',
                'button_color' 	=> 'default',
                'button_url' 	=> 'http://www.google.com',
                'button_target' => '_blank'
			)
		)
	);

	// Main Elements
	$layouts['business_3']['primary'] = array(
		'element_3' => array(
			'type' 			=> 'content',
			'query_type' 	=> 'none',
			'options' 		=> array(
				'source' 		=> 'raw',
				'page_id' 		=> null,
				'raw_content' 	=> "<h2>Welcome to our fancy-schmancy website.</h2>\n\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>\n\n<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>\n\n<p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.</p>\n\n[one_half]\n<h4>We Rock</h4>\n\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>\n\n[/one_half]\n[one_half last]\n<h4>Hire Us</h4>\n\nLorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n[/one_half]",
				'raw_format' 	=> 0
			)
		),
	);

	// Featured Below Elements
	$layouts['business_3']['featured_below'] = array();

	/*--------------------------------------------*/
	/* (4) Business Homepage #4
	/*--------------------------------------------*/

	// Information
	$layouts['business_4'] = array(
		'name'				=> __('Business Homepage #4', 'theme-blvd-layout-builder'),
		'id'				=> 'business_4',
		'preview'			=> $imgpath . '/sample-business_4.png',
		'sidebar_layout'	=> 'full_width',
		'dir'				=> null,
		'uri'				=> null
	);

	// Featured Elements
	$layouts['business_4']['featured'] = array();

	// Main Elements
	$layouts['business_4']['primary'] = array(
		'element_2' => array(
			'type' 			=> 'headline',
			'query_type' 	=> 'none',
			'options' 		=> array(
				'text' 			=> 'Welcome to our website',
				'tagline' 		=> '',
				'tag' 			=> 'h1',
				'align' 		=> 'left'
			)
		),
		'element_3' => array(
			'type' => 'columns',
            'query_type' => 'none',
            'options' => array(
                'setup' => array(
					'num' => '3',
					'width' => array(
						'2' => 'grid_6-grid_6',
						'3' => 'grid_6-grid_3-grid_3', // => 50% | 25% | 25%
						'4' => 'grid_3-grid_3-grid_3-grid_3',
						'5' => 'grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1'
					)
				),
                'col_1' => array(
					'type' 			=> 'raw',
					'page' 			=> null,
					'raw' 			=> "<img src=\"$imgpath/business_4.jpg\" class=\"pretty\" />\n\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla in bibendum enim. Nunc in est vitae leo imperdiet suscipit et sagittis leo. Nullam consectetur placerat sem, vitae feugiat lorem posuere nec. Etiam et magna nunc, et faucibus elit. Integer vitae pretium sem. Duis vitae lorem magna, ac tincidunt dolor. Phasellus justo metus, luctus in hendrerit eu, mattis eget lacus. Donec nulla turpis, euismod aliquam aliquam sed, semper vitae enim. Sed venenatis ligula eu enim tempor eget imperdiet dui pulvinar. Etiam et magna nunc, et faucibus elit. Integer vitae pretium sem.",
					'raw_format' 	=> 1
				),
                'col_2' => array(
					'type' 			=> 'raw',
					'page' 			=> null,
					'raw' 			=> "[icon image=\"clock\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat\n\n[icon image=\"pie_chart\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
					'raw_format' 	=> 1
				),
                'col_3' => array(
					'type' 			=> 'raw',
					'page' 			=> null,
					'raw' 			=> "[icon image=\"coffee_mug\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n[icon image=\"computer\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
					'raw_format' 	=> 1
				),
                'col_4' => array(
					'type' 			=> null,
					'page' 			=> null,
					'raw' 			=> null,
					'raw_format' 	=> 1
				),
                'col_5' => array(
					'type' 			=> null,
					'page' 			=> null,
					'raw' 			=> null,
					'raw_format' 	=> 1
				)
            )
		),
		'element_4' => array(
			'type' 			=> 'post_grid_slider',
			'query_type' 	=> 'secondary',
			'options' 		=> array(
				'fx' 			=> 'slide',
				'timeout' 		=> 0,
				'nav_standard' 	=> 1,
				'nav_arrows' 	=> 1,
				'pause_play' 	=> 1,
				'categories' 	=> array('all'=>1),
				'columns' 		=> 4,
				'rows' 			=> 1,
				'numberposts' 	=> -1,
				'orderby' 		=> 'post_date',
				'order' 		=> 'DESC',
				'offset' 		=> 0
			)
		)
	);

	// Featured Below Elements
	$layouts['business_4']['featured_below'] = array();

	/*--------------------------------------------*/
	/* (5) Classic Magazine #1
	/*--------------------------------------------*/

	// Information
	$layouts['magazine_1'] = array(
		'name'				=> __('Classic Magazine #1', 'theme-blvd-layout-builder'),
		'id'				=> 'magazine_1',
		'preview'			=> $imgpath . '/sample-magazine_1.png',
		'sidebar_layout'	=> 'sidebar_right',
		'dir'				=> null,
		'uri'				=> null
	);

	// Featured Elements
	$layouts['magazine_1']['featured'] = array();

	// Main Elements
	$layouts['magazine_1']['primary'] = array(
		'element_1' => array(
			'type' 			=> 'slider',
			'query_type' 	=> 'secondary',
			'options' 		=> array(
				'slider_id' => null
			)
		),
		'element_2' => array(
			'type'			=> 'post_grid_paginated',
			'query_type'	=> 'primary',
			'options' 		=> array(
				'categories' 	=> array('all'=>1),
				'columns' 		=> 2,
				'rows' 			=> 3,
				'orderby' 		=> 'post_date',
				'order' 		=> 'DESC',
				'offset' 		=> 0
			)
		)
	);

	// Featured Below Elements
	$layouts['magazine_1']['featured_below'] = array();

	/*--------------------------------------------*/
	/* (6) Classic Magazine #2
	/*--------------------------------------------*/

	// Information
	$layouts['magazine_2'] = array(
		'name'				=> __('Classic Magazine #2', 'theme-blvd-layout-builder'),
		'id'				=> 'magazine_2',
		'preview'			=> $imgpath . '/sample-magazine_2.png',
		'sidebar_layout'	=> 'sidebar_right'
	);

	// Featured Elements
	$layouts['magazine_2']['featured'] = array();

	// Main Elements
	$layouts['magazine_2']['primary'] = array(
		'element_1' => array(
			// 1 post featured above everything else
			'type' 			=> 'post_list',
			'query_type'	=> 'secondary',
			'options' 		=> array(
				'categories' 	=> array('all'=>1),
				'thumbs' 		=> 'full',
				'content' 		=> 'default',
				'numberposts' 	=> 1,
				'orderby' 		=> 'post_date',
				'order' 		=> 'DESC',
				'offset' 		=> 0,
				'link' 			=> 0,
				'link_text' 	=> 'View All Posts',
				'link_url' 		=> 'http://www.your-site.com/your-blog-page',
				'link_target' 	=> '_self'
			)
		),
		'element_2' => array(
			// Continue post with offset = 1
			'type' 			=> 'post_grid',
			'query_type' 	=> 'secondary',
			'options' 		=> array(
				'categories' 	=> array('all'=>1),
				'columns' 		=> 3,
				'rows' 			=> 3,
				'orderby' 		=> 'post_date',
				'order' 		=> 'DESC',
				'offset' 		=> 1,
				'link' 			=> 0,
				'link_text' 	=> 'View All Posts &rarr;',
				'link_url' 		=> 'http://www.your-site.com/your-blog-page',
				'link_target' 	=> '_self'
			)
		)
	);

	// Featured Below Elements
	$layouts['magazine_2']['featured_below'] = array();

	/*--------------------------------------------*/
	/* (7) Design Agency
	/*--------------------------------------------*/

	// Information
	$layouts['agency'] = array(
		'name'				=> __('Design Agency', 'theme-blvd-layout-builder'),
		'id'				=> 'agency',
		'preview'			=> $imgpath . '/sample-agency.png',
		'sidebar_layout'	=> 'full_width',
		'dir'				=> null,
		'uri'				=> null
	);

	// Featured Elements
	$layouts['agency']['featured'] = array(
		'element_1' => array(
			'type' => 'slogan',
			'query_type' => 'none',
			'options' => array(
				'slogan' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.',
				'button' => 0,
				'button_text' => 'Get Started Today!',
				'button_color' => 'default',
				'button_url' => 'http://www.your-site.com/your-landing-page',
				'button_target' => '_self'
			)
		),
		'element_2' => array(
			'type' => 'post_grid_slider',
			'query_type' => 'secondary',
			'options' => array(
				'fx' => 'slide',
				'timeout' => 0,
				'nav_standard' => 1,
				'nav_arrows' => 1,
				'pause_play' => 1,
				'categories' => array('all'=>1),
				'columns' => 4,
				'rows' => 2,
				'numberposts' => -1,
				'orderby' => 'post_date',
				'order' => 'DESC',
				'offset' => 0
			)
		)
	);

	// Main Elements
	$layouts['agency']['primary'] = array(
		'element_3' => array(
			'type'			=> 'columns',
			'query_type' 	=> 'none',
			'options' 		=> array(
                'setup' => array(
					'num' => '3',
					'width' => array(
						'2' => 'grid_6-grid_6',
						'3' => 'grid_4-grid_4-grid_4',
						'4' => 'grid_3-grid_3-grid_3-grid_3',
						'5' => 'grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1'
					)
				),
                'col_1' => array(
					'type' 			=> 'raw',
					'page' 			=> null,
					'raw' 			=> "<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"clock\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"computer\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
					'raw_format' 	=> 1
				),
                'col_2' => array(
					'type' 			=> 'raw',
					'page' 			=> null,
					'raw' 			=> "<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"pie_chart\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"image\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
					'raw_format' 	=> 1
				),
                'col_3' => array(
					'type' 			=> 'raw',
					'page' 			=> null,
					'raw' 			=> "<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"coffee_mug\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"camera\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
					'raw_format' 	=> 1
				),
                'col_4' => array(
					'type' 			=> null,
					'page' 			=> null,
					'raw' 			=> null,
					'raw_format' 	=> 1
				),
                'col_5' => array(
					'type' 			=> null,
					'page' 			=> null,
					'raw' 			=> null,
					'raw_format' 	=> 1
				)
            )
		)
	);

	// Featured Below Elements
	$layouts['agency']['featured_below'] = array();

	/*--------------------------------------------*/
	/* (8) Portfolio Homepage
	/*--------------------------------------------*/

	// Information
	$layouts['portfolio'] = array(
		'name'				=> __('Portfolio Homepage', 'theme-blvd-layout-builder'),
		'id'				=> 'portfolio',
		'preview'			=> $imgpath . '/sample-portfolio.png',
		'sidebar_layout'	=> 'full_width',
		'dir'				=> null,
		'uri'				=> null
	);

	// Featured Elements
	$layouts['portfolio']['featured'] = array(
		'element_1' => array(
			'type' 			=> 'slider',
			'query_type' 	=> 'secondary',
			'options' 		=> array(
				'slider_id' => null
			)
		)
	);

	// Primary Elements
	$layouts['portfolio']['primary'] = array(
		'element_2' => array(
			'type' 			=> 'post_grid_paginated',
			'query_type' 	=> 'primary',
			'options' 		=> array(
				'type' 			=> 'post_grid_paginated',
				'query_type' 	=> 'primary',
				'options' 		=> array(
					'categories' 	=> array('all'=>1),
					'columns' 		=> 4,
					'rows'			=> 3,
					'orderby'		=> 'post_date',
					'order'			=> 'DESC',
					'offset' 		=> 0
				)
			)
		)
	);

	// Featured Below Elements
	$layouts['portfolio']['featured_below'] = array();

	/*--------------------------------------------*/
	/* (9) Showcase Blogger
	/*--------------------------------------------*/

	// Information
	$layouts['showcase'] = array(
		'name'				=> __('Showcase Blogger', 'theme-blvd-layout-builder'),
		'id'				=> 'showcase',
		'preview'			=> $imgpath . '/sample-showcase.png',
		'sidebar_layout'	=> 'sidebar_right',
		'dir'				=> null,
		'uri'				=> null
	);

	// Featured Elements
	$layouts['showcase']['featured'] = array(
		'element_1' => array(
			'type'			=> 'slider',
			'query_type'	=> 'secondary',
			'options'		=> array(
				'slider_id' => null
			)
		),
		'element_2' => array(
			'type' 			=> 'slogan',
			'query_type' 	=> 'none',
			'options' 		=> array(
				'slogan'		=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.',
                'button'		=> 1,
                'button_text' 	=> 'Get Started Today!',
                'button_color'	=> 'default',
                'button_url' 	=> 'http://www.google.com',
                'button_target'	=> '_blank'
			)
		)
	);

	// Main Elements
	$layouts['showcase']['primary'] = array(
		'element_3' => array(
			'type'			=> 'post_list_paginated',
			'query_type'	=> 'primary',
			'options'		=> array(
				'categories'	=> array('all'=>1),
				'columns'		=> 4,
				'rows'			=> 3,
				'orderby'		=> 'post_date',
				'order'			=> 'DESC',
				'offset'		=> 0
			)
		)
	);

	// Featured Below Elements
	$layouts['showcase']['featured_below'] = array();

	return $layouts;
}

/**
 * Get CSS classes for featured areas of custom
 * layout for old themes.
 *
 * @since 2.0.1
 */
function themeblvd_builder_legacy_featured_classes( $elements, $area ) {

	$classes = array();

	if ( ! empty( $elements[$area] ) ) {

		$classes[] = 'has_builder';

		foreach ( $elements[$area] as $element ) {
			switch ( $element['type'] ) {
				case 'slider' :
					$classes[] = 'has_slider';
					break;
				case 'post_grid_slider' :
					$classes[] = 'has_slider';
					$classes[] = 'has_grid';
					$classes[] = 'has_post_grid_slider';
					break;
				case 'post_list_slider' :
					$classes[] = 'has_slider';
					$classes[] = 'has_post_list_slider';
					break;
				case 'post_grid' :
					$classes[] = 'has_grid';
					break;
			}
		}

		$sliders = apply_filters('themeblvd_slider_element_list', array('slider', 'post_slider', 'post_grid_slider', 'post_list_slider'));

		// First element classes
		$first_element = array_values( $elements[$area] );
		$first_element = array_shift( $first_element );
		$first_element = $first_element['type'];

		if ( in_array( $first_element, $sliders ) ) {
			$classes[] = 'slider_is_first';
		}

		if ( $first_element == 'post_grid' || $first_element == 'post_grid_slider' ) {
			$classes[] = 'grid_is_first';
		}

		if ( $first_element == 'slogan' ) {
			$classes[] = 'slogan_is_first';
		}

		// Last element classes
		$last_element = end( $elements[$area] );
		$last_element = $last_element['type'];

		if ( in_array( $last_element, $sliders ) ) {
			$classes[] = 'slider_is_last';
		}

		if ( $last_element == 'post_grid' || $last_element == 'post_grid_slider' ) {
			$classes[] = 'grid_is_last';
		}

		if ( $last_element == 'slogan' ) {
			$classes[] = 'slogan_is_last';
		}
	}

	return apply_filters( 'featured_builder_classes', $classes, $elements, $area );
}
