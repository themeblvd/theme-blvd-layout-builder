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
		add_action( 'wp_ajax_themeblvd_add_template', array( $this, 'add_template' ) );
		add_action( 'wp_ajax_themeblvd_apply_template', array( $this, 'apply_template' ) );
		add_action( 'wp_ajax_themeblvd_save_template', array( $this, 'save_template' ) );
		add_action( 'wp_ajax_themeblvd_add_section', array( $this, 'add_section' ) );
		add_action( 'wp_ajax_themeblvd_add_element', array( $this, 'add_element' ) );
		add_action( 'wp_ajax_themeblvd_add_block', array( $this, 'add_block' ) );
		add_action( 'wp_ajax_themeblvd_update_builder_table', array( $this, 'update_table' ) );
		add_action( 'wp_ajax_themeblvd_delete_layout', array( $this, 'delete_layout' ) );
		add_action( 'wp_ajax_themeblvd_edit_layout', array( $this, 'edit_layout' ) );
		add_action( 'wp_ajax_themeblvd_mini_edit_layout', array( $this, 'mini_edit_layout' ) );
		add_action( 'wp_ajax_themeblvd_layout_toggle', array( $this, 'layout_toggle' ) );
		add_action( 'wp_ajax_themeblvd_dup_element', array( $this, 'dup_element' ) );
		add_action( 'wp_ajax_themeblvd_dup_block', array( $this, 'dup_block' ) );

	}

	/**
	 * Add new template
	 *
	 * @since 2.0.0
	 */
	public function add_template() {

		// Make sure Satan isn't lurking
		check_ajax_referer( 'tb_save_layout', 'security' );

		// Handle form data
		parse_str( $_POST['data'], $data );

		// Template name
		$name = stripslashes($data['tb_template_name']);

		// Create new template
		$template_id = $this->admin_page->new_template( array( 'name' => $name ) );

		if ( ! $template_id ) {
			esc_html_e('An error occurred and the template could not be created.', 'theme-blvd-layout-builder');
			die();
		}

		// Post ID
		$post_id = 0;

		if ( isset($data['post_id']) ) {
			$post_id = $data['post_id'];
		}

		// Populate the new template
		$this->admin_page->save_layout( $template_id, $data );

		printf(esc_html__('The new template, %s, was created successfully.', 'theme-blvd-layout-builder'), $name );
		echo '[(=>)]';
		echo $this->admin_page->layout_select( '', 'apply', '_tb_apply_layout', $post_id );
		echo '[(=>)]';
		echo $this->admin_page->layout_select( 0, 'sync', '_tb_custom_layout' );
		die();
	}

	/**
	 * Apply template to layout when editing page
	 *
	 * @since 2.0.0
	 */
	public function apply_template() {

		// Make sure Satan isn't lurking
		check_ajax_referer( 'tb_save_layout', 'security' );

		// Are we applying a sample layout?
		if ( strpos($_POST['data'], '=>') !== false ) {

			// Save the sample layout's data to the current post

			$data = explode( '=>', $_POST['data'] );
			$post_id = $data[0];
			$name = $data[1];

			$samples = themeblvd_get_sample_layouts();

			if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {

				$this->admin_page->sample_uri = trailingslashit($samples[$name]['uri']);
				$this->admin_page->sample_dir = $dir = trailingslashit($samples[$name]['dir']);
				$this->admin_page->sample_asset_uri = trailingslashit($samples[$name]['assets']);
				$xml = $dir.'layout.xml';
				$import = '';

				if ( file_exists($xml) && function_exists('simplexml_load_file') ) {
					$internal_errors = libxml_use_internal_errors(true);
					$import = simplexml_load_file($xml);
				}

				if ( $import ) {
					foreach( $import->data->meta as $meta ) {

						$key = (string)$meta->key;
						$value = (string)$meta->value;
						$value = maybe_unserialize(base64_decode($value));

						// Process images from meta data
						$value = $this->admin_page->sample_images( $value, $key );

						// Store to post meta of new template
						update_post_meta( $post_id, $key, $value );

					}
				}

			} else { // @deprecated

				$elements = array(
					'featured' 			=> $samples[$name]['featured'],
					'primary' 			=> $samples[$name]['primary'],
					'featured_below' 	=> $samples[$name]['featured_below']
				);

				update_post_meta( $post_id, '_tb_builder_elements', $elements );

				$settings = array();

				if ( ! empty( $samples[$name] ) ) {
					$settings = array( 'sidebar_layout' => $samples[$name]['sidebar_layout'] );
				}

				update_post_meta( $post_id, 'settings', $settings );

			}

		} else {

			// Set the post ID to the existing template in order to
			// retrieve it's data when calling edit_layout()
			$post_id = themeblvd_post_id_by_name( $_POST['data'], 'tb_layout' );

		}

		// Edit Layout
		$this->admin_page->edit_layout($post_id);
		die();
	}

	/**
	 * Save template
	 *
	 * @since 2.0.0
	 */
	public function save_template() {

		// Make sure Satan isn't lurking
		check_ajax_referer( 'save_template', 'security' );

		// Handle form data
		parse_str( $_POST['data'], $data );

		// Save layout
		$this->admin_page->save_layout( $data['template_id'], $data );

		die();
	}

	/**
	 * Add new section
	 *
	 * @since 1.0.0
	 */
	public function add_section() {
		$this->admin_page->edit_section( 0 );
		die();
	}

	/**
	 * Add new element
	 *
	 * @since 1.0.0
	 */
	public function add_element() {
		parse_str( $_POST['data'], $data );
		$this->admin_page->edit_element( 0, $data['section_id'], $data['element_type'] );
		die();
	}

	/**
	 * Add new content block
	 *
	 * @since 2.0.0
	 */
	public function add_block() {
		parse_str( $_POST['data'], $data );
		$block_id = uniqid( 'block_'.rand() );
		echo $block_id.'[(=>)]';
		$this->admin_page->edit_block( $data['section_id'], $data['element_id'], $data['block_type'], $block_id, $data['col_num'] );
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
	public function delete_layout() {

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
			printf( _n( '1 Layout', '%s Layouts', count($posts) ), number_format_i18n( count($posts) ) );
			echo '[(=>)]';

			// Display update message
			echo '<div id="setting-error-delete_layout" class="updated fade settings-error ajax-update">';
			echo '	<p><strong>'.esc_html__( 'Layout(s) deleted.', 'theme-blvd-layout-builder' ).'</strong></p>';
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

		// Layout ID
		$layout_id = $_POST['data'];

		// Verify layout data
		$data = new Theme_Blvd_Layout_Builder_Data( $layout_id );
		$data->verify('elements');
		$data->verify('info');
		$data->finalize();

		// Send back layout to edit
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

	/**
	 * Duplicate element
	 *
	 * @since 2.0.0
	 */
	public function dup_element() {

		// Handle form data
		parse_str( $_POST['data'], $data );

		// New element ID
		$new_element_id = uniqid( 'element_'.rand() );

		// There will only be on element in the array,
		// so this loop will have only a single pass.
		foreach ( $data['tb_builder_elements'] as $section_id => $section ) {
			foreach ( $section as $element ) {

				$element_type = $element['type'];

				$element_label = wp_kses( $element['label'], array() );

				$element_settings = null;
				if ( isset( $element['options'] ) ) {
					$element_settings = $this->admin_page->clean( $element_type, $element['options'] );;
				}

				$element_display = null;
				if ( isset( $element['display'] ) ) {
					$element_display = $this->admin_page->clean( $element_type, $element['display'], true );
				}

				$columns = null;
				if ( isset( $element['columns'] ) ) {
					$columns = $element['columns'];
				}

			}
		}

		// Column data
		$column_data = array();

		if ( $columns ) {
			foreach ( $columns as $column_id => $column ) {

				$blocks = array();

				if ( isset( $column['elements']) ) {
					$blocks = $column['elements'];
				}

				if ( count($blocks) > 0 ) {
					foreach ( $blocks as $block_id => $block ) {

						// Sanitize type of content element
						$current_type = wp_kses( $block['type'], array() );

						// Sanitize options for element
						if ( ! empty( $block['options'] ) ) {
							$blocks[$block_id]['options'] = $this->admin_page->clean( $current_type, $block['options'] );
						} else {
							$blocks[$block_id]['options'] = array();
						}

						// Sanitize display options for elements
						if ( ! empty( $block['display'] ) ) {
							$blocks[$block_id]['display'] = $this->admin_page->clean( $current_type, $block['display'], true );
						} else {
							$blocks[$block_id]['display'] = array();
						}

						// Move current block to new key, representing a new unique ID
						$new_block_id = uniqid( 'block_'.rand() );
						$blocks[$new_block_id] = $blocks[$block_id];
						unset($blocks[$block_id]);

					}
				}

				$column_data[$column_id] = array(
					'display'	=> $this->admin_page->clean( 'column', $column['display'] ),
					'elements' 	=> $blocks
				);
			}
		}

		echo $new_element_id.'[(=>)]';
		$this->admin_page->edit_element( 0, $section_id, $element_type, $new_element_id, $element_settings, $element_display, $element_label, $column_data );
		die();
	}

	/**
	 * Duplicate content block
	 *
	 * @since 2.0.0
	 */
	public function dup_block() {

		// Handle form data
		parse_str( $_POST['data'], $data );

		// New block ID
		$block_id = uniqid( 'block_'.rand() );

		// Start the crazy traverse to collect all of the variables
		// we need to pass into edit_block(). Note that each foreach
		// is going to make one pass.
		foreach ( $data['tb_builder_elements'] as $section_id => $elements ) {
			foreach ( $elements as $element_id => $element ) {
				foreach ( $element as $columns ) {
					foreach ( $columns as $col_num => $column ) {

						// Column Number
						$col_num = str_replace('col_', '', $col_num);

						// Options, which need to go through santiziation
						foreach ( $column['elements'] as $block ) {

							// Block type
							$block_type = wp_kses( $block['type'], array() );

							// Sanitize options for element
							if ( ! empty( $block['options'] ) ) {
								$block_settings = $this->admin_page->clean( $block_type, $block['options'] );
							} else {
								$block_settings = array();
							}

							// Sanitize display options for elements
							if ( ! empty( $block['display'] ) ) {
								$block_display = $this->admin_page->clean( $block_type, $block['display'], true );
							} else {
								$block_display = array();
							}

						}
					}
				}
			}
		}

		echo $element_id.'[(=>)]';
		echo $block_id.'[(=>)]';
		echo $col_num.'[(=>)]';
		$this->admin_page->edit_block( $section_id, $element_id, $block_type, $block_id, $col_num, $block_settings, $block_display );
		die();
	}

}
