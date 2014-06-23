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
	 * @param int $id ID of current layout to check
	 */
	public function __construct( $id ) {

		// Post ID for custom layout, tb_layout post type
		$this->id = $id;

		// Plugin version which layout was created with
		$created = get_post_meta( $this->id, 'plugin_version_created', true );
		if ( $created ) {
			$this->created = $created;
		}

		// Plugin version which layout was last saved with
		$saved = get_post_meta( $this->id, 'plugin_version_saved', true );
		if ( $saved ) {
			$this->saved = $saved;
		}

		// Theme Framework version which layout was created with
		$theme_created = get_post_meta( $this->id, 'framework_version_created', true );
		if ( $theme_created ) {
			$this->theme_created = $theme_created;
		}

		// Theme Framework version which layout was last saved with
		$theme_saved = get_post_meta( $this->id, 'framework_version_saved', true );
		if ( $theme_saved ) {
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

		/**
		 * In v2.0 of the Builder, we added display options
		 * to elements to control the background of each element.
		 */
		$this->display_options();

		/**
		 * In v2.0 of the Builder, we eliminated featured
		 * sections for elements.
		 */
		$this->merge_elements();

		/**
		 * In v2.0 of the Builder, we added the system
		 * of "Blocks" to Columns element This will convert
		 * data from the old system to the new block system.
		 *
		 * Also, when the "Tabs" element is transferred to a
		 * block within a Columns element, it will be updated
		 * to use the new sortable option type.
		 */
		$this->to_blocks();

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
			update_post_meta( $this->id, 'plugin_version_saved', $this->version );
			update_post_meta( $this->id, 'framework_version_saved', $this->theme_version );
		}
	}

	/*--------------------------------------------*/
	/* Methods, individual checks to build upon
	/*--------------------------------------------*/

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

		$locations = get_post_meta( $this->id, 'elements', true );

		if ( ! isset($locations['featured']) && ! isset($locations['primary']) && ! isset($locations['featured_below']) ) {
			return;
		}

		$new = array();

		foreach ( $locations as $location_id => $elements ) {
			foreach ( $elements as $element_id => $element ) {

				// Setup display settings
				$element['display'] = array(
					'bg_type'           		=> 'none',
					'text_color'        		=> 'dark',
					'bg_color'          		=> '#f2f2f2',
					'bg_color_opacity'  		=> '1',
					'bg_texture'        		=> 'arches',
					'apply_bg_texture_parallax'	=> 0,
					'bg_texture_parallax'		=> '5',
					'bg_image'          		=> array(
					    'color'             	=> '',
					    'image'             	=> '',
					    'repeat'            	=> 'no-repeat',
					    'position'          	=> 'top center',
					    'attachment'        	=> 'scroll',
					    'size'					=> 'auto'
					),
					'bg_image_parallax_stretch'	=> 1,
					'bg_image_parallax'			=> '5',
					'apply_bg_shade'    		=> 0,
					'bg_shade_color'    		=> '#000000',
					'bg_shade_opacity'  		=> '0.5',
					'apply_popout'				=> 0,
					'apply_padding'     		=> 0,
					'padding_top'       		=> '30px',
					'padding_right'     		=> '30px',
					'padding_bottom'    		=> '30px',
					'padding_left'      		=> '30px',
					'visibility'        		=> array(
					    'hide_on_standard'		=> 0,
					    'hide_on_tablet'    	=> 0,
					    'hide_on_mobile'    	=> 0
					),
					'classes'           		=> ''
				);

				// If this element was in a featured element, automatically
				// apply old feature area styling for background options of element.
				if ( $location_id == 'featured' && themeblvd_supports( 'featured', 'style' ) ) {
					$element['display']['bg_type'] = 'featured';
				} else if ( $location_id == 'featured_below' && themeblvd_supports( 'featured_below', 'style' ) ) {
					$element['display']['bg_type'] = 'featured_below';
				}

				// Move responsive visibility settings from element options to
				// new display options.
				if ( isset( $element['options']['visibility'] ) ) {
					$element['display']['visibility'] = $element['options']['visibility'];
					unset( $element['options']['visibility'] );
				}

				// Move CSS classes setting from element options to new display options.
				if ( isset( $element['options']['classes'] ) ) {
					$element['display']['classes'] = $element['options']['classes'];
					unset( $element['options']['classes'] );
				}

				// Now that display settings are added, add entire element back
				$new[$location_id][$element_id] = $element;
			}
		}

		// Update elements
		update_post_meta( $this->id, 'elements', $new );

		// Allow layout to be finalized at the end of all checks.
		$this->ran = true;

	}

	/**
	 * Verify that elements are merged, and not separated
	 * by old method of Featured/Primary/Featured Below.
	 *
	 * @since 2.0.0
	 */
	public function merge_elements() {

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

		$locations = get_post_meta( $this->id, 'elements', true );

		if ( ! isset($locations['featured']) && ! isset($locations['primary']) && ! isset($locations['featured_below']) ) {
			return;
		}

		$new = array();

		foreach ( $locations as $location_id => $elements ) {
			foreach ( $elements as $element_id => $element ) {
				$new[$element_id] = $element;
			}
		}

		// Update elements
		update_post_meta( $this->id, 'elements', $new );

		// Allow layout to be finalized at the end of all checks.
		$this->ran = true;
	}

	/**
	 * Verify that content block data is setup
	 * correctly. Applies to those updating the
	 * Builder plugin from v1 to v2.0+
	 *
	 * @since 2.0.0
	 */
	public function to_blocks() {

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

		$elements = get_post_meta( $this->id, 'elements', true );

		// Default values for column display data
		$col_display = array(
			'bg_type'           => 'none',
			'text_color'        => 'dark',
			'bg_color'          => '#f2f2f2',
			'bg_color_opacity'  => '1',
			'bg_texture'        => 'arches',
			'bg_image'          => array(
			    'color'             => '',
			    'image'             => '',
			    'repeat'            => 'no-repeat',
			    'position'          => 'top center',
			    'attachment'        => 'scroll'
			),
			'apply_padding'     => 0,
			'padding_top'       => '30px',
			'padding_right'     => '30px',
			'padding_bottom'    => '30px',
			'padding_left'      => '30px',
			'classes'           => ''
		);

		$new = array();

		foreach ( $elements as $element_id => $element ) {

			if ( $element['type'] == 'content' || $element['type'] == 'columns' || $element['type'] == 'tabs' ) {

				// Start settings revisions
				$new[$element_id]['type'] = $element['type'];
				$new[$element_id]['query_type'] = $element['query_type'];
				$new[$element_id]['options'] = array();
				$new[$element_id]['display'] = $element['display'];

				// As columns elements, all will need following options
				$element['options']['height'] = 0;
				$element['options']['align'] = 'top';

				if ( $element['type'] == 'content' ) {

					$blocks = array();
					$block_id = uniqid( 'block_'.rand() );
					$blocks[$block_id] = array();
					$blocks[$block_id]['query_type'] = 'none';

					// Convert Content element
					switch ( $element['options']['source'] ) {

						case 'current' :
							$blocks[$block_id]['type'] = 'current';
							break;

						case 'page' :
							$blocks[$block_id]['type'] = 'page';
							$blocks[$block_id]['options'] = array();
							$blocks[$block_id]['options']['page'] = $element['options']['page_id'];
							break;

						case 'raw' :
							$blocks[$block_id]['type'] = 'raw';
							$blocks[$block_id]['options'] = array();
							$blocks[$block_id]['options']['raw'] = $element['options']['raw_content'];
							$blocks[$block_id]['options']['raw_format'] = $element['options']['raw_format'];
							break;

						case 'widget' :
							$blocks[$block_id]['type'] = 'widget';
							$blocks[$block_id]['options'] = array();
							$blocks[$block_id]['options']['sidebar'] = $element['options']['widget_area'];
							break;
					}

					$column = array(
						'display' 	=> $col_display,
						'blocks'	=> $blocks
					);

					// Add content block to meta data
					update_post_meta( $this->id, $element_id.'_col_1', $column ); // "element_123_col_1"

					// Get rid of the options no longer needed
					unset( $element['options']['source'] );
					unset( $element['options']['page_id'] );
					unset( $element['options']['raw_content'] );
					unset( $element['options']['raw_format'] );
					unset( $element['options']['widget_area'] );

					// Change type to "columns" -- No more "Content"
					// element; it's now be a 1-column columns element.
					$new[$element_id]['type'] = 'columns';

					// Add revised options for this element
					$new[$element_id]['options'] = $element['options'];
					$new[$element_id]['options']['setup'] = '1/1';

				} else if ( $element['type'] == 'columns' ) {

					// Convert Columns element
					foreach ( $element['options'] as $option_id => $settings ) {
						if ( in_array( $option_id, array( 'col_1', 'col_2', 'col_3', 'col_4', 'col_5' ) ) ) {

							$blocks = array();
							$block_id = uniqid( 'block_'.rand() );
							$blocks[$block_id] = array();
							$blocks[$block_id]['query_type'] = 'none';

							// Convert Column
							switch ( $settings['type'] ) {

								case 'current' :
									$blocks[$block_id]['type'] = 'current';
									break;

								case 'page' :
									$blocks[$block_id]['type'] = 'page';
									$blocks[$block_id]['options'] = array();
									$blocks[$block_id]['options']['page'] = $settings['page'];
									break;

								case 'raw' :
									$blocks[$block_id]['type'] = 'raw';
									$blocks[$block_id]['options'] = array();
									$blocks[$block_id]['options']['raw'] = $settings['raw'];
									$blocks[$block_id]['options']['raw_format'] = $settings['raw_format'];
									break;

								case 'widget' :
									$blocks[$block_id]['type'] = 'widget';
									$blocks[$block_id]['options'] = array();
									$blocks[$block_id]['options']['sidebar'] = $settings['sidebar'];
									break;
							}

							$column = array(
								'display' 	=> $col_display,
								'blocks'	=> $blocks
							);

							// Add content block to meta data
							update_post_meta( $this->id, $element_id.'_'.$option_id, $column ); // "element_123_col_1"

						} else if ( $option_id == 'setup' ) {

							if ( is_array( $settings ) ) {

								if ( ! empty( $settings['width'] ) && ! empty( $settings['num'] ) ) {

									$widths = $settings['width'][$settings['num']];
									$widths = explode('-', $widths);

									foreach ( $widths as $key => $value ) {
										$widths[$key] = themeblvd_grid_fraction($value);
									}

									$new[$element_id]['options'][$option_id] = implode('-', $widths);
								}
							}

						} else {

							// Option can remain unchanged
							$new[$element_id]['options'][$option_id] = $settings;

						}
					}

				} else if ( $element['type'] == 'tabs' ) {

					$blocks = array();
					$block_id = uniqid( 'block_'.rand() );

					$blocks[$block_id]['type'] = 'tabs';
					$blocks[$block_id]['query_type'] = 'none';

					// Tabs
					$num = 3;
					$tabs = array();

					if ( ! empty( $element['options']['setup']['num'] ) ) {
						$num = intval($element['options']['setup']['num']);
					}

					for ( $i = 1; $i <= $num; $i++ ) {

						$tab = array();

						// Title
						$tab['title'] = '';

						if ( ! empty( $element['options']['setup']['names']['tab_'.$i] ) ) {
							$tab['title'] = $element['options']['setup']['names']['tab_'.$i];
						}

						// Content
						$tab['content'] = array();

						$tab['content']['type'] = '';
						if ( ! empty( $element['options']['tab_'.$i]['type'] ) ) {
							$tab['content']['type'] = $element['options']['tab_'.$i]['type'];
						}

						switch ( $tab['content']['type'] ) {
							case 'page' :

								$tab['content']['page'] = '';

								if ( ! empty( $element['options']['tab_'.$i]['page'] ) ) {
									$tab['content']['page'] = $element['options']['tab_'.$i]['page'];
								}
								break;

							case 'raw' :

								$tab['content']['raw'] = '';

								if ( ! empty( $element['options']['tab_'.$i]['raw'] ) ) {
									$tab['content']['raw'] = $element['options']['tab_'.$i]['raw'];
								}

								$tab['content']['raw_format'] = 1;

								if ( isset( $element['options']['tab_'.$i]['raw_format'] ) ) {
									$tab['content']['raw_format'] = $element['options']['tab_'.$i]['raw_format'];
								}
								break;

							case 'widget' :

								$tab['content']['sidebar'] = '';

								if ( ! empty( $element['options']['tab_'.$i]['sidebar'] ) ) {
									$tab['content']['sidebar'] = $element['options']['tab_'.$i]['sidebar'];
								}
								break;
						}

						// Add the tab to the rest
						$tabs['tab_'.$i] = $tab;

					}

					$blocks[$block_id]['options']['tabs'] = $tabs;

					// Navigation
					$nav = 'tabs';

					if ( ! empty( $element['options']['setup']['nav'] ) ) {
						$nav = $element['options']['setup']['nav'];
					}

					if ( in_array( $nav, array( 'tabs', 'tabs_above', 'tabs_above', 'tabs_right', 'tabs_left' ) ) ) {
				        $nav = 'tabs';
				    } else if ( in_array( $nav, array( 'pills', 'pills_above', 'pills_above' ) ) ) {
				        $nav = 'pills';
				    }

					$blocks[$block_id]['options']['nav'] = $nav;

					// Style
					$style = 'framed';

					if ( ! empty( $element['options']['setup']['style'] ) ) {
						$style = $element['options']['setup']['style'];
					}

					$blocks[$block_id]['options']['style'] = $style;

					// Fixed height
					$height = 0;

					if ( ! empty( $element['options']['height'] ) ) {
						$height = $element['options']['height'];
					}

					$blocks[$block_id]['options']['height'] = $height;

					// Setup final column
					$column = array(
						'display'	=> $col_display,
						'blocks'	=> $blocks
					);

					// Add content block to meta data
					update_post_meta( $this->id, $element_id.'_col_1', $column ); // "element_123_col_1"

					// Change type to "columns
					$new[$element_id]['type'] = 'columns';

					// Add revised options for this element
					$new[$element_id]['options'] = array();
					$new[$element_id]['options']['setup'] = '1/1';

				}

			} else {

				// Not an element using content blocks; so we can just pass it back through.
				$new[$element_id] = $element;
			}
		}

		// Update elements
		update_post_meta( $this->id, 'elements', $new );

		// Allow layout to be finalized at the end of all checks.
		$this->ran = true;
	}

}