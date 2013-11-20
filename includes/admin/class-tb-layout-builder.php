<?php
/**
 * Layout Builder
 */
class Theme_Blvd_Layout_Builder {

	public $id;
	public $args;
	public $elements;
	public $ajax;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $id string Unique ID for admin page
	 * @param $args array Arguments for admin page
	 * @param $elements array Elements for layout builder
	 */
	public function __construct( $id = 'themeblvd_builder', $args = array(), $elements = array() ) {

		// ID for admin page
		$this->id = $id;

		// Setup arguments for admin page
		$defaults = array(
			'page_title' 	=> __( 'Layout Builder', 'themeblvd_builder' ),
			'menu_title' 	=> __( 'Builder', 'themeblvd_builder' ),
			'icon'			=> 'div',
			'cap'			=> themeblvd_admin_module_cap( 'builder' ),
			'priority'		=> 30
		);
		$this->args = wp_parse_args( $args, $defaults );

		// Elements for builder
		$this->elements = $elements; // If elements passed in
		add_action( 'after_setup_theme', array( $this, 'set_elements' ), 1001 ); // After client API

		// Add Builder admin page
		add_action( 'admin_menu', array( $this, 'add_page' ) );

		// Add Custom Layouts meta box
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

		// Filter on javascript locals specifically for Builder onto
		// Theme Blvd framework locals.
		add_filter( 'themeblvd_locals_js', array( $this, 'add_js_locals' ) );

		// Add ajax functionality to slider admin page
		$this->ajax = new Theme_Blvd_Layout_Builder_Ajax( $this );

	}

	/**
	 * Setup elements for builder after client has had
	 * a chance to use Builder API to modify elements.
	 *
	 * @since 1.0.0
	 */
	public function set_elements() {
		if ( ! $this->elements ) {
			$api = Theme_Blvd_Builder_API::get_instance();
			$this->elements = $api->get_elements();
		}
	}

	/**
	 * Get elements with filter applied. This is the less
	 * optimal way to filter elements, but does give an
	 * oppertunity to filter elements at the latest possible
	 * stage.
	 *
	 * If you're trying to add element options that involve
	 * things that come later in the WP loading process,
	 * like using get_terms() for a registered taxonomy, this
	 * is your filter.
	 *
	 * @since 1.2.0
	 */
	public function get_elements() {
		return apply_filters( 'themeblvd_get_elements', $this->elements );
	}

	/**
	 * Add a menu page for Builder
	 *
	 * @since 1.0.0
	 */
	public function add_page() {

		// Create admin page
		$admin_page = add_object_page( $this->args['page_title'], $this->args['menu_title'], $this->args['cap'], $this->id, array( $this, 'admin_page' ), $this->args['icon'], $this->args['priority'] );

		// Add scripts and styles
		add_action( 'admin_print_styles-'.$admin_page, array( $this, 'load_styles' ) );
		add_action( 'admin_print_scripts-'.$admin_page, array( $this, 'load_scripts' ) );

	}

	/**
	 * Add a meta box for editing/adding layout.
	 *
	 * @since 1.1.0
	 */
	public function add_meta_box() {

		global $pagenow;
		global $typenow;

		$args = apply_filters( 'themeblvd_builder_meta_box', array(
			'id' 		=> 'tb_builder',
			'name'		=> __('Custom Layout', 'themeblvd_builder'),
			'callback'	=> array( $this, 'meta_box' ),
			'post_type'	=> array( 'page' ),
			'context'	=> 'normal',
			'priority'	=> 'default'
		));

		if ( $args['post_type'] ) { // In theory, if you were trying to prevent the metabox or any of its elements from being added, you'd filter $args['post_type'] to null.

			// Include assets
			foreach ( $args['post_type'] as $post_type ) {

				// Include assets
				if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' && $typenow == $post_type ) {

					add_action( 'admin_enqueue_scripts', array( $this, 'load_styles' ) );
					add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

					// Prior to WP 3.5 or Theme Blvd framework v2.3
					if ( ! function_exists( 'wp_enqueue_media' ) || ! function_exists( 'themeblvd_media_uploader' ) ) {
						add_action( 'admin_enqueue_scripts', 'optionsframework_mlu_css', 0 );
						add_action( 'admin_enqueue_scripts', 'optionsframework_mlu_js', 0 );
					}

				}

				// Add meta box
				add_meta_box( $args['id'], $args['name'], $args['callback'], $post_type, $args['context'], $args['priority'] );

			}
		}
	}

	/**
	 * Save metabox for editing layouts from Edit Page screen.
	 *
	 * @since 1.1.0
	 */
	public function save_meta_box() {

		// Verify that this coming from the edit post page.
		if ( ! isset( $_POST['action'] ) || $_POST['action'] != 'editpost' ) {
			return;
		}

		// Verfiy nonce
		if ( ! isset( $_POST['_tb_save_builder_nonce'] ) || ! wp_verify_nonce( $_POST['_tb_save_builder_nonce'], 'themeblvd_save_builder' ) ) {
			return;
		}

		// Verify this is not an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Use our ajax function, which will allow us
		// to save the custom layout manually.
		$this->ajax->save_layout();

	}

	/**
	 * Loads the CSS
	 *
	 * @since 1.0.0
	 */
	public function load_styles() {
		wp_enqueue_style( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'color-picker', TB_FRAMEWORK_URI . '/admin/options/css/colorpicker.min.css' );
		wp_enqueue_style( 'themeblvd_builder', TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/css/builder-style.min.css', null, TB_BUILDER_PLUGIN_VERSION );
	}

	/**
	 * Loads the javascript
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {

		global $pagenow;

		// WP-packaged scripts
		wp_enqueue_script( 'jquery-ui-core');
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'postbox' );

		if ( function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		// Theme Blvd scripts
		wp_enqueue_script( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/js/shared.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/js/options.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'color-picker', TB_FRAMEWORK_URI . '/admin/options/js/colorpicker.min.js', array('jquery') );
		// @TODO change back to min
		wp_enqueue_script( 'themeblvd_builder', TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/js/builder.js', array('jquery'), TB_BUILDER_PLUGIN_VERSION );

		// Add JS locals when needed.
		if ( $pagenow == 'post-new.php' || $pagenow == 'post.php' ) {

			// Edit Page Screen: This is a fallback for prior to
			// framework v2.3 where framework metabox scripts were
			// not localized by default.
			if ( version_compare( TB_FRAMEWORK_VERSION, '2.3.0', '<' ) ) {
				wp_localize_script( 'tb_meta_box-scripts', 'themeblvd', themeblvd_get_admin_locals( 'js' ) ); // @see add_js_locals()
			}

		} else {

			// Localize script for actual Builder page.
			wp_localize_script( 'themeblvd_builder', 'themeblvd', themeblvd_get_admin_locals( 'js' ) ); // @see add_js_locals()

		}

	}

	/**
	 * Add javascript locals for Builder onto framework js
	 * locals that are already established.
	 *
	 * @since 1.1.1
	 */
	public function add_js_locals( $current ) {
		$new = array(
			'edit_layout'			=> __( 'Edit Layout', 'themeblvd_builder' ),
			'delete_layout'			=> __( 'Are you sure you want to delete the layout(s)?', 'themeblvd_builder' ),
			'layout_created'		=> __( 'Layout created!', 'themeblvd_builder' ),
			'save_switch_layout'	=> __( 'Would you like to save the current layout before switching?', 'themeblvd_builder' )
		);
		return array_merge($current, $new);
	}

	/**
	 * Builds out the full admin page.
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {
		?>
		<div id="builder_blvd">
			<div id="optionsframework" class="wrap tb-options-js">

				<div class="admin-module-header">
			    	<?php do_action( 'themeblvd_admin_module_header', 'builder' ); ?>
			    </div>
			    <?php screen_icon( 'tb_builder' ); ?>
			    <h2 class="nav-tab-wrapper">
			        <a href="#manage_layouts" id="manage_layouts-tab" class="nav-tab" title="<?php _e( 'Manage Layouts', 'themeblvd_builder' ); ?>"><?php _e( 'Manage Layouts', 'themeblvd_builder' ); ?></a>
			        <a href="#add_layout" id="add_layout-tab" class="nav-tab" title="<?php _e( 'Add New Layout', 'themeblvd_builder' ); ?>"><?php _e( 'Add New Layout', 'themeblvd_builder' ); ?></a>
			        <a href="#edit_layout" id="edit_layout-tab" class="nav-tab nav-edit-builder" title="<?php _e( 'Edit Layout', 'themeblvd_builder' ); ?>"><?php _e( 'Edit Layout', 'themeblvd_builder' ); ?></a>
			    </h2>

		    	<!-- MANAGE LAYOUT (start) -->

		    	<div id="manage_layouts" class="group">
			    	<form id="manage_builder">
			    		<?php
			    		$manage_nonce = wp_create_nonce( 'themeblvd_manage_builder' );
						echo '<input type="hidden" name="option_page" value="themeblvd_manage_builder" />';
						echo '<input type="hidden" name="_tb_manage_builder_nonce" value="'.$manage_nonce.'" />';
						?>
						<div class="ajax-mitt"><?php $this->manage_layouts(); ?></div>
					</form><!-- #manage_builder (end) -->
				</div><!-- #manage (end) -->

				<!-- MANAGE LAYOUT (end) -->

				<!-- ADD LAYOUT (start) -->

				<div id="add_layout" class="group">
					<form id="add_new_builder">
						<?php
						$add_nonce = wp_create_nonce( 'themeblvd_new_builder' );
						echo '<input type="hidden" name="option_page" value="themeblvd_new_builder" />';
						echo '<input type="hidden" name="_tb_new_builder_nonce" value="'.$add_nonce.'" />';
						$this->add_layout( null );
						?>
					</form><!-- #add_new_builder (end) -->
				</div><!-- #manage (end) -->

				<!-- ADD LAYOUT (end) -->

				<!-- EDIT LAYOUT (start) -->

				<div id="edit_layout" class="group">
					<form id="edit_builder" method="post">
						<?php
						$edit_nonce = wp_create_nonce( 'themeblvd_save_builder' );
						echo '<input type="hidden" name="action" value="update" />';
						echo '<input type="hidden" name="option_page" value="themeblvd_save_builder" />';
						echo '<input type="hidden" name="_tb_save_builder_nonce" value="'.$edit_nonce.'" />';
						?>
						<div class="ajax-mitt"><!-- AJAX inserts edit builder page here. --></div>
					</form>
				</div><!-- #manage (end) -->

				<!-- EDIT LAYOUT (end) -->

				<div class="admin-module-footer">
			    	<?php do_action( 'themeblvd_admin_module_footer', 'builder' ); ?>
			    </div>
			</div><!-- #optionsframework (end) -->
		</div><!-- #builder_blvd (end) -->
		<?php
	}

	/**
	 * Builds out the meta box to edit a page's custom layout.
	 *
	 * @since 1.1.0
	 */
	public function meta_box() {
		global $post;
		$current_layout = get_post_meta( $post->ID, '_tb_custom_layout', true );
		?>
		<div id="builder_blvd">
			<div id="optionsframework" class="tb-options-js">

				<!-- HEADER (start) -->

				<div class="meta-box-nav">
					<div class="select-layout">
						<div class="ajax-overlay"></div>
						<div class="icon-holder">
							<span class="tb-loader ajax-loading"></span>
							<i class="tb-icon-commercial-building"></i>
						</div>
						<?php echo $this->layout_select( $current_layout ); ?>
						<span class="note"><?php _e('Select a custom layout.', 'themeblvd_builder'); ?></span>
					</div>
					<ul>
						<li><a href="#edit_layout"><?php _e('Edit Layout', 'themeblvd_builder'); ?></a></li>
						<li><a href="#add_layout"><?php _e('Add New', 'themeblvd_builder'); ?></a></li>
					</ul>
					<div class="clear"></div>
				</div><!-- .meta-box-nav (end) -->

				<!-- HEADER (end) -->

				<!-- EDIT LAYOUT (start) -->

				<div id="edit_layout" class="group">
					<?php
					$edit_nonce = wp_create_nonce( 'themeblvd_save_builder' );
					echo '<input type="hidden" name="_tb_save_builder_nonce" value="'.$edit_nonce.'" />';
					?>
					<div class="ajax-mitt">
						<?php $this->mini_edit_layout( themeblvd_post_id_by_name( $current_layout, 'tb_layout' ) ); ?>
					</div><!-- .ajax-mitt (end) -->
				</div><!-- #edit_layout (end) -->

				<!-- EDIT LAYOUT (end) -->

				<!-- ADD LAYOUT (start) -->

				<div id="add_layout" class="group">
					<?php
					$add_nonce = wp_create_nonce( 'themeblvd_new_builder' );
					echo '<input type="hidden" name="_tb_new_builder_nonce" value="'.$add_nonce.'" />';
					$this->add_layout( null );
					?>
				</div><!-- #manage (end) -->

				<!-- ADD LAYOUT (end) -->

			</div><!-- #optionsframework (end) -->
		</div><!-- #builder_blvd (end) -->
		<?php
	}

	/**
	 * Generates the the interface to manage layouts.
	 *
	 * @since 1.0.0
	 */
	public function manage_layouts() {

		// Setup columns for management table
		$columns = array(
			array(
				'name' 		=> __( 'Layout Title', 'themeblvd_builder' ),
				'type' 		=> 'title',
			),
			array(
				'name' 		=> __( 'Layout ID', 'themeblvd_builder' ),
				'type' 		=> 'slug',
			)
			/* Hiding the true post ID from user to avoid confusion.
			array(
				'name' 		=> __( 'Layout ID', 'themeblvd_builder' ),
				'type' 		=> 'id',
			)
			*/
		);
		$columns = apply_filters( 'themeblvd_manage_layouts', $columns );

		// Display it all
		echo '<div class="metabox-holder">';
		echo themeblvd_post_table( 'tb_layout', $columns );
		echo '</div><!-- .metabox-holder (end) -->';
	}

	/**
	 * Generates the the interface to add a new layout.
	 *
	 * @since 1.0.0
	 */
	public function add_layout() {

		// Setup sidebar layouts
		$layouts = themeblvd_sidebar_layouts();
		$sidebar_layouts = array( 'default' => __( 'Default Sidebar Layout', 'themeblvd_builder' ) );
		foreach ( $layouts as $layout ) {
			$sidebar_layouts[$layout['id']] = $layout['name'];
		}

		// Setup sample layouts
		$samples = themeblvd_get_sample_layouts();
		$sample_layouts = array();
		if ( $samples ) {
			foreach ( $samples as $sample ) {
				$sample_layouts[$sample['id']] = $sample['name'];
			}
		}

		// Setup existing layouts
		$layouts = get_posts('post_type=tb_layout&numberposts=-1');
		$custom_layouts = array();
		if ( $layouts ) {
			foreach ( $layouts as $layout ) {
				$custom_layouts[$layout->ID] = $layout->post_title;
			}
		}

		// Setup options array to display form
		$options = array();

		// Layout Name
		$options[] = array(
			'name' 		=> __( 'Layout Name', 'themeblvd_builder' ),
			'desc' 		=> __( 'Enter a user-friendly name for your layout. You will not be able to change this after you\'ve created the layout.<br><br><em>Example: My Layout</em>', 'themeblvd_builder' ),
			'id' 		=> 'layout_name',
			'type' 		=> 'text'
		);

		// Start subgroup for starting point
		$options[] = array(
			'type'		=> 'subgroup_start'
		);

		// Starting point
		$options[] = array(
			'name' 		=> __( 'Starting Point', 'themeblvd_builder' ),
			'desc' 		=> __( 'Select if you\'d like to start building your layout from scratch, from a pre-existing layout, or from a sample layout.', 'themeblvd_builder' ),
			'id' 		=> 'layout_start',
			'type' 		=> 'select',
			'options' 	=> array(
				'scratch'	=> __( 'Start From Scratch', 'themeblvd_builder' ),
				'layout'	=> __( 'Start From Existing Layout', 'themeblvd_builder' ),
				'sample'	=> __( 'Start From Sample Layout', 'themeblvd_builder' )
			)
		);
		if ( ! $sample_layouts ) {
			unset( $options[2]['options']['sample'] );
		}
		if ( ! $custom_layouts ) {
			unset( $options[2]['options']['layout'] );
		}

		// Existing Layout
		if ( $custom_layouts ) {
			$options[] = array(
				'name' 		=> __( 'Custom Layouts', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select one of the layouts you created previously to start this new one.', 'themeblvd_builder' ),
				'id' 		=> 'layout_existing',
				'type' 		=> 'select',
				'options' 	=> $custom_layouts,
			);
		}

		// Sample Layouts (only show if there are sample layouts)
		if ( $sample_layouts ) {
			$options[] = array(
				'name' 		=> __( 'Sample Layout', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select a sample layout to start from.', 'themeblvd_builder' ),
				'id' 		=> 'layout_sample',
				'type' 		=> 'select',
				'options' 	=> $sample_layouts,
				'class'		=> 'builder_samples'
			);
		}

		// End subgroup for starting point
		$options[] = array(
			'type'		=> 'subgroup_end'
		);

		// Sidebar Layout
		$options[] = array(
			'name' 		=> __( 'Sidebar Layout', 'themeblvd_builder' ),
			'desc' 		=> __( 'Select your sidebar layout for this page.<br><br><em>Note: You can change this later when editing your layout.</em>', 'themeblvd_builder' ),
			'id' 		=> 'layout_sidebar',
			'type' 		=> 'select',
			'options' 	=> $sidebar_layouts
		);

		$options = apply_filters( 'themeblvd_add_layout', $options );

		// Build form
		$form = themeblvd_option_fields( 'tb_new_layout', $options, null, false );
		?>
		<div class="metabox-holder">
			<div class="postbox">
				<h3><?php _e( 'Add New Layout', 'themeblvd_builder' ); ?></h3>
				<div class="inner-group">
					<?php echo $form[0]; ?>
				</div><!-- .group (end) -->
				<div id="optionsframework-submit">
					<input type="submit" class="button-primary" name="update" value="<?php _e( 'Add New Layout', 'themeblvd_builder' ); ?>">
					<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
		            <div class="clear"></div>
				</div>
			</div><!-- .postbox (end) -->
		</div><!-- .metabox-holder (end) -->
		<?php
	}

	/**
	 * Generates the an indivdual panel to edit an element.
	 * This has been broken into a separate function because
	 * not only does it show each element when loading the
	 * Edit Layout screen, but it's used to insert a new
	 * element when called upon with AJAX.
	 *
	 * @since 1.0.0
	 *
	 * @param string $element_type type of element
	 * @param string $element_id ID for individual slide
	 * @param array $element_settings any current options for current element
	 */
	public function edit_element( $element_type, $element_id, $element_settings = null, $visibility = null ) {
		$elements = $this->get_elements();
		$form = themeblvd_option_fields( 'tb_elements['.$element_id.'][options]', $elements[$element_type]['options'], $element_settings, false );
		?>
		<div id="<?php echo $element_id; ?>" class="widget element-options"<?php if ( $visibility == 'hide' ) echo ' style="display:none"'; ?>>
			<div class="widget-name">
				<a href="#" class="widget-name-arrow">Toggle</a>
				<h3><?php echo $elements[$element_type]['info']['name']; ?></h3>
				<div class="clear"></div>
			</div><!-- .element-name (end) -->
			<div class="widget-content">
				<input type="hidden" class="element-type" name="tb_elements[<?php echo $element_id; ?>][type]" value="<?php echo $element_type; ?>" />
				<input type="hidden" class="element-query" name="tb_elements[<?php echo $element_id; ?>][query_type]" value="<?php echo $elements[$element_type]['info']['query']; ?>" />
				<?php echo $form[0]; ?>
				<div class="submitbox widget-footer">
					<a href="#<?php echo $element_id; ?>" class="submitdelete delete-me" title="<?php _e( 'Are you sure you want to delete this element?', 'themeblvd_builder' ); ?>"><?php _e( 'Delete Element', 'themeblvd_builder' ); ?></a>
					<div class="clear"></div>
				</div><!-- .widget-footer (end) -->
			</div><!-- .element-content (end) -->
		</div><!-- .element-options(end) -->
		<?php
	}

	/**
	 * Generates the the interface to edit the layout.
	 *
	 * @since 1.0.0
	 *
	 * @param $id string ID of layout to edit
	 */
	public function edit_layout( $id ) {
		$api = Theme_Blvd_Builder_API::get_instance();
		$elements = $this->get_elements(); // Elements that can be used in Builder, and NOT elements saved to current layout
		$layout = get_post($id);
		$layout_elements = get_post_meta( $id, 'elements', true );
		$layout_settings = get_post_meta( $id, 'settings', true );
		?>
		<input type="hidden" name="tb_layout_id" value="<?php echo $id; ?>" />
		<div id="poststuff" class="metabox-holder full-width has-right-sidebar">
			<div class="inner-sidebar">
				<div id="layout-publish" class="postbox postbox-publish">
					<h3 class="hndle"><?php _e( 'Publish', 'themeblvd_builder' ); ?> <?php echo stripslashes($layout->post_title); ?></h3>
					<div class="submitbox">
						<div id="major-publishing-actions">
							<div id="delete-action">
								<a class="submitdelete delete_layout" href="#<?php echo $id; ?>"><?php _e( 'Delete', 'themeblvd_builder' ); ?></a>
							</div>
							<div id="publishing-action">
								<input class="button-primary" value="<?php _e( 'Update Layout', 'themeblvd_builder' ); ?>" type="submit" />
								<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" />
							</div>
							<div class="clear"></div>
						</div>
					</div><!-- .submitbox (end) -->
				</div><!-- .post-box (end) -->
				<div id="layout-info" class="postbox postbox-layout-info closed">
					<div class="handlediv" title="<?php echo __('Click to toggle', 'themeblvd_builder'); ?>"><br></div>
					<h3 class="hndle"><?php _e('Layout Information', 'themeblvd_builder' ); ?></h3>
					<div class="tb-widget-content hide">
						<?php
						// Current settings
						$info_settings = array(
							'post_title' 	=> $layout->post_title,
							'post_name'		=> $layout->post_name
						);

						// Setup attribute options
						$info_options = array(
							array(
								'name'		=> __('Layout Name', 'themeblvd_builder' ),
								'id' 		=> 'post_title',
								'desc'		=> __('This title is just for you. It\'ll never be used outside of your WordPress admin panel.', 'themeblvd_builder'),
								'type' 		=> 'text'
							),
							array(
								'name' 		=> __('Layout ID', 'themeblvd_builder' ),
								'id' 		=> 'post_name',
								'desc'		=> __( 'Custom layouts are assigned based on this ID. So if you change this at any point, make sure to also update any pages or options in which you\'ve assigned this specific layout.', 'themeblvd_builder' ),
								'type' 		=> 'text'
							)
						);

						// Display form element
						$form = themeblvd_option_fields( 'tb_layout_info', $info_options, $info_settings, false );
						echo $form[0];
						?>
					</div><!-- .tb-widget-content (end) -->
				</div><!-- .post-box (end) -->
				<div id="layout-options" class="postbox postbox-sidebar-layout closed">
					<div class="handlediv" title="<?php echo __('Click to toggle', 'themeblvd_builder'); ?>"><br></div>
					<h3 class="hndle"><?php _e('Sidebar Layout', 'themeblvd_builder' ); ?></h3>
					<div class="tb-widget-content hide">
						<?php
						// Setup sidebar layouts
						$layouts = themeblvd_sidebar_layouts();
						$sidebar_layouts = array( 'default' => __( 'Default Sidebar Layout', 'themeblvd_builder' ) );

						foreach ( $layouts as $layout )
							$sidebar_layouts[$layout['id']] = $layout['name'];

						$options = array(
							array(
								'id' 		=> 'sidebar_layout',
								'desc'		=> __( 'Select how you\'d like the sidebar(s) arranged in this layout. Your site-wide default sidebar layout can be set from your Theme Options page.<br><br><strong>Note: The sidebar layout is only applied to the "Primary Area" of the custom layout.</strong>', 'themeblvd_builder' ),
								'type' 		=> 'select',
								'options' 	=> $sidebar_layouts
							)
						);

						// Display form element
						$form = themeblvd_option_fields( 'tb_layout_options', $options, $layout_settings, false );
						echo $form[0];
						?>
					</div><!-- .tb-widget-content (end) -->
				</div><!-- .post-box (end) -->
			</div><!-- .inner-sidebar (end) -->
			<div id="post-body">
				<div id="post-body-content">
					<div id="titlediv">
						<div class="ajax-overlay"></div>
						<h2><?php _e( 'Manage Elements', 'themeblvd_builder' ); ?></h2>
						<select>
						<?php
						foreach ( $elements as $element ) {
							if ( $api->is_element( $element['info']['id'] ) ) {
								echo '<option value="'.$element['info']['id'].'=>'.$element['info']['query'].'">'.$element['info']['name'].'</option>';
							}
						}
						?>
						</select>
						<a href="#" id="add_new_element" class="button-secondary"><?php _e( 'Add New Element', 'themeblvd_builder' ); ?></a>
						<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
						<div class="clear"></div>
					</div><!-- #titlediv (end) -->
					<div id="builder">
						<div id="featured">
							<span class="label"><?php _e( 'Featured Above', 'themeblvd_builder' ); ?></span>
							<div class="sortable">
								<?php
								if ( ! empty( $layout_elements ) && ! empty( $layout_elements['featured'] ) ) {
									foreach ( $layout_elements['featured'] as $id => $element ) {
										if ( $api->is_element( $element['type'] ) ) {
											$this->edit_element( $element['type'], $id, $element['options'] );
										}
									}
								}
								?>
							</div><!-- .sortable (end) -->
						</div><!-- #featured (end) -->
						<div id="primary">
							<input type="hidden" name="tb_elements[divider]" value="" />
							<span class="label"><?php _e( 'Primary Area', 'themeblvd_builder' ); ?></span>
							<div class="sortable">
								<?php
								if ( ! empty( $layout_elements ) && ! empty( $layout_elements['primary'] ) ) {
									foreach ( $layout_elements['primary'] as $id => $element ) {
										if ( $api->is_element( $element['type'] ) ) {
											$this->edit_element( $element['type'], $id, $element['options'] );
										}
									}
								}
								?>
							</div><!-- .sortable (end) -->
						</div><!-- #primary (end) -->
						<div id="featured_below">
							<input type="hidden" name="tb_elements[divider_2]" value="" />
							<span class="label"><?php _e( 'Featured Below', 'themeblvd_builder' ); ?></span>
							<div class="sortable">
								<?php
								if ( ! empty( $layout_elements ) && ! empty( $layout_elements['featured_below'] ) ) {
									foreach ( $layout_elements['featured_below'] as $id => $element ) {
										if ( $api->is_element( $element['type'] ) ) {
											$this->edit_element( $element['type'], $id, $element['options'] );
										}
									}
								}
								?>
							</div><!-- .sortable (end) -->
						</div><!-- #primary (end) -->
					</div><!-- #builder (end) -->
				</div><!-- .post-body-content (end) -->
			</div><!-- #post-body (end) -->
		</div><!-- #poststuff (end) -->
		<?php
	}

	/**
	 * Generates the the interface to edit the layout
	 * when in the metabox interface of editing Pages.
	 *
	 * @since 1.1.0
	 *
	 * @param $id string ID of layout to edit
	 */
	public function mini_edit_layout( $id ) {

		$api = Theme_Blvd_Builder_API::get_instance();

		// If no layout (i.e. User selected "none" or one hasn't been chosen yet)
		if ( ! $id ) {
			echo '<p class="warning">'.__('Select a layout to apply and edit it, or create a new one.', 'themeblvd_builder').'</p>';
			return;
		}

		// Get custom layout post
		$elements = $this->get_elements();
		$layout = get_post($id);

		// Check if valid layout
		if ( ! $layout ) {
			echo '<p class="warning">'.__('The layout currently selected no longer exists. Select a different layout to edit, or create a new one.', 'themeblvd_builder').'</p>';
			return;
		}

		// Grab elements and settings for the layout we're editing
		$layout_elements = get_post_meta( $id, 'elements', true );
		$layout_settings = get_post_meta( $id, 'settings', true );
		?>
		<input type="hidden" name="tb_layout_id" value="<?php echo $id; ?>" />
		<h3><?php _e('Edit Layout', 'themeblvd_builder'); ?>: <?php echo $layout->post_title; ?></h3>
		<div id="metabox-builder">
			<div class="edit-layout-wrap">
				<div id="titlediv">
					<div class="ajax-overlay"></div>
					<h2><?php _e( 'Manage Elements', 'themeblvd_builder' ); ?></h2>
					<select>
						<?php
						foreach ( $elements as $element )
							echo '<option value="'.$element['info']['id'].'=>'.$element['info']['query'].'">'.$element['info']['name'].'</option>';
						?>
					</select>
					<a href="#" id="add_new_element" class="button-secondary"><?php _e( 'Add New Element', 'themeblvd_builder' ); ?></a>
					<span class="tb-loader ajax-loading"></span>
					<div class="clear"></div>
				</div><!-- #titlediv (end) -->
				<div id="builder">
					<div id="featured">
						<span class="label"><?php _e( 'Featured Above', 'themeblvd_builder' ); ?></span>
						<div class="sortable">
							<?php
							if ( ! empty( $layout_elements ) && ! empty( $layout_elements['featured'] ) ) {
								foreach ( $layout_elements['featured'] as $id => $element ) {
									if ( $api->is_element( $element['type'] ) ) {
										$this->edit_element( $element['type'], $id, $element['options'] );
									}
								}
							}
							?>
						</div><!-- .sortable (end) -->
					</div><!-- #featured (end) -->
					<div id="primary">
						<input type="hidden" name="tb_elements[divider]" value="" />
						<span class="label"><?php _e( 'Primary Area', 'themeblvd_builder' ); ?></span>
						<div class="sortable">
							<?php
							if ( ! empty( $layout_elements ) && ! empty( $layout_elements['primary'] ) ) {
								foreach ( $layout_elements['primary'] as $id => $element ) {
									if ( $api->is_element( $element['type'] ) ) {
										$this->edit_element( $element['type'], $id, $element['options'] );
									}
								}
							}
							?>
						</div><!-- .sortable (end) -->
					</div><!-- #primary (end) -->
					<div id="featured_below">
						<input type="hidden" name="tb_elements[divider_2]" value="" />
						<span class="label"><?php _e( 'Featured Below', 'themeblvd_builder' ); ?></span>
						<div class="sortable">
							<?php
							if ( ! empty( $layout_elements ) && ! empty( $layout_elements['featured_below'] ) ) {
								foreach ( $layout_elements['featured_below'] as $id => $element ) {
									if ( $api->is_element( $element['type'] ) ) {
										$this->edit_element( $element['type'], $id, $element['options'] );
									}
								}
							}
							?>
						</div><!-- .sortable (end) -->
					</div><!-- #primary (end) -->
				</div><!-- #builder (end) -->
			</div><!-- .edit-layout-wrap (end) -->

			<div class="sidebar-layout-wrap">
				<div class="title">
					<h2><?php _e( 'Sidebar Layout', 'themeblvd_builder' ); ?></h2>
					<div class="clear"></div>
				</div><!-- #titlediv (end) -->
				<div class="sidebar-layout">
					<?php
					// Setup sidebar layouts
					$imagepath =  get_template_directory_uri() . '/framework/admin/assets/images/';
					$sidebar_layouts = array('default' => $imagepath.'layout-default.png');
					$layouts = themeblvd_sidebar_layouts();
					foreach ( $layouts as $layout )
						$sidebar_layouts[$layout['id']] = $imagepath.'layout-'.$layout['id'].'.png';

					// Now convert it to options form
					$options = array(
						array(
							'id' 		=> 'sidebar_layout',
							'desc'		=> __( 'Select how you\'d like the sidebar(s) arranged in this layout. Your site-wide default sidebar layout can be set from your Theme Options page.<br><br><strong>Note: The sidebar layout is only applied to the "Primary Area" of the custom layout.</strong>', 'themeblvd_builder' ),
							'type' 		=> 'images',
							'options' 	=> $sidebar_layouts
						)
					);

					// Display form element
					$form = themeblvd_option_fields( 'tb_layout_options', $options, $layout_settings, false );
					echo $form[0];
					?>
				</div>
			</div><!-- .sidebar-layout-wrap (end) -->

			<div class="custom-layout-note">
				<p><?php _e('Note: For this custom layout to be applied to the current page, you must select the "Custom Layout" page template from your Page Attributes.', 'themeblvd_builder'); ?></p>
			</div>

		</div><!-- #metabox-builder (end) -->
		<?php
	}

	/**
	 * Builds a select menu of current custom layouts.
	 *
	 * @since 1.1.0
	 *
	 * @param string $current Current custom layout to be selected
	 */
	public function layout_select( $current = '' ) {

		$output = '';

		$args = array(
			'post_type'		=> 'tb_layout',
			'order'			=> 'ASC',
			'orderby'		=> 'title',
			'numberposts'	=> -1
		);
		$custom_layouts = get_posts($args);

		$output .= '<div class="tb-fancy-select">';
		$output .= '<select id="tb-layout-toggle" name="_tb_custom_layout">';
		$output .= '<option value="">'.__('- None -', 'themeblvd_builder').'</option>';

		if ( $custom_layouts ) {
			foreach ( $custom_layouts as $custom_layout ) {
				$output .= '<option value="'.$custom_layout->post_name.'" '.selected( $custom_layout->post_name, $current, false ).'>'.$custom_layout->post_title.'</option>';
			}
		}

		$output .= '</select>';
		$output .= '<span class="trigger"></span>';
		$output .= '<span class="textbox"></span>';
		$output .= '</div><!-- .tb-fancy-select (end) -->';

		return $output;
	}

}