<?php
/**
 * Layout Builder Ajax
 */
class Theme_Blvd_Layout_Builder_Ajax {

	public $admin_page;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $admin_page Object from Theme_Blvd_Sliders_Admin class
	 */
	public function __construct( $admin_page ) {

		// Add general slider admin functions for use within Ajax
		$this->admin_page = $admin_page;

		// Hook in Ajax funcition to WP
		add_action( 'wp_ajax_themeblvd_add_layout', array( $this, 'add_layout' ) );
		add_action( 'wp_ajax_themeblvd_save_layout', array( $this, 'save_layout' ) );
		add_action( 'wp_ajax_themeblvd_add_element', array( $this, 'add_element' ) );
		add_action( 'wp_ajax_themeblvd_update_builder_table', array( $this, 'update_table' ) );
		add_action( 'wp_ajax_themeblvd_delete_layout', array( $this, 'delete_layout' ) );
		add_action( 'wp_ajax_themeblvd_edit_layout', array( $this, 'edit_layout' ) );
		add_action( 'wp_ajax_themeblvd_mini_edit_layout', array( $this, 'mini_edit_layout' ) );
		add_action( 'wp_ajax_themeblvd_layout_toggle', array( $this, 'layout_toggle' ) );

	}

	/**
	 * Add new layout
	 *
	 * @since 1.0.0
	 */
	public function add_layout() {

		// Make sure Satan isn't lurking
		check_ajax_referer( 'themeblvd_new_builder', 'security' );

		// Handle form data
		parse_str( $_POST['data'], $config );

		// Setup arguments for new 'layout' post
		$args = array(
			'post_type'			=> 'tb_layout',
			'post_title'		=> $config['tb_new_layout']['layout_name'],
			'post_status' 		=> 'publish',
			'comment_status'	=> 'closed',
			'ping_status'		=> 'closed'
		);

		// Create new post
		$post_id = wp_insert_post( $args );

		// Setup meta
		if ( ! empty( $config['tb_new_layout']['layout_start'] ) ) {

			if ( $config['tb_new_layout']['layout_start'] == 'layout' ) {

				// Configure meta for pre-existing layout
				$layout_id = $config['tb_new_layout']['layout_existing'];
				$elements = get_post_meta( $layout_id, 'elements', true );
				$settings = get_post_meta( $layout_id, 'settings', true );

			} else if ( $config['tb_new_layout']['layout_start'] == 'sample' ) {

				// Configure meta for sample layout
				$samples = themeblvd_get_sample_layouts();
				$current_sample = $samples[$config['tb_new_layout']['layout_sample']];
				$elements = array(
					'featured' => $current_sample['featured'],
					'primary' => $current_sample['primary'],
					'featured_below' => $current_sample['featured_below']
				);
				$settings = array( 'sidebar_layout' => $current_sample['sidebar_layout'] );

			} else {

				// Configure meta for blank layout
				$elements = array();
				$settings = array( 'sidebar_layout' => $config['tb_new_layout']['layout_sidebar'] );

			}
		}

		// Update even if they're empty
		update_post_meta( $post_id, 'elements', $elements );
		update_post_meta( $post_id, 'settings', $settings );

		// Adjust response depending on where the creation
		// of the layout happenned.
		if ( ! isset( $config['action'] ) || $config['action'] != 'editpost' ) {
			// If this coming from the Builder, send back Post
			// ID and edit layout interface.
			echo $post_id.'[(=>)]';
			$this->admin_page->edit_layout( $post_id );
		} else {
			// If this is coming from the Edit Page meta box,
			// send back post slug (i.e. layout ID).
			$post = get_post( $post_id );
			echo $post->post_name;
		}
		die();
	}

	/**
	 * Save layout
	 *
	 * Note: In v1.1.0, this function was configured
	 * to be used from both an ajax process and when
	 * saving a WP page.
	 *
	 * @since 1.0.0
	 */
	public function save_layout() {

		// Determine if this is an AJAX process or not.
		$ajax = defined( 'DOING_AJAX' ) ? true : false;

		// Setup the data depending on whether this
		// is coming from an Ajax process or not.
		if ( $ajax ) {

			// Make sure Satan isn't lurking
			check_ajax_referer( 'themeblvd_save_builder', 'security' );

			// Handle form data
			parse_str( $_POST['data'], $data );

		} else {

			// Setup data
			$data = $_POST;

		}

		// Check to make sure we're coming from
		// the right place.
		if ( ! isset( $data['tb_layout_id'] ) ) {
			return;
		}

		// Layout ID
		$layout_id = $data['tb_layout_id'];

		// Setup elements
		$location = 'featured';
		$elements = array();
		if ( isset( $data['tb_elements'] ) ) {

			// Get default element options
			$default_element_options = $this->admin_page->get_elements();

			// Loop through setting items in 'featured' location
			// until we arrive at the divider line, then
			// continue putting them into the 'primary' area,
			// and then when we hit divider_2, set location to
			// 'featured_below'.
			foreach ( $data['tb_elements'] as $id => $element ) {
				// Featured area elements get assigned first.
				if ( $id == 'divider' ) {
					// ... And now the primary area
					$location = 'primary';
				} else if ( $id == 'divider_2' ) {
					// ... And now the featured below area
					$location = 'featured_below';
				} else {

					// Sanitize element's options
					$clean = array();
					foreach ( $default_element_options[$element['type']]['options'] as $option ) {

						if ( ! isset( $option['id'] ) ) {
							continue;
						}

						if ( ! isset( $option['type'] ) ) {
							continue;
						}

						$option_id = $option['id'];

						// Set checkbox to false if it wasn't sent in the $_POST
						if ( 'checkbox' == $option['type'] ) {
							if ( isset( $element['options'][$option_id] ) ) {
								$element['options'][$option_id] = '1';
							} else {
								$element['options'][$option_id] = '0';
							}
						}

						// Set each item in the multicheck to false if it wasn't sent in the $_POST
						if ( 'multicheck' == $option['type'] ) {
							if ( ! isset( $element['options'][$option_id] ) ) {
								$element['options'][$option_id] = array();
							}
						}

						// For a value to be submitted to database it must pass through a sanitization filter
						if ( has_filter( 'themeblvd_sanitize_' . $option['type'] ) ) {
							$clean[$option_id] = apply_filters( 'themeblvd_sanitize_' . $option['type'], $element['options'][$option_id], $option );
						}

					}
					$element['options'] = $clean;
					$elements[$location][$id] = $element;
				}
			}
		}

		// Setup options
		$options = isset( $data['tb_layout_options'] ) ? $options = $data['tb_layout_options'] : $options = null;

		// Update even if they're empty
		update_post_meta( $layout_id, 'elements', $elements );
		update_post_meta( $layout_id, 'settings', $options );

		// If this is not an ajax process, we're done here.
		// Move on, already. Get over it.
		if ( ! $ajax ) {
			return;
		}

		// Layout Information
		if ( isset( $data['tb_layout_info'] ) ) {

			// Start post data to be updated with the ID
			$post_atts = array(
				'ID' => $layout_id
			);

			// Post Title (only used in admin for reference)
			if ( isset( $data['tb_layout_info']['post_title'] ) ) {
				$post_atts['post_title'] = $data['tb_layout_info']['post_title'];
			}

			// Post Slug (used as custom layout ID, important! )
			if ( isset( $data['tb_layout_info']['post_name'] ) ) {
				$post_atts['post_name'] = $data['tb_layout_info']['post_name'];
			}

			// Update Post info
			wp_update_post( $post_atts );

		}

		// Get most recent layout id after doing the above processes
		$updated_layout = get_post($layout_id);
		$current_layout_id = $updated_layout->post_name;

		// Send current layout ID back with response
		echo $current_layout_id.'[(=>)]';

		// Display update message
		echo '<div id="setting-error-save_options" class="updated fade settings-error ajax-update">';
		echo '	<p><strong>'.__( 'Layout saved.', 'themeblvd_builder' ).'</strong></p>';
		echo '</div>';
		die();
	}

	/**
	 * Add new element
	 *
	 * @since 1.0.0
	 */
	public function add_element() {
		$element_type = $_POST['data'];
		$element_id = uniqid( 'element_'.rand() );
		$this->admin_page->edit_element( $element_type, $element_id );
		die();
	}

	/**
	 * Update layout manager table
	 *
	 * @since 1.0.0
	 */
	public function update_table() {
		$this->admin_page->manage_layouts();
		die();
	}

	/**
	 * Delete layout
	 *
	 * @since 1.0.0
	 */
	function delete_layout() {

		// Make sure Satan isn't lurking
		check_ajax_referer( 'themeblvd_manage_builder', 'security' );

		// Handle data
		parse_str( $_POST['data'], $data );

		// Only run if user selected some layouts to delete
		if ( isset( $data['posts'] ) ) {

			// Delete slider posts
			foreach ( $data['posts'] as $id ) {

				// Can still be recovered from trash
				// if post type's admin UI is turned on.
				wp_delete_post( $id );

			}

			// Send back number of sliders
			$posts = get_posts( array( 'post_type' => 'tb_layout', 'numberposts' => -1 ) );
			echo sprintf( _n( '1 Layout', '%s Layouts', count($posts) ), number_format_i18n( count($posts) ) ).'[(=>)]';

			// Display update message
			echo '<div id="setting-error-delete_layout" class="updated fade settings-error ajax-update">';
			echo '	<p><strong>'.__( 'Layout(s) deleted.', 'themeblvd_builder' ).'</strong></p>';
			echo '</div>';

		}

		die();
	}

	/**
	 * Edit a layout
	 *
	 * @since 1.0.0
	 */
	public function edit_layout() {
		$layout_id = $_POST['data'];
		echo $layout_id.'[(=>)]';
		$this->admin_page->edit_layout( $layout_id );
		die();
	}

	/**
	 * Edit a layout from a meta box on
	 * Edit Page screen.
	 *
	 * @since 1.1.0
	 */
	public function mini_edit_layout() {
		// Security
		check_ajax_referer( 'themeblvd_save_builder', 'security' );
		// Send back interface to edit layout
		$layout_id = themeblvd_post_id_by_name( $_POST['data'], 'tb_layout' );
		$this->admin_page->mini_edit_layout( $layout_id );
		die();
	}

	/**
	 * Send back updated layout toggle menu.
	 *
	 * @since 1.1.0
	 */
	public function layout_toggle() {
		echo $this->admin_page->layout_select( $_POST['data'] );
		die();
	}

}