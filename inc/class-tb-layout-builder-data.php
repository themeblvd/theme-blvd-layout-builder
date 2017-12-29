<?php
/**
 * Verify layout data. This class monitors
 * when layouts are being edited or displayed
 * to ensure that the data stored is up-to-date
 * with the current version of the plugin.
 *
 * With custom layouts, all data is stored within
 * meta data. So, this class limits itself to using
 * get_post_meta() to check the data, which WordPress
 * caches.
 *
 * This will ensure that this class doesn't require
 * any extra DB queries on the load of the site, if
 * everything is up-to-date. If the layout's data
 * wasn't up-to-date, this class will fix the issue
 * for future loads.
 */
class Theme_Blvd_Layout_Builder_Data {

	/*--------------------------------------------*/
	/* Properties, private
	/*--------------------------------------------*/

	/**
	 * The ID of the current layout.
	 *
	 * @since 2.0.0
	 */
	private $id = 0;

	/**
	 * Current version of plugin.
	 *
	 * @since 2.0.0
	 */
	private $version = TB_BUILDER_PLUGIN_VERSION;

	/**
	 * Version of the plugin that the
	 * custom layout was created with.
	 *
	 * @since 2.0.0
	 */
	private $created = '1.0.0';

	/**
	 * Version of the plugin that the
	 * custom layout was last saved with.
	 *
	 * @since 2.0.0
	 */
	private $saved = '1.0.0';

	/**
	 * Version of current theme framework.
	 *
	 * @since 2.0.0
	 */
	private $theme_version = TB_FRAMEWORK_VERSION;

	/**
	 * Version of the theme framework that
	 * the custom layout was created with.
	 *
	 * @since 2.0.0
	 */
	private $theme_created = '2.0.0';

	/**
	 * Version of the theme framework that
	 * the custom layout was last saved with.
	 *
	 * @since 2.0.0
	 */
	private $theme_saved = '2.0.0';

	/**
	 * This will get set to true if any data
	 * manipulation is needed.
	 *
	 * @since 2.0.0
	 */
	private $ran = false;

	/*--------------------------------------------*/
	/* Constructor
	/*--------------------------------------------*/

	/**
	 * Constructor. Sets properties.
	 *
	 * @since 2.0.0
	 *
	 * @param int $id ID of current layout to check.
	 */
	public function __construct( $id ) {

		/**
		 * Post ID for custom layout, tb_layout post type,
		 * or WP post ID if editing layout directly into.
		 */
		$this->id = $id;

		/**
		 * Plugin version which layout was created with.
		 */
		if ( $created = get_post_meta( $this->id, '_tb_builder_plugin_version_created', true ) ) {

			$this->created = $created;

		}

		/**
		 * Plugin version which layout was last saved with.
		 */
		if ( $saved = get_post_meta( $this->id, '_tb_builder_plugin_version_saved', true ) ) {

			$this->saved = $saved;

		}

		/**
		 * Theme Framework version which layout was created with.
		 */
		if ( $theme_created = get_post_meta( $this->id, '_tb_builder_framework_version_created', true ) ) {

			$this->theme_created = $theme_created;

		}

		/**
		 * Theme Framework version which layout was last saved with.
		 */
		if ( $theme_saved = get_post_meta( $this->id, '_tb_builder_framework_version_saved', true ) ) {

			$this->theme_saved = $theme_saved;

		}
	}

	/*--------------------------------------------*/
	/* Methods, general
	/*--------------------------------------------*/

	/**
	 * Run verification of data
	 *
	 * @since 2.0.0
	 *
	 * @param string $type What to verify
	 */
	public function verify( $type ) {

		/**
		 * If there's no elements (old or new) attached to
		 * this post ID, no need to proceed.
		 */
		if ( ! get_post_meta( $this->id, 'elements', true ) && ! get_post_meta( $this->id, '_tb_builder_elements', true ) ) {

			return;

		}

		switch ( $type ) {
			case 'elements' :
				$this->verify_elements();
				break;
			case 'info' :
				$this->verify_info();
				break;
		}
	}

	/**
	 * Run verification of data from all
	 * elements of layout
	 *
	 * @since 2.0.0
	 */
	public function verify_elements() {

		/*
		 * In v2.0 of the Builder, elements are now saved to
		 * meta key "_tb_builder_elements" and not "elements".
		 */
		$this->transfer_element_meta();

		/*
		 * In v2.0 of builder and v2.5 of framework, section
		 * data now exists.
		 */
		$this->add_section_data();

		/*
		 * In v2.0 of the builder and 2.5 of framework, display
		 * options to elements to control the background of each
		 * element.
		 */
		$this->display_options();

		/*
		 * Update element options that changed in builder 2.0
		 * paired with theme 2.5.
		 *
		 * (1) Columns
		 * (2) Jumbotron
		 * (3) Post Grid
		 * (4) Post List
		 * (5) Post Slider
		 * (6) Promo Box
		 * (7) Tabs
		 */
		$this->update_options();

		/*
		 * Element conversions for when plugin 2.0 is
		 * paired with theme 2.5. The following elements
		 * being converted no longer exist.
		 *
		 * (1) Content => [applicable type]
		 * (2) Paginated Post List => Post List
		 * (3) Paginated Post Grid => Post Grid
		 * (4) Post Grid Slider => Post Grid
		 * (5) Paginated List Slider => Post Slider
		 */
		$this->convert_elements();

		/**
		 * Convert full width sliders to standard sliders
		 * with popout enabled.
		 *
		 * This is for themes using framework 2.5+, that
		 * are updating to framework 2.7+.
		 */
		$this->remove_popout_sliders();

		/**
		 * Extend
		 */
		do_action( 'themeblvd_builder_verify_elements', $this );

	}

	/**
	 * Run verification of data from settings
	 * of layout
	 *
	 * @since 2.0.0
	 */
	public function verify_info() {

		/**
		 * Extend
		 */
		do_action( 'themeblvd_builder_verify_info', $this );
	}

	/**
	 * Finalize updating layout.
	 *
	 * @since 2.0.0
	 */
	public function finalize() {
		if ( $this->ran ) {
			update_post_meta( $this->id, '_tb_builder_plugin_version_saved', $this->version );
			update_post_meta( $this->id, '_tb_builder_framework_version_saved', $this->theme_version );
		}
	}

	/*--------------------------------------------*/
	/* Methods, individual checks to build upon
	/*--------------------------------------------*/

	/**
	 * Verify that layout's elements are saved to meta key
	 * "_tb_builder_elements" and not "elements".
	 *
	 * @since 2.0.0
	 */
	public function transfer_element_meta() {

		// If layout is saved after 2.0 of the plugin we're
		// good to go.
		if ( version_compare( $this->saved, '2.0.0', '>=' ) ) {
			return;
		}

		if ( $elements = get_post_meta( $this->id, 'elements', true ) ) {
			// delete_post_meta( $this->id, 'elements' ); // For now, leave commented out. This will allow old element options to exist if user switches back.
			update_post_meta( $this->id, '_tb_builder_elements', $elements);
		}

		// Allow layout to be finalized at the end of all checks.
		$this->ran = true;

	}

	/**
	 * Add section data
	 *
	 * @since 2.0.0
	 */
	public function add_section_data() {

		// If the theme does not contain framework version 2.5+,
		// altering the data will mess things up.
		if ( version_compare( $this->theme_version, '2.5.0', '<' ) ) {
			return;
		}

		// If layout is saved after 2.0 of the plugin and v2.5
		// of the theme framework, we're good to go.
		if ( version_compare( $this->saved, '2.0.0', '>=' ) && version_compare( $this->theme_saved, '2.5.0', '>=' ) ) {
			return;
		}

		if ( ! get_post_meta( $this->id, '_tb_builder_sections', true ) ) {

			$data = array();
			$sections = get_post_meta( $this->id, '_tb_builder_elements', true );

			if ( ! isset( $sections['primary'] ) ) {
				$sections['primary'] = array();
			}

			$sorted = array();

			if ( isset( $sections['featured'] ) ) {
				$sorted['featured'] = $sections['featured'];
			}

			$sorted['primary'] = $sections['primary'];

			if ( isset( $sections['featured_below'] ) ) {
				$sorted['featured_below'] = $sections['featured_below'];
			}

			foreach ( $sorted as $id => $section ) {

				switch ( $id ) {
					case 'featured' :
						$label = 'Featured';
						break;
					case 'featured_below' :
						$label = 'Featured Below';
						break;
					default :
						$label = 'Main';
				}

				$data[$id] = array(
					'label'		=> $label,
					'display'	=> array(
						'bg_type'						=> 'none',
						'text_color'					=> 'dark',
						'bg_color'						=> '#f8f8f8',
						'bg_color_opacity'				=> 1,
						'bg_texture'					=> 'arches',
						'apply_bg_texture_parallax'		=> 0,
						'bg_texture_parallax'			=> 5,
						'bg_image' 						=> array(
							'color'			=> '',
							'image'			=> '',
							'repeat'		=> 'no-repeat',
							'position'		=> 'center top',
							'attachment'	=> 'scroll',
							'size'			=> 'auto',
						),
						'bg_image_parallax'				=> '2',
						'apply_bg_shade'				=> 0,
						'bg_shade_color'				=> '#000000',
						'bg_shade_opacity'				=> '0.5',
						'bg_slideshow'					=> array(),
						'bg_slideshow_crop'				=> 'full',
						'apply_bg_slideshow_parallax'	=> 0,
						'bg_slideshow_parallax'			=> 5,
						'apply_border_top'				=> 0,
						'border_top_color'				=> '#dddddd',
						'border_top_width'				=> '1px',
						'apply_border_bottom'			=> 0,
						'border_bottom_color'			=> '#dddddd',
						'border_bottom_width'			=> '1px',
						'apply_padding'					=> 0,
						'padding_top'					=> '60px',
						'padding_right'					=> '0px',
						'padding_bottom'				=> '60px',
						'padding_left'					=> '0px',
						'apply_padding_tablet'			=> 0,
						'padding_top_tablet'			=> '60px',
						'padding_right_tablet'			=> '0px',
						'padding_bottom_tablet'			=> '60px',
						'padding_left_tablet'			=> '0px',
						'apply_padding_mobile'			=> 0,
						'padding_top_mobile'			=> '60px',
						'padding_right_mobile'			=> '0px',
						'padding_bottom_mobile'			=> '60px',
						'padding_left_mobile'			=> '0px',
						'hide'							=> array(
							'xs' 			=> 0,
							'sm' 			=> 0,
							'md' 			=> 0,
							'lg' 			=> 0
						),
						'classes'						=> ''
					)
				);

				// If the section was a featured area, automatically
				// apply old feature area styling for background options of element.
				if ( $id == 'featured' && themeblvd_supports( 'featured', 'style' ) ) {
					$data[$id]['display']['bg_type'] = 'featured';
				} else if ( $id == 'featured_below' && themeblvd_supports( 'featured_below', 'style' ) ) {
					$data[$id]['display']['bg_type'] = 'featured_below';
				}
			}

			update_post_meta( $this->id, '_tb_builder_sections', $data );

		}

		// Allow layout to be finalized at the end of all checks.
		$this->ran = true;
	}

	/**
	 * Verify that elements are merged, and not separated
	 * by old method of Featured/Primary/Featured Below.
	 *
	 * @since 2.0.0
	 */
	public function display_options() {

		// If the theme does not contain framework version 2.5+,
		// altering the data will mess things up.
		if ( version_compare( $this->theme_version, '2.5.0', '<' ) ) {
			return;
		}

		// If layout is saved after 2.0 of the plugin and v2.5
		// of the theme framework, we're good to go.
		if ( version_compare( $this->saved, '2.0.0', '>=' ) && version_compare( $this->theme_saved, '2.5.0', '>=' ) ) {
			return;
		}

		$sections = get_post_meta( $this->id, '_tb_builder_elements', true );

		if ( ! isset($sections['featured']) && ! isset($sections['primary']) && ! isset($sections['featured_below']) ) {
			return;
		}

		$new = array();

		if ( $sections ) {
			foreach ( $sections as $section_id => $elements ) {
				foreach ( $elements as $element_id => $element ) {

					// Default element label
					$element['label'] = 'Element Label';

					// Setup default display settings
					$element['display'] = array(
						'type' 				=> 'element',
						'apply_padding' 	=> '0',
						'padding_top'		=> '0px',
						'padding_right' 	=> '0px',
						'padding_bottom'	=> '0px',
						'padding_left' 		=> '0px',
						'apply_popout'		=> '0',
						'bg_content'		=> '0',
						'suck_up'			=> '0',
						'suck_down'			=> '0',
						'hide'				=> array(
							'xs' => 0,
							'sm' => 0,
							'md' => 0,
							'lg' => 0
						),
						'classes' 			=> ''
					);

					// Move responsive visibility settings from element options to
					// new display options.
					if ( isset( $element['options']['visibility'] ) ) {

						if ( ! empty( $element['options']['visibility']['hide_on_standard'] ) ) {
							$element['display']['hide']['md'] = 1;
							$element['display']['hide']['lg'] = 1;
						}

						if ( ! empty( $element['options']['visibility']['hide_on_tablet'] ) ) {
							$element['display']['hide']['sm'] = 1;
						}

						if ( ! empty( $element['options']['visibility']['hide_on_mobile'] ) ) {
							$element['display']['hide']['xs'] = 1;
						}

						unset( $element['options']['visibility'] );
					}

					// Move CSS classes setting from element options to new display options.
					if ( isset( $element['options']['classes'] ) ) {
						$element['display']['classes'] = $element['options']['classes'];
						unset( $element['options']['classes'] );
					}

					// Now that display settings are added, add entire element back
					$new[$section_id][$element_id] = $element;
				}
			}
		}

		// Update elements
		update_post_meta( $this->id, '_tb_builder_elements', $new );

		// Allow layout to be finalized at the end of all checks.
		$this->ran = true;

	}

	/**
	 * Update element options that changed in plugin 2.0
	 * paired with theme 2.5.
	 *
	 * (1) Columns
	 * (2) Jumbotron
	 * (3) Post Grid
	 * (4) Post List
	 * (5) Post Slider
	 * (6) Promo Box
	 * (7) Tabs
	 *
	 * @since 2.0.0
	 */
	public function update_options() {

		// If the theme does not contain framework version 2.5+,
		// altering the data will mess things up.
		if ( version_compare( $this->theme_version, '2.5.0', '<' ) ) {
			return;
		}

		// If layout is saved after 2.0.0 of the plugin and v2.5
		// of the theme framework, we're good to go.
		if ( version_compare( $this->saved, '2.0.0', '>=' ) && version_compare( $this->theme_saved, '2.5.0', '>=' ) ) {
			return;
		}

		// Updates for going to framework 2.5 and builder 2.0
		$new = array();

		if ( $sections = get_post_meta( $this->id, '_tb_builder_elements', true ) ) {
			foreach ( $sections as $section_id => $elements ) {

				$new[$section_id] = array();

				foreach ( $elements as $element_id => $element ) {

					$new[$section_id][$element_id] = $element;
					$options = $element['options'];

					switch ( $element['type'] ) {

						/**
						 * Columns
						 */
						case 'columns' :

							$cols = explode( '-', $options['setup']['width'][$options['setup']['num']] );
							$setup = array();

							foreach ( $cols as $col ) {
								$setup[] = themeblvd_grid_fraction($col);
							}

							$num = count($setup);
							$options['setup'] = implode('-', $setup);

							$options['stack'] = 'md';
							$options['height'] = '0';
							$options['align'] = 'top';

							for ( $i = 1; $i <= $num; $i++ ) {

								$col = array();

								$col['display'] = array(
									'type'						=> 'column',
									'bg_type'					=> 'none',
									'text_color'				=> 'dark',
									'bg_color'					=> '#f2f2f2',
									'bg_color_opacity'			=> '1',
									'bg_texture'				=> 'arches',
									'apply_bg_texture_parallax'	=> '0',
									'bg_texture_parallax'		=> '5',
									'bg_image'					=> array(
										'color'			=> '',
										'image'			=> '',
										'repeat'		=> 'no-repeat',
										'position'		=> 'center top',
										'attachment'	=> 'scroll',
										'size'			=> 'auto'
									),
									'bg_image_parallax'			=> '2',
									'apply_padding'				=> '0',
									'padding_top'				=> '30px',
									'padding_right'				=> '30px',
									'padding_bottom'			=> '30px',
									'padding_left'				=> '30px',
									'classes'					=> ''
								);

								$col['elements'] = array();

								$block_id = uniqid( 'block_' . rand() );
								$col['elements'][$block_id] = array();

								switch ( $options['col_'.$i]['type'] ) {

									case 'current' :
										$col['elements'][$block_id]['type'] = 'current';
										break;

									case 'page' :
										$col['elements'][$block_id]['type'] = 'external';
										$col['elements'][$block_id]['options'] = array();
										$col['elements'][$block_id]['options']['post_id'] = themeblvd_post_id_by_name( $options['col_'.$i]['page'], 'page' );
										break;

									case 'raw' :
										$col['elements'][$block_id]['type'] = 'content';
										$col['elements'][$block_id]['options'] = array();
										$col['elements'][$block_id]['options']['content'] = $options['col_'.$i]['raw'];
										$col['elements'][$block_id]['options']['format'] = $options['col_'.$i]['raw_format'];
										$col['elements'][$block_id]['options']['style'] = 'none';
										$col['elements'][$block_id]['options']['text_color'] = 'dark';
										$col['elements'][$block_id]['options']['bg_color'] = '#eeeeee';
										$col['elements'][$block_id]['options']['bg_opacity'] = '1';
										break;

									case 'widget' :
										$col['elements'][$block_id]['type'] = 'widget';
										$col['elements'][$block_id]['options'] = array();
										$col['elements'][$block_id]['options']['sidebar'] = $options['col_'.$i]['sidebar'];

								}

								update_post_meta($this->id, '_tb_builder_'.$element_id.'_col_'.$i, $col);

							}

							break;

						/**
						 * Jumbotron
						 */
						case 'jumbotron' :

							$options['blocks'] = array();

							if ( ! empty( $options['title'] ) ) {
								$options['blocks']['block_1'] = array(
									'text'				=> $options['title'],
								    'size'				=> '450%',
								    'color'				=> '#444444',
								    'apply_bg_color'	=> '0',
								    'bg_color'			=> '#f2f2f2',
								    'bg_opacity'		=> '1',
								    'bold'				=> '0',
								    'italic'			=> '0',
								    'caps'				=> '0',
								    'wpautop'			=> '1'
								);
							}

							if ( ! empty( $options['content'] ) ) {
								$options['blocks']['block_2'] = array(
									'text'				=> $options['content'],
								    'size'				=> '150%',
								    'color'				=> '#444444',
								    'apply_bg_color'	=> '0',
								    'bg_color'			=> '#f2f2f2',
								    'bg_opacity'		=> '1',
								    'bold'				=> '0',
								    'italic'			=> '0',
								    'caps'				=> '0',
								    'wpautop'			=> '1'
								);
							}

							$options['max'] = '';
							$options['align'] = 'center';
							$options['apply_bg_color'] = '0';
							$options['bg_color'] = '#f2f2f2';
							$options['bg_opacity'] = '1';

							$options['buttons_stack'] = '0';
							$options['buttons_block'] = '0';

							$options['buttons'] = array();

							if ( ! empty($options['button']) ) {
								$options['buttons']['btn_1'] = array(
									'color' 		=> $options['button_color'],
									'custom' 		=> array(),
									'text'			=> $options['button_text'],
									'size'			=> $options['button_size'],
									'url'			=> $options['button_url'],
									'target'		=> $options['button_target'],
									'icon_before'	=> '',
									'icon_after'	=> ''
								);
							}

							unset( $options['title'], $options['content'], $options['button'], $options['button_text'], $options['button_color'], $options['button_size'], $options['button_url'], $options['button_target'] );

							break;

						/**
						 * Post Grid
						 */
						case 'post_grid' :

							$options['title'] = '';
							$options['pages'] = '';
							$options['display'] = 'grid';
							$options['paginated_hide'] = '0';
							$options['filter'] = 'category';
							$options['filter_max'] = '-1';
							$options['posts_per_page'] = '12';
							$options['slides'] = '3';
							$options['nav'] = '1';
							$options['thumbs'] = 'default';
							$options['meta'] = 'default';
							$options['excerpt'] = 'default';
							$options['more'] = 'default';
							$options['more_text'] = 'Read More';

							if ( empty($options['crop']) ) {
								$options['crop'] = 'tb_grid';
							}

							break;

						/**
						 * Post List
						 */
						case 'post_list' :

							$options['posts_per_page'] = $options['numberposts'];
							$options['display'] = 'list';
							$options['paginated_hide'] = '0';
							$options['filter'] = 'category';
							$options['meta'] = 'default';
							$options['more'] = 'default';
							$options['more_text'] = 'Read More';

							unset( $options['numberposts'], $options['content'] );

							break;

						/**
						 * Post List
						 */
						case 'post_slider' :

							$old = $options;

							$options = array(
								'style' 			=> 'style-1',
								'source' 			=> $old['source'],
								'categories' 		=> array($old['category'] => 1),
								'tag' 				=> $old['tag'],
								'posts_per_page'	=> $old['numberposts'],
								'orderby' 			=> $old['orderby'],
								'order' 			=> $old['order'],
								'offset' 			=> '0',
								'query' 			=> $old['query'],
								'crop' 				=> $old['image_size'],
								'slide_link' 		=> 'none', // convert below
								'button_color' 		=> 'custom',
								'button_custom'		=> array(
									'bg' 				=> '#ffffff',
									'bg_hover' 			=> '#ffffff',
									'border' 			=> '#ffffff',
									'text' 				=> '#ffffff',
									'text_hover' 		=> '#333333',
									'include_bg' 		=> '0',
									'include_border'	=> '1'
								),
								'button_text'		=> $old['button'], // convert below
								'button_size'		=> 'default',
								'interval'			=> $old['timeout'],
								'pause' 			=> '1',
								'wrap' 				=> '1',
								'nav_standard' 		=> $old['nav_standard'],
								'nav_arrows' 		=> $old['nav_arrows'],
								'nav_thumbs' 		=> '0',
								'dark_text' 		=> '0',
								'thumb_link' 		=> '1',
								'title' 			=> '1',
								'meta' 				=> '1',
								'excerpts' 			=> '0'
							);

							if ( $old['image_link'] == 'option' ) {
								$options['slide_link'] = 'image_link';
							} else if ( $old['image_link'] == 'permalink' ) {
								$options['slide_link'] = 'image_post';
							}

							if ( ! $options['button_text'] ) {
								$option['button_text'] = 'View Post';
							}

							break;

						/**
						 * Promo Box
						 */
						case 'slogan' :

							$old = $options;

							$options = array(
								'headline'				=> $old['slogan'],
								'desc'					=> '',
								'wpautop'				=> '1',
								'style'					=> 'none',
								'bg_color'				=> '#eeeeee',
								'bg_opacity'			=> '1',
								'text_color'			=> '#444444',
								'button'				=> $old['button'],
								'button_color'			=> $old['button_color'],
								'button_custom'			=> array(
									'bg' 				=> '#ffffff',
									'bg_hover'			=> '#ebebeb',
									'border' 			=> '#cccccc',
									'text'				=> '#333333',
									'text_hover'		=> '#333333',
									'include_bg'		=> '1',
									'include_border'	=> '1'
								),
								'button_text'			=> $old['button_text'],
								'button_url'			=> $old['button_url'],
								'button_size'			=> $old['button_size'],
								'button_placement'		=> 'right',
								'button_target'			=> $old['button_target'],
								'button_icon_before'	=> '',
								'button_icon_after'		=> ''
							);

							break;

						/**
						 * Tabs
						 */
						case 'tabs' :

							$old = $options;

							$options = array();

							if ( in_array( $old['setup']['nav'], array('pills', 'pills_above', 'pills_below') ) ) {
								$options['nav'] = 'pills';
							} else {
								$options['nav'] = 'tabs';
							}

							$options['style'] = $old['setup']['style'];
							$options['height'] = $old['height'];

							$options['tabs'] = array();

							for ( $i = 1; $i <= intval($old['setup']['num']); $i++ ) {

								$tab_id = uniqid( 'item_' . rand() );

								$options['tabs'][$tab_id] = array(
									'title'			=> $old['setup']['names']['tab_'.$i],
									'content'		=> array(
										'type'			=> $old['tab_'.$i]['type'],
										'raw'			=> $old['tab_'.$i]['raw'],
										'raw_format'	=> $old['tab_'.$i]['raw_format']
									)
								);

							}
					}

					$new[$section_id][$element_id]['options'] = $options;
				}
			}
		}

		// Update elements
		update_post_meta( $this->id, '_tb_builder_elements', $new );

		// Allow layout to be finalized at the end of all checks.
		$this->ran = true;
	}

	/**
	 * Convert elements from prior to plugin 2.0 and
	 * framework 2.5. This does NOT apply if the current
	 * theme version is prior to 2.5.
	 *
	 * @since 2.0.0
	 */
	public function convert_elements() {

		/*
		 * If the theme does not contain framework version 2.5+,
		 * altering the data will mess things up.
		 */
		if ( version_compare( $this->theme_version, '2.5.0', '<' ) ) {
			return;
		}

		/*
		 * If layout is saved after 2.0 of the plugin and v2.5
		 * of the theme framework, we're good to go.
		 */
		if ( version_compare( $this->saved, '2.0.0', '>=' ) && version_compare( $this->theme_saved, '2.5.0', '>=' ) ) {
			return;
		}

		$new = array();

		$sections = get_post_meta( $this->id, '_tb_builder_elements', true );

		if ( $sections ) {

			foreach ( $sections as $section_id => $elements ) {

				$new[$section_id] = array();

				foreach ( $elements as $element_id => $element ) {

					$old = $element['options'];

					switch ( $element['type'] ) {

						/**
						 * Content to Current, External, Widget (or remain Content)
						 */
						case 'content' :

							switch ( $old['source'] ) {

								case 'current' :
									$element['type'] = 'current';
									$element['options'] = array();
									break;

								case 'external' :
									$element['type'] = 'external';
									$element['options'] = array();
									$element['options']['post_id'] = themeblvd_post_id_by_name( $old['page_id'], 'page' );
									break;

								case 'widget_area' :
									$element['type'] = 'widget';
									$element['options'] = array();
									$element['options']['sidebar'] = $old['widget_area'];
									break;

								default : // remain Content element
									$element['options'] = array(
										'content'		=> $old['raw_content'],
										'format'		=> $old['raw_format'],
										'style'			=> 'none',
										'text_color'	=> 'dark',
										'bg_color'		=> '#eeeeee',
										'bg_opacity' 	=> '1'
									);
							}

							break;

						/**
						 * Paginated post list to post list
						 */
						case 'post_list_paginated' :

							$element['type'] = 'post_list';

							$element['options'] = array(
								'source' 			=> $old['source'],
								'categories'		=> $old['categories'],
								'tag'				=> $old['tag'],
								'posts_per_page'	=> $old['posts_per_page'],
								'orderby' 			=> $old['orderby'],
								'order'				=> $old['order'],
								'offset'			=> '0',
								'query'				=> $old['query'],
								'display'			=> 'paginated',
								'paginated_hide'	=> '0',
								'filter'			=> 'category',
								'thumbs'			=> 'default',
								'meta'				=> 'default',
								'more'				=> 'default',
								'more_text'			=> 'Read More'
							);

							break;

						/**
						 * Post list slider to post slider
						 */
						case 'post_list_slider' :

							$element['type'] = 'post_slider';

							$element['options'] = array(
								'style' 			=> 'style-1',
								'source' 			=> $old['source'],
								'categories' 		=> $old['categories'],
								'tag' 				=> $old['tag'],
								'posts_per_page'	=> $old['numberposts'],
								'orderby' 			=> $old['orderby'],
								'order' 			=> $old['order'],
								'offset' 			=> $old['offset'],
								'query' 			=> $old['query'],
								'crop' 				=> 'slider-large',
								'slide_link' 		=> 'button',
								'button_color' 		=> 'custom',
								'button_custom'		=> array(
									'bg' 				=> '#ffffff',
									'bg_hover' 			=> '#ffffff',
									'border' 			=> '#ffffff',
									'text' 				=> '#ffffff',
									'text_hover' 		=> '#333333',
									'include_bg' 		=> '0',
									'include_border'	=> '1'
								),
								'button_text'		=> 'View Post',
								'button_size'		=> 'default',
								'interval'			=> $old['timeout'],
								'pause' 			=> '1',
								'wrap' 				=> '1',
								'nav_standard' 		=> $old['nav_standard'],
								'nav_arrows' 		=> $old['nav_arrows'],
								'nav_thumbs' 		=> '0',
								'dark_text' 		=> '0',
								'thumb_link' 		=> '1',
								'title' 			=> '1',
								'meta' 				=> '1',
								'excerpts' 			=> '0'
							);

							break;

						/**
						 * Paginated post grid/Post grid slider to post grid
						 */
						case 'post_grid_paginated' :
						case 'post_grid_slider' :

							$element['type'] = 'post_grid';

							$element['options'] = array(
								'title'				=> '',
								'source' 			=> $old['source'],
								'categories' 		=> $old['categories'],
								'tag' 				=> $old['tag'],
								'orderby' 			=> $old['orderby'],
								'order' 			=> $old['order'],
								'offset'			=> '0',
								'pages'				=> '',
								'query' 			=> $old['query'],
								'display'			=> 'paginated',
								'columns'			=> $old['columns'],
								'rows'				=> $old['rows'],
								'paginated_hide'	=> '0',
								'filter'			=> 'category',
								'filter_max'		=> '-1',
								'posts_per_page'	=> '12',
								'slides'			=> '3',
								'timeout'			=> '3',
								'nav'				=> '1',
								'thumbs'			=> 'default',
								'meta'				=> 'default',
								'excerpt'			=> 'default',
								'more'				=> 'default',
								'more_text'			=> 'Read More',
								'crop'				=> $old['crop']
							);

							if ( empty($element['options']['crop']) ) {
								$element['options']['crop'] = 'tb_grid';
							}

							if ( $element['type'] == 'post_grid_slider' ) {
								$element['options']['display'] = 'slider';
								$element['options']['nav'] = $old['nav_standard'];
							}

					}

					$new[$section_id][$element_id] = $element;

				}
			}
		}

		// Update elements.
		update_post_meta( $this->id, '_tb_builder_elements', $new );

		// Allow layout to be finalized at the end of all checks.
		$this->ran = true;

	}

	/**
	 * Convert full width sliders to standard sliders
	 * with popout enabled.
	 *
	 * This is for themes using framework 2.5+, that
	 * are updating to framework 2.7+.
	 *
	 * @since 2.2.0
	 */
	public function remove_popout_sliders() {

		/*
		 * If the theme does not contain framework version
		 * 2.5+, this doesn't apply.
		 */
		if ( version_compare( $this->theme_version, '2.5.0', '<' ) ) {
			return;
		}

		/*
		 * If layout is saved after 2.2 of the plugin and v2.7
		 * of the theme framework, we're good to go.
		 */
		if ( version_compare( $this->saved, '2.2.0', '>=' ) && version_compare( $this->theme_saved, '2.7.0', '>=' ) ) {
			return;
		}

		$new = array();

		$sections = get_post_meta( $this->id, '_tb_builder_elements', true );

		if ( $sections ) {

			foreach ( $sections as $section_id => $elements ) {

				$new[ $section_id ] = array();

				foreach ( $elements as $element_id => $element ) {

					/*
					 * Convert Simple Slider (Full Width) to
					 * Simple Slider.
					 *
					 * And convert Post Slider (Full Width) to
					 * Post Slider.
					 */
					if ( 'simple_slider_popout' === $element['type'] || 'post_slider_popout' === $element['type'] ) {

						if ( 'post_slider_popout' === $element['type'] ) {

							$element['type'] = 'post_slider';

						} else {

							$element['type'] = 'simple_slider';

						}

						if ( isset( $element['options']['cover'] ) ) {

							unset( $element['options']['cover'] );

						}

						if ( isset( $element['options']['position'] ) ) {

							unset( $element['options']['position'] );

						}

						if ( isset( $element['options']['height_desktop'] ) ) {

							unset( $element['options']['height_desktop'] );

						}

						if ( isset( $element['options']['height_tablet'] ) ) {

							unset( $element['options']['height_tablet'] );

						}

						if ( isset( $element['options']['height_mobile'] ) ) {

							unset( $element['options']['height_mobile'] );

						}
					}

					$new[ $section_id ][ $element_id ] = $element;

				}
			}
		}

		// Update elements.
		update_post_meta( $this->id, '_tb_builder_elements', $new );

		// Allow layout to be finalized at the end of all checks.
		$this->ran = true;

	}
}
