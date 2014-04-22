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
			case 'settings' :
				$this->verify_settings();
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
		 * In v2.0 of the Builder, we added the system
		 * of "Content Blocks" to Columns and Content
		 * elements. This will convert data from the old
		 * system to the new content block system.
		 */
		$this->content_blocks();

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
	public function verify_settings() {

		/**
		 * Extend
		 */
		do_action( 'themeblvd_builder_verify_settings', $this );
	}

	/*--------------------------------------------*/
	/* Methods, individual checks to build upon
	/*--------------------------------------------*/

	/**
	 * Verify that content block data is setup
	 * correctly. Applies to those updating the
	 * Builder plugin from v1 to v2.0+
	 *
	 * @since 2.0.0
	 */
	public function content_blocks() {

		// If layout is saved after 2.0 of the plugin,
		// we're good to go.
		if ( version_compare( $this->saved, '2.0.0', '>=' ) ) {
			return;
		}

		$locations = get_post_meta( $this->id, 'elements', true );

		$new = array();

		foreach ( $locations as $location_id => $elements ) {
			foreach ( $elements as $element_id => $element ) {

				if ( $element['type'] == 'content' || $element['type'] == 'columns' ) {

					// Start revises settings
					$new[$location_id][$element_id]['type'] = $element['type'];
					$new[$location_id][$element_id]['query_type'] = $element['query_type'];
					$new[$location_id][$element_id]['options'] = array();

					if ( $element['type'] == 'content' ) {

						$blocks = array();
						$block_id = uniqid( 'block_'.rand() );
						$blocks[$block_id] = array();

						// Convert Content element
						switch ( $element['options']['source'] ) {

							case 'current' :
								$blocks[$block_id]['type'] = 'current';
								break;

							case 'page' :
								$blocks[$block_id]['type'] = 'page';
								$blocks[$block_id]['options'] = array();
								$blocks[$block_id]['options']['page_id'] = $element['options']['page_id'];
								break;

							case 'raw' :
								$blocks[$block_id]['type'] = 'raw';
								$blocks[$block_id]['options'] = array();
								$blocks[$block_id]['options']['text'] = $element['options']['raw_content'];
								$blocks[$block_id]['options']['format'] = $element['options']['raw_format'];
								break;

							case 'widget' :
								$blocks[$block_id]['type'] = 'widget';
								$blocks[$block_id]['options'] = array();
								$blocks[$block_id]['options']['widget_area'] = $element['options']['widget_area'];
								break;
						}

						// Add content block to meta data
						update_post_meta( $this->id, $element_id.'_col_1', $blocks ); // "element_123_col_1"

						// Get rid of the options no longer needed
						unset( $element['options']['source'] );
						unset( $element['options']['page_id'] );
						unset( $element['options']['raw_content'] );
						unset( $element['options']['raw_format'] );
						unset( $element['options']['widget_area'] );

						// Add revised options for this element
						$new[$location_id][$element_id]['options'] = $element['options'];

					} else if ( $element['type'] == 'columns' ) {

						// Convert Columns element
						foreach ( $element['options'] as $option_id => $settings ) {
							if ( in_array( $option_id, array( 'col_1', 'col_2', 'col_3', 'col_4', 'col_5' ) ) ) {

								$blocks = array();
								$block_id = uniqid( 'block_'.rand() );
								$blocks[$block_id] = array();

								// Convert Column
								switch ( $settings['type'] ) {

									case 'current' :
										$blocks[$block_id]['type'] = 'current';
										break;

									case 'page' :
										$blocks[$block_id]['type'] = 'page';
										$blocks[$block_id]['options'] = array();
										$blocks[$block_id]['options']['page_id'] = $settings['page'];
										break;

									case 'raw' :
										$blocks[$block_id]['type'] = 'raw';
										$blocks[$block_id]['options'] = array();
										$blocks[$block_id]['options']['text'] = $settings['raw'];
										$blocks[$block_id]['options']['format'] = $settings['raw_format'];
										break;

									case 'widget' :
										$blocks[$block_id]['type'] = 'widget';
										$blocks[$block_id]['options'] = array();
										$blocks[$block_id]['options']['widget_area'] = $settings['sidebar'];
										break;
								}

								// Add content block to meta data
								update_post_meta( $this->id, $element_id.'_'.$option_id, $blocks ); // "element_123_col_1"

							} else {

								// Option can remain unchanged
								$new[$location_id][$element_id]['options'][$option_id] = $settings;

							}
						}
					}

				} else {

					// Not an element using content blocks; so we can just pass it back through.
					$new[$location_id][$element_id] = $element;
				}
			}
		}

		// Update elements
		update_post_meta( $this->id, 'elements', $new );
		update_post_meta( $this->id, 'plugin_version_saved', $this->version );

	}
}