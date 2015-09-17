<?php
/**
 * Import custom layout.
 *
 * @author		Jason Bobich
 * @copyright	Copyright (c) Jason Bobich
 * @link		http://jasonbobich.com
 * @link		http://themeblvd.com
 * @package 	Theme Blvd WordPress Framework
 */
class Theme_Blvd_Import_Layout {

	public $id = '';
	public $args = array();
	private $error = '';

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id A unique ID for this exporter
	 */
	public function __construct( $id, $args = array() ) {

		$this->id = $id;

		$defaults = array(
			'redirect'	=> admin_url() 	// Where to redirect after import is finished
		);
		$this->args = wp_parse_args( $args, $defaults );

		// Add Importer page
		add_action( 'admin_menu', array( $this, 'add_page' ) );

		// Process import form
		add_action( 'admin_init', array( $this, 'import' ) );

		// Check if we're loading after a successful import
		add_action( 'admin_init', array( $this, 'success' ) );
	}

	/**
	 * Add the hidden admin page to WordPress.
	 *
	 * @since 2.0.0
	 */
	public function add_page() {
		add_submenu_page( null, null, null, themeblvd_admin_module_cap('options'), $this->id.'-import-layout', array( $this, 'admin_page' ) );
	}

	/**
	 * Display the hidden admin page.
	 *
	 * @since 2.0.0
	 */
	public function admin_page() {
		?>
		<h2><?php esc_html_e('Import Template', 'theme-blvd-layout-builder'); ?></h2>
		<p><?php esc_html_e('Upload an XML file previously exported from the Builder.', 'theme-blvd-layout-builder'); ?></p>
		<form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form" action="admin.php?page=<?php echo $this->id; ?>-import-layout&amp;themeblvd_import=true">
			<p>
				<label for="upload"><?php esc_html_e('Choose a file from your computer:', 'theme-blvd-layout-builder'); ?></label><br />
				<input type="file" id="upload" name="import" size="25" />
				<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'themeblvd_import_'.$this->id ); ?>" />
				<input type="hidden" name="max_file_size" value="33554432" />
			</p>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button" value="<?php esc_attr_e('Upload file and import', 'theme-blvd-layout-builder'); ?>" disabled="" />
			</p>
		</form>
		<?php
	}

	/**
	 * Process the uploaded file and import the data.
	 *
	 * @since 2.0.0
	 */
	public function import() {

		if ( empty( $_GET['themeblvd_import'] ) ) {
			return;
		}

		// Check security nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'themeblvd_import_'.$this->id ) ) {
			return;
		}

		$import = '';
		$file = '';

		if ( isset( $_FILES['import'] ) ) {

			// Needs to be an XML file
			if ( $_FILES['import']['type'] != 'text/xml' ) {
				$this->error = __('Error. You must upload an XML file.', 'theme-blvd-layout-builder');
				add_action( 'admin_notices', array( $this, 'fail' ) );
				return;
			}

			$file = $_FILES['import']['tmp_name'];
		}

		// Parse the file
		if ( file_exists( $file ) && function_exists( 'simplexml_load_file' ) ) {
			$internal_errors = libxml_use_internal_errors(true);
			$import = simplexml_load_file( $file );
		}

		if ( ! $import ) {
			$this->error = __('Error. The XML file could not be read.', 'theme-blvd-layout-builder');
			add_action( 'admin_notices', array( $this, 'fail' ) );
			return;
		}

		$info = $import->info;
		$data = $import->data->meta;

		if ( ! $info || ! $data ) {
			$this->error = __('Error. The XML file was not formatted properly.', 'theme-blvd-layout-builder');
			add_action( 'admin_notices', array( $this, 'fail' ) );
			return;
		}

		// Setup arguments for new 'layout' post
		$args = array(
			'post_type'			=> 'tb_layout',
			'post_name'			=> (string)$info->id,
			'post_title'		=> (string)$info->name,
			'post_status' 		=> 'publish',
			'comment_status'	=> 'closed',
			'ping_status'		=> 'closed'
		);

		// Create new post
		$layout_id = wp_insert_post( $args );

		// Attach all meta data to new layout
		foreach( $data as $meta ) {

			$key = (string)$meta->key;
			$value = (string)$meta->value;
			$value = maybe_unserialize(base64_decode($value));

			update_post_meta( $layout_id, $key, $value );

		}

		// Success!
		wp_redirect( $this->args['redirect'].'&settings-updated=themeblvd_import_success' );
		exit;

	}

	/**
	 * Get the URL of our hidden admin page.
	 *
	 * @since 2.0.0
	 */
	public function get_url() {
		return admin_url('admin.php?page='.$this->id.'-import-layout');
	}

	/**
	 * Hook success notice.
	 *
	 * @since 2.0.0
	 */
	public function success() {
		if ( ! empty( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'themeblvd_import_success' ) {
			add_action( 'themeblvd_builder_update', array( $this, 'success_display' ) );
		}
	}

	/**
	 * Display success notice.
	 *
	 * @since 2.0.0
	 */
	public function success_display() {
		?>
		<div class="themeblvd-updated updated fade" style="margin-left: 0;">
			<p><strong><?php esc_html_e('Custom layout imported successfully.', 'theme-blvd-layout-builder'); ?></strong></p>
		</div>
		<?php
	}

	/**
	 * Fail notice.
	 *
	 * @since 2.0.0
	 */
	public function fail() {
		?>
		<div class="themeblvd-updated error settings-error" style="margin-left: 0;">
			<p><strong><?php echo esc_html($this->error); ?></strong></p>
		</div>
		<?php
	}
}
