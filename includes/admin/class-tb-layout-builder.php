<?php
/**
 * Layout Builder
 */
class Theme_Blvd_Layout_Builder {

	public $id;
	public $args;
	public $elements;
	public $blocks;
	public $ajax;
	private $import_url;

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
		add_action( 'after_setup_theme', array( $this, 'set_blocks' ), 1001 ); // After client API

		// Add Builder admin page
		add_action( 'admin_menu', array( $this, 'add_page' ) );

		// Manage Custom Layouts via Edit Page screen
		add_action( 'admin_init', array( $this, 'editor_builder_init' ) );

		// Filter on javascript locals specifically for Builder onto
		// Theme Blvd framework locals.
		add_filter( 'themeblvd_locals_js', array( $this, 'add_js_locals' ) );

		// Admin <body> classes
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );

		// Add Editor into page, which Builder can use for editing
		// content of elements.
		add_action( 'current_screen', array( $this, 'add_editor' ) );

		// Add icon browser into page, which Builder can use for
		// inserting icons.
		add_action( 'current_screen', array( $this, 'add_icon_browser' ) );

		// Add texture browser into page, which Builder can use for
		// selecting textures.
		add_action( 'current_screen', array( $this, 'add_texture_browser' ) );

		// Add ajax functionality in Builder
		$this->ajax = new Theme_Blvd_Layout_Builder_Ajax( $this );

		// Make advanced option types available in Builder
		if ( class_exists( 'Theme_Blvd_Advanced_Options' ) ) {
			$advanced = Theme_Blvd_Advanced_Options::get_instance();
			$advanced->create('datasets');
			$advanced->create('locations');
			$advanced->create('sectors');
			$advanced->create('slider');
			$advanced->create('social_media');
			$advanced->create('tabs');
			$advanced->create('testimonials');
			$advanced->create('toggles');
		}

		// Allow for importing
		$args = array(
			'redirect' => admin_url('admin.php?page='.$this->id) // Builder page URL
		);
		$import = new Theme_Blvd_Import_Layout( $this->id, $args );
		$this->importer_url = $import->get_url(); // URL of page where importer is

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
	 * Setup blocks for builder after client has had
	 * a chance to use Builder API to modify blocks.
	 *
	 * @since 1.0.0
	 */
	public function set_blocks() {
		if ( ! $this->blocks ) {
			$api = Theme_Blvd_Builder_API::get_instance();
			$this->blocks = $api->get_blocks();
		}
	}

	/**
	 * Get blocks with filter applied.
	 *
	 * @since 2.0.0
	 */
	public function get_blocks() {
		return apply_filters( 'themeblvd_get_blocks', $this->blocks );
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
	public function editor_builder_init() {

		global $pagenow;
		global $typenow;

		$post_types = apply_filters( 'themeblvd_editor_builder_post_types', array('page') );

		if ( $post_types ) {
			foreach ( $post_types as $post_type ) {
				if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' && $typenow == $post_type ) {

					add_action( 'save_post', array( $this, 'save_editor_builder' ) );
					add_filter( 'the_editor', array( $this, 'add_editor_tab' ) );
					add_filter( 'edit_form_after_title', array( $this, 'start_editor_builder' ) );
					add_filter( 'edit_form_after_editor', array( $this, 'add_editor_builder' ) );
					add_filter( 'edit_form_after_editor', array( $this, 'end_editor_builder' ) );

					add_action( 'admin_enqueue_scripts', array( $this, 'load_styles' ) );
					add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

					// Prior to WP 3.5 or Theme Blvd framework v2.3
					if ( ! function_exists( 'wp_enqueue_media' ) || ! function_exists( 'themeblvd_media_uploader' ) ) {
						add_action( 'admin_enqueue_scripts', 'optionsframework_mlu_css', 0 );
						add_action( 'admin_enqueue_scripts', 'optionsframework_mlu_js', 0 );
					}

				}
			}
		}
	}

	/**
	 * Save when editing layouts from Edit Page screen.
	 *
	 * @since 1.1.0
	 */
	public function save_editor_builder() {

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
	 * Add "Layout" tab next to Visual and Text
	 * tabs when editing pages.
	 *
	 * @since 2.0.0
	 */
	public function add_editor_tab( $editor ) {
		if ( strpos( $editor, 'id="wp-content-editor-container"' ) !== false ) {
			$tab = '<a href="#" id="content-layout" class="tb-switch-editor switch-layout">'.__('Builder', 'themeblvd_builder').'</a>';
			$editor = apply_filters( 'themeblvd_edit_layout_tab', $tab ).$editor;
		}
		return $editor;
	}

	/**
	 * Add opening DIV before entire WP editor
	 *
	 * @since 2.0.0
	 */
	public function start_editor_builder() {
		echo '<div id="tb-editor-builder">';
	}

	/**
	 * Add Builder after WP's editor.
	 *
	 * @since 2.0.0
	 */
	public function add_editor_builder( $editor ) {
		$this->editor_builder();
	}

	/**
	 * Add closing DIV around after WP editor
	 *
	 * @since 2.0.0
	 */
	public function end_editor_builder() {
		echo '</div><!-- #tb-editor-builder (end) -->';
	}

	/**
	 * Loads the CSS
	 *
	 * @since 1.0.0
	 */
	public function load_styles() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'color-picker', TB_FRAMEWORK_URI . '/admin/options/css/colorpicker.min.css' );
		wp_enqueue_style( 'themeblvd_builder', TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/css/builder-style.min.css', null, TB_BUILDER_PLUGIN_VERSION );

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
			wp_enqueue_style( 'codemirror', TB_FRAMEWORK_URI . '/admin/assets/plugins/codemirror/codemirror.min.css', null, '4.0' );
			wp_enqueue_style( 'codemirror-theme', TB_FRAMEWORK_URI . '/admin/assets/plugins/codemirror/themeblvd.min.css', null, '4.0' );
		}
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
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'wp-color-picker' );

		if ( function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		// Google Maps
		wp_enqueue_script( 'themeblvd_gmap', 'https://maps.googleapis.com/maps/api/js', array(), null );

		// Theme Blvd scripts
		if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
			wp_enqueue_script( 'themeblvd_modal', TB_FRAMEWORK_URI . '/admin/assets/js/modal.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		}
		wp_enqueue_script( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/js/shared.min.js', array('jquery'), TB_FRAMEWORK_VERSION );

		wp_enqueue_script( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/js/options.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'color-picker', TB_FRAMEWORK_URI . '/admin/options/js/colorpicker.min.js', array('jquery') );
		wp_enqueue_script( 'themeblvd_builder', TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/js/builder.js', array('jquery'), TB_BUILDER_PLUGIN_VERSION );

		// Code editor and FontAwesome
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
			wp_enqueue_script( 'codemirror', TB_FRAMEWORK_URI . '/admin/assets/plugins/codemirror/codemirror.min.js', null, '4.0' );
			wp_enqueue_script( 'codemirror-modes', TB_FRAMEWORK_URI . '/admin/assets/plugins/codemirror/modes.min.js', null, '4.0' );
			wp_enqueue_style( 'fontawesome', TB_FRAMEWORK_URI . '/assets/plugins/fontawesome/css/font-awesome.min.css', null, TB_FRAMEWORK_VERSION );
		}

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
			'delete_text'			=> __( 'Delete', 'themeblvd_builder' ),
			'delete_block'			=> __( 'Are you sure you want to delete the content block?', 'themeblvd_builder' ),
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
		<div id="builder_blvd" class="primary">
			<div id="optionsframework" class="wrap tb-options-js">

				<div class="admin-module-header">
			    	<?php do_action( 'themeblvd_admin_module_header', 'builder' ); ?>
			    </div>
			    <?php screen_icon( 'tb_builder' ); ?>
			    <h2 class="nav-tab-wrapper">
			        <a href="#manage_layouts" id="manage_layouts-tab" class="nav-tab" title="<?php _e( 'Manage Layouts', 'themeblvd_builder' ); ?>"><?php _e( 'Manage Layouts', 'themeblvd_builder' ); ?></a>
			        <a href="#add_layout" id="add_layout-tab" class="nav-tab" title="<?php _e( 'Add New Layout', 'themeblvd_builder' ); ?>"><?php _e( 'Add New Layout', 'themeblvd_builder' ); ?></a>
			        <a href="#edit_layout" id="edit_layout-tab" class="nav-tab nav-edit-builder hide" title="<?php _e( 'Edit Layout', 'themeblvd_builder' ); ?>"><?php _e( 'Edit Layout', 'themeblvd_builder' ); ?></a>
			    </h2>

			    <?php do_action('themeblvd_builder_update'); ?>

		    	<!-- MANAGE LAYOUT (start) -->

		    	<div id="manage_layouts" class="group hide">
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

				<div id="add_layout" class="group hide">
					<form id="add_new_builder">
						<?php
						$add_nonce = wp_create_nonce( 'themeblvd_new_builder' );
						echo '<input type="hidden" name="option_page" value="themeblvd_new_builder" />';
						echo '<input type="hidden" name="_tb_new_builder_nonce" value="'.$add_nonce.'" />';
						$import = version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=') ? true : false;
						$this->add_layout( $import );
						?>
					</form><!-- #add_new_builder (end) -->
				</div><!-- #manage (end) -->

				<!-- ADD LAYOUT (end) -->

				<!-- EDIT LAYOUT (start) -->

				<div id="edit_layout" class="group hide">
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
	public function editor_builder() {
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
							<span class="tb-loader ajax-loading">
								<i class="tb-icon-spinner"></i>
							</span>
							<i class="tb-icon-commercial-building"></i>
						</div>
						<?php echo $this->layout_select( $current_layout ); ?>
						<span class="note"><?php _e('Select a custom layout.', 'themeblvd_builder'); ?></span>
					</div>

					<a href="#" class="button-secondary add-new-layout"><?php _e('New Layout', 'themeblvd_builder'); ?></a>
					<a href="#" class="button-primary save-new-layout"><?php _e( 'Create Layout', 'themeblvd_builder' ); ?></a>
					<a href="#" class="button-secondary cancel-new-layout"><?php _e('Cancel', 'themeblvd_builder'); ?></a>

					<div class="clear"></div>
				</div><!-- .meta-box-nav (end) -->

				<!-- HEADER (end) -->

				<!-- EDIT LAYOUT (start) -->

				<div id="edit_layout">
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

				<div id="add_layout">
					<?php
					$add_nonce = wp_create_nonce( 'themeblvd_new_builder' );
					echo '<input type="hidden" name="_tb_new_builder_nonce" value="'.$add_nonce.'" />';
					$this->add_layout();
					?>
				</div><!-- #manage (end) -->

				<!-- ADD LAYOUT (end) -->

			</div><!-- #optionsframework (end) -->

			<div class="custom-layout-note">
				<p><?php _e('Note: For your custom layout to be applied to the current page, you must select the "Custom Layout" page template from your Page Attributes. Also, remember that if you have the same layout applied to any other pages, your edits here will effect those other pages, as well. You can further manage your custom layouts from the <a href="admin.php?page=themeblvd_builder" target="_blank">Builder</a>.', 'themeblvd_builder'); ?></p>
			</div>

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
	public function add_layout( $import = false ) {

		// Setup sample layouts
		$samples = themeblvd_get_sample_layouts();
		$sample_layouts = array();
		if ( $samples ) {
			foreach ( $samples as $sample ) {
				$sample_layouts[$sample['id']] = $sample['name'];
			}
		}

		// Setup existing layouts
		$layouts = get_posts('post_type=tb_layout&order=ASC&orderby=title&numberposts=-1');
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
			'desc' 		=> __( 'Enter a user-friendly name for your layout.<br><em>Example: My Layout</em>', 'themeblvd_builder' ),
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
		if ( ! $sample_layouts || version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
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
		if ( $sample_layouts && version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) {

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
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) { // @deprecated

			// Setup sidebar layouts
			$layouts = themeblvd_sidebar_layouts();
			$sidebar_layouts = array( 'default' => __( 'Default Sidebar Layout', 'themeblvd_builder' ) );
			foreach ( $layouts as $layout ) {
				$sidebar_layouts[$layout['id']] = $layout['name'];
			}

			$options[] = array(
				'name' 		=> __( 'Sidebar Layout', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select your sidebar layout for this page.<br><br><em>Note: You can change this later when editing your layout.</em>', 'themeblvd_builder' ),
				'id' 		=> 'layout_sidebar',
				'type' 		=> 'select',
				'options' 	=> $sidebar_layouts
			);
		}

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
					<?php if ( $import ) : ?>
						<a href="<?php echo $this->importer_url; ?>" class="tb-tooltip-link button-secondary button-import-layout" title="<?php _e('Import layout from XML file.', 'themeblvd_builder'); ?>"><?php _e('Import Layout', 'themeblvd_builder'); ?></a>
					<?php endif; ?>
					<input type="submit" class="button-primary" name="update" value="<?php _e( 'Add New Layout', 'themeblvd_builder' ); ?>">
					<span class="tb-loader ajax-loading">
						<i class="tb-icon-spinner"></i>
					</span>
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
	 * @param string $layout_id ID of current custom layout element is apart of
	 * @param string $element_type type of element
	 * @param string $element_id ID for individual slide
	 * @param array $element_settings Any current settings for current element
	 * @param array $element_display Any current settings for element's display
	 * @param array $column_data If we don't want column data to be pulled from meta, we can feed it in here
	 */
	public function edit_element( $layout_id, $element_type, $element_id, $element_settings = null, $element_display = null, $column_data = null, $element_label = null ) {

		$api = Theme_Blvd_Builder_API::get_instance();
		$elements = $this->get_elements();
		$blocks = $this->get_blocks();
		$field_name = 'tb_elements['.$element_id.'][options]';

		// Options
		$form = array();

		if ( ! empty( $elements[$element_type]['options'] ) ) {
			$form = themeblvd_option_fields( $field_name, $elements[$element_type]['options'], $element_settings, false );
		}
		?>
		<div id="<?php echo $element_id; ?>" class="widget element-options" data-field-name="<?php echo $field_name; ?>">
			<div class="widget-name top-widget-name">
				<i class="tb-icon-sort"></i>
				<a href="#" class="widget-name-arrow tb-tooltip-link" data-tooltip-toggle="1" data-tooltip-text-1="<?php _e('Show Element Options', 'themeblvd_builder'); ?>" data-tooltip-text-2="<?php _e('Hide Element Options', 'themeblvd_builder'); ?>">
					<i class="tb-icon-up-dir"></i>
				</a>
				<?php if ( $elements[$element_type]['support']['background'] && version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) : ?>
					<a href="#" class="tb-element-background-options tb-tooltip-link" data-target="<?php echo $element_id; ?>_background_form" data-title="<?php _e('Element Display', 'themeblvd_builder'); ?>" data-tooltip-text="<?php _e('Element Display', 'themeblvd_builder'); ?>">
						<i class="tb-icon-picture"></i>
					</a>
				<?php endif; ?>
				<div class="element-label tb-tooltip-link-XXX" data-tooltip-text="<?php _e('Click to Edit Label', 'themeblvd_builder'); ?>">
					<?php $label = $element_label !== null ? $element_label : __('Element Label', 'themeblvd_builder'); ?>
					<span class="label-text"><?php echo $label; ?></span>
					<input type="text" class="label-input" name="tb_elements[<?php echo $element_id; ?>][label]" value="<?php echo esc_attr($label); ?>" />
				</div>
				<h3><?php echo $elements[$element_type]['info']['name']; ?></h3>
				<div class="clear"></div>
			</div><!-- .element-name (end) -->
			<?php if ( $elements[$element_type]['support']['background'] && version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) : ?>
				<div class="element-background-options-wrap hide">
					<div id="<?php echo $element_id; ?>_background_form" class="element-background-options">
						<?php
						$display_options = $this->get_display_options( $elements, $element_type );
						$display_form = themeblvd_option_fields( 'tb_elements['.$element_id.'][display]', $display_options, $element_display, false );
						echo $display_form[0];
						?>
					</div>
				</div>
			<?php endif; ?>
			<div class="widget-content <?php echo 'element-'.$element_type; ?>">

				<input type="hidden" class="element-type" name="tb_elements[<?php echo $element_id; ?>][type]" value="<?php echo $element_type; ?>" />
				<input type="hidden" class="element-query" name="tb_elements[<?php echo $element_id; ?>][query_type]" value="<?php echo $elements[$element_type]['info']['query']; ?>" />

				<!-- ELEMENT OPTIONS (start) -->

				<?php if ( $form ) : ?>
					<?php echo $form[0]; ?>
				<?php endif; ?>

				<!-- ELEMENT OPTIONS (end) -->

				<?php if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) : ?>

					<?php if ( 'content' == $element_type || 'columns' == $element_type ) : ?>

						<!-- COLUMNS/CONTENT (start) -->

						<?php
						$col_count = 2; // Default
						if ( 'content' == $element_type ) {
							$col_count = 1;
						}

						if ( $element_settings && ! empty( $element_settings['setup'] ) ) {
							$col_count = count( explode('-', $element_settings['setup'] ) );
						}

						$display_options = $this->get_display_options();
						?>
						<div class="columns-config columns-<?php echo $col_count; ?>">

							<?php for ( $i = 1; $i <= 5; $i++ ) : ?>

								<?php
								// Saved column data
								$display_settings = array();
								$saved_blocks = array();

								if ( isset( $column_data['col_'.$i] ) ) {

									// Column data was forced in through function's parameters.

									if ( isset( $column_data['col_'.$i]['display'] ) ) {
										$display_settings = $column_data['col_'.$i]['display'];
									}

									if ( isset( $column_data['col_'.$i]['blocks'] ) ) {
										$saved_blocks = $column_data['col_'.$i]['blocks'];
									}

								} else {

									// Get content blocks for column
									$column_data = get_post_meta( $layout_id, $element_id.'_col_'.$i, true );

									if ( isset( $column_data['display'] ) ) {
										$display_settings = $column_data['display'];
									}

									if ( isset( $column_data['blocks'] ) ) {
										$saved_blocks = $column_data['blocks'];
									}
								}
								?>

								<div class="column col-<?php echo $i; ?>">
									<div class="column-inner">

										<input class="col-num" type="hidden" value="<?php echo $i; ?>" />

										<div class="column-heading">

											<?php if ( $col_count > 1 ) : ?>
												<h4><?php printf(__('Column %s', 'themeblvd_builder'), $i); ?></h4>
											<?php else : ?>
												<h4><?php _e('Blocks', 'themeblvd_builder'); ?></h4>
											<?php endif; ?>

											<a href="#" class="tb-element-background-options tb-tooltip-link" data-target="<?php echo $element_id; ?>_col_<?php echo $i; ?>_background_form" data-title="<?php _e('Column Display', 'themeblvd_builder'); ?>" data-tooltip-text="<?php _e('Column Display', 'themeblvd_builder'); ?>">
												<i class="tb-icon-picture"></i>
											</a>

											<a href="#" class="add-block tb-tooltip-link" data-tooltip-text="<?php _e('Add Block', 'themeblvd_builder'); ?>" data-tooltip-position="top">
												<i class="tb-icon-plus-circled"></i>
											</a>

											<div class="tb-fancy-select condensed tb-tooltip-link" data-tooltip-text="<?php _e('Type of Block to Add', 'themeblvd_builder'); ?>">
												<select class="block-type">
													<?php
													foreach ( $blocks as $block ) {
														if ( $api->is_block( $block['info']['id'] ) ) {
															echo '<option value="'.$block['info']['id'].'=>'.$block['info']['query'].'">'.$block['info']['name'].'</option>';
														}
													}
													?>
												</select>
												<span class="trigger"></span>
												<span class="textbox"></span>
											</div><!-- .tb-fancy-select (end) -->

											<a href="#" class="add-block button-secondary" title="<?php _e('Add Block', 'themeblvd_builder'); ?>"><?php _e('Add Block', 'themeblvd_builder'); ?></a>

											<div class="clear"></div>
										</div><!-- .column-heading (end) -->

										<div class="element-background-options-wrap hide">
											<div id="<?php echo $element_id; ?>_col_<?php echo $i; ?>_background_form" class="element-background-options">
												<?php
												$display_form = themeblvd_option_fields( 'tb_elements['.$element_id.'][columns][col_'.$i.'][display]', $display_options, $display_settings, false );
												echo $display_form[0];
												?>
											</div>
										</div>

										<div class="column-blocks">
											<?php
											// Display all content blocks for column
											if ( is_array($saved_blocks) && count($saved_blocks) > 0 ) {
												foreach ( $saved_blocks as $block_id => $block ) {

													if ( ! empty( $block['type'] ) && $api->is_block( $block['type'] ) ) {

														$block_options = array();
														if ( isset( $block['options'] ) ) {
															$block_options = $block['options'];
														}

														$this->edit_block( $element_id, $block['type'], $block_id, $i, $block_options );
													}
												}
											}
											?>
										</div><!-- .column-blocks (end) -->

									</div><!-- .column-inner (end) -->
								</div><!-- .column (end) -->

							<?php endfor; ?>

							<div class="clear"></div>

						</div><!-- .columns-config (end) -->

						<!-- COLUMNS/CONTENT (end) -->

					<?php endif; ?>
				<?php endif; ?>

				<div class="submitbox widget-footer">
					<a href="#<?php echo $element_id; ?>" class="duplicate-element tb-tooltip-link" data-tooltip-text="<?php _e( 'Duplicate Element', 'themeblvd_builder' ); ?>"><i class="tb-icon-copy"></i></a>
					<a href="#<?php echo $element_id; ?>" class="submitdelete delete-element" title="<?php _e( 'Are you sure you want to delete this element?', 'themeblvd_builder' ); ?>"><?php _e( 'Delete Element', 'themeblvd_builder' ); ?></a>
					<div class="clear"></div>
				</div><!-- .widget-footer (end) -->
			</div><!-- .element-content (end) -->
		</div>
		<?php
	}

	/**
	 * Generates the an indivdual panel to edit a content block.
	 *
	 * @since 2.0.0
	 *
	 * @param string $element_id ID of element that contains this content block
	 * @param string $block_type type of block
	 * @param string $block_id ID for individual slide
	 * @param int $col_num Number of column block is located in
	 * @param array $block_settings any current options for current block
	 */
	public function edit_block( $element_id, $block_type, $block_id, $col_num, $block_settings = null ) {

		$blocks = $this->get_blocks();
		$field_name = 'tb_elements['.$element_id.'][columns][col_'.$col_num.'][blocks]['.$block_id.']';

		// Options form
		$block_form = array();
		if ( ! empty( $blocks[$block_type]['options'] ) ) {
			$block_form = themeblvd_option_fields( $field_name.'[options]', $blocks[$block_type]['options'], $block_settings, false );
		}

		// Setup height for modal options
		$options_height = '';
		if ( ! empty( $blocks[$block_type]['info']['height'] ) ) {
			$options_height = $blocks[$block_type]['info']['height'];
		}

		// Whether to show options icon link
		$options = false;

		if ( count( $blocks[$block_type]['options'] ) >= 2 ) {
			$options = true;
		} else if ( count( $blocks[$block_type]['options'] ) == 1 ) {
			if ( ! isset( $blocks[$block_type]['options']['content'] ) && ! isset( $blocks[$block_type]['options']['html'] ) ) {
				$options = true;
			}
		}

		// Blocks that have "content" option,
		// but aren't meant to have an editor
		$exclude_editor = array('post_list', 'post_list_paginated', 'post_list_slider' );
		?>
		<div id="<?php echo $block_id; ?>" class="widget content-block" data-element-id="<?php echo $element_id; ?>" data-field-name="<?php echo $field_name.'[options]'; ?>">

			<div class="content-block-handle">

				<h3><?php echo $blocks[$block_type]['info']['name']; ?></h3>

				<nav class="content-block-nav">

					<?php if ( $options ) : ?>
						<a href="#" class="tb-content-block-options-link tb-tooltip-link" data-target="<?php echo $block_id; ?>_options_form" data-tooltip-text="<?php _e('Edit Options', 'themeblvd_builder'); ?>" data-button_delete="<?php _e('Delete', 'themeblvd_builder'); ?>" data-button_secondary="<?php _e('Duplicate', 'themeblvd_builder'); ?>" data-title="<?php echo $blocks[$block_type]['info']['name']; ?>" data-height="<?php echo $options_height; ?>"><i class="tb-icon-cog"></i></a>
					<?php endif; ?>

					<?php if ( isset( $blocks[$block_type]['options']['content'] ) && ! in_array( $block_type, $exclude_editor ) ) : ?>
						<a href="#" class="tb-textarea-editor-link tb-content-block-editor-link tb-tooltip-link" data-tooltip-text="<?php _e('Edit Content', 'themeblvd'); ?>" data-target="themeblvd-editor-modal" data-button_delete="<?php _e('Delete', 'themeblvd_builder'); ?>" data-button_secondary="<?php _e('Duplicate', 'themeblvd_builder'); ?>"><i class="tb-icon-pencil"></i></a>
					<?php endif; ?>

					<?php if ( isset( $blocks[$block_type]['options']['html'] ) ) : ?>
						<a href="#" class="tb-textarea-code-link tb-content-block-code-link tb-tooltip-link" data-tooltip-text="<?php _e('Edit Code', 'themeblvd'); ?>" data-title="<?php _e('Edit Code', 'themeblvd_builder'); ?>" data-button_delete="<?php _e('Delete', 'themeblvd_builder'); ?>" data-button_secondary="<?php _e('Duplicate', 'themeblvd_builder'); ?>" data-target="<?php echo $block_id; ?>"><i class="tb-icon-code"></i></a>
					<?php endif; ?>

				</nav><!--.content-block-nav (end) -->

				<div class="clear"></div>
			</div><!-- .content-block-handle (end) -->

			<div class="content-block-options <?php echo 'block-'.$block_type; ?>">
				<div id="<?php echo $block_id; ?>_options_form" class="content-block-form">
					<input type="hidden" name="<?php echo $field_name; ?>[type]" value="<?php echo $block_type; ?>" />
					<input type="hidden" name="<?php echo $field_name; ?>[query_type]" value="<?php echo $blocks[$block_type]['info']['query']; ?>" class="element-query" />
					<?php if ( $block_form ) : ?>
						<?php echo $block_form[0]; ?>
					<?php endif; ?>
				</div><!-- .widget-form (end) -->
			</div><!-- .mini-widget-content (end) -->
		</div>
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
								<span class="tb-loader ajax-loading">
									<i class="tb-icon-spinner"></i>
								</span>
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

				<?php if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '<') ) : // After framework 2.5, no more sidebar layouts in custom layouts ?>
					<div id="layout-options" class="postbox postbox-sidebar-layout closed">
						<div class="handlediv" title="<?php echo __('Click to toggle', 'themeblvd_builder'); ?>"><br></div>
						<h3 class="hndle"><?php _e('Sidebar Layout', 'themeblvd_builder' ); ?></h3>
						<div class="tb-widget-content hide">
							<?php
							// Setup sidebar layouts
							$layouts = themeblvd_sidebar_layouts();
							$sidebar_layouts = array( 'default' => __( 'Default Sidebar Layout', 'themeblvd_builder' ) );

							foreach ( $layouts as $layout ) {
								$sidebar_layouts[$layout['id']] = $layout['name'];
							}

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
				<?php endif; ?>

			</div><!-- .inner-sidebar (end) -->
			<div id="post-body">
				<div id="post-body-content">
					<div id="titlediv">
						<div class="ajax-overlay"></div>
						<h2><?php _e( 'Manage Elements', 'themeblvd_builder' ); ?></h2>
						<div class="tb-fancy-select tb-tooltip-link" data-tooltip-text="<?php _e('Type of Element to Add', 'themeblvd_builder'); ?>">
							<select>
							<?php
							foreach ( $elements as $element ) {
								if ( $api->is_element( $element['info']['id'] ) ) {
									echo '<option value="'.$element['info']['id'].'=>'.$element['info']['query'].'">'.$element['info']['name'].'</option>';
								}
							}
							?>
							</select>
							<span class="trigger"></span>
							<span class="textbox"></span>
						</div><!-- .tb-fancy-select (end) -->
						<a href="#" id="add_new_element" class="button-secondary"><?php _e( 'Add New Element', 'themeblvd_builder' ); ?></a>
						<span class="tb-loader ajax-loading">
							<i class="tb-icon-spinner"></i>
						</span>
						<div class="clear"></div>
					</div><!-- #titlediv (end) -->
					<div id="builder">

						<?php if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) : ?>

							<div class="primary sortable">
								<?php
								if ( is_array( $layout_elements ) ) {
									foreach ( $layout_elements as $element_id => $element ) {
										if ( $api->is_element( $element['type'] ) ) {

											$label = null;
											if ( isset( $element['label'] ) ) {
												$label = $element['label'];
											}

											$this->edit_element( $id, $element['type'], $element_id, $element['options'], $element['display'], null, $label );
										}
									}
								}
								?>
							</div><!-- .sortable (end) -->

						<?php else : // @deprecated Builder setup since framework 2.5.0 ?>

							<div id="featured">
								<span class="label"><?php _e( 'Featured Above', 'themeblvd_builder' ); ?></span>
								<div class="sortable">
									<?php
									if ( ! empty( $layout_elements ) && ! empty( $layout_elements['featured'] ) ) {
										foreach ( $layout_elements['featured'] as $element_id => $element ) {
											if ( $api->is_element( $element['type'] ) ) {
												$this->edit_element( $id, $element['type'], $element_id, $element['options'] );
											}
										}
									}
									?>
								</div><!-- .sortable (end) -->
							</div><!-- #featured (end) -->
							<div id="primary">
								<input type="hidden" name="tb_elements[divider]" value="" />
								<span class="label"><?php _e( 'Primary Area', 'themeblvd_builder' ); ?></span>
								<div class="primary sortable">
									<?php
									if ( ! empty( $layout_elements ) && ! empty( $layout_elements['primary'] ) ) {
										foreach ( $layout_elements['primary'] as $element_id => $element ) {
											if ( $api->is_element( $element['type'] ) ) {
												$this->edit_element( $id, $element['type'], $element_id, $element['options'] );
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
										foreach ( $layout_elements['featured_below'] as $element_id => $element ) {
											if ( $api->is_element( $element['type'] ) ) {
												$this->edit_element( $id, $element['type'], $element_id, $element['options'] );
											}
										}
									}
									?>
								</div><!-- .sortable (end) -->
							</div><!-- #featured_below (end) -->

						<?php endif;?>

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
		<div id="metabox-builder">
			<div class="edit-layout-wrap">
				<div id="titlediv">
					<div class="ajax-overlay"></div>
					<h2><?php _e( 'Manage Elements', 'themeblvd_builder' ); ?></h2>
					<div class="tb-fancy-select tb-tooltip-link" data-tooltip-text="<?php _e('Type of Element to Add', 'themeblvd_builder'); ?>">
						<select>
							<?php
							foreach ( $elements as $element ) {
								if ( $api->is_element( $element['info']['id'] ) ) {
									echo '<option value="'.$element['info']['id'].'=>'.$element['info']['query'].'">'.$element['info']['name'].'</option>';
								}
							}
							?>
						</select>
						<span class="trigger"></span>
						<span class="textbox"></span>
					</div><!-- .tb-fancy-select (end) -->
					<a href="#" id="add_new_element" class="button-secondary"><?php _e( 'Add New Element', 'themeblvd_builder' ); ?></a>
					<span class="tb-loader ajax-loading">
						<i class="tb-icon-spinner"></i>
					</span>
					<div class="clear"></div>
				</div><!-- #titlediv (end) -->
				<div id="builder">

					<?php if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) : ?>

						<div class="primary sortable">
							<?php
							if ( is_array( $layout_elements ) ) {
								foreach ( $layout_elements as $element_id => $element ) {
									if ( $api->is_element( $element['type'] ) ) {

										$label = null;
										if ( isset( $element['label'] ) ) {
											$label = $element['label'];
										}

										$this->edit_element( $id, $element['type'], $element_id, $element['options'], $element['display'], null, $label );
									}
								}
							}
							?>
						</div><!-- .sortable (end) -->

					<?php else : // @deprecated Builder setup since framework 2.5.0 ?>

						<div id="featured">
							<span class="label"><?php _e( 'Featured Above', 'themeblvd_builder' ); ?></span>
							<div class="sortable">
								<?php
								if ( ! empty( $layout_elements ) && ! empty( $layout_elements['featured'] ) ) {
									foreach ( $layout_elements['featured'] as $element_id => $element ) {
										if ( $api->is_element( $element['type'] ) ) {
											$this->edit_element( $id, $element['type'], $element_id, $element['options'] );
										}
									}
								}
								?>
							</div><!-- .sortable (end) -->
						</div><!-- #featured (end) -->
						<div id="primary">
							<input type="hidden" name="tb_elements[divider]" value="" />
							<span class="label"><?php _e( 'Primary Area', 'themeblvd_builder' ); ?></span>
							<div class="primary sortable">
								<?php
								if ( ! empty( $layout_elements ) && ! empty( $layout_elements['primary'] ) ) {
									foreach ( $layout_elements['primary'] as $element_id => $element ) {
										if ( $api->is_element( $element['type'] ) ) {
											$this->edit_element( $id, $element['type'], $element_id, $element['options'] );
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
									foreach ( $layout_elements['featured_below'] as $element_id => $element ) {
										if ( $api->is_element( $element['type'] ) ) {
											$this->edit_element( $id, $element['type'], $element_id, $element['options'] );
										}
									}
								}
								?>
							</div><!-- .sortable (end) -->
						</div><!-- #featured_below (end) -->

					<?php endif; ?>

				</div><!-- #builder (end) -->
			</div><!-- .edit-layout-wrap (end) -->

			<?php if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '<') ) : // After framework 2.5, no more sidebar layouts in custom layouts ?>

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

						foreach ( $layouts as $layout ) {
							$sidebar_layouts[$layout['id']] = $imagepath.'layout-'.$layout['id'].'.png';
						}

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

			<?php endif; ?>

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

		$output .= '<div class="tb-fancy-select condensed">';
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

	/**
	 * Get options for element display options form
	 *
	 * @since 2.0.0
	 */
	public function get_display_options( $elements = null, $element_type = null ) {

		$is_element = true;
		if ( ! $elements || ! $element_type ) {
			$is_element = false;
		}

		$bg_types = array(
			'none'		=> __('No background', 'themeblvd_builder'),
			'color'		=> __('Custom color', 'themeblvd_builder'),
			'texture'	=> __('Custom color + texture', 'themeblvd_builder'),
			'image'		=> __('Custom color + image', 'themeblvd_builder')
		);

		if ( $is_element && themeblvd_supports( 'featured', 'style' ) ) {
			$bg_types['featured'] = __('Theme\'s preset "Featured" area background', 'themeblvd_builder');
		}

		if ( $is_element && themeblvd_supports( 'featured_below', 'style' ) ) {
			$bg_types['featured_below'] = __('Theme\'s preset "Featured Below" area background', 'themeblvd_builder');
		}

		$options = array(
			'subgroup_start' => array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide-toggle'
			),
			'bg_type' => array(
				'id'		=> 'bg_type',
				'name'		=> __('Apply Background', 'themeblvd_builder'),
				'desc'		=> __('Select if you\'d like to apply a custom background and how you want to control it.', 'themeblvd_builder'),
				'std'		=> 'none',
				'type'		=> 'select',
				'options'	=> apply_filters( 'themeblvd_builder_bg_types', $bg_types ),
				'class'		=> 'trigger'
			),
			'text_color' => array(
				'id'		=> 'text_color',
				'name'		=> __('Text Color'),
				'desc'		=> __('If you\'re using a dark background color, select to show light text, and vice versa.<br><br><em>Note: When using "Light Text" on a darker background color, general styling on more complex items may be limited.</em>', 'themeblvd_builder'),
				'std'		=> 'dark',
				'type'		=> 'select',
				'options'	=> array(
					'dark'	=> __('Dark Text', 'themeblvd_builder'),
					'light'	=> __('Light Text', 'themeblvd_builder')
				),
				'class'		=> 'hide receiver receiver-color receiver-texture receiver-image'
			),
			'bg_color' => array(
				'id'		=> 'bg_color',
				'name'		=> __('Background Color', 'themeblvd_builder'),
				'desc'		=> __('Select a background color.', 'themeblvd_builder'),
				'std'		=> '#f2f2f2',
				'type'		=> 'color',
				'class'		=> 'hide receiver receiver-color receiver-texture receiver-image'
			),
			'bg_color_opacity' => array(
				'id'		=> 'bg_color_opacity',
				'name'		=> __('Background Color Opacity', 'themeblvd_builder'),
				'desc'		=> __('Select the opacity of the background color. Selecting "1" means that the background color is not transparent, at all.', 'themeblvd_builder'),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
					'0.1'	=> '0.1',
					'0.2'	=> '0.2',
					'0.3'	=> '0.3',
					'0.4'	=> '0.4',
					'0.5'	=> '0.5',
					'0.6'	=> '0.6',
					'0.7'	=> '0.7',
					'0.8'	=> '0.8',
					'0.9'	=> '0.9',
					'1'		=> '1.0'
				),
				'class'		=> 'hide receiver receiver-color receiver-texture receiver-image'
			),
			'bg_texture' => array(
				'id'		=> 'bg_texture',
				'name'		=> __('Background Texture', 'themeblvd_builder'),
				'desc'		=> __('Select a background texture.', 'themeblvd_builder'),
				'type'		=> 'select',
				'select'	=> 'textures',
				'class'		=> 'hide receiver receiver-texture'
			),
			'subgroup_start_2' => array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide hide receiver receiver-texture'
			),
			'apply_bg_texture_parallax' => array(
				'id'		=> 'apply_bg_texture_parallax',
				'name'		=> null,
				'desc'		=> __('Apply parallax scroll effect to background texture.', 'themeblvd_builder'),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'bg_texture_parallax' => array(
				'id'		=> 'bg_texture_parallax',
				'name'		=> __('Parallax Intensity', 'themeblvd_builder'),
				'desc'		=> __('Select the instensity of the scroll effect. 1 is the least intense, and 10 is the most intense.', 'themeblvd_builder'),
				'type'		=> 'slide',
				'std'		=> '5',
				'options'	=> array(
					'min'	=> '1',
					'max'	=> '10',
					'step'	=> '1'
				),
				'class'		=> 'hide receiver'
			),
			'subgroup_end_2' => array(
				'type'		=> 'subgroup_end'
			),
			'subgroup_start_3' => array(
				'type'		=> 'subgroup_start',
				'class'		=> 'select-parallax hide receiver receiver-image'
			),
			'bg_image' => array(
				'id'		=> 'bg_image',
				'name'		=> __('Background Image', 'themeblvd_builder'),
				'desc'		=> __('Select a background image.', 'themeblvd_builder'),
				'type'		=> 'background',
				'color'		=> false,
				'parallax'	=> true
			),
			'bg_image_parallax_stretch' => array(
				'id'		=> 'bg_image_parallax_stretch',
				'name'		=> __('Parallax: Stretch Background', 'themeblvd_builder'),
				'desc'		=> __('When this is checked, your background image will be expanded to fit horizontally, but never condensed. &mdash; <em>Note: This will only work if Background Repeat is set to "No Repeat."</em>', 'themeblvd_builder'),
				'type'		=> 'checkbox',
				'std'		=> '1',
				'class'		=> 'hide parallax'
			),
			'bg_image_parallax' => array(
				'id'		=> 'bg_image_parallax',
				'name'		=> __('Parallax: Intensity', 'themeblvd_builder'),
				'desc'		=> __('Select the instensity of the scroll effect. 1 is the least intense, and 10 is the most intense.', 'themeblvd_builder'),
				'type'		=> 'slide',
				'std'		=> '2',
				'options'	=> array(
					'min'	=> '1',
					'max'	=> '10',
					'step'	=> '1'
				),
				'class'		=> 'hide parallax'
			),
			'subgroup_end_3' => array(
				'type'		=> 'subgroup_end'
			)
		);

		if ( $is_element ) {

			$options['subgroup_start_4'] = array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide hide receiver receiver-image'
			);

			$options['apply_bg_shade'] = array(
				'id'		=> 'apply_bg_shade',
				'name'		=> null,
				'desc'		=> __('Shade background image with transparent color.', 'themeblvd_builder'),
				'std'		=> 0,
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			);

			$options['bg_shade_color'] = array(
				'id'		=> 'bg_shade_color',
				'name'		=> __('Shade Color', 'themeblvd_builder'),
				'desc'		=> __('Select the color you want overlaid on your background image.', 'themeblvd_builder'),
				'std'		=> '#000000',
				'type'		=> 'color',
				'class'		=> 'hide receiver'
			);

			$options['bg_shade_opacity'] = array(
				'id'		=> 'bg_shade_opacity',
				'name'		=> __('Shade Opacity', 'themeblvd_builder'),
				'desc'		=> __('Select the opacity of the shade color overlaid on your background image.', 'themeblvd_builder'),
				'std'		=> '0.5',
				'type'		=> 'select',
				'options'	=> array(
					'0.1'	=> '0.1',
					'0.2'	=> '0.2',
					'0.3'	=> '0.3',
					'0.4'	=> '0.4',
					'0.5'	=> '0.5',
					'0.6'	=> '0.6',
					'0.7'	=> '0.7',
					'0.8'	=> '0.8',
					'0.9'	=> '0.9'
				),
				'class'		=> 'hide receiver'
			);

			$options['subgroup_end_4'] = array(
				'type'		=> 'subgroup_end',
			);

		}

		$options['subgroup_end'] = array(
			'type' 		=> 'subgroup_end'
		);

		if ( $is_element && $elements[$element_type]['support']['popout'] ) {

			$options['apply_popout'] = array(
				'id'		=> 'apply_popout',
				'name'		=> null,
				'desc'		=> __('Stretch content of element to fill outer container. &mdash; <em>Note: If you\'re using a theme design that is not displayed in a stretch layout, this option will not be as pronounced.</em>', 'themeblvd_builder'),
				'std'		=> 0,
				'type'		=> 'checkbox'
			);

			if ( $elements[$element_type]['support']['popout'] === 'force' ) {
				$options['apply_popout']['inactive'] = 'true';
			}
		}

		if ( ! $is_element || $elements[$element_type]['support']['padding'] ) {

			if ( $is_element ) {
				$term = __('element', 'themeblvd_builder');
			} else {
				$term = __('column', 'themeblvd_builder');
			}

			$options['subgroup_start_5'] = array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide'
			);
			$options['apply_padding'] = array(
				'id'		=> 'apply_padding',
				'name'		=> null,
				'desc'		=> sprintf(__('Apply custom padding around %s.', 'themeblvd_builder'), $term),
				'std'		=> 0,
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			);
			$options['padding_top'] = array(
				'id'		=> 'padding_top',
				'name'		=> __('Top Padding', 'themeblvd_builder'),
				'desc'		=> sprintf(__('Set the padding on the top of the %s.', 'themeblvd_builder'), $term),
				'std'		=> '30px',
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);
			$options['padding_right'] = array(
				'id'		=> 'padding_right',
				'name'		=> __('Right Padding', 'themeblvd_builder'),
				'desc'		=> sprintf(__('Set the padding on the right of the %s.', 'themeblvd_builder'), $term),
				'std'		=> '30px',
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);
			$options['padding_bottom'] = array(
				'id'		=> 'padding_bottom',
				'name'		=> __('Bottom Padding', 'themeblvd_builder'),
				'desc'		=> sprintf(__('Set the padding on the bottom of the %s.', 'themeblvd_builder'), $term),
				'std'		=> '30px',
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);
			$options['padding_left'] = array(
				'id'		=> 'padding_left',
				'name'		=> __('Left Padding', 'themeblvd_builder'),
				'desc'		=> sprintf(__('Set the padding on the left of the %s.', 'themeblvd_builder'), $term),
				'std'		=> '30px',
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);
			$options['subgroup_end_5'] = array(
				'type' => 'subgroup_end'
			);
		}

		// Advanced element properties
		$screen_options = Theme_Blvd_Layout_Builder_Screen::get_instance();
		$screen_settings = $screen_options->get_value();

		if ( $is_element ) {

			$options['visibility'] = array(
		    	'id' 		=> 'visibility',
				'name'		=> __( 'Responsive Visibility', 'themeblvd_builder' ),
				'desc'		=> __( 'Select any resolutions you\'d like to <em>hide</em> this element on. This is optional, but can be utilized to deliver different content to different devices.', 'themeblvd_builder' ),
				'type'		=> 'multicheck',
				'class'		=> 'section-visibility',
				'options'	=> array(
					'hide_on_standard' 	=> __( 'Hide on Standard Resolutions', 'themeblvd_builder' ),
					'hide_on_tablet' 	=> __( 'Hide on Tablets', 'themeblvd_builder' ),
					'hide_on_mobile' 	=> __( 'Hide on Mobile Devices', 'themeblvd_builder' )
				)
			);

			if ( empty( $screen_settings['visibility'] ) ) {
				$options['visibility']['class'] .= ' hide';
			}

		}

		$options['classes'] = array(
	    	'id' 		=> 'classes',
			'name'		=> __( 'CSS Classes', 'themeblvd_builder' ),
			'desc'		=> __( 'Enter any CSS classes you\'d like attached to the element.', 'themeblvd_builder' ),
			'type'		=> 'text',
			'class'		=> 'section-classes'
		);

		if ( empty( $screen_settings['classes'] ) ) {
			$options['classes']['class'] .= ' hide';
		}

		return apply_filters( 'themeblvd_builder_display_options', $options );
	}

	/**
	 * Add any required classes to admin <body>
	 *
	 * @since 2.0.0
	 */
	public function body_class( $classes ) {

		$page = get_current_screen();

		if ( $page->base == 'toplevel_page_'.$this->id || ( $page->base == 'post' &&  $page->id == 'page' ) ) {

			$classes = explode( " ", $classes );

			$classes[] = 'themeblvd-builder';

			// If user is using a theme with Theme
			// Blvd framework prior to 2.5.
			if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
				$classes[] = 'themeblvd-builder-legacy-1';
			}

			$classes = implode( " ", $classes );

		}

		return $classes;
	}

	/**
	 * Hook in hidden editor modal.
	 *
	 * @since 2.0.0
	 */
	public function add_editor() {

		// Requires Framework 2.5+
		if ( function_exists( 'themeblvd_editor' ) ) {

			$page = get_current_screen();

			if ( $page->base == 'toplevel_page_'.$this->id || ( $page->base == 'post' &&  $page->id == 'page' ) ) {
				add_action( 'in_admin_header', array( $this, 'display_editor' ) );
			}
		}
	}
	public function display_editor() {
		themeblvd_editor( array( 'delete' => true, 'duplicate' => true ) );
	}

	/**
	 * Hook in hidden icon browser modal(s).
	 *
	 * @since 2.0.0
	 */
	public function add_icon_browser() {

		// Requires Framework 2.5+
		if ( function_exists( 'themeblvd_icon_browser' ) ) {

			$page = get_current_screen();

			if ( $page->base == 'toplevel_page_'.$this->id || ( $page->base == 'post' &&  $page->id == 'page' ) ) {
				add_action( 'in_admin_header', array( $this, 'display_icon_browser' ) );
			}
		}
	}
	public function display_icon_browser() {
		themeblvd_icon_browser( array( 'type' => 'vector' ) );
		themeblvd_icon_browser( array( 'type' => 'image' ) );
	}

	/**
	 * Hook in hidden texture browser modal.
	 *
	 * @since 2.5.0
	 */
	public function add_texture_browser() {

		// Requires Framework 2.5+
		if ( function_exists( 'themeblvd_icon_browser' ) ) {

			$page = get_current_screen();

			if ( $page->base == 'toplevel_page_'.$this->id || ( $page->base == 'post' &&  $page->id == 'page' ) ) {
				add_action( 'in_admin_header', 'themeblvd_texture_browser' );
			}
		}
	}
}