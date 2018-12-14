<?php
/**
 * Add "Updates Notices" to Plugins page.
 *
 * @since 2.2.2
 */
class Theme_Blvd_Layout_Builder_Upgrade_Notice {

	/**
	 * A single instance of this class.
	 *
	 * @since 2.2.0
	 */
	private static $instance = null;

	/**
     * Creates or returns an instance of this class.
     *
     * @since 2.2.2
     *
     * @return Theme_Blvd_Layout_Builder_Upgrade_Notice A single instance of this class.
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
	 * @since 2.2.2
	 */
	public function __construct() {

		add_action( 'in_plugin_update_message-theme-blvd-layout-builder/tb-builder.php', array( $this, 'in_plugin_update_message' ), 10, 2 );

	}

	/**
	 * Add notice to plugin row.
	 *
	 * @since 2.2.2
	 */
	public function in_plugin_update_message( $args, $response ) {

		$message = '';

		if ( ! empty( $args['upgrade_notice'] ) ) {

			$notice = $args['upgrade_notice'];

			/*
			 * Get data from upgrade notice string.
			 *
			 * The update notice should be a string formatted
			 * like: "Compatible Themes: Foo 1.0+, Bar 1.1+"
			 */
			$notice = str_replace( '<p>Compatible Themes: ', '', $notice );

			$notice = str_replace( '</p>', '', $notice );

			$themes = explode( ', ', $notice );

			$theme_data = wp_get_theme();

			$theme_name = $theme_data->get( 'Name' );

			$required = '';

			/*
			 * Loop through themes extracted from data
			 * upgrade string.
			 *
			 * Each theme will be a string formatted
			 * like, "Foo 1.1+".
			 *
			 * So we can break this into its Name and
			 * Version, and then match it against the
			 * installed theme Name and Version.
			 */
			foreach ( $themes as $theme ) {

				/*
				 * Does one of the theme names from the upgrade
				 * notice match the current theme name?
				 */
				if ( 0 === strpos( $theme, $theme_name ) ) {

					$required = str_replace( $theme_name, '', $theme );

					$required = str_replace( '+', '', $required );

					$required = trim( $required );

				}

				break;

			}

			/*
			 * Is the installed version of the theme less
			 * than what's required?
			 *
			 * If not, we don't need to display an update
			 * notice at all.
			 */
			if ( $required && version_compare( $theme_data->get( 'Version' ), $required, '<' ) ) {

				$message = sprintf(
					__( 'This update requires that you also update to %s %s+.', 'theme-blvd-layout-builder' ),
					$theme_name,
					$required
				);

				$message = '<br><br><strong>' . $message . '</strong>';

			}

		}

		echo apply_filters( 'themeblvd_builder_in_plugin_update_message', $message );

	}

}
