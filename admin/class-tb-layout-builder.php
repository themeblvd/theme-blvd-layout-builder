<?php
/**
 * Layout Builder
 */
class Theme_Blvd_Layout_Builder {
	
	public $id;
	public $args;
	public $elements;
	
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
			'page_title' 	=> __( 'Layout Builder', 'themeblvd' ),
			'menu_title' 	=> __( 'Builder', 'themeblvd' ),
			'icon'			=> 'div',
			'cap'			=> themeblvd_admin_module_cap( 'builder' ),
			'priority'		=> 30
		);
		$this->args = wp_parse_args( $args, $defaults );
		
		// Elements for builder
		$this->elements = $elements ? $elements : themeblvd_get_elements();
		
		// Add slider admin page
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		
		// Add ajax functionality to slider admin page
		include_once( TB_BUILDER_PLUGIN_DIR . '/admin/class-tb-layout-builder-ajax.php' );
		$ajax = new Theme_Blvd_Layout_Builder_Ajax( $this );
	
	}
	
	/**
	 * Add a menu page for Builder
	 *
	 * @since 1.0.0 
	 */
	function add_page() {
		$admin_page = add_object_page( $this->args['page_title'], $this->args['menu_title'], $this->args['cap'], $this->id, array( $this, 'admin_page' ), $this->args['icon'], $this->args['priority'] );
		add_action( 'admin_print_styles-'.$admin_page, array( $this, 'load_styles' ) );
		add_action( 'admin_print_scripts-'.$admin_page, array( $this, 'load_scripts' ) );
		add_action( 'admin_print_styles-'.$admin_page, 'optionsframework_mlu_css', 0 );
		add_action( 'admin_print_scripts-'.$admin_page, 'optionsframework_mlu_js', 0 );
	}

	/**
	 * Loads the CSS
	 *
	 * @since 1.0.0
	 */
	function load_styles() {
		wp_enqueue_style( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'color-picker', TB_FRAMEWORK_URI . '/admin/options/css/colorpicker.min.css' );
		wp_enqueue_style( 'themeblvd_builder', TB_BUILDER_PLUGIN_URI . '/admin/assets/css/builder-style.min.css', null, TB_BUILDER_PLUGIN_VERSION );
	}

	/**
	 * Loads the javascript
	 *
	 * @since 1.0.0 
	 */
	function load_scripts() {
		wp_enqueue_script( 'jquery-ui-core');
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/js/shared.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_localize_script( 'themeblvd_admin', 'themeblvd', themeblvd_get_admin_locals( 'js' ) );
		wp_enqueue_script( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/js/options.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'color-picker', TB_FRAMEWORK_URI . '/admin/options/js/colorpicker.min.js', array('jquery') );
		wp_enqueue_script( 'themeblvd_builder', TB_BUILDER_PLUGIN_URI . '/admin/assets/js/builder.min.js', array('jquery'), TB_BUILDER_PLUGIN_VERSION );
		wp_localize_script( 'themeblvd_builder', 'themeblvd', themeblvd_get_admin_locals( 'js' ) );
	}

	/**
	 * Builds out the full admin page.
	 *
	 * @since 1.0.0 
	 */
	function admin_page() {
		?>
		<div id="builder_blvd">
			<div id="optionsframework" class="wrap">
				
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
						echo '<input type="hidden" name="_wpnonce" value="'.$manage_nonce.'" />';
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
						echo '<input type="hidden" name="_wpnonce" value="'.$add_nonce.'" />';
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
						echo '<input type="hidden" name="_wpnonce" value="'.$edit_nonce.'" />';
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
		foreach( $layouts as $layout )
			$sidebar_layouts[$layout['id']] = $layout['name'];
		
		// Setup sample layouts
		$samples = themeblvd_get_sample_layouts();
		$sample_layouts = array();
		if( $samples ) {
			$sample_layouts = array( false => '- Start From Scratch -' );
			foreach( $samples as $sample )
				$sample_layouts[$sample['id']] = $sample['name'];
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
		
		// Sample Layouts (only show if there are sample layouts)
		if( $sample_layouts ) {
			$options[] = array( 
				'name' 		=> __( 'Starting Point', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select if you\'d like to start building your layout from scratch or from a pre-built sample layout.', 'themeblvd_builder' ),
				'id' 		=> 'layout_start',
				'type' 		=> 'select',
				'options' 	=> $sample_layouts,
				'class'		=> 'builder_samples'
			);
		}
		
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
		$form = themeblvd_option_fields( 'options', $options, null, false );
		?>
		<div class="metabox-holder">
			<div class="postbox">
				<h3><?php _e( 'Add New Layout', 'themeblvd_builder' ); ?></h3>
				<form id="add_new_slider">
					<div class="inner-group">
						<?php echo $form[0]; ?>
					</div><!-- .group (end) -->
					<div id="optionsframework-submit">
						<input type="submit" class="button-primary" name="update" value="<?php _e( 'Add New Layout', 'themeblvd_builder' ); ?>">
						<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
			            <div class="clear"></div>
					</div>
				</form><!-- #add_new_slider (end) -->
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
		$elements = $this->elements;
		$form = themeblvd_option_fields( 'elements['.$element_id.'][options]', $elements[$element_type]['options'], $element_settings, false );
		?>
		<div id="<?php echo $element_id; ?>" class="widget element-options"<?php if( $visibility == 'hide' ) echo ' style="display:none"'; ?>>					
			<div class="widget-name">
				<a href="#" class="widget-name-arrow">Toggle</a>
				<h3><?php echo $elements[$element_type]['info']['name']; ?></h3>
			</div><!-- .element-name (end) -->
			<div class="widget-content">
				<input type="hidden" class="element-type" name="elements[<?php echo $element_id; ?>][type]" value="<?php echo $element_type; ?>" />
				<input type="hidden" class="element-query" name="elements[<?php echo $element_id; ?>][query_type]" value="<?php echo $elements[$element_type]['info']['query']; ?>" />
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
		$elements = $this->elements;
		$layout = get_post($id);
		$layout_elements = get_post_meta( $id, 'elements', true );
		$layout_settings = get_post_meta( $id, 'settings', true );
		?>
		<input type="hidden" name="layout_id" value="<?php echo $id; ?>" />
		<div id="poststuff" class="metabox-holder full-width has-right-sidebar">
			<div class="inner-sidebar">
				<div class="postbox postbox-publish">
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
				<div class="postbox postbox-layout-info">
					<h3 class="hndle"><?php _e('Layout Information', 'themeblvd_builder' ); ?></h3>
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
					$form = themeblvd_option_fields( 'info', $info_options, $info_settings, false );
					echo $form[0]; 
					?>
				</div><!-- .post-box (end) -->
				<div class="postbox postbox-sidebar-layout">
					<h3 class="hndle"><?php _e('Sidebar Layout', 'themeblvd_builder' ); ?></h3>
					<?php
					// Setup sidebar layouts
					$layouts = themeblvd_sidebar_layouts();
					$sidebar_layouts = array( 'default' => __( 'Default Sidebar Layout', 'themeblvd_builder' ) );
					foreach( $layouts as $layout )
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
					$form = themeblvd_option_fields( 'options', $options, $layout_settings, false );
					echo $form[0]; 
					?>
				</div><!-- .post-box (end) -->
			</div><!-- .inner-sidebar (end) -->
			<div id="post-body">
				<div id="post-body-content">
					<div id="titlediv">
						<div class="ajax-overlay"></div>
						<h2><?php _e( 'Manage Elements', 'themeblvd_builder' ); ?></h2>
						<select>
						<?php
						foreach( $elements as $element )
							echo '<option value="'.$element['info']['id'].'=>'.$element['info']['query'].'">'.$element['info']['name'].'</option>';
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
								if( ! empty( $layout_elements ) && ! empty( $layout_elements['featured'] ) ) {
									foreach( $layout_elements['featured'] as $id => $element ) {
										if( $this->is_element( $element['type'] ) ) {
											$this->edit_element( $element['type'], $id, $element['options'] );
										}
									}
								}
								?>
							</div><!-- .sortable (end) -->
						</div><!-- #featured (end) -->
						<div id="primary">
							<input type="hidden" name="elements[divider]" value="" />
							<span class="label"><?php _e( 'Primary Area', 'themeblvd_builder' ); ?></span>
							<div class="sortable">
								<?php
								if( ! empty( $layout_elements ) && ! empty( $layout_elements['primary'] ) ) {
									foreach( $layout_elements['primary'] as $id => $element ) {
										if( $this->is_element( $element['type'] ) ) {
											$this->edit_element( $element['type'], $id, $element['options'] );
										}
									}
								}
								?>
							</div><!-- .sortable (end) -->
						</div><!-- #primary (end) -->
						<div id="featured_below">
							<input type="hidden" name="elements[divider_2]" value="" />
							<span class="label"><?php _e( 'Featured Below', 'themeblvd_builder' ); ?></span>
							<div class="sortable">
								<?php
								if( ! empty( $layout_elements ) && ! empty( $layout_elements['featured_below'] ) ) {
									foreach( $layout_elements['featured_below'] as $id => $element ) {
										if( $this->is_element( $element['type'] ) ) {
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
	 * Check if element is currently registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $element_id ID of element type to check for
	 * @return boolean $exists If element exists or not
	 */
	public function is_element( $element_id ) {		
		$exists = false;
		if( array_key_exists ( $element_id, $this->elements ) )
			$exists = true;
		return $exists;
	}

}