<?php
/**
 * Display plugin notices to the user
 */
class Theme_Blvd_Layout_Builder_Notices {

	/*--------------------------------------------*/
	/* Properties, private
	/*--------------------------------------------*/

	/**
	 * A single instance of this class.
	 *
	 * @since 2.0.0
	 */
	private static $instance = null;

	/**
	 * The type of error.
	 *
	 * @since 2.0.0
	 */
	private $error = array();

	/**
	 * Whether the plugin should completely stop running.
	 *
	 * @since 2.0.0
	 */
	private $stop = false;

	/*--------------------------------------------*/
	/* Constructor
	/*--------------------------------------------*/

	/**
     * Creates or returns an instance of this class.
     *
     * @since 2.0.0
     *
     * @return Theme_Blvd_Layout_Builder_Notices A single instance of this class.
     */
	public static function get_instance() {

		if ( self::$instance == null ) {
            self::$instance = new self;
        }

        return self::$instance;
	}

	/**
	 * Constructor. Hook everything in.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {

		$v = 0;

		if ( defined('TB_FRAMEWORK_VERSION') ) {
			$v = TB_FRAMEWORK_VERSION;
		}

		if ( ! $v ) {
			$this->error[] = 'framework';
			$this->stop = true;
		} else if ( version_compare( $v, '2.2.0', '<' ) ) {
			$this->error[] = 'framework-2-2';
			$this->stop = true;
		}

		/*
		if ( version_compare( $v, '2.5.0', '>=' ) && version_compare( $v, '2.7.0', '<' ) ) {
			$this->error[] = 'update-theme';
		}
		*/

		if ( version_compare( $v, '2.4.8', '<' ) ) {
			$this->error[] = 'update-theme';
		}

		if ( version_compare( $v, '2.5.0', '>=' ) ) {

			$options = get_option( themeblvd_get_option_name() );

			if ( ! empty($options['homepage_content']) && $options['homepage_content'] == 'custom_layout' ) {
				$this->error[] = 'old-homepage';
			}
		}

		if ( $this->error ) {
			add_action( 'admin_notices', array( $this, 'show' ) );
			add_action( 'admin_init', array( $this, 'disable' ) );
		}

	}

	/*--------------------------------------------*/
	/* Methods, general
	/*--------------------------------------------*/

	/**
	 * Show error message
	 *
	 * @since 2.0.0
	 */
	public function show() {

		global $current_user;

		if ( $this->error ) {

			$theme = wp_get_theme( get_template() );

			$changelog_url = apply_filters( 'themeblvd_changelog_link', 'http://themeblvd.com/changelog/?theme=' . get_template() );

			$changelog = '<a href="' . $changelog_url . '" target="_blank">' . esc_html__('theme\'s changelog', 'theme-blvd-layout-builder') . '</a>';

			foreach ( $this->error as $error ) {
				if ( ! get_user_meta( $current_user->ID, 'tb-nag-'.$error, true ) ) {

					echo '<div class="updated">';
					echo '<p><strong>Theme Blvd Layout Builder</strong>: '.esc_html( $this->get_message($error) ).'</p>';

					// Theme specifics
					if ( in_array($error, array('framework-2-2', 'framework-2-5', 'update-theme') ) ) {
						echo '<p>';
						printf( esc_html__('You\'re currently using %s v%s. See %s.', 'theme-blvd-layout-builder'), $theme->get('Name'), $theme->get('Version'), $changelog );
						echo '</p>';
					}

					// Dismiss link
					echo '<p><a href="'.esc_url( $this->disable_url($error) ).'">'.esc_html__('Dismiss this notice', 'theme-blvd-layout-builder').'</a> | <a href="http://www.themeblvd.com" target="_blank">'.esc_html__('Visit ThemeBlvd.com', 'theme-blvd-layout-builder').'</a></p>';

					echo '</div>';
				}
			}
		}
	}

	/**
	 * Disable error message
	 *
	 * @since 2.0.0
	 */
	public function disable() {

		global $current_user;

		if ( ! isset($_GET['nag-ignore']) ) {
			return;
		}

		if ( strpos($_GET['nag-ignore'], 'tb-nag-') !== 0 ) { // meta key must start with "tb-nag-"
			return;
		}

		if ( isset($_GET['security']) && wp_verify_nonce( $_GET['security'], 'themeblvd-builder-nag' ) ) {
			add_user_meta( $current_user->ID, $_GET['nag-ignore'], 'true', true );
		}
	}

	/**
	 * Disable a nag message URL.
	 *
	 * @since 2.0.0
	 */
	private function disable_url( $id ) {

		global $pagenow;

		$url = admin_url( $pagenow );

		if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
			$url .= sprintf( '?%s&nag-ignore=%s', $_SERVER['QUERY_STRING'], 'tb-nag-'.$id );
		} else {
			$url .= sprintf( '?nag-ignore=%s', 'tb-nag-'.$id );
		}

		$url .= sprintf( '&security=%s', wp_create_nonce('themeblvd-builder-nag') );

		return $url;
	}


	/*--------------------------------------------*/
	/* Methods, accessors
	/*--------------------------------------------*/

	/**
	 * Get individual error message
	 *
	 * @since 2.0.0
	 */
	private function get_message( $type ) {

		$message = '';

		$messages = array(
			'framework' 		=> __('You are not using a theme with the Theme Blvd Framework, and so this plugin will not do anything.', 'theme-blvd-layout-builder'),
			'framework-2-2' 	=> __('You are not using a theme with Theme Blvd Framework v2.2+, and so this plugin will not do anything. Check to see if there is an updated version of your theme.', 'theme-blvd-layout-builder'),
			'old-homepage'		=> __('It appears you are using an old method of applying a custom layout to your website\'s homepage. Follow these steps to get up-to-date:<ol><li>Create a static page and apply a custom layout to it.</li><li>Go to <em>Settings > Reading > Frontpage displays</em>, and select this page as your static frontpage.</li></ol>', 'theme-blvd-layout-builder'),
			'update-theme'		=> __('It looks like you don\'t have the latest compatible version of your theme installed. For best results with this version of the layout builder, you should update to the latest version of your theme.', 'theme-blvd-layout-builder')
		);

		if ( isset( $messages[$type] ) ) {
			$message = $messages[$type];
		}

		return $message;
	}

	/**
	 * Determine if plugin should stop
	 *
	 * @since 2.0.0
	 */
	public function do_stop() {
		return $this->stop;
	}

}
