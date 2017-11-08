<?php
/**
 * Add "Screen Options" tab to Layout Builder
 */
class Theme_Blvd_Layout_Builder_Screen {

	private static $instance = null;
	private $admin_page;
	private $options_name;

	/**
     * Creates or returns an instance of this class.
     *
     * @since 1.1.1
     *
     * @return Theme_Blvd_Layout_Builder_Screen A single instance of this class.
     */
	public static function get_instance() {

		if ( self::$instance == null ) {
            self::$instance = new self;
        }

        return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.2.1
	 *
	 * @param $admin_page string WP Admin page to add screen options tab to
	 */
	public function __construct() {
		$this->admin_page = 'toplevel_page_themeblvd_builder';
		$this->options_name = apply_filters( 'themeblvd_builder_screen_options_name', 'themeblvd_builder_screen_options' );
		add_filter( 'screen_settings', array( $this, 'output' ) );
		add_action( 'wp_ajax_themeblvd_save_screen_settings', array( $this, 'save' ) );
	}

	/**
	 * Output for screen options tab's content.
	 *
	 * @since 1.2.1
	 */
	public function output( $output ) {

		// Get current admin screen
		$screen = get_current_screen();

		// Only filter output if this is our Layout
		// Builder page
		if ( $this->admin_page == $screen->id ) {

			$props = $this->get_props();

			if ( is_array( $props ) && count( $props ) > 0 ) {

				$output .= '<form id="themeblvd_builder_screen_options">';

				$nonce = wp_create_nonce( 'themeblvd_save_screen_options' );
				$output .= '<input type="hidden" class="security" name="_tb_screen_options_nonce" value="'.$nonce.'" />';

				$output .= '<h5>'.esc_html__('Show advanced element properties', 'theme-blvd-layout-builder').'</h5>';
				$output .= '<div class="metabox-prefs">';

				$value = $this->get_value();

				foreach ( $props as $prop ) {

					$checked = '';

					if ( ! empty( $value[$prop['id']] ) ) {
						$checked = ' checked';
					}

					$output .= '<label for="'.$prop['id'].'-hide"><input class="hide-column-tog" name="'.$prop['id'].'-hide" type="checkbox" id="'.$prop['id'].'-hide" value="'.$prop['id'].'"'.$checked.'>'.$prop['name'].'</label>';
				}

				$output .= '<br class="clear">';
				$output .= '</div>';

				$output .= '</form>';
			}
		}

		return $output;
	}

	/**
	 * Output for screen options tab's content.
	 *
	 * @since 1.2.1
	 */
	private function get_props() {

		$props = array(
			'visibility' => array(
				'name'	=> __( 'Responsive Visibility', 'theme-blvd-layout-builder' ),
				'id'	=> 'visibility',
				'std'	=> true
			),
			'classes' => array(
				'name'	=> __( 'CSS Classes', 'theme-blvd-layout-builder' ),
				'id'	=> 'classes',
				'std'	=> false
			)
		);

		return apply_filters( 'themeblvd_builder_screen_props', $props );
	}

	/**
	 * Get the default display settings.
	 *
	 * @since 1.2.1
	 */
	private function get_default() {

		$props = $this->get_props();
		$default = array();

		if ( is_array( $props ) && count( $props ) > 0 ) {
			foreach ( $props as $prop ) {
				$default[$prop['id']] = $prop['std'];
			}
		}

		return apply_filters( 'themeblvd_builder_screen_default', $default );
	}

	/**
	 * Get values.
	 *
	 * @since 1.2.1
	 */
	public function get_value() {

		$value = get_option( $this->options_name );

		if ( ! $value ) {
			$value = $this->get_default();
		}

		return apply_filters( 'themeblvd_builder_screen_value', $value );
	}

	/**
	 * Get the option name used for get_option()
	 *
	 * @since 1.2.1
	 */
	public function get_options_name() {
		return $this->options_name;
	}

	/**
	 * Save value via Ajax
	 *
	 * @since 1.2.1
	 */
	public function save() {

		check_ajax_referer( 'themeblvd_save_screen_options', 'security' );

		$data = $_POST['data'];

		$value = $this->get_value();

		$id = $data[0];
		$id = str_replace( '-hide', '', $id );
		$show = $data[1];

		if ( $show == 'on' ) {
			$value[$id] = true;
		} else {
			$value[$id] = false;
		}

		update_option( $this->options_name, $value );

		die();

	}

}
