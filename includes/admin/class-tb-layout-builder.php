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
	private $updated = '';
	private $error = '';
	public $sample_dir = '';
	public $sample_uri = '';
	public $sample_asset_uri = '';

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
			'page_title' 	=> __( 'Templates', 'theme-blvd-layout-builder' ),
			'menu_title' 	=> __( 'Templates', 'theme-blvd-layout-builder' ),
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
		add_action( 'themeblvd_builder_update', array( $this, 'notice' ) );

		// Manage Custom Layouts via Edit Page screen
		add_action( 'current_screen', array( $this, 'builder_init' ) );

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

		// Add post browser into page, which user can use to find
		// ID's of posts in Builder elements.
		add_action( 'current_screen', array( $this, 'add_post_browser' ) );

		// Add texture browser into page, which Builder can use for
		// selecting textures.
		add_action( 'current_screen', array( $this, 'add_texture_browser' ) );

		// Add ajax functionality in Builder
		$this->ajax = new Theme_Blvd_Layout_Builder_Ajax( $this );

		// Make advanced option types available in Builder
		if ( class_exists( 'Theme_Blvd_Advanced_Options' ) ) {
			$advanced = Theme_Blvd_Advanced_Options::get_instance();
			$advanced->create('bars');
			$advanced->create('buttons');
			$advanced->create('datasets');
			$advanced->create('locations');
			$advanced->create('logos');
			$advanced->create('price_cols');
			$advanced->create('sectors');
			$advanced->create('slider');
			$advanced->create('social_media');
			$advanced->create('tabs');
			$advanced->create('testimonials');
			$advanced->create('text_blocks');
			$advanced->create('toggles');
		}

		// Allow for importing
		if ( class_exists('Theme_Blvd_Import_Layout') ) {

			$args = array(
				'redirect' => admin_url('admin.php?page='.$this->id) // Builder page URL
			);

			$import = new Theme_Blvd_Import_Layout( $this->id, $args );
			$this->importer_url = $import->get_url(); // URL of page where importer is
		}

	}

	/*--------------------------------------------*/
	/* Mutators
	/*--------------------------------------------*/

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

	/*--------------------------------------------*/
	/* Accessors
	/*--------------------------------------------*/

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
	 * Get blocks with filter applied.
	 *
	 * @since 2.0.0
	 */
	public function get_blocks() {
		return apply_filters( 'themeblvd_get_blocks', $this->blocks );
	}

	/*--------------------------------------------*/
	/* Builder Admin Assets
	/*--------------------------------------------*/

	/**
	 * Loads the CSS
	 *
	 * @since 1.0.0
	 */
	public function load_styles() {

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'themeblvd_admin', esc_url( TB_FRAMEWORK_URI . '/admin/assets/css/admin-style.min.css' ), null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_options', esc_url( TB_FRAMEWORK_URI . '/admin/options/css/admin-style.min.css' ), null, TB_FRAMEWORK_VERSION );

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<') ) {
			wp_enqueue_style( 'color-picker', esc_url( TB_FRAMEWORK_URI . '/admin/options/css/colorpicker.min.css' ) );
		}

		$file = TB_BUILDER_DEBUG ? 'builder-style.css' : 'builder-style.min.css';
		wp_enqueue_style( 'theme-blvd-layout-builder', esc_url( TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/css/'.$file ), null, TB_BUILDER_PLUGIN_VERSION );

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
			wp_enqueue_style( 'codemirror', esc_url( TB_FRAMEWORK_URI . '/admin/assets/plugins/codemirror/codemirror.min.css' ), null, '4.0' );
			wp_enqueue_style( 'codemirror-theme', esc_url( TB_FRAMEWORK_URI . '/admin/assets/plugins/codemirror/themeblvd.min.css' ), null, '4.0' );
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
		wp_enqueue_script( 'themeblvd_gmap', esc_url( 'https://maps.googleapis.com/maps/api/js' ), array(), null );

		// Theme Blvd scripts
		if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
			wp_enqueue_script( 'themeblvd_modal', esc_url( TB_FRAMEWORK_URI . '/admin/assets/js/modal.min.js' ), array('jquery'), TB_FRAMEWORK_VERSION );
		}

		wp_enqueue_script( 'themeblvd_admin', esc_url( TB_FRAMEWORK_URI . '/admin/assets/js/shared.min.js' ), array('jquery'), TB_FRAMEWORK_VERSION );

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<') ) {
			wp_enqueue_script( 'color-picker', esc_url( TB_FRAMEWORK_URI . '/admin/options/js/colorpicker.min.js' ), array('jquery') );
		}

		// Builder script
		$file = TB_BUILDER_DEBUG ? 'builder.js' : 'builder.min.js';
		wp_enqueue_script( 'theme-blvd-layout-builder', esc_url( TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/js/'.$file ), array('jquery'), TB_BUILDER_PLUGIN_VERSION );

		// Code editor and FontAwesome
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
			wp_enqueue_script( 'codemirror', esc_url( TB_FRAMEWORK_URI . '/admin/assets/plugins/codemirror/codemirror.min.js' ), null, '4.0' );
			wp_enqueue_script( 'codemirror-modes', esc_url( TB_FRAMEWORK_URI . '/admin/assets/plugins/codemirror/modes.min.js' ), null, '4.0' );
			wp_enqueue_style( 'fontawesome', esc_url( TB_FRAMEWORK_URI . '/assets/plugins/fontawesome/css/font-awesome.min.css' ), null, TB_FRAMEWORK_VERSION );
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
			wp_localize_script( 'theme-blvd-layout-builder', 'themeblvd', themeblvd_get_admin_locals( 'js' ) ); // @see add_js_locals()

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
			'builder'				=> __( 'Builder', 'theme-blvd-layout-builder' ),
			'column'				=> __( 'Column', 'theme-blvd-layout-builder' ),
			'columns'				=> __( 'Columns', 'theme-blvd-layout-builder' ),
			'edit_layout'			=> __( 'Edit Layout', 'theme-blvd-layout-builder' ),
			'delete_text'			=> __( 'Delete', 'theme-blvd-layout-builder' ),
			'delete_block'			=> __( 'Are you sure you want to delete the content block?', 'theme-blvd-layout-builder' ),
			'delete_layout'			=> __( 'Are you sure you want to delete the template(s)?', 'theme-blvd-layout-builder' ),
			'layout_created'		=> __( 'Layout created!', 'theme-blvd-layout-builder' ),
			'no_layouts'			=> __( 'Oops! You didn\'t select any templates to delete.', 'theme-blvd-layout-builder' ),
			'save_switch_layout'	=> __( 'Would you like to save the current layout before switching?', 'theme-blvd-layout-builder' ),
			'shift_up_error'		=> __( 'The section can\'t be shifted up any further.', 'theme-blvd-layout-builder' ),
			'shift_down_error'		=> __( 'The section can\'t be shifted down any further.', 'theme-blvd-layout-builder' ),
			'template_apply'		=> __( 'Are you sure you want to apply the selected starting point? Any work on the current layout for this page will be erased.', 'theme-blvd-layout-builder' ),
			'template_desc'			=> __( 'Enter a name for the new template.', 'theme-blvd-layout-builder' ),
			'template_save'			=> __( 'Save as Template', 'theme-blvd-layout-builder' ),
			'template_title'		=> __( 'Save Current Layout as Template', 'theme-blvd-layout-builder' ),
			'template_updated'		=> __( 'The template has been saved.', 'theme-blvd-layout-builder'),
		);
		return array_merge($current, $new);
	}

	/*--------------------------------------------*/
	/* Builder Admin Page Display
	/*--------------------------------------------*/

	/**
	 * Add a menu page for Builder
	 *
	 * @since 1.0.0
	 */
	public function add_page() {

		// Create admin page
		$admin_page = add_menu_page( $this->args['page_title'], $this->args['menu_title'], $this->args['cap'], $this->id, array( $this, 'page' ), $this->args['icon'], $this->args['priority'] );

		// Add scripts and styles
		add_action( 'admin_print_styles-'.$admin_page, array( $this, 'load_styles' ) );
		add_action( 'admin_print_scripts-'.$admin_page, array( $this, 'load_scripts' ) );

	}

	/**
	 * Builds out the full admin page.
	 *
	 * @since 1.0.0
	 */
	public function page() {

		// Set active tab ID.
		$active = 'manage';

		if ( ! empty( $_GET['tab'] ) && in_array( $_GET['tab'], array('manage', 'add', 'edit') ) ) {
			$active = $_GET['tab'];
		}

		// Template ID to edit (if necessary)
		$template_id = '';

		if ( $active == 'edit' && ! empty( $_GET['template'] ) ) {
			$template_id = $_GET['template'];
		}

		// Delete template
		if ( ! empty( $_GET['delete'] ) ) {
			if ( wp_verify_nonce( $_GET['security'], 'delete_template' ) ) {
				wp_delete_post( $_GET['delete'], true );
				$this->error = __('The template has been deleted.', 'theme-blvd-layout-builder');
			}
		}

		// Delete multiple templates
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'trash' ) {
			if ( wp_verify_nonce( $_POST['tb_nonce'], 'delete_template' ) ) {

				if ( $_POST['posts'] ) {
					foreach ( $_POST['posts'] as $post_id ) {
						wp_delete_post( $post_id, true );
					}
				}

				$this->error = __('The templates have been deleted.', 'theme-blvd-layout-builder');

			}
		}

		// Save new template
		if ( ! empty( $_POST['new_template'] ) ) {
			if ( wp_verify_nonce( $_POST['tb_nonce'], 'new_template' ) ) {
				$template_id = $this->new_template( $_POST['new_template'] );
				$this->updated = __('New template created.', 'theme-blvd-layout-builder');
			}
		}

		// Update template
		if ( $template_id && isset( $_POST['action'] ) && $_POST['action'] == 'save_template' ) {
			if ( wp_verify_nonce( $_POST['tb_nonce'], 'save_template' ) ) {
				$this->save_layout( $template_id, $_POST );
				$this->updated = __('The template has been saved.', 'theme-blvd-layout-builder');
			}
		}
		?>
		<div id="builder_blvd" class="primary">
			<div id="optionsframework" class="wrap">

				<div class="admin-module-header">
			    	<?php do_action( 'themeblvd_admin_module_header', 'builder' ); ?>
			    </div>

			    <h2 class="nav-tab-wrapper">
			        <a href="<?php echo admin_url('admin.php?page='.$this->id); ?>" class="nav-tab<?php if ($active == 'manage') echo ' nav-tab-active'; ?>" title="<?php esc_attr_e( 'Manage Templates', 'theme-blvd-layout-builder' ); ?>"><?php esc_html_e( 'Manage Templates', 'theme-blvd-layout-builder' ); ?></a>
			        <a href="<?php echo admin_url('admin.php?page='.$this->id.'&tab=add'); ?>" class="nav-tab<?php if ($active == 'add') echo ' nav-tab-active'; ?>" title="<?php esc_attr_e( 'Add Template', 'theme-blvd-layout-builder' ); ?>"><?php esc_html_e( 'Add Template', 'theme-blvd-layout-builder' ); ?></a>
			        <?php if ( $active == 'edit' ) : ?>
			       		<span class="nav-tab<?php if ($active == 'edit') echo ' nav-tab-active'; ?>" title="<?php esc_attr_e( 'Edit Template', 'theme-blvd-layout-builder' ); ?>"><?php esc_html_e( 'Edit Template', 'theme-blvd-layout-builder' ); ?></span>
			       	<?php endif; ?>
			    </h2>

			    <?php
			    // Display notices
			    do_action('themeblvd_builder_update');

			    // Display correct admin page
				switch( $active ) {

					case 'manage' :
						$this->page_manage();
						break;

					case 'add' :
						$this->page_add();
						break;

					case 'edit' :
						$this->page_edit( $template_id );
						break;

				}
				?>

				<div class="admin-module-footer">
			    	<?php do_action( 'themeblvd_admin_module_footer', 'builder' ); ?>
			    </div><!-- .admin-module-footer (end) -->

			</div><!-- #optionsframework (end) -->
		</div><!-- #builder_blvd (end) -->
		<?php
	}

	/**
	 * Display page to manage layouts.
	 *
	 * @since 2.0.0
	 */
	public function page_manage() {
		$templates = get_posts( array( 'post_type' => 'tb_layout', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<div id="manage_layouts" class="metabox-holder">
	    	<form id="manage_builder" action="<?php echo admin_url('admin.php?page='.$this->id); ?>" method="post">
	    		<?php echo '<input type="hidden" name="tb_nonce" value="'.wp_create_nonce( 'delete_template' ).'" />'; ?>
				<div class="tablenav top clearfix">
					<div class="alignleft actions">
						<select name="action">
							<option value="-1" selected="selected"><?php esc_attr_e( 'Bulk Actions', 'themeblvd' ); ?></option>
							<option value="trash"><?php esc_html_e( 'Delete Templates', 'themeblvd' ); ?></option>
						</select>
						<input type="submit" id="doaction" class="button-secondary action" value="<?php esc_attr_e( 'Apply', 'themeblvd' ); ?>">
					</div>
					<div class="alignright tablenav-pages">
						<span class="displaying-num"><?php printf( esc_html( _n( '1 Template', '%s Templates', count($templates) ) ), number_format_i18n( count($templates) ) ); ?></span>
					</div>
				</div><!-- .tablenav (end) -->
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox"></th>
							<th class="head-title"><?php esc_html_e('Template Name', 'theme-blvd-layout-builder'); ?></th>
							<th class="head-slug"><?php esc_html_e('Template ID', 'theme-blvd-layout-builder'); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox"></th>
							<th class="head-title"><?php esc_html_e('Template Name', 'theme-blvd-layout-builder'); ?></th>
							<th class="head-slug"><?php esc_html_e('Template ID', 'theme-blvd-layout-builder'); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php if ( $templates ) : ?>
							<?php foreach ( $templates as $template ) : ?>
								<tr id="row-<?php echo $template->ID; ?>">
									<th scope="row" class="check-column">
										<input type="checkbox" name="posts[]" value="<?php echo $template->ID; ?>" />
									</th>
									<td class="post-title page-title column-title">
										<strong><a href="<?php echo esc_url( add_query_arg( array('page' => $this->id, 'tab' => 'edit', 'template' => $template->ID), admin_url('admin.php') ) ); ?>" class="title-link edit-tb_layout" title="Edit"><?php echo esc_html($template->post_title); ?></a></strong>
										<div class="row-actions">
											<span class="edit">
												<a href="<?php echo esc_url( add_query_arg( array('page' => $this->id, 'tab' => 'edit', 'template' => $template->ID), admin_url('admin.php') ) ); ?>" class="edit-post edit-tb_layout" title="Edit"><?php esc_attr_e('Edit', 'theme-blvd-layout-builder' ); ?></a> |
											</span>
											<?php if ( class_exists('Theme_Blvd_Export_Layout') ) : ?>
												<?php if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) : ?>
													<span class="export">
														<a href="<?php echo esc_url( add_query_arg( array('page' => 'themeblvd_builder', 'themeblvd_export_layout' => 'true', 'layout' => $template->ID, 'security' => wp_create_nonce('themeblvd_export_layout')), admin_url('admin.php') ) ); ?>" class="export-layout" title="<?php esc_attr_e( 'Export', 'themeblvd' ); ?>"><?php esc_html_e( 'Export', 'themeblvd' ); ?></a> |
													</span>
												<?php endif; ?>
												<?php if ( defined('TB_SAMPLE_LAYOUT_PLUGIN_VERSION') ) : ?>
													<span class="export-sample">
														<a href="<?php echo esc_url( add_query_arg( array('page' => 'themeblvd_builder', 'themeblvd_export_sample_layout' => 'true', 'layout' => $template->ID, 'security' => wp_create_nonce('themeblvd_export_sample_layout')), admin_url('admin.php') ) ); ?>" class="export-layout" title="<?php esc_attr_e( 'Export as Sample Layout', 'themeblvd' ); ?>"><?php esc_html_e( 'Export as Sample Layout', 'themeblvd' ); ?></a> |
													</span>
												<?php endif; ?>
											<?php endif; ?>
											<span class="trash">
												<a href="<?php echo esc_url( add_query_arg( array('page' => $this->id, 'delete' => $template->ID, 'security' => wp_create_nonce('delete_template')), admin_url('admin.php') ) ); ?>" class="delete-layout"><?php esc_html_e('Delete', 'theme-blvd-layout-builder'); ?></a>
											</span>
										</div>
									</td>
									<td class="post-slug">
										<?php echo esc_html($template->post_name); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="3">
									<div class="warning">
										<p><strong><?php esc_html_e('You haven\'t created any templates yet.', 'theme-blvd-layout-builder'); ?></strong></p>
										<p><?php esc_html_e('Templates are useful to create custom layouts for pages more quickly. Templates can be applied to the custom layout of individual pages for a quick starting point, or synced for continuity. Manage your templates here, and utilize them when editing a page. Click "Add Template" above to get started.', 'theme-blvd-layout-builder'); ?></p>
									</div>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</form><!-- #manage_builder (end) -->
		</div><!-- #manage (end) -->
		<?php
	}

	/**
	 * Display page to add a new layout.
	 *
	 * @since 2.0.0
	 */
	public function page_add() {

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
		$options['name'] = array(
			'name' 		=> __( 'Template Name', 'theme-blvd-layout-builder' ),
			'desc' 		=> __( 'Enter a user-friendly name for your template.<br><em>Example: My Layout</em>', 'theme-blvd-layout-builder' ),
			'id' 		=> 'name',
			'type' 		=> 'text'
		);

		// Start subgroup for starting point
		$options['subgroup_start'] = array(
			'type'		=> 'subgroup_start',
			'class'		=> 'show-hide-toggle'
		);

		// Starting point
		$options['start'] = array(
			'name' 		=> __( 'Starting Point', 'theme-blvd-layout-builder' ),
			'desc' 		=> __( 'Select if you\'d like to start building your template from scratch, from a pre-existing template, or from a sample layout.', 'theme-blvd-layout-builder' ),
			'id' 		=> 'start',
			'type' 		=> 'select',
			'options' 	=> array(
				'scratch'	=> __( 'Start From Scratch', 'theme-blvd-layout-builder' ),
				'layout'	=> __( 'Start From Existing Template', 'theme-blvd-layout-builder' ),
				'sample'	=> __( 'Start From Sample Layout', 'theme-blvd-layout-builder' )
			),
			'class'		=> 'trigger'
		);
		if ( ! $sample_layouts ) {
			unset( $options['start']['options']['sample'] );
		}
		if ( ! $custom_layouts ) {
			unset( $options['start']['options']['layout'] );
		}

		// Existing Layout
		if ( $custom_layouts ) {
			$options['existing'] = array(
				'name' 		=> __( 'Custom Templates', 'theme-blvd-layout-builder' ),
				'desc' 		=> __( 'Select one of the layouts you created previously to start this new one.', 'theme-blvd-layout-builder' ),
				'id' 		=> 'existing',
				'type' 		=> 'select',
				'options' 	=> $custom_layouts,
				'class'		=> 'hide receiver receiver-layout'
			);
		}

		// Sample Layouts (only show if there are sample layouts)
		if ( $sample_layouts ) {

			$options['sample'] = array(
				'name' 		=> __( 'Sample Layout', 'theme-blvd-layout-builder' ),
				'desc' 		=> __( 'Select a sample layout to start from.<br><br><em>Note: Not all sample layouts will look exactly as pictured, as you will most likely see slight variations, depending on the specific theme you\'re using.</em>', 'theme-blvd-layout-builder' ),
				'id' 		=> 'sample',
				'type' 		=> 'select',
				'options' 	=> $sample_layouts,
				'class'		=> 'hide builder-samples receiver receiver-sample'
			);

			if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
				$options['sample']['desc'] = __( 'Select a sample layout to start from.', 'theme-blvd-layout-builder' );
				$options['sample']['class'] = 'builder_samples';
			}
		}

		// End subgroup for starting point
		$options[] = array(
			'type' => 'subgroup_end'
		);

		// Sidebar Layout
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) { // @deprecated

			// Setup sidebar layouts
			$layouts = themeblvd_sidebar_layouts();
			$sidebar_layouts = array( 'default' => __( 'Default Sidebar Layout', 'theme-blvd-layout-builder' ) );

			foreach ( $layouts as $layout ) {
				$sidebar_layouts[$layout['id']] = $layout['name'];
			}

			$options[] = array(
				'name' 		=> __( 'Sidebar Layout', 'theme-blvd-layout-builder' ),
				'desc' 		=> __( 'Select your sidebar layout for this page.<br><br><em>Note: You can change this later when editing your template.</em>', 'theme-blvd-layout-builder' ),
				'id' 		=> 'sidebar',
				'type' 		=> 'select',
				'options' 	=> $sidebar_layouts,
				'class'		=> 'hide receiver receiver-scratch'
			);
		}

		$options = apply_filters( 'themeblvd_add_layout', $options );

		// Build form
		$form = themeblvd_option_fields( 'new_template', $options, null, false );
		?>
		<div id="add_layout">
			<form action="<?php echo admin_url('admin.php?page='.$this->id.'&tab=edit'); ?>" method="post">
				<?php
				echo '<input type="hidden" name="tb_nonce" value="'.wp_create_nonce( 'new_template' ).'" />';
				$import = version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=') && class_exists('Theme_Blvd_Import_Layout') ? true : false;
				?>
				<div class="metabox-holder">
					<div class="postbox">
						<h3><?php esc_html_e( 'New Template', 'theme-blvd-layout-builder' ); ?></h3>
						<div class="inner-group">
							<?php echo $form[0]; ?>
						</div><!-- .group (end) -->
						<div id="optionsframework-submit">
							<?php if ( $import ) : ?>
								<a href="<?php echo esc_url($this->importer_url); ?>" class="tb-tooltip-link button-secondary button-import-layout" title="<?php esc_attr_e('Import template from XML file.', 'theme-blvd-layout-builder'); ?>"><?php esc_html_e('Import Template', 'theme-blvd-layout-builder'); ?></a>
							<?php endif; ?>
							<input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Add New Template', 'theme-blvd-layout-builder' ); ?>">
							<span class="tb-loader ajax-loading">
								<i class="tb-icon-spinner"></i>
							</span>
				            <div class="clear"></div>
						</div>
					</div><!-- .postbox (end) -->
				</div><!-- .metabox-holder (end) -->
			</form><!-- #add_new_builder (end) -->
		</div><!-- #manage (end) -->
		<?php
	}

	/**
	 * Display page to edit a layout.
	 *
	 * @since 2.0.0
	 */
	public function page_edit( $template_id ) {

		if ( ! $template_id ) {
			$this->notice( __('No Template ID given to edit.', 'theme-blvd-layout-builder'), 'error', false );
			return;
		}

		// Verify layout data
		$data = new Theme_Blvd_Layout_Builder_Data( $template_id );
		$data->verify('elements');
		$data->verify('info');
		$data->finalize();

		// Get post object for template
		$template = get_post( $template_id );
		?>
		<div id="tb-edit-layout">
			<form id="edit_builder" method="post" action="<?php echo esc_url( add_query_arg( array('page' => $this->id, 'tab' => 'edit', 'template' => $template_id), admin_url('admin.php') ) ); ?>">
				<input type="hidden" name="tb_nonce" value="<?php echo wp_create_nonce('save_template'); ?>" />
				<input type="hidden" name="template_id" value="<?php echo esc_attr($template_id); ?>" />
				<input type="hidden" name="action" value="save_template" />
				<div id="poststuff" class="metabox-holder full-width has-right-sidebar">

					<!-- MAIN (start) -->

					<div id="post-body">
						<div id="post-body-content">
							<?php $this->edit_layout( $template_id, true ); ?>
						</div><!-- .post-body-content (end) -->
					</div><!-- #post-body (end) -->

					<!-- MAIN (end) -->

					<!-- SIDEBAR (start) -->

					<div class="inner-sidebar">
						<?php if ( TB_BUILDER_DEBUG ) : ?>
							<div id="layout-publish" class="postbox postbox-publish">
								<h3 class="hndle"><?php esc_html_e( 'DEBUG', 'theme-blvd-layout-builder' ); ?></h3>
								<div class="submitbox">
									<div id="major-publishing-actions">
										<div id="publishing-action">
											<input class="button-primary" value="<?php esc_attr_e( 'Update Template (non-ajax)', 'theme-blvd-layout-builder' ); ?>" type="submit" />
										</div>
										<div class="clear"></div>
									</div>
								</div><!-- .submitbox (end) -->
							</div><!-- .post-box (end) -->
						<?php endif; ?>
						<div id="layout-publish" class="postbox postbox-publish">
							<h3 class="hndle"><?php esc_html_e( 'Publish', 'theme-blvd-layout-builder' ); ?> <?php echo esc_html($template->post_title); ?></h3>
							<div class="submitbox">
								<div id="major-publishing-actions">
									<div id="delete-action">
										<a class="submitdelete delete-layout" href="<?php echo esc_url( add_query_arg( array('page' => $this->id, 'delete' => $template_id, 'security' => wp_create_nonce('delete_template')), admin_url('admin.php') ) ); ?>"><?php esc_html_e( 'Delete', 'theme-blvd-layout-builder' ); ?></a>
									</div>
									<div id="publishing-action">
										<a href="#" class="ajax-save-template button-primary" title="<?php esc_html_e( 'Update Template', 'theme-blvd-layout-builder' ); ?>"><?php esc_html_e( 'Update Template', 'theme-blvd-layout-builder' ); ?></a>
										<span class="tb-loader ajax-loading">
											<i class="tb-icon-spinner"></i>
										</span>
									</div>
									<div class="clear"></div>
								</div>
							</div><!-- .submitbox (end) -->
						</div><!-- .post-box (end) -->
						<div id="layout-info" class="postbox postbox-layout-info closed">
							<div class="handlediv" title="<?php esc_attr_e('Click to toggle', 'theme-blvd-layout-builder'); ?>"><br></div>
							<h3 class="hndle"><?php esc_html_e('Template Information', 'theme-blvd-layout-builder' ); ?></h3>
							<div class="tb-widget-content hide">
								<?php
								// Current settings
								$info_settings = array(
									'post_title' 	=> $template->post_title,
									'post_name'		=> $template->post_name
								);

								// Setup attribute options
								$info_options = array(
									array(
										'name'		=> __('Template Name', 'theme-blvd-layout-builder' ),
										'id' 		=> 'post_title',
										'desc'		=> null,
										'type' 		=> 'text'
									),
									array(
										'name' 		=> __('Template ID', 'theme-blvd-layout-builder' ),
										'id' 		=> 'post_name',
										'desc'		=> null,
										'type' 		=> 'text'
									)
								);

								// Display form element
								$form = themeblvd_option_fields( 'template_info', $info_options, $info_settings, false );
								echo $form[0];
								?>
							</div><!-- .tb-widget-content (end) -->
						</div><!-- .post-box (end) -->

						<?php if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '<') ) : // After framework 2.5, no more sidebar layouts in custom layouts ?>
							<div id="layout-options" class="postbox postbox-sidebar-layout closed">
								<div class="handlediv" title="<?php esc_attr_e('Click to toggle', 'theme-blvd-layout-builder'); ?>"><br></div>
								<h3 class="hndle"><?php esc_html_e('Sidebar Layout', 'theme-blvd-layout-builder' ); ?></h3>
								<div class="tb-widget-content hide">
									<?php
									// Setup sidebar layouts
									$layouts = themeblvd_sidebar_layouts();
									$sidebar_layouts = array( 'default' => __( 'Default Sidebar Layout', 'theme-blvd-layout-builder' ) );

									foreach ( $layouts as $layout ) {
										$sidebar_layouts[$layout['id']] = $layout['name'];
									}

									$options = array(
										array(
											'id' 		=> 'sidebar_layout',
											'desc'		=> __( 'Select how you\'d like the sidebar(s) arranged in this layout. Your site-wide default sidebar layout can be set from your Theme Options page.<br><br><strong>Note: The sidebar layout is only applied to the "Primary Area" of the custom layout.</strong>', 'theme-blvd-layout-builder' ),
											'type' 		=> 'select',
											'options' 	=> $sidebar_layouts
										)
									);

									// Display form element
									$layout_settings = get_post_meta( $template_id, 'settings', true );
									$form = themeblvd_option_fields( 'tb_layout_options', $options, $layout_settings, false );
									echo $form[0];
									?>
								</div><!-- .tb-widget-content (end) -->
							</div><!-- .post-box (end) -->
						<?php endif; ?>

					</div><!-- .inner-sidebar (end) -->

					<!-- SIDEBAR (end) -->

				</div><!-- #poststuff (end) -->
			</form>
		</div><!-- #manage (end) -->
		<?php
	}

	/**
	 * Display admin page notice
	 *
	 * @since 2.0.0
	 */
	public function notice( $notice = '', $type = 'updated', $fade = true ) {

		if ( $notice ) {

			$class = 'themeblvd-updated '.$type;

			if ( $fade ) {
				$class .= ' fade';
			}

			printf('<div class="%s"><p><strong>%s</strong></p></div>', $class, esc_html($notice) );

		} else if ( $this->updated ) {
			echo '<div class="themeblvd-updated updated fade"><p><strong>'.esc_html($this->updated).'</strong></p></div>';
		} else if ( $this->error ) {
			echo '<div class="themeblvd-updated error fade settings-error"><p><strong>'.esc_html($this->error).'</strong></p></div>';
		}
	}

	/*--------------------------------------------*/
	/* Builder Data Handling
	/*--------------------------------------------*/

	/**
	 * Create and save new layout.
	 *
	 * @since 2.0.0
	 */
	public function new_template( $data = array() ) {

		$defaults = array(
			'start'		=> '',
			'name'		=> 'Template',
			'existing'	=> '',
			'sample'	=> '',
			'sidebar'	=> 'sidebar_right' // @deprecated
		);
		$data = wp_parse_args( $data, $defaults );

		// Setup arguments for new 'layout' post
		$args = array(
			'post_type'			=> 'tb_layout',
			'post_title'		=> $data['name'],
			'post_status' 		=> 'publish',
			'comment_status'	=> 'closed',
			'ping_status'		=> 'closed'
		);

		// Create new post
		$post_id = wp_insert_post( $args );

		$columns = array();

		if ( $data['start'] == 'layout' ) {

			// Use Pre-Existing Template

			// Section data
			$section_data = get_post_meta( $data['existing'], '_tb_builder_sections', true );
			update_post_meta( $post_id, '_tb_builder_sections', $section_data );

			// Elements
			$sections = get_post_meta( $data['existing'], '_tb_builder_elements', true );
			update_post_meta( $post_id, '_tb_builder_elements', $sections );

			// Save any column data
			if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
				foreach ( $sections as $elements ) {
					if ( $elements ) {
						foreach ( $elements as $element_id => $element ) {
							if ( $element['type'] == 'columns' ) {
								for ( $i = 1; $i <= 5; $i++ ) {
									$col_id = '_tb_builder_'.$element_id.'_col_'.strval($i);
								    $column = get_post_meta( $data['existing'], $col_id, true );
								    update_post_meta( $post_id, $col_id, $column );
								}
							}
						}
					}
				}
			}

		} else if ( $data['start'] == 'sample' ) {

			// Use Plugin Sample Layout

			$samples = themeblvd_get_sample_layouts();

			if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {

				$this->sample_uri = trailingslashit($samples[$data['sample']]['uri']); // not currently used for anything
				$this->sample_dir = $dir = trailingslashit($samples[$data['sample']]['dir']);
				$this->sample_asset_uri = trailingslashit($samples[$data['sample']]['assets']);;
				$xml = $dir.'layout.xml';
				$import = '';

				// Parse the file
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
						$value = $this->sample_images( $value, $key );

						// Store to post meta of new template
						update_post_meta( $post_id, $key, $value );

					}
				}

			} else { // @deprecated

				$elements = array(
					'featured' 			=> $samples[$data['sample']]['featured'],
					'primary' 			=> $samples[$data['sample']]['primary'],
					'featured_below' 	=> $samples[$data['sample']]['featured_below']
				);

				update_post_meta( $post_id, '_tb_builder_elements', $elements );

			}

		} else {

			// Start template from scratch

			if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) {
				update_post_meta( $post_id, '_tb_builder_elements', array( 'primary' => array() ) );
			} else {
				update_post_meta( $post_id, '_tb_builder_elements', array( 'featured' => array(), 'primary' => array(), 'featured_below' => array(), ) );
			}
		}

		// Store version numbers that this layout was created with
		update_post_meta( $post_id, '_tb_builder_plugin_version_created', TB_BUILDER_PLUGIN_VERSION );
		update_post_meta( $post_id, '_tb_builder_plugin_version_saved', TB_BUILDER_PLUGIN_VERSION );
		update_post_meta( $post_id, '_tb_builder_framework_version_created', TB_FRAMEWORK_VERSION );
		update_post_meta( $post_id, '_tb_builder_framework_version_saved', TB_FRAMEWORK_VERSION );

		// If using an old theme, save the sidebar layout
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) { // @deprecated

			$settings = array();

			if ( $data['start'] == 'layout' ) {

				$settings = get_post_meta( $data['existing'], 'settings', true );

			} else if ( $data['start'] == 'sample' ) {

				if ( ! empty( $samples[$data['sample']] ) ) {
					$settings = array( 'sidebar_layout' => $samples[$data['sample']]['sidebar_layout'] );
				}

			} else {

				if ( isset( $data['sidebar'] ) ) {
					$settings['sidebar_layout'] = $data['sidebar'] ;
				}
			}

			update_post_meta( $post_id, 'settings', $settings );
		}

		return $post_id;
	}

	/**
	 * Save a layout
	 *
	 * @since 2.0.0
	 *
	 * @param $post_id string ID of template, or ID of post with layout
	 * @param $data array All data passed from form to be saved
	 */
	public function save_layout( $post_id, $data ) {

		// DEBUG
		// echo '<pre>'; print_r($data); echo '</pre>';

		$sections = array('primary'=>array());
		$elements = array();
		$columns = array(); // Keep track of any columns of elements from "Columns" or "Hero Unit Slider" elements
		$element_id_list = array(); // For cleanup later on

		// Layout Sections
		if ( isset( $data['tb_builder_sections'] ) ) {

			$sections = array();

			foreach ( $data['tb_builder_sections'] as $section_id => $section ) {
				$sections[$section_id] = array(
					'label'		=> wp_kses( $section['label'], array() ),
					'display'	=> $this->clean( 'section', $section['display'] )
				);
			}

			// Store meta to post
			update_post_meta( $post_id, '_tb_builder_sections', $sections );

		}

		// Elements (by section)

		// Ensure that if any sections were left empty, we still save them as empty.
		foreach ( $sections as $section_id => $section ) {
			$elements[$section_id] = array();
		}

		// Elements data is sent with them separated into sections, which is how
		// we'll save them at the end, as well.
		if ( isset( $data['tb_builder_elements'] ) ) {
			foreach ( $data['tb_builder_elements'] as $section_id => $section_elements ) {

				$elements[$section_id] = array();

				foreach ( $section_elements as $element_id => $element ) {

					// Keep element ID list for cleanup later on
					$element_id_list[] = $element_id;

					// Separate columns
					if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
						if ( $element['type'] == 'columns' || $element['type'] == 'jumbotron_slider' ) {

							$total = 1;

							if ( $element['type'] == 'columns' ) {
								$total = 5;
							}

							for ( $i = 1; $i <= $total; $i++ ) {
							    if ( isset( $element['columns']['col_'.$i] ) ) {
							    	$columns[$element_id.'_col_'.$i] = $element['columns']['col_'.$i];
							    } else {
							    	$columns[$element_id.'_col_'.$i] = array(); // We need to save empty columns!
							    }
							}
							unset($element['columns']);
						}
					}

					// Sanitize label
					$element['label'] = wp_kses( $element['label'], array() );

					// Sanitize element's options
					if ( ! empty( $element['options'] ) ) {
						$element['options'] = $this->clean( $element['type'], $element['options'] );
					} else {
						$element['options'] = array();
					}

					// Element display options
					if ( isset( $element['display'] ) ) { // If using theme with framework prior to 2.5, this won't be set
						$element['display'] = $this->clean( $element['type'], $element['display'], true );
					} else {
						$element['display'] = array();
					}

					// And finally, add the element
					$elements[$section_id][$element_id] = $element;

				}
			}
		}

		// Store meta to post
		update_post_meta( $post_id, '_tb_builder_elements', $elements );

		// Columns
		if ( count($columns) > 0 ) {
			foreach ( $columns as $column_id => $column ) {

				$elements = array();

				if ( isset( $column['elements']) ) {
					$elements = $column['elements'];
				}

				if ( count($elements) > 0 ) {
					foreach ( $elements as $element_id => $element ) {

						// Sanitize type of content element
						$element_type = wp_kses( $element['type'], array() );

						// Sanitize options for element
						if ( ! empty( $element['options'] ) ) {
							$elements[$element_id]['options'] = $this->clean( $element_type, $element['options'] );
						} else {
							$elements[$element_id]['options'] = array();
						}

						// Sanitize display options for elements
						if ( ! empty( $element['display'] ) ) {
							$elements[$element_id]['display'] = $this->clean( $element_type, $element['display'], true );
						} else {
							$elements[$element_id]['display'] = array();
						}
					}
				}

				$column_data = array(
					'display'	=> $this->clean( 'column', $column['display'] ),
					'elements' 	=> $elements
				);

				// Save column of content elements
				update_post_meta( $post_id, '_tb_builder_'.$column_id, $column_data ); // "element_123_col_1"
			}
		}

		// Clean up meta - In order to avoid the layout post
		// getting cluttered with unused meta data, we'll loop
		// through and delete any columns that aren't associated
		// with any current elements in the layout.
		$element_id = '';
		$meta = get_post_meta( $post_id );

		if ( is_array($meta) && count($meta) > 0 ) {
			foreach ( $meta as $key => $value ) {
				if ( strpos( $key, 'tb_element_' ) !== false ) {

					$element_id = $key;
					$element_id = str_replace('_tb_builder_', '', $element_id);

					for ( $i = 1; $i <= 5; $i++ ) {
						$element_id = str_replace('_col_'.$i, '', $element_id);
					}

					if ( ! in_array( $element_id, $element_id_list ) ) {
						delete_post_meta( $post_id, $key );
					}

				}
			}
		}

		// Sidebar Layout (@deprecated)
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			if ( isset( $data['tb_layout_options'] ) ) {
				update_post_meta( $post_id, 'settings', $data['tb_layout_options'] );
			}
		}

		// Template Info
		if ( isset( $data['template_info'] ) && get_post_type($post_id) == 'tb_layout' ) {

			// Start post data to be updated with the ID
			$post_atts = array(
				'ID' 			=> $post_id,
				'post_title' 	=> $data['template_info']['post_title'],
				'post_name' 	=> $data['template_info']['post_name']
			);

			// Update Post info
			wp_update_post( $post_atts );

		}

		// Store version numbers that this layout is being saved with
		update_post_meta( $post_id, '_tb_builder_plugin_version_saved', TB_BUILDER_PLUGIN_VERSION );
		update_post_meta( $post_id, '_tb_builder_framework_version_saved', TB_FRAMEWORK_VERSION );

	}

	/**
	 * Sanitize element, section, or column options
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type of element, or "section" or "column"
	 * @param array $settings Settings that we're sanitizing
	 * @param bool $display If type of element, force to do sanitization on that elment's display options
	 */
	public function clean( $type, $settings, $display = false ) {

		$options = array();
		$clean = array();

		// Determine options we're sanitizing $settings against.
		if ( $type == 'section' ) {

			// Display options for sections (general)
			$options = $this->get_display_options('section');

		} else if ( $type == 'column' ) {

			// Display options for columns (general)
			$options = $this->get_display_options('column');

		} else {

			if ( $display ) {

				// Display options for the specific element type
				$options = $this->get_display_options('element', $type);

			} else {

				// Options for all elements through API
				$api = Theme_Blvd_Builder_API::get_instance();
				$elements = $this->get_elements();

				// And narrow down to the options fo rour specific element type
				if ( isset( $elements[$type]['options'] ) ) {
					$options = $elements[$type]['options'];
				}

			}

		}

		// Start sanitization
		foreach ( $options as $option ) {

			if ( ! isset( $option['id'] ) ) {
				continue;
			}

			if ( ! isset( $option['type'] ) ) {
				continue;
			}

			$option_id = $option['id'];

			// Set checkbox to false if it wasn't sent in the $_POST
			if ( $option['type'] == 'checkbox' ) {

				if ( ! empty( $option['inactive'] ) ) {

					if ( $option['inactive'] === 'true' ) {
						$settings[$option_id] = '1';
					} else if( $option['inactive'] === 'false' ) {
						$settings[$option_id] = '0';
					}

				} else {

					if ( isset( $settings[$option_id] ) && $settings[$option_id] !== '0'  ) {
						$settings[$option_id] = '1';
					} else {
						$settings[$option_id] = '0';
					}

				}
			}

			// Set each item in the multicheck to false if it wasn't sent in the $_POST
			if (  $option['type'] == 'multicheck' ) {
				if ( ! isset( $settings[$option_id] ) ) {
					$settings[$option_id] = array();
				}
			}

			// If option wasn't sent through, set it the default
			if ( ! isset( $settings[$option_id] ) ) {
				if ( isset( $option['std'] ) ) {
					$clean[$option_id] = $option['std'];
				} else {
					$clean[$option_id] = '';
				}
				continue;
			}

			// For slider option type, if the option set has crop setting attached,
			// we can apply that for saving the slider option.
			if ( $option['type'] == 'slider' ) {

				$crop = 'full';

				if ( ! empty( $settings[$option_id.'_crop'] ) ) {
					$crop = wp_kses( $settings[$option_id.'_crop'], array() );
				}

				$settings[$option_id]['crop'] = $crop;

			}

			// For button option type, set checkbox to false if it wasn't
			// sent in the $_POST
			if ( $option['type'] == 'button' ) {
				if ( ! isset( $settings[$option_id]['include_bg'] ) ) {
					$settings[$option_id]['include_bg'] = '0';
				}
				if ( ! isset( $settings[$option_id]['include_border'] ) ) {
					$settings[$option_id]['include_border'] = '0';
				}
			}

			// For a value to be submitted to database it must pass through a sanitization filter
			if ( has_filter( 'themeblvd_sanitize_' . $option['type'] ) ) {
				$clean[$option_id] = apply_filters( 'themeblvd_sanitize_'.$option['type'], $settings[$option_id], $option );
			}

		}

		return $clean;
	}

	/*--------------------------------------------*/
	/* Sample Layout Helpers
	/*--------------------------------------------*/

	/**
	 * Process images from meta values imported with sample layout.
	 *
	 * @since 2.0.0
	 */
	public function sample_images( $value, $key ) {

		// Section Data
		if ( $key == '_tb_builder_sections' ) {

			$this->sample_filters('add');

			if ( $value ) {
				foreach ( $value as $section_id => $section ) {
					$new[$section_id] = $section;
					$new[$section_id]['display'] = $this->clean( 'section', $section['display'] );
				}
			}

			$this->sample_filters('remove');

			return $new;
		}

		// Top-Level Elements
		if ( $key == '_tb_builder_elements' ) {

			$this->sample_filters('add');

			if ( $value ) {
				foreach ( $value as $section_id => $elements ) {

					$new[$section_id] = array();

					if ( $elements ) {
						foreach ( $elements as $element_id => $element ) {
							$new[$section_id][$element_id] = $element;
							$new[$section_id][$element_id]['display'] = $this->clean( $element['type'], $element['display'], true );
							$new[$section_id][$element_id]['options'] = $this->clean( $element['type'], $element['options'] );
						}
					}
				}
			}

			$this->sample_filters('remove');

			return $new;
		}

		// Columns
		if ( strpos( $key, '_col_' ) !== false ) {

			$this->sample_filters('add');

			$new = array();

			// Column display options
			if ( ! empty( $value['display'] ) ) {
				$new['display'] = $this->clean( 'column', $value['display'] );
			}

			// Column elements
			if ( ! empty( $value['elements'] ) ) {
				foreach ( $value['elements'] as $block_id => $block ) {
					$new['elements'][$block_id] = $block;
					$new['elements'][$block_id]['display'] = $this->clean( $block['type'], $block['display'], true );
					$new['elements'][$block_id]['options'] = $this->clean( $block['type'], $block['options'] );
				}
			}

			$this->sample_filters('remove');

			return $new;
		}

		return $value;
	}

	/**
	 * Add or Remove option filters for processing
	 * images of sample layouts.
	 *
	 * @since 2.0.0
	 */
	public function sample_filters( $todo ) {

		if ( $todo == 'add' ) {

			// Add filter for all option types we want to check
			add_filter( 'themeblvd_sanitize_text', array( $this, 'sample_option_filter' ) );
			add_filter( 'themeblvd_sanitize_textarea', array( $this, 'sample_option_filter' ) );
			add_filter( 'themeblvd_sanitize_upload', array( $this, 'sample_option_filter' ) );
			add_filter( 'themeblvd_sanitize_slider', array( $this, 'sample_option_filter' ) );
			add_filter( 'themeblvd_sanitize_logos', array( $this, 'sample_option_filter' ) );
			add_filter( 'themeblvd_sanitize_background_video', array( $this, 'sample_option_filter' ) );

			remove_filter( 'themeblvd_sanitize_slider', 'themeblvd_sanitize_slider' );
			remove_filter( 'themeblvd_sanitize_logos', 'themeblvd_sanitize_logos' );
			remove_filter( 'themeblvd_sanitize_upload', 'themeblvd_sanitize_upload' );

		} else if ( $todo == 'remove' ) {

			// Remove filter for all option types we want to check
			remove_filter( 'themeblvd_sanitize_text', array( $this, 'sample_option_filter' ) );
			remove_filter( 'themeblvd_sanitize_textarea', array( $this, 'sample_option_filter' ) );
			remove_filter( 'themeblvd_sanitize_upload', array( $this, 'sample_option_filter' ) );
			remove_filter( 'themeblvd_sanitize_slider', array( $this, 'sample_option_filter' ) );
			remove_filter( 'themeblvd_sanitize_logos', array( $this, 'sample_option_filter' ) );
			remove_filter( 'themeblvd_sanitize_background_video', array( $this, 'sample_option_filter' ) );

			add_filter( 'themeblvd_sanitize_slider', 'themeblvd_sanitize_slider' );
			add_filter( 'themeblvd_sanitize_logos', 'themeblvd_sanitize_logos' );
			add_filter( 'themeblvd_sanitize_upload', 'themeblvd_sanitize_upload' );

		}

	}

	/**
	 * The filter function added onto any option
	 * types we're filtering for processing images
	 * of sample layouts.
	 *
	 * @since 2.0.0
	 */
	public function sample_option_filter( $val ) {

		if ( is_string($val) ) {

			// text, textarea, simple upload
			$val = $this->sample_media_replace($val);

		} else if ( isset( $val['src'] ) ) {

			// complex upload (src)
			$val['src'] = $this->sample_media_replace($val['src']);

			if ( ! empty( $val['full'] ) ) {
				$val['full'] = $this->sample_media_replace($val['full']);
			}

		} else if ( isset( $val['mp4'] ) ) {

			// video background
			if ( ! empty( $val['mp4'] ) ) {
				$val['mp4'] = $this->sample_media_replace($val['mp4'], 'video');
			}

			if ( ! empty( $val['webm'] ) ) {
				$val['webm'] = $this->sample_media_replace($val['webm'], 'video');
			}

			if ( ! empty( $val['fallback'] ) ) {
				$val['fallback'] = $this->sample_media_replace($val['fallback'], 'img');
			}

		} else if ( is_array($val) ) {

			// sortable image group
			foreach ( $val as $key => $item ) {
				if ( isset($item['src']) && isset($item['thumb']) ) {
					$val[$key]['id'] = 0;
					$val[$key]['src'] = $this->sample_media_replace($item['src']);
					$val[$key]['thumb'] = $this->sample_media_replace($item['thumb']);
				} else {
					unset($val[$key]);
				}
			}
		}

		return $val;
	}

	/**
	 * For processing images of sample layouts, take
	 * a string like [img]example.jpg[/img] and change
	 * it to the full URL of the image located within
	 * the Builder plugin on the user's server.
	 *
	 * @since 2.0.0
	 */
	public function sample_media_replace( $str, $media = 'img' ) {

		$pattern = sprintf( '/%s(.*?)%s/', preg_quote('['.$media.']', '/'), preg_quote('[/'.$media.']', '/') );

		preg_match_all( $pattern, $str, $img );

		$find = $img[0];
		$replace = $img[1];

		foreach ( $find as $key => $val ) {
			$url = $this->sample_asset_uri . $replace[$key];
			$str = str_replace( $val, $url, $str );
		}

		return $str;
	}

	/*--------------------------------------------*/
	/* Builder (for Edit Page screen)
	/*--------------------------------------------*/

	/**
	 * Add a meta box for editing/adding layout.
	 *
	 * @since 1.1.0
	 */
	public function builder_init() {

		global $pagenow;
		global $typenow;

		$post_types = apply_filters( 'themeblvd_editor_builder_post_types', array('page') );

		if ( $post_types ) {
			foreach ( $post_types as $post_type ) {
				if ( ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) && $typenow == $post_type ) {

					add_action( 'save_post', array( $this, 'save_post' ) );
					add_action( 'edit_form_after_title', array( $this, 'builder' ) );

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
	public function save_post( $post_id ) {

		global $_POST;

		// echo '<pre>'; print_r($_POST); echo '</pre>';
		// die();

		// Verify that this coming from the edit post page.
		if ( ! isset( $_POST['action'] ) || $_POST['action'] != 'editpost' ) {
			return;
		}

		// Verfiy nonce
		if ( ! isset( $_POST['tb_nonce'] ) || ! wp_verify_nonce( $_POST['tb_nonce'], 'tb_save_layout' ) ) {
			return;
		}

		// Save template sync
		if ( ! empty( $_POST['_tb_custom_layout'] ) ) {
			$template = wp_kses( $_POST['_tb_custom_layout'], array() );
			update_post_meta( $post_id, '_tb_custom_layout', $template );
		}

		// Save layout to post
		$this->save_layout( $post_id, $_POST );

	}

	/**
	 * Display Builder in WP's editor.
	 *
	 * @since 2.0.0
	 */
	public function builder( $post ) {

		// Verify layout data
		$data = new Theme_Blvd_Layout_Builder_Data( $post->ID );
		$data->verify('elements');
		$data->verify('info');
		$data->finalize();

		// Link to "Templates" section of WP Admin
		$templates_link = sprintf('<a href="%s" target="_blank">%s</a>', esc_url( add_query_arg( array('page' => $this->id), admin_url('admin.php') ) ), esc_html__('Templates', 'theme-blvd-layout-builder') );

		// ID of template to sync with (optional)
		$sync_id = get_post_meta( $post->ID, '_tb_custom_layout', true );

		// Classes to show or hide the sections
		$sync_post_id = 0;
		$edit_hide = '';
		$sync_hide = 'hide';

		if ( $sync_id ) {
			$sync_post_id = themeblvd_post_id_by_name( $sync_id, 'tb_layout' );
			$edit_hide = 'hide';
			$sync_hide = '';
		}
		?>
		<div id="tb-editor-builder" class="<?php if(get_post_meta($post->ID, '_wp_page_template', true) == 'template_builder.php') echo 'template-active'; ?>">
			<div id="builder_blvd">
				<div id="optionsframework" class="tb-options-js">

					<input type="hidden" name="tb_nonce" value="<?php echo wp_create_nonce('tb_save_layout'); ?>" />
					<input type="hidden" name="tb_post_id" value="<?php echo esc_attr($post->ID); ?>" />

					<!-- HEADER (start) -->

					<div class="meta-box-nav clearfix">

						<div class="ajax-overlay add-element"></div>
						<div class="ajax-overlay sync-overlay <?php echo $sync_hide; ?>"></div>

						<div class="icon-holder">
							<span class="tb-loader ajax-loading">
								<i class="tb-icon-spinner"></i>
							</span>
							<i class="tb-icon-commercial-building"></i>
						</div>

						<div class="select-layout apply tb-tooltip-link" data-tooltip-text="<?php esc_attr_e('Select a starting point for this page\'s custom layout.', 'theme-blvd-layout-builder'); ?>">
							<?php echo $this->layout_select( '', 'apply', '_tb_apply_layout', $post->ID ); ?>
						</div>

						<div class="select-layout sync tb-tooltip-link" data-tooltip-text="<?php esc_attr_e('Select a template to sync this page\'s custom layout with.', 'theme-blvd-layout-builder'); ?>">
							<?php echo $this->layout_select( $sync_id, 'sync', '_tb_custom_layout' ); ?>
						</div>

						<a href="#" id="save-new-template" class="button-secondary"><?php esc_html_e('Save as Template', 'theme-blvd-layout-builder'); ?></a>

					</div><!-- .meta-box-nav (end) -->

					<!-- HEADER (end) -->

					<!-- EDIT LAYOUT (start) -->

					<div id="tb-edit-layout" class="<?php echo $edit_hide; ?>">

						<div class="ajax-overlay full-overlay">
							<span class="tb-loader ajax-loading">
								<i class="tb-icon-spinner"></i>
							</span>
						</div>

						<div class="ajax-mitt">
							<?php $this->edit_layout( $post->ID ); ?>
						</div><!-- .ajax-mitt (end) -->

					</div><!-- #tb-edit-layout (end) -->

					<!-- EDIT LAYOUT (end) -->

					<!-- TEMPLATE SYNC (start) -->

					<div id="tb-sync-layout" class="<?php echo $sync_hide; ?>">
						<h3><i class="tb-icon-arrows-ccw"></i><?php esc_html_e('Synced with Template:', 'theme-blvd-layout-builder'); ?> <span class="title"><?php echo get_the_title($sync_post_id); ?></span></h3>
						<p><?php printf( esc_html__('This page\'s layout is currently synced with the template selected above, which can only be edited from the %s page.', 'theme-blvd-layout-builder'), $templates_link ); ?></p>
						<p><?php printf( '<a href="#" id="tb-template-unsync" class="button-secondary unsync">%s</a>', esc_html__('Unsync Layout', 'theme-blvd-layout-builder') ); ?></p>
					</div><!-- #tb-sync-layout (end) -->

					<!-- TEMPLATE SYNC (end) -->

					<!-- FOOTER (start) -->

					<div class="tb-builder-footer">
						<p><i class="tb-icon-logo-stroke wp-ui-text-highlight"></i> Layout Builder by <a href="http://www.themeblvd.com" target="_blank">Theme Blvd</a> &#8212; <?php esc_html_e('Version', 'theme-blvd-layout-builder'); ?>: <?php echo TB_BUILDER_PLUGIN_VERSION; ?>
					</div>

					<!-- FOOTER (end) -->

				</div><!-- #optionsframework (end) -->
			</div><!-- #builder_blvd (end) -->
		</div><!-- #tb-editor-builder (end) -->
		<?php
	}

	/*--------------------------------------------*/
	/* General Builder Parts
	/*--------------------------------------------*/

	/**
	 * Edit a layout, template or in a page
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id ID of template, or ID of post with layout
	 * @param bool $template Only TRUE if editing a template
	 */
	public function edit_layout( $post_id = 0, $template = false ) {

		$api = Theme_Blvd_Builder_API::get_instance();
		$elements = $this->get_elements(); // Elements that can be used in Builder, NOT elements saved to current layout

		$saved_elements = get_post_meta( $post_id, '_tb_builder_elements', true );

		$saved_sections = get_post_meta( $post_id, '_tb_builder_sections', true );

		if ( ! $saved_elements || ! $saved_sections ) {

			if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) {

				// Working with a template just created
				$saved_elements = $saved_sections = array(
					'primary' => array()
				);

			} else {

				// If we're using an old theme prior to 2.5, there will
				// never be saved section data
				$saved_sections = array(
					'featured' => array(),
					'primary' => array(),
					'featured_below' => array()
				);

				// Working with a temlate just created
				if ( ! $saved_elements ) {
					$saved_elements = array(
						'featured' => array(),
						'primary' => array(),
						'featured_below' => array()
					);
				}
			}

		}
		?>
		<div class="manage-elements">

			<div class="ajax-overlay add-element"></div>

			<h2><?php esc_html_e( 'Manage Elements', 'theme-blvd-layout-builder' ); ?></h2>

			<div class="tb-fancy-select tb-tooltip-link" data-tooltip-text="<?php esc_attr_e('Type of Element to Add', 'theme-blvd-layout-builder'); ?>">
				<select>
					<?php
					foreach ( $elements as $element ) {
						if ( $api->is_element( $element['info']['id'] ) ) {
							echo '<option value="'.esc_attr($element['info']['id']).'">'.esc_attr($element['info']['name']).'</option>';
						}
					}
					?>
				</select>
				<span class="trigger"></span>
				<span class="textbox"></span>
			</div><!-- .tb-fancy-select (end) -->

			<a href="#" id="add_new_element" class="button-secondary"><?php esc_html_e( 'Add Element', 'theme-blvd-layout-builder' ); ?></a>

			<?php if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) : ?>
				<a href="#" id="add_new_section" class="button-secondary"><?php esc_html_e( 'Add Section', 'theme-blvd-layout-builder' ); ?></a>
			<?php endif; ?>

			<span class="tb-loader ajax-loading">
				<i class="tb-icon-spinner"></i>
			</span>

			<div class="clear"></div>
		</div><!-- .manage-elements (end) -->

		<div id="builder">
			<?php
			if ( is_array($saved_sections) && count($saved_sections) > 0 ) {
				foreach ( $saved_sections as $section_id => $section ) {

					$elements = array();

					if ( isset( $saved_elements[$section_id] ) ) {
						$elements = $saved_elements[$section_id];
					}

					$this->edit_section( $post_id, $section_id, $elements, $section );
				}
			}
			?>
		</div><!-- #builder (end) -->

		<?php if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) && ! $template ) : ?>
			<div class="sidebar-layout-wrap">

				<div class="title">
					<h2><?php esc_html_e( 'Sidebar Layout', 'theme-blvd-layout-builder' ); ?></h2>
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
							'desc'		=> __( 'Select how you\'d like the sidebar(s) arranged in this layout. Your site-wide default sidebar layout can be set from your Theme Options page.<br><br><strong>Note: The sidebar layout is only applied to the "Primary Area" of the custom layout.</strong>', 'theme-blvd-layout-builder' ),
							'type' 		=> 'images',
							'options' 	=> $sidebar_layouts
						)
					);

					$layout_settings = get_post_meta($post_id, 'settings', true);

					if ( ! $layout_settings ) {
						$layout_settings = array('sidebar_layout' => 'default');
					}

					// Display form element
					$form = themeblvd_option_fields( 'tb_layout_options', $options, $layout_settings, false );
					echo $form[0];
					?>
				</div>
			</div><!-- .sidebar-layout-wrap (end) -->
		<?php endif; ?>

		<?php
	}

	/**
	 * Generates a section of elements.
	 *
	 * @since 2.0.0
	 *
	 * @param $post_id string ID of template, or ID of post with layout
	 * @param string $section_id ID of the current section
	 * @param array $elements The elements to display
	 * @param array $data Section data - display options, label, etc
	 */
	public function edit_section( $post_id, $section_id = '', $elements = array(), $data = array() ) {

		// Get Builder API
		$api = Theme_Blvd_Builder_API::get_instance();

		// If no section ID, it means we're starting a new section.
		if ( ! $section_id ) {
			$section_id = uniqid('section_');
		}

		// Label
		$label = __('Section Label', 'theme-blvd-layout-builder');

		if ( isset( $data['label'] ) ) {
			$label = $data['label'];
		}

		// Display options
		$display = array();

		if ( isset( $data['display'] ) ) {
			$display = $data['display'];
		}

		// Anything needed for older themes
		$legacy = false;

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			$legacy = true;

			switch ( $section_id ) {
				case 'primary' :
					$label = __('Primary Area', 'theme-blvd-layout-builder');
					break;
				case 'featured' :
					$label = __('Featured Above', 'theme-blvd-layout-builder');
					break;
				case 'featured_below' :
					$label = __('Featured Below', 'theme-blvd-layout-builder');
			}
		}
		?>
		<div id="<?php echo $section_id; ?>" class="element-section">

			<!-- SECTION HEADER (start) -->

			<div class="section-header">

				<?php if ( $legacy ) : ?>

					<div class="section-label legacy">
						<span class="label-text"><?php echo esc_html($label); ?></span>
					</div>

				<?php else : ?>

					<?php if ( $section_id == 'primary' ) : ?>
						<span data-tooltip-text="<?php esc_attr_e('Section cannot be deleted', 'theme-blvd-layout-builder'); ?>" class="tb-tooltip-link locked-section"><i class="tb-icon-lock"></i></span>
					<?php else : ?>
						<a href="#" title="<?php esc_attr_e('Delete Section', 'theme-blvd-layout-builder'); ?>" data-confirm="<?php esc_attr_e('Are you sure you want to delete this section and its elements?', 'theme-blvd-layout-builder'); ?>" class="tb-tooltip-link delete-section"><i class="tb-icon-cancel-circled"></i></a>
					<?php endif; ?>

					<div class="section-label dynamic-label">
						<span class="label-text"><?php echo esc_html($label); ?></span>
						<input type="text" class="label-input" name="tb_builder_sections[<?php echo esc_attr($section_id); ?>][label]" value="<?php echo esc_attr($label); ?>" />
					</div>

					<div class="section-options clearfix">
						<a href="#" class="edit-section-display tb-tooltip-link" data-target="<?php echo esc_attr($section_id); ?>_background_form" data-title="<?php esc_attr_e('Section Display', 'theme-blvd-layout-builder'); ?>" data-tooltip-text="<?php esc_attr_e('Section Display', 'theme-blvd-layout-builder'); ?>">
							<i class="tb-icon-picture"></i>
						</a>
						<a href="#" class="shift-section-down tb-tooltip-link" data-tooltip-text="<?php esc_attr_e('Shift Section Down', 'theme-blvd-layout-builder'); ?>">
							<i class="tb-icon-circle-arrow-down"></i>
						</a>
						<a href="#" class="shift-section-up tb-tooltip-link" data-tooltip-text="<?php esc_attr_e('Shift Section Up', 'theme-blvd-layout-builder'); ?>">
							<i class="tb-icon-circle-arrow-up"></i>
						</a>
					</div><!-- .section-options (end) -->

				<?php endif; ?>

			</div><!-- .section-header (end) -->

			<!-- SECTION HEADER (end) -->

			<!-- SECTION BACKGROUND HIDDEN FORM (start) -->

			<?php if ( ! $legacy ) : ?>
				<div class="section-background-options-wrap hide">
					<div id="<?php echo esc_attr($section_id); ?>_background_form" class="section-background-options">
						<?php
						$display_options = $this->get_display_options( 'section' );
						$display_form = themeblvd_option_fields( 'tb_builder_sections['.$section_id.'][display]', $display_options, $display, false );
						echo $display_form[0];
						?>
					</div>
				</div>
			<?php endif; ?>

			<!-- SECTION BACKGROUND HIDDEN FORM (end) -->

			<!-- SECTION ELEMENTS (start) -->

			<div class="elements sortable <?php echo esc_attr($section_id); if ( ! $elements ) echo ' empty'; ?>">
				<?php
				if ( $elements ) {
					foreach ( $elements as $element_id => $element ) {
						if ( $api->is_element( $element['type'] ) ) {

							$label = __('Element Label', 'theme-blvd-layout-builder');

							if ( isset( $element['label'] ) ) {
								$label = $element['label'];
							}

							$display = null;
							if ( isset( $element['display'] ) ) {
								$display = $element['display'];
							}

							$this->edit_element( $post_id, $section_id, $element['type'], $element_id, $element['options'], $display, $label );
						}
					}
				}
				?>
			</div><!-- .sortable (end) -->

			<!-- SECTION ELEMENTS (end) -->

		</div><!-- .section (end) -->
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
	 * @param $post_id string ID of template, or ID of post with layout
	 * @param string $section_id ID of section this element is in
	 * @param string $element_type type of element
	 * @param string $element_id ID for individual slide
	 * @param array $element_settings Any current settings for current element
	 * @param array $element_display Any current display settings for element
	 * @param array $element_display Any current settings for element's display
	 * @param array $column_data If we don't want column data to be pulled from meta, we can feed it in here
	 */
	public function edit_element( $post_id, $section_id, $element_type, $element_id = '', $element_settings = null, $element_display = null, $element_label = null, $column_data = null ) {

		// If no element ID, it means we're starting a new element.
		if ( ! $element_id ) {
			$element_id = uniqid( 'element_'.rand() );
		}

		$api = Theme_Blvd_Builder_API::get_instance();
		$elements = $this->get_elements();
		$field_name = 'tb_builder_elements['.$section_id.']['.$element_id.'][options]';

		// Options
		$form = array();

		if ( ! empty( $elements[$element_type]['options'] ) ) {
			$form = themeblvd_option_fields( $field_name, $elements[$element_type]['options'], $element_settings, false );
		}

		if ( ! empty( $form[0] ) ) {
			$form[0] = str_replace('id="content"', 'id="option-content"', $form[0]); // Having anything with ID "content" will screw up the WP editor
		}
		?>
		<div id="<?php echo esc_attr($element_id); ?>" class="widget element-options" data-field-name="<?php echo esc_attr($field_name); ?>">

			<div class="widget-name top-widget-name widget-name-closed">

				<i class="tb-icon-sort"></i>

				<a href="#" class="widget-name-arrow tb-tooltip-link" data-tooltip-toggle="1" data-tooltip-text-1="<?php esc_attr_e('Show Element Options', 'theme-blvd-layout-builder'); ?>" data-tooltip-text-2="<?php esc_attr_e('Hide Element Options', 'theme-blvd-layout-builder'); ?>">
					<i class="tb-icon-up-dir"></i>
				</a>

				<?php if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) : ?>
					<a href="#" class="edit-element-display tb-tooltip-link" data-target="<?php echo esc_attr($element_id); ?>_background_form" data-title="<?php esc_attr_e('Element Display', 'theme-blvd-layout-builder'); ?>" data-tooltip-text="<?php esc_attr_e('Element Display', 'theme-blvd-layout-builder'); ?>">
						<i class="tb-icon-picture"></i>
					</a>
				<?php endif; ?>

				<div class="element-label dynamic-label" data-tooltip-text="<?php esc_attr_e('Click to Edit Label', 'theme-blvd-layout-builder'); ?>">
					<?php $label = $element_label !== null ? $element_label : __('Element Label', 'theme-blvd-layout-builder'); ?>
					<span class="label-text"><?php echo esc_html($label); ?></span>
					<input type="text" class="label-input" name="<?php echo esc_attr( "tb_builder_elements[$section_id][$element_id][label]" ); ?>'" value="<?php echo esc_attr($label); ?>" />
				</div>

				<h3><?php echo esc_html( $elements[$element_type]['info']['name'] ); ?></h3>

				<div class="clear"></div>

			</div><!-- .element-name (end) -->

			<?php if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) : ?>
				<div class="element-display-options-wrap hide">
					<div id="<?php echo $element_id; ?>_background_form" class="element-display-options">
						<?php
						$display_options = $this->get_display_options( 'element', $element_type );
						$display_form = themeblvd_option_fields( 'tb_builder_elements['.$section_id.']['.$element_id.'][display]', $display_options, $element_display, false );
						echo $display_form[0];
						?>
					</div>
				</div>
			<?php endif; ?>

			<div class="widget-content hide <?php echo sanitize_html_class("element-$element_type"); ?>">

				<input type="hidden" class="element-type" name="<?php echo esc_attr( "tb_builder_elements[$section_id][$element_id][type]" ); ?>" value="<?php echo esc_attr($element_type); ?>" />

				<!-- ELEMENT OPTIONS (start) -->

				<?php if ( ($element_type == 'columns' || $element_type == 'jumbotron_slider') && version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) :

					if ( $element_type == 'columns' ) {

						$col_count = 2; // Default
						$col_config = '1/2 - 1/2'; // Default

						if ( $element_settings && ! empty( $element_settings['setup'] ) ) {
							$col_count = count( explode('-', $element_settings['setup'] ) );
							$col_config = str_replace( '-', ' - ', $element_settings['setup'] );
						}

					} else {

						$col_count = 1; // Always only 1 column

					}
					?>
					<div class="columns-header clearfix">
						<?php if ( $element_type == 'columns' ) : ?>
							<span class="info">
								<span class="col-count"><?php printf( esc_html( _n( '1 Column', '%s Columns', $col_count, 'theme-blvd-layout-builder' ) ), $col_count ); ?></span>
								<span class="col-config"><?php echo esc_attr($col_config); ?></span>
							</span>
						<?php endif; ?>
						<span class="action"><a href="#" class="edit-columns-config" data-showing="0" data-text-show="<?php esc_attr_e('Edit Setup', 'theme-blvd-layout-builder'); ?>" data-text-hide="<?php esc_attr_e('Hide Setup', 'theme-blvd-layout-builder'); ?>"><?php esc_html_e('Edit Setup', 'theme-blvd-layout-builder'); ?></a>
					</div><!-- .columns-header (end) -->
				<?php endif; ?>

				<?php if ( $form ) : ?>
					<?php echo $form[0]; ?>
				<?php endif; ?>

				<!-- ELEMENT OPTIONS (end) -->

				<?php if ( ($element_type == 'columns' || $element_type == 'jumbotron_slider') && version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) : ?>

					<!-- COLUMNS (start) -->

					<div class="columns-config columns-<?php echo $col_count; ?>">

						<?php
						$total = 1;

						if ( $element_type == 'columns' ) {
							$total = 5;
						}
						?>

						<?php for ( $i = 1; $i <= $total; $i++ ) : ?>

							<?php
							// Saved column data
							$display_settings = array();
							$saved_blocks = array();

							if ( isset( $column_data['col_'.$i] ) ) {

								// Column data was forced in through function's parameters.

								if ( isset( $column_data['col_'.$i]['display'] ) ) {
									$display_settings = $column_data['col_'.$i]['display'];
								}

								if ( isset( $column_data['col_'.$i]['elements'] ) ) {
									$saved_blocks = $column_data['col_'.$i]['elements'];
								}

							} else {

								// Get content blocks for column
								$column_data = get_post_meta( $post_id, '_tb_builder_'.$element_id.'_col_'.$i, true );

								if ( isset( $column_data['display'] ) ) {
									$display_settings = $column_data['display'];
								}

								if ( isset( $column_data['elements'] ) ) {
									$saved_blocks = $column_data['elements'];
								}
							}
							?>

							<div class="column col-<?php echo $i; ?>">
								<div class="column-inner">

									<input class="col-num" type="hidden" value="<?php echo $i; ?>" />

									<div class="column-heading">

										<?php if ( $element_type == 'columns' ) : ?>

											<?php if ( $col_count > 1 ) : ?>
												<h4><?php printf( esc_html__('Column %s', 'theme-blvd-layout-builder'), $i ); ?></h4>
											<?php else : ?>
												<h4><?php esc_html_e('Elements', 'theme-blvd-layout-builder'); ?></h4>
											<?php endif; ?>

											<a href="#" class="tb-element-display-options edit-element-display tb-tooltip-link" data-target="<?php echo esc_attr($element_id); ?>_col_<?php echo $i; ?>_background_form" data-title="<?php esc_attr_e('Column Display', 'theme-blvd-layout-builder'); ?>" data-tooltip-text="<?php esc_attr_e('Column Display', 'theme-blvd-layout-builder'); ?>">
												<i class="tb-icon-picture"></i>
											</a>

											<a href="#" class="add-block tb-tooltip-link" data-tooltip-text="<?php esc_attr_e('Add Element', 'theme-blvd-layout-builder'); ?>" data-tooltip-position="top">
												<i class="tb-icon-plus-circled"></i>
											</a>

											<div class="tb-fancy-select condensed tb-tooltip-link" data-tooltip-text="<?php esc_attr_e('Type of Element to Add', 'theme-blvd-layout-builder'); ?>">
												<select class="block-type">
													<?php
													foreach ( $elements as $block ) {
														if ( $api->is_block( $block['info']['id'] ) ) {
															echo '<option value="'.esc_attr($block['info']['id']).'">'.esc_html($block['info']['name']).'</option>';
														}
													}
													?>
												</select>
												<span class="trigger"></span>
												<span class="textbox"></span>
											</div><!-- .tb-fancy-select (end) -->

											<a href="#" class="add-block button-secondary" title="<?php esc_attr_e('Add Element', 'theme-blvd-layout-builder'); ?>"><?php esc_html_e('Add Element', 'theme-blvd-layout-builder'); ?></a>

										<?php else : ?>

											<h4><?php esc_html_e('Hero Units', 'theme-blvd-layout-builder'); ?></h4>
											<input type="hidden" class="block-type" value="jumbotron" />
											<a href="#" class="add-block button-secondary" title="<?php esc_attr_e('Add Hero Unit', 'theme-blvd-layout-builder'); ?>"><?php esc_html_e('Add Hero Unit', 'theme-blvd-layout-builder'); ?></a>

										<?php endif; ?>

										<div class="clear"></div>

									</div><!-- .column-heading (end) -->

									<div class="element-display-options-wrap hide">
										<div id="<?php echo esc_attr($element_id); ?>_col_<?php echo $i; ?>_background_form" class="element-display-options">
											<?php
											$display_options = $this->get_display_options('column');
											$display_form = themeblvd_option_fields( 'tb_builder_elements['.$section_id.']['.$element_id.'][columns][col_'.$i.'][display]', $display_options, $display_settings, false );
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

													$block_display = array();

													if ( isset( $block['display'] ) ) {
														$block_display = $block['display'];
													}

													$block_options = array();

													if ( isset( $block['options'] ) ) {
														$block_options = $block['options'];
													}

													$this->edit_block( $section_id, $element_id, $block['type'], $block_id, $i, $block_options, $block_display );
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

					<!-- COLUMNS (end) -->

				<?php endif; ?>

				<div class="submitbox widget-footer clearfix">
					<a href="#<?php echo esc_attr($element_id); ?>" class="submitdelete delete-element" title="<?php esc_attr_e( 'Are you sure you want to delete this element?', 'theme-blvd-layout-builder' ); ?>"><?php esc_html_e( 'Delete', 'theme-blvd-layout-builder' ); ?></a>
					<a href="#<?php echo esc_attr($element_id); ?>" class="duplicate-element tb-tooltip-link" data-tooltip-text="<?php esc_attr_e( 'Duplicate Element', 'theme-blvd-layout-builder' ); ?>"><i class="tb-icon-copy"></i></a>
				</div><!-- .widget-footer (end) -->

			</div><!-- .element-content (end) -->
		</div>
		<?php
	}

	/**
	 * Generates the an indivdual panel to edit a "block"
	 * which is just an element within a parent columns element.
	 *
	 * @since 2.0.0
	 *
	 * @param string $section_id ID of the section the parent element belongs to
	 * @param string $element_id ID of the parent element that contains this block
	 * @param string $block_type Type of block (i.e. element)
	 * @param string $block_id ID of block
	 * @param int $col_num Number of column block is located in
	 * @param array $block_settings any current settings for block
	 * @param array $block_display any current display settings for block
	 */
	public function edit_block( $section_id, $element_id, $block_type, $block_id, $col_num, $block_settings = null, $block_display = null ) {

		$blocks = $this->get_elements();
		$field_name = 'tb_builder_elements['.$section_id.']['.$element_id.'][columns][col_'.$col_num.'][elements]['.$block_id.']';

		// Options form
		$block_form = array();

		if ( $block_type == 'html' ) {
			$blocks[$block_type]['options']['html']['type'] = 'textarea';
		}

		if ( ! empty( $blocks[$block_type]['options'] ) ) {
			$block_form = themeblvd_option_fields( $field_name.'[options]', $blocks[$block_type]['options'], $block_settings, false );
		}

		if ( ! empty( $block_form[0] ) ) {
			$block_form[0] = str_replace('id="content"', 'id="option-content"', $block_form[0]); // Having anything with ID "content" will screw up the WP editor
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
		?>
		<div id="<?php echo esc_attr($block_id); ?>" class="widget block block-widget" data-element-id="<?php echo esc_attr($element_id); ?>" data-field-name="<?php echo esc_attr( $field_name.'[options]' ); ?>">

			<div class="block-widget-name block-widget-name-closed clearfix">

				<i class="tb-icon-sort"></i>

				<a href="#" class="block-widget-name-arrow">
					<i class="tb-icon-up-dir"></i>
				</a>

				<h3><?php echo esc_html( $blocks[$block_type]['info']['name'] ); ?></h3>

			</div><!-- .block-name (end) -->

			<div class="block-widget-content clearfix hide">

				<a href="#<?php echo esc_attr($block_id); ?>" class="delete-block" title="<?php esc_attr_e( 'Are you sure you want to delete this element?', 'theme-blvd-layout-builder' ); ?>"><?php esc_html_e( 'Delete', 'theme-blvd-layout-builder' ); ?></a>

				<nav class="block-nav">

					<?php if ( $options ) : ?>
						<a href="#" class="tb-block-options-link tb-tooltip-link" data-target="<?php echo esc_attr($block_id); ?>_options_form" data-tooltip-text="<?php esc_attr_e('Edit Options', 'theme-blvd-layout-builder'); ?>" data-title="<?php echo esc_attr( $blocks[$block_type]['info']['name'] ); ?>">
							<i class="tb-icon-cog"></i>
						</a>
					<?php endif; ?>

					<?php if ( isset( $blocks[$block_type]['options']['html'] ) ) : ?>
						<a href="#" class="tb-textarea-code-link tb-block-code-link tb-tooltip-link" data-tooltip-text="<?php esc_attr_e('Edit Code', 'themeblvd'); ?>" data-title="<?php esc_attr_e('Edit Code', 'theme-blvd-layout-builder'); ?>" data-target="<?php echo esc_attr($block_id); ?>">
							<i class="tb-icon-code"></i>
						</a>
					<?php endif; ?>

					<a href="#" class="edit-block-display tb-tooltip-link" data-target="<?php echo esc_attr($block_id); ?>_background_form" data-title="<?php esc_attr_e('Edit Display', 'theme-blvd-layout-builder'); ?>" data-tooltip-text="<?php esc_attr_e('Edit Display', 'theme-blvd-layout-builder'); ?>">
						<i class="tb-icon-picture"></i>
					</a>

					<a href="#" class="duplicate-block tb-tooltip-link" data-tooltip-text="<?php esc_attr_e( 'Duplicate', 'theme-blvd-layout-builder' ); ?>">
						<i class="tb-icon-copy"></i>
					</a>

				</nav><!--.block-nav (end) -->
			</div><!-- .block-widget-content (end) -->

			<div class="element-display-options-wrap hide">
				<div id="<?php echo $block_id; ?>_background_form" class="element-display-options">
					<?php
					$display = $this->get_display_options( 'block', $block_type );
					$display_form = themeblvd_option_fields( $field_name.'[display]', $display, $block_display, false );
					echo $display_form[0];
					?>
				</div>
			</div>

			<div class="block-options <?php echo sanitize_html_class("block-$block_type"); ?>">
				<div id="<?php echo esc_attr($block_id); ?>_options_form" class="block-form">
					<input type="hidden" name="<?php echo esc_attr($field_name); ?>[type]" value="<?php echo esc_attr($block_type); ?>" />
					<?php if ( $block_form ) : ?>
						<?php echo $block_form[0]; ?>
					<?php endif; ?>
				</div><!-- .widget-form (end) -->
			</div><!-- .mini-widget-content (end) -->
		</div>
		<?php
	}

	/**
	 * Builds a select menu of current custom layouts.
	 *
	 * @since 1.1.0
	 *
	 * @param string $current Current custom layout to be selected
	 */
	public function layout_select( $current = '', $type = 'apply', $name = '', $post_id = 0 ) {

		$output = '';

		$args = array(
			'post_type'		=> 'tb_layout',
			'order'			=> 'ASC',
			'orderby'		=> 'title',
			'numberposts'	=> -1
		);
		$custom_layouts = get_posts($args);

		$start_text = '';

		if ( $type == 'apply' ) {
			$start_text = __('Apply Starting Point', 'theme-blvd-layout-builder');
		} else if ( $type == 'sync' ) {
			$start_text = __('Sync with Template', 'theme-blvd-layout-builder');
		}

		$output .= '<div class="tb-fancy-select condensed">';
		$output .= sprintf( '<select id="tb-template-%s" name="%s">', esc_attr($type), esc_attr($name) );

		if ( $start_text ) {
			$output .= sprintf( '<option value="">- %s -</option>', esc_html($start_text) );
		}

		if ( $custom_layouts ) {

			if ( $type == 'apply' ) {
				$output .= '<optgroup label="'.esc_attr__('Templates', 'theme-blvd-layout-builder').'">';
			}

			foreach ( $custom_layouts as $custom_layout ) {
				$output .= sprintf( '<option value="%s" %s>%s</option>', esc_attr($custom_layout->post_name), selected( $custom_layout->post_name, $current, false ), esc_html($custom_layout->post_title) );
			}

			if ( $type == 'apply' ) {
				$output .= '</optgroup>';
			}

		}

		if ( $type == 'apply' ) {

			$samples = themeblvd_get_sample_layouts();
			$sample_layouts = array();

			if ( $samples ) {

				$output .= '<optgroup label="'.esc_attr__('Sample Layouts', 'theme-blvd-layout-builder').'">';

				foreach ( $samples as $sample ) {
					$output .= sprintf( '<option value="%s=>%s" %s>%s</option>', esc_attr($post_id), esc_attr($sample['id']), selected( $sample['id'], $current, false ), esc_html($sample['name']) );
				}

				$output .= '</optgroup>';
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
	 *
	 * @param string $type The Type of options display - section, element, block, or column
	 * @param string $element_type If section type is element, the element type
	 */
	public function get_display_options( $type, $element_type = null ) {

		$options = array(
			'type' => array(
				'id' 	=> 'type',
				'std'	=> $type,
				'type' 	=> 'hidden'
			)
		);

		if ( $type == 'section' || $type == 'column' ) {

			$options['subgroup_start'] = array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide-toggle'
			);

			$options['bg_type'] = array(
				'id'		=> 'bg_type',
				'name'		=> __('Apply Background', 'theme-blvd-layout-builder'),
				'desc'		=> __('Select if you\'d like to apply a custom background and how you want to control it.', 'theme-blvd-layout-builder'),
				'std'		=> 'none',
				'type'		=> 'select',
				'options'	=> themeblvd_get_bg_types($type),
				'class'		=> 'trigger'
			);

			$options['text_color'] = array(
				'id'		=> 'text_color',
				'name'		=> __('Text Color'),
				'desc'		=> __('Only use this option, if needed, depending on how your theme is setup. If you\'re using a dark background color, and you need to display light text you can do that here, and vice versa.', 'theme-blvd-layout-builder'),
				'std'		=> 'none',
				'type'		=> 'select',
				'options'	=> array(
					'none'	=> __('None', 'theme-blvd-layout-builder'),
					'dark'	=> __('Force Dark Text', 'theme-blvd-layout-builder'),
					'light'	=> __('Force Light Text', 'theme-blvd-layout-builder')
				),
				'class'		=> 'hide receiver receiver-color receiver-texture receiver-image receiver-slideshow receiver-video'
			);

			$options['bg_color'] = array(
				'id'		=> 'bg_color',
				'name'		=> __('Background Color', 'theme-blvd-layout-builder'),
				'desc'		=> __('Select a background color.', 'theme-blvd-layout-builder'),
				'std'		=> '#f8f8f8',
				'type'		=> 'color',
				'class'		=> 'hide receiver receiver-color receiver-texture receiver-image'
			);

			$options['bg_color_opacity'] = array(
				'id'		=> 'bg_color_opacity',
				'name'		=> __('Background Color Opacity', 'theme-blvd-layout-builder'),
				'desc'		=> __('Select the opacity of the background color. Selecting "100%" means that the background color is not transparent, at all.', 'theme-blvd-layout-builder'),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
					'0.05'	=> '5%',
					'0.1'	=> '10%',
					'0.15'	=> '15%',
					'0.2'	=> '20%',
					'0.25'	=> '25%',
					'0.3'	=> '30%',
					'0.35'	=> '35%',
					'0.4'	=> '40%',
					'0.45'	=> '45%',
					'0.5'	=> '50%',
					'0.55'	=> '55%',
					'0.6'	=> '60%',
					'0.65'	=> '65%',
					'0.7'	=> '70%',
					'0.75'	=> '75%',
					'0.8'	=> '80%',
					'0.85'	=> '85%',
					'0.9'	=> '90%',
					'0.95'	=> '95%',
					'1'		=> '100%'
				),
				'class'		=> 'hide receiver receiver-color receiver-texture receiver-image'
			);

			$options['bg_texture'] = array(
				'id'		=> 'bg_texture',
				'name'		=> __('Background Texture', 'theme-blvd-layout-builder'),
				'desc'		=> __('Select a background texture.', 'theme-blvd-layout-builder'),
				'type'		=> 'select',
				'select'	=> 'textures',
				'class'		=> 'hide receiver receiver-texture'
			);

			$options['apply_bg_texture_parallax'] = array(
				'id'		=> 'apply_bg_texture_parallax',
				'name'		=> null,
				'desc'		=> __('Apply parallax scroll effect to background texture.', 'theme-blvd-layout-builder'),
				'type'		=> 'checkbox',
				'class'		=> 'hide receiver receiver-texture'
			);

			$options['subgroup_start_2'] = array(
				'type'		=> 'subgroup_start',
				'class'		=> 'select-parallax hide receiver receiver-image'
			);

			$options['bg_image'] = array(
				'id'		=> 'bg_image',
				'name'		=> __('Background Image', 'theme-blvd-layout-builder'),
				'desc'		=> __('Select a background image.', 'theme-blvd-layout-builder'),
				'type'		=> 'background',
				'color'		=> false,
				'parallax'	=> true
			);

			$options['subgroup_end_2'] = array(
				'type'		=> 'subgroup_end'
			);

			$options['bg_video'] = array(
				'id'		=> 'bg_video',
				'name'		=> __('Background Video', 'theme-blvd-layout-builder'),
				'desc'		=> __('You can upload a web-video file (mp4, webm, ogv), or input a URL to a video page on YouTube or Vimeo. Your fallback image will display on mobile devices.', 'theme-blvd-layout-builder').'<br><br>'.__('Examples:', 'theme-blvd-layout-builder').'<br>https://vimeo.com/79048048<br>http://www.youtube.com/watch?v=5guMumPFBag',
				'type'		=> 'background_video',
				'class'		=> 'hide receiver receiver-video'
			);

			if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.2', '<=' ) ) { // old description
				$options['bg_video']['desc'] = __('Setup a background video. For best results, make sure to use all three fields. The <em>.webm</em> file will display in Google Chrome, while the <em>.mp4</em> will display in most other modnern browsers. Your fallback image will display on mobile and in browsers that don\'t support HTML5 video.', 'theme-blvd-layout-builder');
			}

			// Extended Background options (for section only)
			if ( $type == 'section' ) {

				$options['subgroup_start_3'] = array(
					'type'		=> 'subgroup_start',
					'class'		=> 'show-hide hide receiver receiver-image receiver-slideshow receiver-video'
				);

				$options['apply_bg_shade'] = array(
					'id'		=> 'apply_bg_shade',
					'name'		=> null,
					'desc'		=> __('Shade background with transparent color.', 'theme-blvd-layout-builder'),
					'std'		=> 0,
					'type'		=> 'checkbox',
					'class'		=> 'trigger'
				);

				$options['bg_shade_color'] = array(
					'id'		=> 'bg_shade_color',
					'name'		=> __('Shade Color', 'theme-blvd-layout-builder'),
					'desc'		=> __('Select the color you want overlaid on your background.', 'theme-blvd-layout-builder'),
					'std'		=> '#000000',
					'type'		=> 'color',
					'class'		=> 'hide receiver'
				);

				$options['bg_shade_opacity'] = array(
					'id'		=> 'bg_shade_opacity',
					'name'		=> __('Shade Opacity', 'theme-blvd-layout-builder'),
					'desc'		=> __('Select the opacity of the shade color overlaid on your background.', 'theme-blvd-layout-builder'),
					'std'		=> '0.5',
					'type'		=> 'select',
					'options'	=> array(
						'0.05'	=> '5%',
						'0.1'	=> '10%',
						'0.15'	=> '15%',
						'0.2'	=> '20%',
						'0.25'	=> '25%',
						'0.3'	=> '30%',
						'0.35'	=> '35%',
						'0.4'	=> '40%',
						'0.45'	=> '45%',
						'0.5'	=> '50%',
						'0.55'	=> '55%',
						'0.6'	=> '60%',
						'0.65'	=> '65%',
						'0.7'	=> '70%',
						'0.75'	=> '75%',
						'0.8'	=> '80%',
						'0.85'	=> '85%',
						'0.9'	=> '90%',
						'0.95'	=> '95%'
					),
					'class'		=> 'hide receiver'
				);

				$options['subgroup_end_3'] = array(
					'type'		=> 'subgroup_end'
				);

				$options['subgroup_start_4'] = array(
					'type'		=> 'subgroup_start',
					'class'		=> 'section-bg-slideshow hide receiver receiver-slideshow'
				);

				$options['bg_slideshow'] = array(
					'id' 		=> 'bg_slideshow',
					'name'		=> __('Slideshow Images', 'theme-blvd-layout-builder'),
					'desc'		=> null,
					'type'		=> 'slider'
				);

				$options['bg_slideshow_crop'] = array(
					'name' 		=> __( 'Slideshow Crop Size', 'theme-blvd-layout-builder' ),
					'desc' 		=> __( 'Select the crop size to be used for the background slideshow images. Remember that the background images will be stretched to cover the area.', 'theme-blvd-layout-builder' ),
					'id' 		=> 'bg_slideshow_crop',
					'std' 		=> 'full',
					'type' 		=> 'select',
					'select'	=> 'crop'
				);

				$options['apply_bg_slideshow_parallax'] = array(
					'id'		=> 'apply_bg_slideshow_parallax',
					'name'		=> null,
					'desc'		=> __('Apply parallax scroll effect to background slideshow.', 'theme-blvd-layout-builder'),
					'type'		=> 'checkbox'
				);

				$options['subgroup_end_4'] = array(
					'type'		=> 'subgroup_end'
				);

				$options['subgroup_start_5'] = array(
					'type'		=> 'subgroup_start',
					'class'		=> 'show-hide'
				);

				$options['apply_border_top'] = array(
					'id'		=> 'apply_border_top',
					'name'		=> null,
					'desc'		=> '<strong>'.__('Top Border', 'theme-blvd-layout-builder').'</strong>: '.__('Apply top border to section.', 'theme-blvd-layout-builder'),
					'std'		=> 0,
					'type'		=> 'checkbox',
					'class'		=> 'trigger'
				);

				$options['border_top_color'] = array(
					'id'		=> 'border_top_color',
					'name'		=> __('Top Border Color', 'theme-blvd-layout-builder'),
					'desc'		=> __('Select a color for the top border.', 'theme-blvd-layout-builder'),
					'std'		=> '#dddddd',
					'type'		=> 'color',
					'class'		=> 'hide receiver'
				);

				$options['border_top_width'] = array(
					'id'		=> 'border_top_width',
					'name'		=> __('Top Border Width', 'theme-blvd-layout-builder'),
					'desc'		=> __('Select a width in pixels for the top border.', 'theme-blvd-layout-builder'),
					'std'		=> '1px',
					'type'		=> 'slide',
					'options'	=> array(
						'units'		=> 'px',
						'min'		=> '1',
						'max'		=> '10'
					),
					'class'		=> 'hide receiver'
				);

				$options['subgroup_end_5'] = array(
					'type'		=> 'subgroup_end'
				);

				$options['subgroup_start_6'] = array(
					'type'		=> 'subgroup_start',
					'class'		=> 'show-hide'
				);

				$options['apply_border_bottom'] = array(
					'id'		=> 'apply_border_bottom',
					'name'		=> null,
					'desc'		=> '<strong>'.__('Bottom Border', 'theme-blvd-layout-builder').'</strong>: '.__('Apply bottom border to section.', 'theme-blvd-layout-builder'),
					'std'		=> 0,
					'type'		=> 'checkbox',
					'class'		=> 'trigger'
				);

				$options['border_bottom_color'] = array(
					'id'		=> 'border_bottom_color',
					'name'		=> __('Bottom Border Color', 'theme-blvd-layout-builder'),
					'desc'		=> __('Select a color for the bottom border.', 'theme-blvd-layout-builder'),
					'std'		=> '#dddddd',
					'type'		=> 'color',
					'class'		=> 'hide receiver'
				);

				$options['border_bottom_width'] = array(
					'id'		=> 'border_bottom_width',
					'name'		=> __('Bottom Border Width', 'theme-blvd-layout-builder'),
					'desc'		=> __('Select a width in pixels for the bottom border.', 'theme-blvd-layout-builder'),
					'std'		=> '1px',
					'type'		=> 'slide',
					'options'	=> array(
						'units'		=> 'px',
						'min'		=> '1',
						'max'		=> '10'
					),
					'class'		=> 'hide receiver'
				);

				$options['subgroup_end_6'] = array(
					'type'		=> 'subgroup_end'
				);

			}

			$options['subgroup_end'] = array(
				'type' 		=> 'subgroup_end'
			);

		}

		// Section blending
		if ( $type == 'section' ) {

			// Blend up
			$options['blend_up'] = array(
				'id'		=> 'blend_up',
				'name'		=> null,
				'desc'		=> '<strong>'.__('Blend Up', 'theme-blvd-layout-builder').':</strong> '.__('Blend to section above.', 'theme-blvd-layout-builder'),
				'std'		=> 0,
				'type'		=> 'checkbox'
			);

			// Blend down
			$options['blend_down'] = array(
				'id'		=> 'blend_down',
				'name'		=> null,
				'desc'		=> '<strong>'.__('Blend Down', 'theme-blvd-layout-builder').':</strong> '.__('Blend to section below.', 'theme-blvd-layout-builder'),
				'std'		=> 0,
				'type'		=> 'checkbox'
			);

		}

		// Custom padding options
		switch ( $type ) {
			case 'section' :
				$first_title = __('Desktop Padding', 'theme-blvd-layout-builder');
				$id_suffix = '_desktop';
				$term = __('section', 'theme-blvd-layout-builder');
				$default = array('60px', '0px', '60px', '0px');
				break;
			case 'column' :
				$first_title = __('Padding', 'theme-blvd-layout-builder');
				$id_suffix = '';
				$term = __('column', 'theme-blvd-layout-builder');
				$default = array('30px', '30px', '30px', '30px');
				break;
			case 'element' :
			case 'block' :
				$first_title = __('Padding', 'theme-blvd-layout-builder');
				$id_suffix = '';
				$default = array('0px', '0px', '0px', '0px');
				$term = __('element', 'theme-blvd-layout-builder');
		}

		// Desktop padding
		$options['subgroup_start_7'] = array(
			'type'		=> 'subgroup_start',
			'class'		=> 'show-hide'
		);

		$options['apply_padding'.$id_suffix] = array(
			'id'		=> 'apply_padding'.$id_suffix,
			'name'		=> null,
			'desc'		=> '<strong>'.$first_title.':</strong> '.sprintf(__('Apply custom padding around %s.', 'theme-blvd-layout-builder'), $term),
			'std'		=> 0,
			'type'		=> 'checkbox',
			'class'		=> 'trigger'
		);

		$options['padding_top'.$id_suffix] = array(
			'id'		=> 'padding_top'.$id_suffix,
			'name'		=> __('Top Padding', 'theme-blvd-layout-builder'),
			'desc'		=> sprintf(__('Set the padding on the top of the %s.', 'theme-blvd-layout-builder'), $term),
			'std'		=> $default[0],
			'type'		=> 'slide',
			'options'	=> array(
				'units'		=> 'px',
				'min'		=> '0',
				'max'		=> '600'
			),
			'class'		=> 'hide receiver'
		);

		$options['padding_right'.$id_suffix] = array(
			'id'		=> 'padding_right'.$id_suffix,
			'name'		=> __('Right Padding', 'theme-blvd-layout-builder'),
			'desc'		=> sprintf(__('Set the padding on the right of the %s.', 'theme-blvd-layout-builder'), $term),
			'std'		=> $default[1],
			'type'		=> 'slide',
			'options'	=> array(
				'units'		=> 'px',
				'min'		=> '0',
				'max'		=> '600'
			),
			'class'		=> 'hide receiver'
		);

		$options['padding_bottom'.$id_suffix] = array(
			'id'		=> 'padding_bottom'.$id_suffix,
			'name'		=> __('Bottom Padding', 'theme-blvd-layout-builder'),
			'desc'		=> sprintf(__('Set the padding on the bottom of the %s.', 'theme-blvd-layout-builder'), $term),
			'std'		=> $default[2],
			'type'		=> 'slide',
			'options'	=> array(
				'units'		=> 'px',
				'min'		=> '0',
				'max'		=> '600'
			),
			'class'		=> 'hide receiver'
		);

		$options['padding_left'.$id_suffix] = array(
			'id'		=> 'padding_left'.$id_suffix,
			'name'		=> __('Left Padding', 'theme-blvd-layout-builder'),
			'desc'		=> sprintf(__('Set the padding on the left of the %s.', 'theme-blvd-layout-builder'), $term),
			'std'		=> $default[3],
			'type'		=> 'slide',
			'options'	=> array(
				'units'		=> 'px',
				'min'		=> '0',
				'max'		=> '600'
			),
			'class'		=> 'hide receiver'
		);

		$options['subgroup_end_7'] = array(
			'type' => 'subgroup_end'
		);

		if ( $type == 'section' ) {

			// Tablet Padding
			$options['subgroup_start_8'] = array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide'
			);

			$options['apply_padding_tablet'] = array(
				'id'		=> 'apply_padding_tablet',
				'name'		=> null,
				'desc'		=> '<strong>'.__('Tablet Padding', 'theme-blvd-layout-builder').':</strong> '.sprintf(__('Apply custom padding around %s when at the tablet viewport size.', 'theme-blvd-layout-builder'), $term),
				'std'		=> 0,
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			);

			$options['padding_top_tablet'] = array(
				'id'		=> 'padding_top_tablet',
				'name'		=> __('Tablet Top Padding', 'theme-blvd-layout-builder'),
				'desc'		=> sprintf(__('Set the padding on the top of the %s.', 'theme-blvd-layout-builder'), $term),
				'std'		=> $default[0],
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);

			$options['padding_right_tablet'] = array(
				'id'		=> 'padding_right_tablet',
				'name'		=> __('Tablet Right Padding', 'theme-blvd-layout-builder'),
				'desc'		=> sprintf(__('Set the padding on the right of the %s.', 'theme-blvd-layout-builder'), $term),
				'std'		=> $default[1],
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);

			$options['padding_bottom_tablet'] = array(
				'id'		=> 'padding_bottom_tablet',
				'name'		=> __('Tablet Bottom Padding', 'theme-blvd-layout-builder'),
				'desc'		=> sprintf(__('Set the padding on the bottom of the %s.', 'theme-blvd-layout-builder'), $term),
				'std'		=> $default[2],
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);

			$options['padding_left_tablet'] = array(
				'id'		=> 'padding_left_tablet',
				'name'		=> __('Tablet Left Padding', 'theme-blvd-layout-builder'),
				'desc'		=> sprintf(__('Set the padding on the left of the %s.', 'theme-blvd-layout-builder'), $term),
				'std'		=> $default[3],
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);

			$options['subgroup_end_8'] = array(
				'type' => 'subgroup_end'
			);

			// Mobile Padding
			$options['subgroup_start_9'] = array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide'
			);

			$options['apply_padding_mobile'] = array(
				'id'		=> 'apply_padding_mobile',
				'name'		=> null,
				'desc'		=> '<strong>'.__('Mobile Padding', 'theme-blvd-layout-builder').':</strong> '.sprintf(__('Apply custom padding around %s when at the mobile viewport size.', 'theme-blvd-layout-builder'), $term),
				'std'		=> 0,
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			);

			$options['padding_top_mobile'] = array(
				'id'		=> 'padding_top_mobile',
				'name'		=> __('Mobile Top Padding', 'theme-blvd-layout-builder'),
				'desc'		=> sprintf(__('Set the padding on the top of the %s.', 'theme-blvd-layout-builder'), $term),
				'std'		=> $default[0],
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);

			$options['padding_right_mobile'] = array(
				'id'		=> 'padding_right_mobile',
				'name'		=> __('Mobile Right Padding', 'theme-blvd-layout-builder'),
				'desc'		=> sprintf(__('Set the padding on the right of the %s.', 'theme-blvd-layout-builder'), $term),
				'std'		=> $default[1],
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);

			$options['padding_bottom_mobile'] = array(
				'id'		=> 'padding_bottom_mobile',
				'name'		=> __('Mobile Bottom Padding', 'theme-blvd-layout-builder'),
				'desc'		=> sprintf(__('Set the padding on the bottom of the %s.', 'theme-blvd-layout-builder'), $term),
				'std'		=> $default[2],
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);

			$options['padding_left_mobile'] = array(
				'id'		=> 'padding_left_mobile',
				'name'		=> __('Mobile Left Padding', 'theme-blvd-layout-builder'),
				'desc'		=> sprintf(__('Set the padding on the left of the %s.', 'theme-blvd-layout-builder'), $term),
				'std'		=> $default[3],
				'type'		=> 'slide',
				'options'	=> array(
					'units'		=> 'px',
					'min'		=> '0',
					'max'		=> '600'
				),
				'class'		=> 'hide receiver'
			);

			$options['subgroup_end_9'] = array(
				'type' => 'subgroup_end'
			);

		}

		if ( $type == 'element' || $type == 'block' ) {

			$elements = $this->get_elements();

			// Whether to pop element out of content restraint
			if ( $type == 'element' && $elements[$element_type]['support']['popout'] ) {

				$options['apply_popout'] = array(
					'id'		=> 'apply_popout',
					'name'		=> null,
					'desc'		=> '<strong>'.__('Popout', 'theme-blvd-layout-builder').':</strong> '.__('Stretch content of element to fill outer container. &mdash; <em>Note: If you\'re using a theme design that is not displayed in a stretch layout, this option will not be as pronounced.</em>', 'theme-blvd-layout-builder'),
					'std'		=> 0,
					'type'		=> 'checkbox'
				);

				if ( $elements[$element_type]['support']['popout'] === 'force' ) {
					$options['apply_popout']['inactive'] = 'true';
				}

			}

			// Content background
			$options['bg_content'] = array(
				'id'		=> 'bg_content',
				'name'		=> null,
				'desc'		=> '<strong>'.__('Content Background', 'theme-blvd-layout-builder').':</strong> '.__('Add theme\'s default content background color around element. &mdash; <em>Note: This can be helpful if the element sits in a section or column that has a background color set.</em>', 'theme-blvd-layout-builder'),
				'std'		=> 0,
				'type'		=> 'checkbox'
			);

			// Suck up/down
			$options['suck_up'] = array(
				'id'		=> 'suck_up',
				'name'		=> null,
				'desc'		=> '<strong>'.__('Suck Up', 'theme-blvd-layout-builder').':</strong> '.__('Suck element up closer to the element that comes before it.', 'theme-blvd-layout-builder'),
				'std'		=> 0,
				'type'		=> 'checkbox'
			);
			$options['suck_down'] = array(
				'id'		=> 'suck_down',
				'name'		=> null,
				'desc'		=> '<strong>'.__('Suck Down', 'theme-blvd-layout-builder').':</strong> '.__('Suck element down closer to the element that comes after it.', 'theme-blvd-layout-builder'),
				'std'		=> 0,
				'type'		=> 'checkbox'
			);
		}

		if ( $type != 'column' ) {

			$options['hide'] = array(
		    	'id' 		=> 'hide',
				'name'		=> __( 'Responsive Visibility', 'theme-blvd-layout-builder' ),
				'desc'		=> __( 'Select any resolutions you\'d like to <em>hide</em> this item on. This is optional, but can be utilized to deliver different content to different devices.', 'theme-blvd-layout-builder' ),
				'type'		=> 'multicheck',
				'class'		=> 'section-visibility',
				'options'	=> array(
					'xs'	=> __('Hide on phones', 'theme-blvd-layout-builder'),
					'sm'	=> __('Hide on tablets', 'theme-blvd-layout-builder'),
					'md'	=> __('Hide on small desktops', 'theme-blvd-layout-builder'),
					'lg'	=> __('Hide on large desktops', 'theme-blvd-layout-builder')
				)
			);

			// Modified for Jump Start dev versions
			// @TODO Eventually we can remove this
			if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '==' ) ) {

				$theme = wp_get_theme( get_template() );

				if ( $theme->get('Name') == 'Jump Start' && in_array( $theme->get('Version'), array('2.0.0-beta1', '2.0.0-beta2', '2.0.0-beta3', '2.0.0-RC1') ) ) {

					unset( $options['hide'] );

					$options['visibility'] = array(
				    	'id' 		=> 'visibility',
						'name'		=> __( 'Responsive Visibility', 'theme-blvd-layout-builder' ),
						'desc'		=> __( 'Select any resolutions you\'d like to <em>hide</em> this item on. This is optional, but can be utilized to deliver different content to different devices.', 'theme-blvd-layout-builder' ),
						'type'		=> 'multicheck',
						'class'		=> 'section-visibility',
						'options'	=> array(
							'hide_on_standard' 	=> __( 'Hide on Standard Resolutions', 'theme-blvd-layout-builder' ),
							'hide_on_tablet' 	=> __( 'Hide on Tablets', 'theme-blvd-layout-builder' ),
							'hide_on_mobile' 	=> __( 'Hide on Mobile Devices', 'theme-blvd-layout-builder' )
						)
					);
				}
			}

		}

		$options['classes'] = array(
	    	'id' 		=> 'classes',
			'name'		=> __( 'CSS Classes', 'theme-blvd-layout-builder' ),
			'desc'		=> __( 'Enter any CSS classes you\'d like attached.', 'theme-blvd-layout-builder' ),
			'type'		=> 'text',
			'class'		=> 'section-classes'
		);

		return apply_filters( 'themeblvd_builder_display_options', $options, $type, $element_type );
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

	/*--------------------------------------------*/
	/* Hidden Modals
	/*--------------------------------------------*/

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
		themeblvd_editor();
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

			if ( $page->base == 'toplevel_page_'.$this->id || ( $page->base == 'post' && $page->id == 'page' ) ) {
				add_action( 'in_admin_header', array( $this, 'display_icon_browser' ) );
			}
		}
	}
	public function display_icon_browser() {
		themeblvd_icon_browser( array( 'type' => 'vector' ) );
	}

	/**
	 * Hook in hidden post browser modal.
	 *
	 * @since 2.0.0
	 */
	public function add_post_browser() {

		// Requires Framework 2.5+
		if ( function_exists( 'themeblvd_post_browser' ) ) {

			$page = get_current_screen();

			if ( $page->base == 'toplevel_page_'.$this->id || ( $page->base == 'post' && $page->id == 'page' ) ) {
				add_action( 'in_admin_header', 'themeblvd_post_browser' );
			}
		}
	}

	/**
	 * Hook in hidden texture browser modal.
	 *
	 * @since 2.5.0
	 */
	public function add_texture_browser() {

		// Requires Framework 2.5+
		if ( function_exists( 'themeblvd_texture_browser' ) ) {

			$page = get_current_screen();

			if ( $page->base == 'toplevel_page_'.$this->id || ( $page->base == 'post' &&  $page->id == 'page' ) ) {
				add_action( 'in_admin_header', 'themeblvd_texture_browser' );
			}
		}
	}
}
