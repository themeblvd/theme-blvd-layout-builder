<?php
/**
 * Theme Blvd Layout Builder API
 *
 * This sets up the default Builder elements and sample
 * layouts. Also, this class adds and API to add/remove
 * these elements and sample layouts.
 *
 * @author		Jason Bobich
 * @copyright	Copyright (c) Jason Bobich
 * @link		http://jasonbobich.com
 * @link		http://themeblvd.com
 * @package 	Theme Blvd WordPress Framework
 */
class Theme_Blvd_Builder_API {

	/*--------------------------------------------*/
	/* Properties, private
	/*--------------------------------------------*/

	/**
	 * A single instance of this class.
	 *
	 * @since 1.1.1
	 */
	private static $instance = null;

	/**
	 * A quick overview of registered elements as the
	 * API moves along. Can be accesed from admin or
	 * frontend.
	 *
	 * @since 1.1.1
	 */
	private $registered_elements = array();

	/**
	 * Core framework builder elements and settings.
	 * WP-Admin only.
	 *
	 * @since 1.1.1
	 */
	private $core_elements = array();

	/**
	 * Elements and settings added through client API
	 * mutators. WP-Admin only.
	 *
	 * @since 1.1.1
	 */
	private $client_elements = array();

	/**
	 * Elements to remove from Layout Builder.
	 * WP-Admin only.
	 *
	 * @since 1.1.1
	 */
	private $remove_elements = array();

	/**
	 * Final array of elements and settings. This combines
	 * $core_elements and $client_elements. WP-Admin only.
	 *
	 * @since 1.1.1
	 */
	private $elements = array();

	/**
	 * A quick overview of registered content blocks
	 * as the API moves along. Can be accesed from admin
	 * or frontend.
	 *
	 * @since 2.0.0
	 */
	private $registered_blocks = array();

	/**
	 * Core framework content blocks and settings.
	 * WP-Admin only.
	 *
	 * @since 2.0.0
	 */
	private $core_blocks = array();

	/**
	 * Content blocks and settings added through
	 * client API mutators. WP-Admin only.
	 *
	 * @since 2.0.0
	 */
	private $client_blocks = array();

	/**
	 * Content blocks to remove from Layout
	 * Builder. WP-Admin only.
	 *
	 * @since 2.0.0
	 */
	private $remove_blocks = array();

	/**
	 * Final array of content blocks and settings. This
	 * combines $core_blocks and $client_blocks.
	 * WP-Admin only.
	 *
	 * @since 2.0.0
	 */
	private $blocks = array();

	/**
	 * Core framework sample layouts. WP-Admin only.
	 *
	 * @since 1.1.1
	 */
	private $core_layouts = array();

	/**
	 * Sample layouts added through client API mutators.
	 * WP-Admin only.
	 *
	 * @since 1.1.1
	 */
	private $client_layouts = array();

	/**
	 * Sample layouts to remove from Layout Builder.
	 * WP-Admin only.
	 *
	 * @since 1.1.1
	 */
	private $remove_layouts = array();

	/**
	 * Final array of sample layouts. This combines
	 * $core_layouts and $client_layouts. WP-Admin only.
	 *
	 * @since 1.1.1
	 */
	private $layouts = array();

	/*--------------------------------------------*/
	/* Constructor
	/*--------------------------------------------*/

	/**
     * Creates or returns an instance of this class.
     *
     * @since 1.1.1
     *
     * @return Theme_Blvd_Builder_API A single instance of this class.
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
	 * @since 1.1.1
	 */
	private function __construct() {

		// Setup registered elements/blocks reference for frontend and
		// admin. This allows us to keep track of elements without
		// consuming as much memory on the frontend.
		$this->set_registered_elements();
		$this->set_registered_blocks();

		if ( is_admin() ) {

			// Setup framework default elements and sample
			// layouts to build onto for Builder interface.
			$this->set_core_elements();
			$this->set_core_blocks();
			$this->set_core_layouts();

			// Establish elements and sample layouts based on
			// client modifications combined with framework defaults.
			add_action( 'after_setup_theme', array( $this, 'set_elements' ), 1000 );
			add_action( 'after_setup_theme', array( $this, 'set_blocks' ), 1000 );
			add_action( 'after_setup_theme', array( $this, 'set_layouts' ), 1000 );

		}


	}

	/*--------------------------------------------*/
	/* Methods, mutators
	/*--------------------------------------------*/

	/**
	 * Set originally registered elements. As client API moves
	 * along, this will be modified, allowing elements to be
	 * registered or de-registered.
	 *
	 * @since 1.1.1
	 */
	private function set_registered_elements() {

		$this->registered_elements = array(
			'breadcrumbs',
			'columns',
			'content',
			'divider',
			'headline',
			'jumbotron',
			'post_grid_paginated',
			'post_grid',
			'post_grid_slider',
			'post_list_paginated',
			'post_list',
			'post_list_slider',
			'slogan'
		);

		// Elements requiring framework 2.5
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) {
			$this->registered_elements[] = 'html';
			$this->registered_elements[] = 'image';
			$this->registered_elements[] = 'milestones';
			$this->registered_elements[] = 'simple_slider';
			$this->registered_elements[] = 'simple_slider_popout';
			$this->registered_elements[] = 'video';
		}

		// Elements @deprecated as of framework 2.5, but being
		// added for old theme compat
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			$this->registered_elements[] = 'content';
			$this->registered_elements[] = 'tabs';
		}

		$this->registered_elements = apply_filters( 'themeblvd_registered_elements', $this->registered_elements );
	}

	/**
	 * Set original core elements. These will be later merged
	 * with API client-added elements. WP-Admin only, see constructer.
	 *
	 * @since 1.1.1
	 */
	private function set_core_elements() {

		$this->core_elements = array();

		/*--------------------------------------------*/
		/* Option helpers
		/*--------------------------------------------*/

		// Setup array for floating sidebars
		$sidebars = array();
		if ( defined( 'TB_SIDEBARS_PLUGIN_VERSION' ) ) {
			$sidebars = themeblvd_get_select( 'sidebars' );
			if ( ! $sidebars ) {
				$sidebars['null'] = __( 'You haven\'t created any floating widget areas yet.', 'themeblvd_builder' );
			}
			if ( ! defined( 'TB_SIDEBARS_PLUGIN_VERSION' ) ) {
				$sidebars['null'] = __( 'You need to have the Theme Blvd Widget Areas plugin installed for this feature.', 'themeblvd_builder' );
			}
		}

		// Setup array for categories select
		$categories_select = themeblvd_get_select( 'categories' );

		// Setup array for categories group of checkboxes
		$categories_multicheck = $categories_select;
		unset( $categories_multicheck['null'] );

		/*--------------------------------------------*/
		/* Breadcrumbs
		/*--------------------------------------------*/

		$this->core_elements['breadcrumbs'] = array();

		// Information
		$this->core_elements['breadcrumbs']['info'] = array(
			'name'		=> __( 'Breadcrumbs', 'themeblvd_builder' ),
			'id'		=> 'breadcrumbs',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_breadcrumbs',
			'shortcode'	=> null,
			'desc'		=> __( 'Breadcrumb trail for the current page.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['breadcrumbs']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		$this->core_elements['breadcrumbs']['options'] = array(
			// ...
		);

		/*--------------------------------------------*/
		/* Columns
		/*--------------------------------------------*/

		$this->core_elements['columns'] = array();

		// Information
		$this->core_elements['columns']['info'] = array(
			'name' 		=> __('Columns', 'themeblvd_builder'),
			'id'		=> 'columns',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_columns',
			'shortcode'	=> false,
			'desc' 		=> __( 'Row of columns with custom content', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['columns']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['columns']['options'] = array(
		   	'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'columns'
		    ),
		   	'setup' => array(
				'id' 		=> 'setup',
				'name'		=> __( 'Setup Columns', 'themeblvd_builder' ),
				'desc'		=> null,
				'std'		=> '1/2-1/2',
				'type'		=> 'columns',
				'options'	=> 'element'
			),
			'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide column-height'
		    ),
		    'height' => array(
		    	'id' 		=> 'height',
				'name'		=> __( 'Column Height', 'themeblvd_builder' ),
				'desc'		=> __( 'Force all columns to be equal in height, based on content. &mdash; <em>Note: This can be helpful when applying backgrounds to individual columns</em>.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '0',
				'class'		=> 'trigger'
			),
			'align' => array(
		    	'id' 		=> 'align',
				'name'		=> __( 'Content Alignment', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to align the content within the equal height columns.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'top',
				'options'	=> array(
					'top'		=> __('Top', 'themeblvd_builder'),
					'middle'	=> __('Middle', 'themeblvd_builder'),
					'bottom'	=> __('Bottom', 'themeblvd_builder')
				),
				'class'		=> 'hide receiver'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		// Options for columns element prior to dynamic content
		// block support added in Theme Blvd framework 2.5.0
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			$column_legacy_options = array(
				'col_1' => array(
					'id' 		=> 'col_1',
					'name'		=> __( 'Column #1', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the first column.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'class'		=> 'col_1',
					'options'	=> array( 'widget', 'current', 'page', 'raw' )
				),
				'col_2' => array(
					'id' 		=> 'col_2',
					'name'		=> __( 'Column #2', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the second column.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'class'		=> 'col_2',
					'options'	=> array( 'widget', 'current', 'page', 'raw' )
				),
				'col_3' => array(
					'id' 		=> 'col_3',
					'name'		=> __( 'Column #3', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the third column.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'class'		=> 'col_3',
					'options'	=> array( 'widget', 'current', 'page', 'raw' )
				),
				'col_4' => array(
					'id' 		=> 'col_4',
					'name'		=> __( 'Column #4', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the fourth column.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'class'		=> 'col_4',
					'options'	=> array( 'widget', 'current', 'page', 'raw' )
				),
				'col_5' => array(
					'id' 		=> 'col_5',
					'name'		=> __( 'Column #5', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the fifth column.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'class'		=> 'col_5',
					'options'	=> array( 'widget', 'current', 'page', 'raw' )
				),
				'subgroup_end' => array(
			    	'type'		=> 'subgroup_end'
			    )
			);

			unset( $this->core_elements['columns']['options']['subgroup_end'] );
			unset( $this->core_elements['columns']['options']['subgroup_start_2'] );
			unset( $this->core_elements['columns']['options']['height'] );
			unset( $this->core_elements['columns']['options']['align'] );
			unset( $this->core_elements['columns']['options']['subgroup_end_2'] );

			$this->core_elements['columns']['options'] = array_merge( $this->core_elements['columns']['options'], $column_legacy_options );

		}

		/*--------------------------------------------*/
		/* Content
		/*--------------------------------------------*/

		// Content element @deprecated
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			$this->core_elements['content'] = array();

			// Information
			$this->core_elements['content']['info'] = array(
				'name' 		=> __('Content', 'themeblvd_builder'),
				'id'		=> 'content',
				'query'		=> 'none',
				'hook'		=> null,
				'shortcode'	=> false,
				'desc'		=> __( 'Content from external page or current page', 'themeblvd_builder' )
			);

			// Support
			$this->core_elements['content']['support'] = array(
				'background' 	=> false,
				'popout'		=> false,
				'padding'		=> false
			);

			// Options
			$this->core_elements['content']['options'] = array(
				'subgroup_start' => array(
			    	'type'		=> 'subgroup_start'
			    ),
			    'source' => array(
			    	'id' 		=> 'source',
					'name'		=> __( 'Content Source', 'themeblvd_builder' ),
					'desc'		=> __( 'Choose where you\'d like to have content feed from. The content can either be from the current page you\'re applying this layout to or an external page.', 'themeblvd_builder' ),
					'type'		=> 'select',
					'options'	=> array(
						'current' 		=> __( 'Content from current page', 'themeblvd_builder' ),
				        'external' 		=> __( 'Content from external page', 'themeblvd_builder' ),
				        'raw'			=> __( 'Raw content', 'themeblvd_builder' ),
				        'widget_area'	=> __( 'Floating Widget Area', 'themeblvd_builder' )
					),
					'class'		=> 'custom-content-types'
				),
				'page_id' => array(
			    	'id' 		=> 'page_id',
					'name'		=> __( 'External Page', 'themeblvd_builder' ),
					'desc'		=> __( 'Choose the external page you\'d like to pull content from.', 'themeblvd_builder' ),
					'type'		=> 'select',
					'options'	=> themeblvd_get_select( 'pages' ),
					'class'		=> 'hide page-content'
				),
				'raw_content' => array(
			    	'id' 		=> 'raw_content',
					'name'		=> __( 'Raw Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter in the content you\'d like to show. You may use basic HTML, and most shortcodes.', 'themeblvd_builder' ),
					'type'		=> 'textarea',
					'class'		=> 'hide raw-content'
				),
				'raw_format' => array(
			    	'id' 		=> 'raw_format',
					'name'		=> __( 'Raw Content Formatting', 'themeblvd_builder' ),
					'desc'		=> __( 'Apply WordPress automatic formatting.', 'themeblvd_builder' ),
					'type'		=> 'checkbox',
					'std'		=> '1',
					'class'		=> 'hide raw-content'
				),
				'widget_area' => array(
			    	'id' 		=> 'widget_area',
					'name'		=> __( 'Floating Widget Area', 'themeblvd_builder' ),
					'desc'		=> __( 'Select from your floating custom widget areas. In order for a custom widget area to be "floating" you must have it configured this way in the Widget Area manager.', 'themeblvd_builder' ),
					'type'		=> 'select',
					'options'	=> $sidebars,
					'class'		=> 'hide widget_area-content'
				),
				'subgroup_end' => array(
			    	'type'		=> 'subgroup_end'
			    )
			);

			// The selection of a floating widget area is only
			// possible if the Widget Areas plugin is installed.
			if ( ! defined( 'TB_SIDEBARS_PLUGIN_VERSION' ) ) {
				unset( $this->core_elements['content']['options']['source']['options']['widget_area'] );
				unset( $this->core_elements['content']['options']['widget_area'] );
			}
		}

		/*--------------------------------------------*/
		/* Divider
		/*--------------------------------------------*/

		$this->core_elements['divider'] = array();

		// Information
		$this->core_elements['divider']['info'] = array(
			'name' 		=> __( 'Divider', 'themeblvd_builder' ),
			'id'		=> 'divider',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_divider',
			'shortcode'	=> '[divider]',
			'desc' 		=> __( 'Simple divider line to break up content', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['divider']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['divider']['options'] = array(
			'type' => array(
		    	'id' 		=> 'type',
				'name'		=> __( 'Divider Type', 'themeblvd_builder' ),
				'desc'		=> __( 'Select which style of divider you\'d like to use here.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'options'		=> array(
			        'dashed' 	=> __( 'Dashed Line', 'themeblvd_builder' ),
			        'shadow' 	=> __( 'Shadow Line', 'themeblvd_builder' ),
					'solid' 	=> __( 'Solid Line', 'themeblvd_builder' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Headline
		/*--------------------------------------------*/

		$this->core_elements['headline'] = array();

		// Information
		$this->core_elements['headline']['info'] = array(
			'name' 		=> __( 'Headline', 'themeblvd_builder' ),
			'id'		=> 'headline',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_headline',
			'shortcode'	=> false,
			'desc'		=> __( 'Simple &lt;H&gt; header title', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['headline']['support'] = array(
			'background' 	=> true,
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['headline']['options'] = array(
			'text' => array(
				'id' 		=> 'text',
				'name'		=> __( 'Headline Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the text you\'d like to use for your headline. It is better if you use plain text here and not try and use HTML tags. Additionally, if you\'d like to automatically pull the title from the current page, insert <em>%page_title%</em> here.', 'themeblvd_builder' ),
				'type'		=> 'textarea',
			),
			'tagline' => array(
		    	'id' 		=> 'tagline',
				'name'		=> __( 'Tagline', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter any text you\'d like to display below the headline as a tagline. Feel free to leave this blank. It is better if you use plain text here and not try and use HTML tags.', 'themeblvd_builder' ),
				'type'		=> 'textarea'
			),
		    'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Headline Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the type of header tag you\'d like to wrap this headline.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'options'	=> array(
					'h1' => __( '&lt;h1&gt;Your Headline&lt;/h1&gt;', 'themeblvd_builder' ),
					'h2' => __( '&lt;h2&gt;Your Headline&lt;/h2&gt;', 'themeblvd_builder' ),
					'h3' => __( '&lt;h3&gt;Your Headline&lt;/h3&gt;', 'themeblvd_builder' ),
					'h4' => __( '&lt;h4&gt;Your Headline&lt;/h4&gt;', 'themeblvd_builder' ),
					'h5' => __( '&lt;h5&gt;Your Headline&lt;/h5&gt;', 'themeblvd_builder' ),
					'h6' => __( '&lt;h6&gt;Your Headline&lt;/h6&gt;', 'themeblvd_builder' )
				)
			),
			'align' => array(
				'id' 		=> 'align',
				'name'		=> __( 'Headline Alignment', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like the text in this title to align.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'options'		=> array(
			        'left' 		=> __( 'Align Left', 'themeblvd_builder' ),
			        'center' 	=> __( 'Center', 'themeblvd_builder' ),
					'right' 	=> __( 'Align Right', 'themeblvd_builder' )
				)
			)
		);

		/*--------------------------------------------*/
		/* HTML Block
		/*--------------------------------------------*/

		$this->core_elements['html'] = array();

		// Information
		$this->core_elements['html']['info'] = array(
			'name'		=> __( 'HTML', 'themeblvd_builder' ),
			'id'		=> 'html',
			'query'		=> 'none',
			'hook'		=> 'html',
			'shortcode'	=> '',
			'desc'		=> __( 'A block of HTML/JavaScript code.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['html']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['html']['options'] = array(
			'html' => array(
				'id' 		=> 'html',
				'type'		=> 'code',
				'lang'		=> 'html',
				'class'		=> 'tight' // CSS class will remove margin from bottom of option so it looks nicer alone w/ no description or following options
		    )
		);

		/*--------------------------------------------*/
		/* Image
		/*--------------------------------------------*/

		$this->core_elements['image'] = array();

		// Information
		$this->core_elements['image']['info'] = array(
			'name'		=> __( 'Image', 'themeblvd_builder' ),
			'id'		=> 'image',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_image',
			'shortcode'	=> null,
			'desc'		=> __( 'An image, which can be linked or framed to look like a "featured" image.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['image']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['image']['options'] = array(
			'image' => array(
		    	'id' 		=> 'image',
				'name'		=> __( 'Image URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the image to be used.', 'themeblvd_builder' ),
				'type'		=> 'upload',
				'advanced'	=> true
			),
			'subgroup_start' => array(
				'type' 		=> 'subgroup_start',
				'class'		=> 'show-hide-toggle desc-toggle'
			),
			'link' => array(
				'id' 		=> 'link',
				'name'		=> __( 'Link', 'themeblvd_builder' ),
				'desc'		=> __( 'Select if and how this image should be linked.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'options'	=> array(
			        'none'		=> __( 'No Link', 'themeblvd' ),
			        '_self' 	=> __( 'Link to webpage in same window.', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'Link to webpage in new window.', 'themeblvd_builder' ),
			        'image' 	=> __( 'Link to image in lightbox popup.', 'themeblvd_builder' ),
			        'video' 	=> __( 'Link to video in lightbox popup.', 'themeblvd_builder' )
				),
				'class'		=> 'trigger'
			),
			'link_url' => array(
				'id' 		=> 'link_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> array(
			        '_self' 	=> __( 'Enter a URL to a webpage.<br />Ex: http://yoursite.com/example', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'Enter a URL to a webpage.<br />Ex: http://google.com', 'themeblvd_builder' ),
			        'image' 	=> __( 'Enter a URL to an image file.<br />Ex: http://yoursite.com/uploads/image.jpg', 'themeblvd_builder' ),
			        'video' 	=> __( 'Enter a URL to a YouTube or Vimeo page.<br />Ex: http://vimeo.com/11178250‎</br />Ex: https://youtube.com/watch?v=ginTCwWfGNY', 'themeblvd_builder' )
				),
				'type'		=> 'text',
				'std'		=> '',
				'pholder'	=> 'http://',
				'class'		=> 'receiver receiver-_self receiver-_blank receiver-image receiver-video'
			),
			'subgroup_end' => array(
				'type' 		=> 'subgroup_end'
			),
			'frame' => array(
		    	'id' 		=> 'frame',
				'name'		=> __( 'Image Frame', 'themeblvd_builder' ),
				'desc'		=> __( 'Add frame around the image.', 'themeblvd_builder' ),
				'type'		=> 'checkbox'
			)
		);

		/*--------------------------------------------*/
		/* Jumbotron
		/*--------------------------------------------*/

		$this->core_elements['jumbotron'] = array();

		// Information
		$this->core_elements['jumbotron']['info'] = array(
			'name'		=> __( 'Jumbotron', 'themeblvd_builder' ),
			'id'		=> 'jumbotron',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_jumbotron',
			'shortcode'	=> '[jumbotron]',
			'desc'		=> __( 'Bootstrap\'s Jumbotron unit, also knows as a "Hero" unit.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['jumbotron']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['jumbotron']['options'] = array(
			'title' => array(
				'id' 		=> 'title',
				'name' 		=> __( 'Title', 'themeblvd_builder'),
				'desc'		=> __( 'Enter the text you\'d like to show for a title.', 'themeblvd_builder'),
				'type'		=> 'text'
		    ),
			'content' => array(
				'id' 		=> 'content',
				'name' 		=> __( 'Content', 'themeblvd_builder'),
				'desc'		=> __( 'Enter in the content you\'d like to show. You may use basic HTML, and most shortcodes.', 'themeblvd_builder'),
				'std'		=> '',
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
		    ),
		    'wpautop' => array(
		    	'id' 		=> 'wpautop',
				'name'		=> __( 'Content Formatting', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply WordPress automatic formatting to above content.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
			'text_color' => array(
				'id' 		=> 'text_color',
				'name' 		=> __( 'Text Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select a text color for the Jumbotron unit.', 'themeblvd_builder'),
				'std'		=> '#444444',
				'type'		=> 'color'
		    ),
		    'text_align' => array(
				'id' 		=> 'text_align',
				'name' 		=> __( 'Text Alignment', 'themeblvd_builder'),
				'desc'		=> __( 'Select how you\'d like the text within the unit aligned.', 'themeblvd_builder'),
				'std'		=> 'center',
				'type'		=> 'select',
				'options'	=> array(
					'left' 		=> __( 'Left', 'themeblvd_builder' ),
					'right' 	=> __( 'Right', 'themeblvd_builder' ),
					'center' 	=> __( 'Center', 'themeblvd_builder' )
				)
		    ),
		    'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide'
		    ),
			'button' => array(
		    	'id' 		=> 'button',
				'name'		=> __( 'Button', 'themeblvd_builder' ),
				'desc'		=> __( 'Show button at the bottom of unit?', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'hide receiver show-hide-toggle'
		    ),
			'button_color' => array(
				'id' 		=> 'button_color',
				'name'		=> __( 'Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what color you\'d like to use for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'trigger',
				'options'	=> themeblvd_colors()
			),
			'button_custom' => array(
				'id' 		=> 'button_custom',
				'name'		=> __( 'Custom Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Configure a custom style for the button.', 'themeblvd_builder' ),
				'std'		=> array(
					'bg' 				=> '#ffffff',
					'bg_hover'			=> '#ebebeb',
					'border' 			=> '#cccccc',
					'text'				=> '#333333',
					'text_hover'		=> '#333333',
					'include_bg'		=> 1,
					'include_border'	=> 1
				),
				'type'		=> 'button',
				'class'		=> 'receiver receiver-custom'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'button_text' => array(
				'id' 		=> 'button_text',
				'name'		=> __( 'Button Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the button.', 'themeblvd_builder' ),
				'std'		=> 'Get Started Today!',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_size' => array(
				'id' 		=> 'button_size',
				'name'		=> __( 'Button Size', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the size you\'d like used for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'large',
				'class'		=> 'hide receiver',
				'options'	=> array(
					'mini' 		=> __( 'Mini', 'themeblvd_builder' ),
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'default' 	=> __( 'Normal', 'themeblvd_builder' ),
					'large' 	=> __( 'Large', 'themeblvd_builder' ),
					'x-large' 	=> __( 'Extra Large', 'themeblvd_builder' )
				)
			),
			'button_url' => array(
				'id' 		=> 'button_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the full URL where you want the button\'s link to go.', 'themeblvd_builder' ),
				'std'		=> 'http://www.your-site.com/your-landing-page',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_target' => array(
				'id' 		=> 'button_target',
				'name'		=> __( 'Link Target', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you want the button to open the webpage.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'	=> array(
			        '_self' 	=> __( 'Same Window', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'New Window', 'themeblvd_builder' ),
			        'lightbox' 	=> __( 'Lightbox Popup', 'themeblvd_builder' )
				)
			),
			'button_icon_before' => array(
				'id' 		=> 'button_icon_before',
				'name'		=> __( 'Icon Before Button Text (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Icon before text of button. This can be any FontAwesome vector icon ID.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'vector',
				'class'		=> 'hide receiver'
			),
			'button_icon_after' => array(
				'id' 		=> 'button_icon_after',
				'name'		=> __( 'Icon After Button Text (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Icon after text of button. This can be any FontAwesome vector icon ID.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'vector',
				'class'		=> 'hide receiver'
			),
			'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			unset( $this->core_elements['jumbotron']['options']['button_icon_before'] );
			unset( $this->core_elements['jumbotron']['options']['button_icon_after'] );
		}

		/*--------------------------------------------*/
		/* Milestones
		/*--------------------------------------------*/

		$this->core_elements['milestones'] = array();

		// Information
		$this->core_elements['milestones']['info'] = array(
			'name'		=> __( 'Milestones', 'themeblvd_builder' ),
			'id'		=> 'milestones',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_milestones',
			'shortcode'	=> null,
			'desc'		=> __( 'A set of milestone numbers followed by taglines.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['milestones']['support'] = array(
			'background' 	=> true,
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['milestones']['options'] = array(
			'milestones' => array(
		    	'id' 		=> 'milestones',
				'name'		=> null,
				'desc'		=> null,
				'type'		=> 'milestones'
			),
			'milestone_size' => array(
				'id'		=> 'milestone_size',
				'name' 		=> __( 'Milestone Text Size', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the size of the milestone numbers.', 'themeblvd_builder' ),
				'std'		=> array('size' => '40px'),
				'type'		=> 'typography',
				'atts'		=> array('size')
		    ),
		    'text_size' => array(
				'id'		=> 'text_size',
				'name' 		=> __( 'Description Text Size', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the size of the milestone description.', 'themeblvd_builder' ),
				'std'		=> array('size' => '15px'),
				'type'		=> 'typography',
				'atts'		=> array('size')
		    )
		);

		/*--------------------------------------------*/
		/* Post Grid (paginated)
		/*--------------------------------------------*/

		$this->core_elements['post_grid_paginated'] = array();

		// Information
		$this->core_elements['post_grid_paginated']['info'] = array(
			'name'		=> __( 'Post Grid (paginated)', 'themeblvd_builder' ),
			'id'		=> 'post_grid_paginated',
			'query'		=> 'primary',
			'hook'		=> 'themeblvd_post_grid_paginated',
			'shortcode'	=> '[post_grid]',
			'desc'		=> __( 'Full paginated grid of posts', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_grid_paginated']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_grid_paginated']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ&numberposts=10<br><br><em>Note: The number of posts displayed is determined from the rows and columns.</em>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'columns' => array(
		    	'id' 		=> 'columns',
				'name'		=> __( 'Columns', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how many posts per row you\'d like displayed.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> '3',
				'options'	=> array(
			        '2' 	=> __( '2 Columns', 'themeblvd_builder' ),
			        '3' 	=> __( '3 Columns', 'themeblvd_builder' ),
			        '4' 	=> __( '4 Columns', 'themeblvd_builder' ),
			        '5' 	=> __( '5 Columns', 'themeblvd_builder' )
				)
			),
			'rows' => array(
		    	'id' 		=> 'rows',
				'name'		=> __( 'Rows per page', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of rows <strong>per page</strong> you\'d like to show. The number you enter here will be multiplied by the amount of columns you selected in the previous option to figure out how many posts should be showed on each page. You can leave this option blank if you\'d like to show all posts from the categories you\'ve selected on a single page.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3'
			),
			'crop' => array(
		    	'id' 		=> 'crop',
				'name'		=> __( 'Custom Image Crop Size (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a custom image crop size. Always leave this blank unless you know what you\'re doing here. When left blank, the theme will generate this crop size for you depending on the amount of columns in your post grid.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> ''
			)
		);

		/*--------------------------------------------*/
		/* Post Grid
		/*--------------------------------------------*/

		$this->core_elements['post_grid'] = array();

		// Information
		$this->core_elements['post_grid']['info'] = array(
			'name'		=> __( 'Post Grid', 'themeblvd_builder' ),
			'id'		=> 'post_grid',
			'query'		=> 'secondary',
			'hook'		=> 'themeblvd_post_grid',
			'shortcode'	=> '[post_grid]',
			'desc'		=> __( 'Grid of posts followed by optional link', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_grid']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_grid']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'offset' => array(
		    	'id' 		=> 'offset',
				'name'		=> __( 'Offset', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>. Utilizing this option could be useful, for example, if you wanted to have the first post in an element above this one, and then you could offset this set by <em>1</em> so the posts start after that post in the previous element. If that makes no sense, just ignore this option and leave it at <em>0</em>!', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ<br><br><em>Note: You cannot set the number of posts because this is generated in a grid based on the rows and columns.</em>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'columns' => array(
		    	'id' 		=> 'columns',
				'name'		=> __( 'Columns', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how many posts per row you\'d like displayed.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> '3',
				'options'	=> array(
			        '2' 	=> __( '2 Columns', 'themeblvd_builder' ),
			        '3' 	=> __( '3 Columns', 'themeblvd_builder' ),
			        '4' 	=> __( '4 Columns', 'themeblvd_builder' ),
			        '5' 	=> __( '5 Columns', 'themeblvd_builder' )
				)
			),
			'rows' => array(
		    	'id' 		=> 'rows',
				'name'		=> __( 'Rows', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of rows you\'d like to show. The number you enter here will be multiplied by the amount of columns you selected in the previous option to figure out how many posts should be showed. You can leave this option blank if you\'d like to show all posts from the categories you\'ve selected.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3'
			),
			'crop' => array(
		    	'id' 		=> 'crop',
				'name'		=> __( 'Custom Image Crop Size (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a custom image crop size. Always leave this blank unless you know what you\'re doing here. When left blank, the theme will generate this crop size for you depending on the amount of columns in your post grid.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> ''
			)
		);

		/*--------------------------------------------*/
		/* Post Grid Slider
		/*--------------------------------------------*/

		$this->core_elements['post_grid_slider'] = array();

		// Information
		$this->core_elements['post_grid_slider']['info'] = array(
			'name'		=> __( 'Post Grid Slider', 'themeblvd_builder' ),
			'id'		=> 'post_grid_slider',
			'query'		=> 'secondary',
			'hook'		=> 'themeblvd_post_grid_slider',
			'shortcode'	=> '[post_grid_slider]',
			'desc'		=> __( 'Slider of posts in a grid', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_grid_slider']['support'] = array(
			'background' 	=> true,
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_grid_slider']['options'] = array(
			'fx' => array(
		    	'id' 		=> 'fx',
				'name'		=> __( 'Transition Effect', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the effect you\'d like used to transition from one slide to the next.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'slide',
				'options'	=> array(
			        'fade' 	=> __( 'Fade', 'themeblvd_builder' ),
					'slide'	=> __( 'Slide', 'themeblvd_builder' )
				)
			),
			'timeout' => array(
		    	'id' 		=> 'timeout',
				'name'		=> __( 'Speed', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of seconds you\'d like in between trasitions. You may use <em>0</em> to disable the slider from auto advancing.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0'
			),
			'nav_standard' => array(
				'id'		=> 'nav_standard',
				'name'		=> __( 'Show standard slideshow navigation?', 'themeblvd_builder' ),
				'desc'		=> __( 'The standard navigation are the little dots that appear below the slider.' , 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show navigation.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
				)
			),
			'nav_arrows' => array(
				'id'		=> 'nav_arrows',
				'name'		=> __( 'Show next/prev slideshow arrows?', 'themeblvd_builder' ),
				'desc'		=> __( 'These arrows allow the user to navigation from one slide to the next.' , 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show arrows.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show them.', 'themeblvd_builder' )
				)
			),
			'pause_play' => array(
				'id'		=> 'pause_play',
				'name'		=> __( 'Show pause/play button?', 'themeblvd_builder' ),
				'desc'		=> __('Note that if you have the speed set to 0, this option will be ignored. ', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show pause/play button.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
				)
			),
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'numberposts' => array(
		    	'id' 		=> 'numberposts',
				'name'		=> __( 'Total Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the maximum number of posts you\'d like to show from the categories selected. You can use <em>-1</em> to show all posts from the selected categories.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '-1',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'offset' => array(
		    	'id' 		=> 'offset',
				'name'		=> __( 'Offset', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>. Utilizing this option could be useful, for example, if you wanted to have the first post in an element above this one, and then you could offset this set by <em>1</em> so the posts start after that post in the previous element. If that makes no sense, just ignore this option and leave it at <em>0</em>!', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'columns' => array(
		    	'id' 		=> 'columns',
				'name'		=> __( 'Columns', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how many posts per row you\'d like displayed.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> '3',
				'options'	=> array(
			        '2' 	=> __( '2 Columns', 'themeblvd_builder' ),
			        '3' 	=> __( '3 Columns', 'themeblvd_builder' ),
			        '4' 	=> __( '4 Columns', 'themeblvd_builder' ),
			        '5' 	=> __( '5 Columns', 'themeblvd_builder' )
				)
			),
			'rows' => array(
		    	'id' 		=> 'rows',
				'name'		=> __( 'Rows per slide', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of rows <strong>per slide</strong> you\'d like to show. The number you enter here will be multiplied by the amount of columns you selected in the previous option to figure out how many posts should be showed on each slide.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3'
			),
			'crop' => array(
		    	'id' 		=> 'crop',
				'name'		=> __( 'Custom Image Crop Size (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a custom image crop size. Always leave this blank unless you know what you\'re doing here. When left blank, the theme will generate this crop size for you depending on the amount of columns in your post grid.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> ''
			)
		);

		/*--------------------------------------------*/
		/* Post List (paginated)
		/*--------------------------------------------*/

		$this->core_elements['post_list_paginated'] = array();

		// Information
		$this->core_elements['post_list_paginated']['info'] = array(
			'name' 		=> __( 'Post List (paginated)', 'themeblvd_builder' ),
			'id'		=> 'post_list_paginated',
			'query'		=> 'primary',
			'hook'		=> 'themeblvd_post_list_paginated',
			'shortcode'	=> '[post_list]',
			'desc'		=> __( 'Full paginated list of posts', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_list_paginated']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_list_paginated']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'posts_per_page' => array(
		    	'id' 		=> 'posts_per_page',
				'name'		=> __( 'Posts per page', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of posts <strong>per page</strong> you\'d like to show. You can enter <em>-1</em> if you\'d like to show all posts from the categories you\'ve selected on a single page.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '6',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ&posts_per_page=10', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end',
		    ),
			'thumbs' => array(
				'id' 		=> 'thumbs',
				'name' 		=> __( 'Featured Images', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the size of the post list\'s thumbnails or whether you\'d like to hide them all together when posts are listed.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'small'		=> __( 'Show small thumbnails.', 'themeblvd_builder' ),
					'full' 		=> __( 'Show full-width thumbnails.', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide thumbnails.', 'themeblvd_builder' )
				)
			),
			'content' => array(
				'id' 		=> 'content',
				'name' 		=> __( 'Show excerpts of full content?', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Choose whether you want to show full content or post excerpts only.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'content'	=> __( 'Show full content.', 'themeblvd_builder' ),
					'excerpt' 	=> __( 'Show excerpt only.', 'themeblvd_builder' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Post List
		/*--------------------------------------------*/

		$this->core_elements['post_list'] = array();

		// Information
		$this->core_elements['post_list']['info'] = array(
			'name'		=> __( 'Post List', 'themeblvd_builder' ),
			'id'		=> 'post_list',
			'query'		=> 'secondary',
			'hook'		=> 'themeblvd_post_list',
			'shortcode'	=> '[post_list]',
			'desc'		=> __( 'List of posts followed by optional link', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_list']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_list']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'numberposts' => array(
		    	'id' 		=> 'numberposts',
				'name'		=> __( 'Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the <strong>total number</strong> of posts you\'d like to show. You can enter <em>-1</em> if you\'d like to show all posts from the categories you\'ve selected.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '6',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'offset' => array(
		    	'id' 		=> 'offset',
				'name'		=> __( 'Offset', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>. Utilizing this option could be useful, for example, if you wanted to have the first post in an element above this one, and then you could offset this set by <em>1</em> so the posts start after that post in the previous element. If that makes no sense, just ignore this option and leave it at <em>0</em>!', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ&numberposts=10', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'thumbs' => array(
				'id' 		=> 'thumbs',
				'name' 		=> __( 'Featured Images', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Select the size of the post list\'s thumbnails or whether you\'d like to hide them all together when posts are listed.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'small'		=> __( 'Show small thumbnails.', 'themeblvd_builder' ),
					'full' 		=> __( 'Show full-width thumbnails.', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide thumbnails.', 'themeblvd_builder' )
				)
			),
			'content' => array(
				'id' 		=> 'content',
				'name' 		=> __( 'Show excerpts of full content?', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Choose whether you want to show full content or post excerpts only.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'content'	=> __( 'Show full content.', 'themeblvd_builder' ),
					'excerpt' 	=> __( 'Show excerpt only.', 'themeblvd_builder' )
				)
			),
			'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide'
		    )
		);

		/*--------------------------------------------*/
		/* Post List Slider
		/*--------------------------------------------*/

		$this->core_elements['post_list_slider'] = array();

		// Information
		$this->core_elements['post_list_slider']['info'] = array(
			'name'		=> __( 'Post List Slider', 'themeblvd_builder' ),
			'id'		=> 'post_list_slider',
			'query'		=> 'secondary',
			'hook'		=> 'themeblvd_post_list_slider',
			'shortcode'	=> '[post_list_slider]',
			'desc'		=> __( 'Slider of posts listed out', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_list_slider']['support'] = array(
			'background' 	=> true,
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_list_slider']['options'] = array(
			'fx' => array(
		    	'id' 		=> 'fx',
				'name'		=> __( 'Transition Effect', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the effect you\'d like used to transition from one slide to the next.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'slide',
				'options'	=> array(
			        'fade' 	=> 'Fade',
					'slide'	=> 'Slide'
				)
			),
			'timeout' => array(
		    	'id' 		=> 'timeout',
				'name'		=> __( 'Speed', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of seconds you\'d like in between trasitions. You may use <em>0</em> to disable the slider from auto advancing.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0'
			),
			'nav_standard' => array(
				'id'		=> 'nav_standard',
				'name'		=> __( 'Show standard slideshow navigation?', 'themeblvd_builder' ),
				'desc'		=> __( 'The standard navigation are the little dots that appear below the slider.' , 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show navigation.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
				)
			),
			'nav_arrows' => array(
				'id'		=> 'nav_arrows',
				'name'		=> __( 'Show next/prev slideshow arrows?', 'themeblvd_builder' ),
				'desc'		=> __( 'These arrows allow the user to navigation from one slide to the next.' , 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show arrows.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show them.', 'themeblvd_builder' )
				)
			),
			'pause_play' => array(
				'id'		=> 'pause_play',
				'name'		=> __( 'Show pause/play button?', 'themeblvd_builder' ),
				'desc'		=> __('Note that if you have the speed set to 0, this option will be ignored. ', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show pause/play button.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
				)
			),
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'numberposts' => array(
		    	'id' 		=> 'numberposts',
				'name'		=> __( 'Total Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the maximum number of posts you\'d like to show from the categories selected. You can use <em>-1</em> to show all posts from the selected categories.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '-1',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'offset' => array(
		    	'id' 		=> 'offset',
				'name'		=> __( 'Offset', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>. Utilizing this option could be useful, for example, if you wanted to have the first post in an element above this one, and then you could offset this set by <em>1</em> so the posts start after that post in the previous element. If that makes no sense, just ignore this option and leave it at <em>0</em>!', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ&numberposts=10', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'thumbs' => array(
				'id' 		=> 'thumbs',
				'name' 		=> __( 'Featured Images', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Select the size of the post list\'s thumbnails or whether you\'d like to hide them all together when posts are listed.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'small'		=> __( 'Show small thumbnails.', 'themeblvd_builder' ),
					'full' 		=> __( 'Show full-width thumbnails.', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide thumbnails.', 'themeblvd_builder' )
				)
			),
			'content' => array(
				'id' 		=> 'content',
				'name' 		=> __( 'Show excerpts of full content?', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Choose whether you want to show full content or post excerpts only.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'content'	=> __( 'Show full content.', 'themeblvd_builder' ),
					'excerpt' 	=> __( 'Show excerpt only.', 'themeblvd_builder' )
				)
			),
			'posts_per_slide' => array(
		    	'id' 		=> 'posts_per_slide',
				'name'		=> __( 'Posts per slide', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of posts <strong>per slide</strong> you\'d like to show.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3'
			)
		);

		/*--------------------------------------------*/
		/* Post Slider
		/*--------------------------------------------*/

		if ( defined( 'TB_SLIDERS_PLUGIN_VERSION' ) ) {

			$this->core_elements['post_slider'] = array();

			// Information
			$this->core_elements['post_slider']['info'] = array(
				'name'		=> __( 'Post Slider', 'themeblvd_builder' ),
				'id'		=> 'post_slider',
				'query'		=> 'secondary',
				'hook'		=> 'themeblvd_post_slider',
				'shortcode'	=> '[post_slider]',
				'desc'		=> __( 'Slider generated from group of posts', 'themeblvd_builder' )
			);

			// Support
			$this->core_elements['post_slider']['support'] = array(
				'background' 	=> true,
				'popout'		=> false,
				'padding'		=> true
			);

			// Options
			$this->core_elements['post_slider']['options'] = array(
				'post_slider_desc' => array(
					'id' 		=> 'post_slider_desc',
					'desc' 		=> __( 'The "Post Slider" element works with the <a href="http://wordpress.org/extend/plugins/theme-blvd-sliders" target="_blank">Theme Blvd Sliders</a> plugin you\'ve installed. It works a little differently than the framework\'s default "Post List Slider" and "Post Grid Slider" elements. The point of this element is to mimic custom sliders setup under the Slider Manager, but provide you a way to automatically set them up by feeding the slides directly from posts.', 'themeblvd_builder' ),
					'type' 		=> 'info'
				),
				'subgroup_start' => array(
			    	'type'		=> 'subgroup_start',
			    	'class'		=> 'show-hide-toggle'
			    ),
				'fx' => array(
			    	'id' 		=> 'fx',
					'name'		=> __( 'Transition Effect', 'themeblvd_builder' ),
					'desc'		=> __( 'Select the effect you\'d like used to transition from one slide to the next.', 'themeblvd_builder' ),
					'type'		=> 'select',
					'std'		=> 'slide',
					'options'	=> array(
				        'fade' 	=> __( 'Fade', 'themeblvd_builder' ),
						'slide'	=> __( 'Slide', 'themeblvd_builder' )
					),
					'class' 	=> 'trigger'
				),
				'smoothheight' => array(
					'id'		=> 'smoothheight',
					'name'		=> __( 'Smooth Height', 'themeblvd_builder' ),
					'desc'		=> __( 'When using the "Slide" transition, this will allow the height of each slide to adjust automatically if slides are not equal in height.', 'themeblvd_builder' ),
					'std'		=> 'true',
					'type'		=> 'select',
					'options'	=> array(
			            'true' 	=> 'Yes, enable smoothHeight.',
						'false'	=> 'No, display as height of tallest slide.'
					),
					'class'		=> 'hide receiver receiver-slide'
				),
				'subgroup_end' => array(
			    	'type'		=> 'subgroup_end'
			    ),
				'timeout' => array(
			    	'id' 		=> 'timeout',
					'name'		=> __( 'Speed', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter the number of seconds you\'d like in between trasitions. You may use <em>0</em> to disable the slider from auto advancing.', 'themeblvd_builder' ),
					'type'		=> 'text',
					'std'		=> '3'
				),
				'nav_standard' => array(
					'id'		=> 'nav_standard',
					'name'		=> __( 'Show standard slideshow navigation?', 'themeblvd_builder' ),
					'desc'		=> __( 'The standard navigation are the little dots that appear below the slider.' , 'themeblvd_builder' ),
					'std'		=> '1',
					'type'		=> 'select',
					'options'	=> array(
			            '1'	=> __( 'Yes, show navigation.', 'themeblvd_builder' ),
			            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
					)
				),
				'nav_arrows' => array(
					'id'		=> 'nav_arrows',
					'name'		=> __( 'Show next/prev slideshow arrows?', 'themeblvd_builder' ),
					'desc'		=> __( 'These arrows allow the user to navigation from one slide to the next.' , 'themeblvd_builder' ),
					'std'		=> '1',
					'type'		=> 'select',
					'options'	=> array(
			            '1'	=> __( 'Yes, show arrows.', 'themeblvd_builder' ),
			            '0'	=> __( 'No, don\'t show them.', 'themeblvd_builder' )
					)
				),
				'pause_play' => array(
					'id'		=> 'pause_play',
					'name'		=> __( 'Show pause/play button?', 'themeblvd_builder' ),
					'desc'		=> __( 'Note that if you have the speed set to 0, this option will be ignored.', 'themeblvd_builder' ),
					'std'		=> '1',
					'type'		=> 'select',
					'options'	=> array(
			            '1'	=> __( 'Yes, show pause/play button.', 'themeblvd_builder' ),
			            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
					)
				),
				'pause_on_hover' => array(
					'id'		=> 'pause_on_hover',
					'name'		=> __( 'Enable pause on hover?', 'themeblvd_builder' ),
					'desc'		=> __( 'Select if you\'d like to implement the pause on hover feature.' , 'themeblvd_builder' ),
					'std'		=> 'disable',
					'type'		=> 'select',
					'options'	=> array(
			            'pause_on'		=> __( 'Pause on hover only.', 'themeblvd_builder' ),
			            'pause_on_off'	=> __( 'Pause on hover and resume when hovering off.', 'themeblvd_builder' ),
			            'disable'		=> __( 'No, disable this all together.', 'themeblvd_builder' ),
					)
				),
				'subgroup_start_2' => array(
			    	'type'		=> 'subgroup_start',
			    	'class'		=> 'show-hide-toggle'
			    ),
				'image' => array(
					'id'		=> 'image',
					'name'		=> __( 'Image Display', 'themeblvd_builder' ),
					'desc'		=> __( 'Select how you\'d like the "featured image" from the post to be displayed in the slider.', 'themeblvd_builder' ),
					'std'		=> 'full',
					'type'		=> 'select',
					'options'	=> array(
						'full' 			=> __( 'Full Size', 'themeblvd_builder' ),
						'align-left'	=> __( 'Aligned Left', 'themeblvd_builder' ),
						'align-right'	=> __( 'Aligned Right', 'themeblvd_builder' )
					),
					'class' 	=> 'trigger'
				),
				'image_size' => array(
					'id'		=> 'image_size',
					'name'		=> __( 'Image Crop Size', 'themeblvd_builder' ),
					'desc'		=> __( 'When your image is set to display "Full Size" you can enter a crustom crop size here.', 'themeblvd_builder' ),
					'std'		=> 'slider-large',
					'type'		=> 'text',
					'class'		=> 'hide receiver receiver-full'
				),
				'subgroup_end_2' => array(
			    	'type'		=> 'subgroup_end'
			    ),
				'image_link' => array(
					'id'		=> 'image_link',
					'name'		=> __( 'Image Link', 'themeblvd_builder' ),
					'desc'		=> __( 'Select how you\'d like the image link to work for each post.', 'themeblvd_builder' ),
					'std'		=> 'permalink',
					'type'		=> 'select',
					'options'	=> array(
						'option' 	=> __( 'Use each post\'s current featured image link setting.', 'themeblvd_builder' ),
						'permalink' => __( 'Link each image to its post.', 'themeblvd_builder' ),
						'lightbox'	=> __( 'Link each image to enlarged featured image in lightbox.', 'themeblvd_builder' ),
						'none'		=> __( 'Images do not link anywhere.', 'themeblvd_builder' )
					)
				),
				'button' => array(
					'id'		=> 'button',
					'name'		=> __( 'Button Text Leading to Post', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter in the text you\'d like for the button placed after the excerpt leading to the post. Leave blank to not include a button at all.<br><br>Ex: Read More', 'themeblvd_builder' ),
					'pholder'	=> __( 'Leave blank for no button...', 'themeblvd_builder' ),
					'std'		=> '',
					'type'		=> 'text'
				),
				'subgroup_start_3' => array(
			    	'type'		=> 'subgroup_start',
			    	'class'		=> 'show-hide-toggle'
			    ),
				'source' => array(
			    	'id' 		=> 'source',
					'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
					'desc'		=> __( 'Select how you\'d like to pull posts to generate this slider.', 'themeblvd_builder' ),
					'type'		=> 'select',
					'std'		=> 'tag',
					'options'	=> array(
				        'category' 	=> __( 'Category', 'themeblvd_builder' ),
				        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
				        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
					),
					'class' 	=> 'trigger'
				),
				'category' => array(
			    	'id' 		=> 'category',
					'name'		=> __( 'Category', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter a category slug to pull most recent posts from.', 'themeblvd_builder' ),
					'type'		=> 'text',
					'class' 	=> 'hide receiver receiver-category'
				),
				'tag' => array(
			    	'id' 		=> 'tag',
					'name'		=> __( 'Tag', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter a tag to pull most recent posts from.', 'themeblvd_builder' ),
					'type'		=> 'text',
					'class' 	=> 'hide receiver receiver-tag'
				),
				'numberposts' => array(
			    	'id' 		=> 'numberposts',
					'name'		=> __( 'Total Number of Posts', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter the maximum number of posts you\'d like to pull. You can use <em>-1</em> to show all posts from the selected criteria.', 'themeblvd_builder' ),
					'type'		=> 'text',
					'std'		=> '5',
					'class' 	=> 'hide receiver receiver-category receiver-tag'
				),
				'orderby' => array(
			    	'id' 		=> 'orderby',
					'name'		=> __( 'Order By', 'themeblvd_builder' ),
					'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
					'type'		=> 'select',
					'std'		=> 'date',
					'options'	=> array(
				        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
				        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
				        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
				        'rand' 			=> __( 'Random', 'themeblvd_builder' )
					),
					'class' 	=> 'hide receiver receiver-category receiver-tag'
				),
				'order' => array(
			    	'id' 		=> 'order',
					'name'		=> __( 'Order', 'themeblvd_builder' ),
					'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
					'type'		=> 'select',
					'std'		=> 'DESC',
					'options'	=> array(
				        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
				        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
					),
					'class' 	=> 'hide receiver receiver-category receiver-tag'
				),
				'query' => array(
			    	'id' 		=> 'query',
					'name'		=> __( 'Custom Query', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ&numberposts=10', 'themeblvd_builder' ),
					'type'		=> 'text',
					'class' 	=> 'hide receiver receiver-query'
				),
				'subgroup_end_3' => array(
			    	'type'		=> 'subgroup_end'
			    ),
			    'mobile_fallback' => array(
			    	'id' 		=> 'mobile_fallback',
					'name'		=> __( 'How to display on mobile devices?', 'themeblvd_builder' ),
					'desc'		=> __( 'Select how you\'d like this slider to be displayed on mobile devices. Sometimes full, animated sliders can cause problems on mobile devices, and so you may find it\'s better to setup a fallback option.', 'themeblvd_builder' ),
					'type'		=> 'radio',
					'std'		=> 'full_list',
					'options'	=> array(
						'full_list' 	=> __( 'List out slides for a more user-friendly mobile experience.', 'themeblvd_builder' ),
						'first_slide' 	=> __( 'Show first slide only for a more simple mobile experience.', 'themeblvd_builder' ),
						'display' 		=> __( 'Attempt to show full animated slider on mobile devices.', 'themeblvd_builder' )
					)
				)
			);

		}

		/*--------------------------------------------*/
		/* Simple Slider
		/*--------------------------------------------*/

		$this->core_elements['simple_slider'] = array();

		// Information
		$this->core_elements['simple_slider']['info'] = array(
			'name'		=> __( 'Simple Slider', 'themeblvd_builder' ),
			'id'		=> 'simple_slider',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_simple_slider',
			'shortcode'	=> null,
			'desc'		=> __( 'Simple slider, constructed within the Layout Builder.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['simple_slider']['support'] = array(
			'background' 	=> true,
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['simple_slider']['options'] = array(
			'subgroup_start' => array(
				'type'		=> 'subgroup_start'
			),
			'images' => array(
		    	'id' 		=> 'images',
				'name'		=> null,
				'desc'		=> null,
				'type'		=> 'slider'
			),
			'crop' => array(
				'name' 		=> __( 'Image Crop Size', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the crop size to be used for the images. Remember that the slider will be scaled proportionally to fit within its container.', 'themeblvd' ),
				'id' 		=> 'crop',
				'std' 		=> 'slider-large',
				'type' 		=> 'select',
				'select'	=> 'crop',
				'class'		=> 'match-trigger' // Will send the value of this to hidden crop sizes with class "match" within each slide
			),
			'subgroup_end' => array(
				'type'		=> 'subgroup_end',
			),
			'interval' => array(
				'id'		=> 'interval',
				'name' 		=> __( 'Speed', 'themeblvd_builder' ),
				'desc' 		=> __( 'Seconds in between slider transitions. You can use 0 for the slider to not auto rotate.', 'themeblvd_builder' ),
				'std'		=> '5',
				'type'		=> 'text'
		    ),
			'pause' => array(
				'id'		=> 'pause',
				'desc' 		=> __( 'Pause slider on hover.', 'themeblvd_builder' ),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'wrap' => array(
				'id'		=> 'wrap',
				'desc'		=> __( 'Cycle continuously without hard stops.', 'themeblvd_builder' ),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'nav_standard' => array(
				'id'		=> 'nav_standard',
				'desc'		=> __( 'Show standard navigation indicator dots.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'nav_arrows' => array(
				'id'		=> 'nav_arrows',
				'desc'		=> __( 'Show standard navigation arrows.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'nav_thumbs' => array(
				'id'		=> 'nav_thumbs',
				'desc'		=> __( 'Show thumbnail navigation.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			),
			'link' => array(
				'id'		=> 'thumb_link',
				'desc'		=> __( 'Apply hover effect to linked images.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'dark_text'	=> array(
				'id'		=> 'dark_text',
				'desc'		=> __( 'Use dark navigation elements and dark text for any titles and descriptions.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			)
		);

		/*--------------------------------------------*/
		/* Simple Slider (Full Width)
		/*--------------------------------------------*/

		$this->core_elements['simple_slider_popout'] = array();

		// Information
		$this->core_elements['simple_slider_popout']['info'] = array(
			'name'		=> __( 'Simple Slider (Full Width)', 'themeblvd_builder' ),
			'id'		=> 'simple_slider_popout',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_simple_slider_popout',
			'shortcode'	=> null,
			'desc'		=> __( 'Simple slider, constructed within the Layout Builder.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['simple_slider_popout']['support'] = array(
			'background' 	=> true,
			'popout'		=> 'force',
			'padding'		=> true
		);

		// Options
		$this->core_elements['simple_slider_popout']['options'] = array(
			'subgroup_start' => array(
				'type'		=> 'subgroup_start'
			),
			'images' => array(
		    	'id' 		=> 'images',
				'name'		=> null,
				'desc'		=> null,
				'type'		=> 'slider'
			),
			'crop' => array(
				'name' 		=> __( 'Image Crop Size', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the crop size to be used for the images. Remember that the slider will be scaled proportionally to fit within its container.', 'themeblvd' ),
				'id' 		=> 'crop',
				'std' 		=> 'full',
				'type' 		=> 'select',
				'select'	=> 'crop',
				'class'		=> 'match-trigger' // Will send the value of this to hidden crop sizes with class "match" within each slide
			),
			'subgroup_end' => array(
				'type'		=> 'subgroup_end',
			),
			'interval' => array(
				'id'		=> 'interval',
				'name' 		=> __( 'Speed', 'themeblvd_builder' ),
				'desc' 		=> __( 'Seconds in between slider transitions. You can use 0 for the slider to not auto rotate.', 'themeblvd_builder' ),
				'std'		=> '5',
				'type'		=> 'text'
		    ),
			'pause' => array(
				'id'		=> 'pause',
				'desc' 		=> __( 'Pause slider on hover.', 'themeblvd_builder' ),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'wrap' => array(
				'id'		=> 'wrap',
				'desc'		=> __( 'Cycle continuously without hard stops.', 'themeblvd_builder' ),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'nav_standard' => array(
				'id'		=> 'nav_standard',
				'desc'		=> __( 'Show standard navigation indicator dots.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'nav_arrows' => array(
				'id'		=> 'nav_arrows',
				'desc'		=> __( 'Show standard navigation arrows.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'nav_thumbs' => array(
				'id'		=> 'nav_thumbs',
				'desc'		=> __( 'Show thumbnail navigation.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			),
			'link' => array(
				'id'		=> 'thumb_link',
				'desc'		=> __( 'Apply hover effect to linked images.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'dark_text'	=> array(
				'id'		=> 'dark_text',
				'desc'		=> __( 'Use dark navigation elements and dark text for any titles and descriptions.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			),
			'subgroup_start_2' => array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide'
			),
			'cover'	=> array(
				'id'		=> 'cover',
				'desc'		=> __( 'Stretch images full-width of outer container. &mdash; <em>Note: When this is NOT checked, images display and scale down with their natural image dimension ratio. Also, if you\'re using a theme design that is not displayed in a stretch layout, this option, will not be as pronounced.</em>', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'position' => array(
				'id'		=> 'position',
				'name' 		=> __( 'Vertical Alignment', 'themeblvd_builder' ),
				'desc' 		=> __( 'As the browser window changes, your slider images will be stretched, and thus will not always be fully visable. Here, you can select how you want the images aligned in the current slider area.', 'themeblvd_builder' ),
				'std'		=> 'center center',
				'type'		=> 'select',
				'options'	=> array(
					'center top' 	=> __('Align to the top', 'themeblvd_builder'),
					'center center' => __('Align to the middle', 'themeblvd_builder'),
					'center bottom' => __('Align to the bottom', 'themeblvd_builder'),
				),
				'class'		=> 'hide receiver'
		    ),
			'height_desktop' => array(
				'id'		=> 'height_desktop',
				'name' 		=> __( 'Desktop Height', 'themeblvd_builder' ),
				'desc' 		=> __( 'Slider height (in pixels) when displayed at the standard desktop viewport range.', 'themeblvd_builder' ),
				'std'		=> '400',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
		    ),
		    'height_tablet' => array(
				'id'		=> 'height_tablet',
				'name' 		=> __( 'Tablet Height', 'themeblvd_builder' ),
				'desc' 		=> __( 'Slider height (in pixels) when displayed at the standard desktop viewport range.', 'themeblvd_builder' ),
				'std'		=> '300',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
		    ),
		    'height_mobile' => array(
				'id'		=> 'height_mobile',
				'name' 		=> __( 'Mobile Height', 'themeblvd_builder' ),
				'desc' 		=> __( 'Slider height (in pixels) when displayed at the standard desktop viewport range.', 'themeblvd_builder' ),
				'std'		=> '200',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
		    ),
			'subgroup_end_2' => array(
				'type'		=> 'subgroup_end'
			),
		);

		/*--------------------------------------------*/
		/* Slider
		/*--------------------------------------------*/

		if ( defined( 'TB_SLIDERS_PLUGIN_VERSION' ) ) {

			$this->core_elements['slider'] = array();

			// Information
			$this->core_elements['slider']['info'] = array(
				'name'		=> __( 'Slider', 'themeblvd_builder' ),
				'id'		=> 'slider',
				'query'		=> 'secondary',
				'hook'		=> 'themeblvd_slider',
				'shortcode'	=> '[slider]',
				'desc'		=> __( 'User-built slideshow', 'themeblvd_builder' )
			);

			// Support
			$this->core_elements['slider']['support'] = array(
				'background' 	=> true,
				'popout'		=> false,
				'padding'		=> true
			);

			// Options
			$this->core_elements['slider']['options'] = array(
				'slider_desc' => array(
					'id' 		=> 'slider_desc',
					'desc' 		=> __( 'The "Slider" element works with the <a href="http://wordpress.org/extend/plugins/theme-blvd-sliders" target="_blank">Theme Blvd Sliders</a> plugin you\'ve installed. You can use it to pull any sliders you\'ve created from the Slider Manager.', 'themeblvd_builder' ),
					'type' 		=> 'info'
				),
			    'slider_id' => array(
			    	'id' 		=> 'slider_id',
					'name'		=> __( 'Choose Slider', 'themeblvd_builder' ),
					'desc'		=> __( 'Choose from the sliders you\'ve created. You can edit these sliders at any time under the \'Sliders\' tab above.', 'themeblvd_builder' ),
					'type'		=> 'select',
					'options'	=> themeblvd_get_select( 'sliders' )
				)
			);

		}

		/*--------------------------------------------*/
		/* Slogan
		/*--------------------------------------------*/

		$this->core_elements['slogan'] = array();

		// Information
		$this->core_elements['slogan']['info'] = array(
			'name'		=> __( 'Slogan', 'themeblvd_builder' ),
			'id'		=> 'slogan',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_slogan',
			'shortcode'	=> '[slogan]',
			'desc'		=> __( 'Slogan with optional button', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['slogan']['support'] = array(
			'background' 	=> true,
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['slogan']['options'] = array(
			'slogan' => array(
				'id' 		=> 'slogan',
				'name' 		=> __( 'Setup Slogan', 'themeblvd_builder'),
				'desc'		=> __( 'Enter the text you\'d like to show.', 'themeblvd_builder'),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
		    ),
		    'text_size' => array(
				'id' 		=> 'text_size',
				'name' 		=> __( 'Slogan Text Size', 'themeblvd_builder'),
				'desc'		=> __( 'Select how large you\'d like the text in the slogan to be.', 'themeblvd_builder'),
				'std'		=> 'large',
				'type'		=> 'select',
				'options'	=> array(
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'default' 	=> __( 'Normal', 'themeblvd_builder' ),
					'medium' 	=> __( 'Medium', 'themeblvd_builder' ),
					'large' 	=> __( 'Large', 'themeblvd_builder' )
				)
		    ),
		    'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide'
		    ),
			'button' => array(
		    	'id' 		=> 'button',
				'name'		=> __( 'Button', 'themeblvd_builder' ),
				'desc'		=> __( 'Show call-to-action button next to slogan?', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'hide receiver show-hide-toggle'
		    ),
			'button_color' => array(
				'id' 		=> 'button_color',
				'name'		=> __( 'Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what color you\'d like to use for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'trigger',
				'options'	=> themeblvd_colors()
			),
			'button_custom' => array(
				'id' 		=> 'button_custom',
				'name'		=> __( 'Custom Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Configure a custom style for the button.', 'themeblvd_builder' ),
				'std'		=> array(
					'bg' 				=> '#ffffff',
					'bg_hover'			=> '#ebebeb',
					'border' 			=> '#cccccc',
					'text'				=> '#333333',
					'text_hover'		=> '#333333',
					'include_bg'		=> 1,
					'include_border'	=> 1
				),
				'type'		=> 'button',
				'class'		=> 'receiver receiver-custom'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'button_text' => array(
				'id' 		=> 'button_text',
				'name'		=> __( 'Button Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the button.', 'themeblvd_builder' ),
				'std'		=> 'Get Started Today!',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_size' => array(
				'id' 		=> 'button_size',
				'name'		=> __( 'Button Size', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the size you\'d like used for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'large',
				'class'		=> 'hide receiver',
				'options'	=> array(
					'mini' 		=> __( 'Mini', 'themeblvd_builder' ),
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'default' 	=> __( 'Normal', 'themeblvd_builder' ),
					'large' 	=> __( 'Large', 'themeblvd_builder' ),
					'x-large' 	=> __( 'Extra Large', 'themeblvd_builder' )
				)
			),
			'button_url' => array(
				'id' 		=> 'button_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the full URL where you want the button\'s link to go.', 'themeblvd_builder' ),
				'std'		=> 'http://www.your-site.com/your-landing-page',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_target' => array(
				'id' 		=> 'button_target',
				'name'		=> __( 'Link Target', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you want the button to open the webpage.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'	=> array(
			        '_self' 	=> __( 'Same Window', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'New Window', 'themeblvd_builder' ),
			        'lightbox' 	=> __( 'Lightbox Popup', 'themeblvd_builder' )
				)
			),
			'button_icon_before' => array(
				'id' 		=> 'button_icon_before',
				'name'		=> __( 'Icon Before Button Text (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Icon before text of button. This can be any FontAwesome vector icon ID.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'vector',
				'class'		=> 'hide receiver'
			),
			'button_icon_after' => array(
				'id' 		=> 'button_icon_after',
				'name'		=> __( 'Icon After Button Text (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Icon after text of button. This can be any FontAwesome vector icon ID.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'vector',
				'class'		=> 'hide receiver'
			),
			'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			unset( $this->core_elements['slogan']['options']['button_icon_before'] );
			unset( $this->core_elements['slogan']['options']['button_icon_after'] );
		}

		/*--------------------------------------------*/
		/* Tabs -- @deprecated
		/*--------------------------------------------*/

		// Tabs is no longer an element. It's a content block. For
		// people using older theme versions, we'll give it to
		// them as an element.
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			$this->core_elements['tabs'] = array();

			// Information
			$this->core_elements['tabs']['info'] = array(
				'name' 		=> __( 'Tabs', 'themeblvd_builder' ),
				'id'		=> 'tabs',
				'query'		=> 'none',
				'hook'		=> 'themeblvd_tabs',
				'shortcode'	=> '[tabs]',
				'desc' 		=> __( 'Set of tabbed content', 'themeblvd_builder' )
			);

			// Support
			$this->core_elements['tabs']['support'] = array(
				'background' 	=> false,
				'popout'		=> false,
				'padding'		=> false
			);

			$this->core_elements['tabs']['options'] = array(
				'subgroup_start' => array(
			    	'type'		=> 'subgroup_start',
			    	'class'		=> 'tabs'
			    ),
			   	'setup' => array(
					'id' 		=> 'setup',
					'name'		=> __( 'Setup Tabs', 'themeblvd_builder' ),
					'desc'		=> __( 'Choose the number of tabs along with inputting a name for each one. These names are what will appear on the actual tab buttons across the top of the tab set.', 'themeblvd_builder' ),
					'type'		=> 'tabs'
				),
				'height' => array(
					'id' 		=> 'height',
					'name'		=> __( 'Fixed Height', 'themeblvd_builder' ),
					'desc'		=> __( 'Apply automatic fixed height across all tabs.<br><br>This just takes the height of the tallest tab\'s content and applies that across all tabs. This can help with "page jumping" in the case that not all tabs have equal amount of content. It can also help in the case when you\'re getting unwanted scrollbars on the inner content areas of tabs.', 'themeblvd_builder' ),
					'std'		=> 1,
					'type'		=> 'checkbox'
				),
				'tab_1' => array(
					'id' 		=> 'tab_1',
					'name'		=> __( 'Tab #1 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the first tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_2' => array(
					'id' 		=> 'tab_2',
					'name'		=> __( 'Tab #2 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the second tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_3' => array(
					'id' 		=> 'tab_3',
					'name'		=> __( 'Tab #3 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the third tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_4' => array(
					'id' 		=> 'tab_4',
					'name'		=> __( 'Tab #4 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the fourth tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_5' => array(
					'id' 		=> 'tab_5',
					'name'		=> __( 'Tab #5 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the fifth tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_6' => array(
					'id' 		=> 'tab_6',
					'name'		=> __( 'Tab #6 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the sixth tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_7' => array(
					'id' 		=> 'tab_7',
					'name'		=> __( 'Tab #7 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the seventh tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_8' => array(
					'id' 		=> 'tab_8',
					'name'		=> __( 'Tab #8 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the eighth tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_9' => array(
					'id' 		=> 'tab_9',
					'name'		=> __( 'Tab #9 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the ninth tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_10' => array(
					'id' 		=> 'tab_10',
					'name'		=> __( 'Tab #10 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the tenth tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_11' => array(
					'id' 		=> 'tab_11',
					'name'		=> __( 'Tab #11 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the eleventh tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'tab_12' => array(
					'id' 		=> 'tab_12',
					'name'		=> __( 'Tab #12 Content', 'themeblvd_builder' ),
					'desc'		=> __( 'Configure the content for the twelfth tab.', 'themeblvd_builder' ),
					'type'		=> 'content',
					'options'	=> array( 'page', 'raw', 'widget' )
				),
				'subgroup_end' => array(
			    	'type'		=> 'subgroup_end'
			    )
			);
		}

		/*--------------------------------------------*/
		/* Video
		/*--------------------------------------------*/

		$this->core_elements['video'] = array();

		// Information
		$this->core_elements['video']['info'] = array(
			'name'		=> __( 'Video', 'themeblvd_builder' ),
			'id'		=> 'video',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_video',
			'shortcode'	=> null,
			'desc'		=> __( 'A responsive, full-width video.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['video']['support'] = array(
			'background' 	=> true,
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['video']['options'] = array(
			'video' => array(
		    	'id' 		=> 'video',
				'name'		=> __( 'Video URL', 'themeblvd_builder' ),
				'desc'		=> __( '<p>Upload a video or enter a video URL compatible with <a href="" target="_blank">WordPress\'s oEmbed</a>.</p><p>Examples:<br />http://vimeo.com/11178250</br />http://youtube.com/watch?v=ginTCwWfGNY</p>', 'themeblvd_builder' ),
				'type'		=> 'upload',
				'video'		=> true
			)
		);

		/*--------------------------------------------*/
		/* Global element options
		/*--------------------------------------------*/

		// As of framework 2.5, this is @deprecated -- These options have been moved to display options.
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			$screen_options = Theme_Blvd_Layout_Builder_Screen::get_instance();
			$screen_settings = $screen_options->get_value();

			foreach ( $this->core_elements as $id => $element ) {

				// Responsive Visibility
				$this->core_elements[$id]['options']['visibility'] = array(
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
					$this->core_elements[$id]['options']['visibility']['class'] .= ' hide';
				}

				// CSS Classes
				$this->core_elements[$id]['options']['classes'] = array(
			    	'id' 		=> 'classes',
					'name'		=> __( 'CSS Classes', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter any CSS classes you\'d like attached to the element.<br><br><em>Hint: Use class "element-unstyled" to remove your theme\'s styling around this element.</em>', 'themeblvd_builder' ),
					'type'		=> 'text',
					'class'		=> 'section-classes'
				);

				if ( empty( $screen_settings['classes'] ) ) {
					$this->core_elements[$id]['options']['classes']['class'] .= ' hide';
				}

			}
		}

		/*--------------------------------------------*/
		/* Extend
		/*--------------------------------------------*/

		$this->core_elements = apply_filters( 'themeblvd_core_elements', $this->core_elements );

	}

	/**
	 * Set elements by combining core elements and client-added
	 * elements. Then remove any elements that have been set to
	 * be removed. This happens at the "after_setup_theme" hook
	 * with a priority of 1000.
	 *
	 * @since 1.1.1
	 */
	public function set_elements() {

		// Combine core elements with client elements
		$this->elements = array_merge( $this->core_elements, $this->client_elements );

		// Remove elements
		if ( $this->remove_elements ) {
			foreach ( $this->remove_elements as $element_id ) {
				if ( isset( $this->elements[$element_id] ) ) {
					unset( $this->elements[$element_id] );
				}
			}
		}

		// Extend
		$this->elements = apply_filters( 'themeblvd_elements', $this->elements );

	}

	/**
	 * Set originally registered blocks. As client API moves
	 * along, this will be modified, allowing blocks to be
	 * registered or de-registered.
	 *
	 * @since 2.0.0
	 */
	private function set_registered_blocks() {
		$this->registered_blocks = array(
			'content',
			'alert',
			'contact',
			'current',
			'html',
			'icon_box',
			'image',
			'jumbotron',
			'page',
			'panel',
			'post_grid',
			'post_grid_paginated',
			'post_grid_slider',
			'post_list',
			'post_list_paginated',
			'post_list_slider',
			'quote',
			'raw',
			'simple_slider',
			'slogan',
			'tabs',
			'toggles',
			'video',
			'widget'
		);
		$this->registered_blocks = apply_filters( 'themeblvd_registered_blocks', $this->registered_blocks );
	}

	/**
	 * Set original content blocks. These will be later merged
	 * with API client-added blocks. WP-Admin only, see constructer.
	 *
	 * @since 2.0.0
	 */
	private function set_core_blocks() {

		$this->core_blocks = array();

		/*--------------------------------------------*/
		/* Option helpers
		/*--------------------------------------------*/

		// Setup array for categories select
		$categories_select = themeblvd_get_select( 'categories' );

		// Setup array for categories group of checkboxes
		$categories_multicheck = $categories_select;
		unset( $categories_multicheck['null'] );

		/*--------------------------------------------*/
		/* Content - Editor
		/*--------------------------------------------*/

		$this->core_blocks['content'] = array();

		// Information
		$this->core_blocks['content']['info'] = array(
			'name' 		=> __( 'Content', 'themeblvd_builder' ),
			'id'		=> 'content',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['content']['options'] = array(
			'content' => array(
		    	'id' 		=> 'content',
				'name'		=> __( 'Content', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the content you\'d like to show for this content block.', 'themeblvd_builder' ),
				'type'		=> 'editor_modal',
				'class'		=> 'hide'
			),
			'subgroup_start' => array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide'
		    ),
			'wrap' => array(
				'id' 		=> 'wrap',
				'name'		=> __( 'Background', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply background color to content block.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
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
				'class'		=> 'hide receiver'
			),
			'bg_color' => array(
				'id'		=> 'bg_color',
				'name'		=> __('Background Color', 'themeblvd_builder'),
				'desc'		=> __('Select a background color.', 'themeblvd_builder'),
				'std'		=> '#f2f2f2',
				'type'		=> 'color',
				'class'		=> 'hide receiver'
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
				'class'		=> 'hide receiver'
			),
			'subgroup_end' => array(
				'type'		=> 'subgroup_end'
		    )
		);

		/*--------------------------------------------*/
		/* Alert
		/*--------------------------------------------*/

		$this->core_blocks['alert'] = array();

		// Information
		$this->core_blocks['alert']['info'] = array(
			'name' 		=> __( 'Alert', 'themeblvd_builder' ),
			'id'		=> 'alert',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['alert']['options'] = array(
			'content' => array(
		    	'id' 		=> 'content',
				'name'		=> __( 'Content', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the content of the alert.', 'themeblvd_builder' ),
				'type'		=> 'editor_modal',
				'class'		=> 'hide'
			),
			'style' => array(
				'name' 		=> __( 'Style', 'themeblvd_shortcodes' ),
				'desc' 		=> __( 'The style of the alert.', 'themeblvd' ),
				'id' 		=> 'style',
				'std' 		=> 'info',
				'type' 		=> 'select',
				'options' 	=> array(
					'info' 		=> __('Info (blue)', 'themeblvd_builder'),
					'success' 	=> __('Success (green)', 'themeblvd_builder'),
					'danger' 	=> __('Danger (red)', 'themeblvd_builder'),
					'warning' 	=> __('Warning (yellow)', 'themeblvd_builder')
				)
			),
			'class' => array(
				'name' 		=> __( 'CSS Class (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'Any CSS classes you\'d like to add.', 'themeblvd_builder' ),
				'id' 		=> 'class',
				'std' 		=> '',
				'type' 		=> 'text'
			)
		);

		/*--------------------------------------------*/
		/* Contact Bar
		/*--------------------------------------------*/

		$this->core_blocks['contact'] = array();

		// Information
		$this->core_blocks['contact']['info'] = array(
			'name' 		=> __( 'Contact Bar', 'themeblvd_builder' ),
			'id'		=> 'contact',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['contact']['options'] = array(
			'buttons' => array(
		    	'id' 		=> 'buttons',
				'name'		=> __( 'Buttons', 'themeblvd_builder' ),
				'desc'		=> __( 'Configure the buttons to be used for the contact bar.', 'themeblvd_builder' ),
				'type'		=> 'social_media'
			),
			'style' => array(
				'name' 		=> __( 'Style', 'themeblvd_builder' ),
				'desc' 		=> __( 'Style of the how the buttons will appear.', 'themeblvd_builder' ),
				'id' 		=> 'style',
				'std' 		=> 'grey',
				'type' 		=> 'select',
				'options' 	=> array(
					'grey' 		=> __('Flat Grey', 'themeblvd_builder'),
					'dark' 		=> __('Flat Dark', 'themeblvd_builder'),
					'light' 	=> __('Flat Light', 'themeblvd_builder'),
					'color' 	=> __('Color', 'themeblvd_builder')
				)
			),
			'tooltip' => array(
				'name' 		=> __( 'Tooltip', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the placement of the tooltips. The tooltip is pulled from the "Label" of each button.', 'themeblvd_builder' ),
				'id' 		=> 'tooltip',
				'std' 		=> 'top',
				'type' 		=> 'select',
				'options' 	=> array(
					'top' 		=> __('Tooltips on top', 'themeblvd_builder'),
					'bottom' 	=> __('Tooltips on bottom', 'themeblvd_builder'),
					'disable' 	=> __('Disable tooltips', 'themeblvd_builder')
				)
			)
		);

		/*--------------------------------------------*/
		/* Current Page
		/*--------------------------------------------*/

		$this->core_blocks['current'] = array();

		// Information
		$this->core_blocks['current']['info'] = array(
			'name' 		=> __( 'Current Page', 'themeblvd_builder' ),
			'id'		=> 'current',
			'query'		=> 'none',
			'height'	=> 'medium'
		);

		// Options
		$this->core_blocks['current']['options'] = array(
			'current_info' => array(
		    	'id' 		=> 'current_info',
				'desc'		=> __( 'The content will be pulled from the current page the layout is applied to.', 'themeblvd_builder' ),
				'type'		=> 'info'
			)
		);

		/*--------------------------------------------*/
		/* External Page
		/*--------------------------------------------*/

		$this->core_blocks['page'] = array();

		// Information
		$this->core_blocks['page']['info'] = array(
			'name' 		=> __( 'External Page', 'themeblvd_builder' ),
			'id'		=> 'page',
			'query'		=> 'none',
			'height'	=> 'medium'
		);

		// Options
		$this->core_blocks['page']['options'] = array(
			'page' => array(
		    	'id' 		=> 'page',
				'name'		=> __( 'Page', 'themeblvd_builder' ),
				'desc'		=> __( 'Select from your website\'s pages to pull content from.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'select'	=> 'pages'
			)
		);

		/*--------------------------------------------*/
		/* Headline
		/*--------------------------------------------*/

		$this->core_blocks['headline'] = array();

		// Information
		$this->core_blocks['headline']['info'] = array(
			'name' 		=> __( 'Headline', 'themeblvd_builder' ),
			'id'		=> 'headline',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['headline']['options'] = array(
			'text' => array(
				'id' 		=> 'text',
				'name'		=> __( 'Headline Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the text you\'d like to use for your headline. It is better if you use plain text here and not try and use HTML tags. Additionally, if you\'d like to automatically pull the title from the current page, insert <em>%page_title%</em> here.', 'themeblvd_builder' ),
				'type'		=> 'textarea',
			),
			'tagline' => array(
		    	'id' 		=> 'tagline',
				'name'		=> __( 'Tagline', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter any text you\'d like to display below the headline as a tagline. Feel free to leave this blank. It is better if you use plain text here and not try and use HTML tags.', 'themeblvd_builder' ),
				'type'		=> 'textarea'
			),
		    'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Headline Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the type of header tag you\'d like to wrap this headline.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'options'	=> array(
					'h1' => __( '&lt;h1&gt;Your Headline&lt;/h1&gt;', 'themeblvd_builder' ),
					'h2' => __( '&lt;h2&gt;Your Headline&lt;/h2&gt;', 'themeblvd_builder' ),
					'h3' => __( '&lt;h3&gt;Your Headline&lt;/h3&gt;', 'themeblvd_builder' ),
					'h4' => __( '&lt;h4&gt;Your Headline&lt;/h4&gt;', 'themeblvd_builder' ),
					'h5' => __( '&lt;h5&gt;Your Headline&lt;/h5&gt;', 'themeblvd_builder' ),
					'h6' => __( '&lt;h6&gt;Your Headline&lt;/h6&gt;', 'themeblvd_builder' )
				)
			),
			'align' => array(
				'id' 		=> 'align',
				'name'		=> __( 'Headline Alignment', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like the text in this title to align.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'options'		=> array(
			        'left' 		=> __( 'Align Left', 'themeblvd_builder' ),
			        'center' 	=> __( 'Center', 'themeblvd_builder' ),
					'right' 	=> __( 'Align Right', 'themeblvd_builder' )
				)
			)
		);

		/*--------------------------------------------*/
		/* HTML Block
		/*--------------------------------------------*/

		$this->core_blocks['html'] = array();

		// Information
		$this->core_blocks['html']['info'] = array(
			'name' 		=> __( 'HTML', 'themeblvd_builder' ),
			'id'		=> 'html',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['html']['options'] = array(
			'html' => array(
		    	'id' 		=> 'html',
				'name'		=> __( 'HTML Content', 'themeblvd_builder' ),
				'desc'		=> __( 'Add your HTML into the editor.', 'themeblvd_builder' ),
				'type'		=> 'textarea' // Doesn't need to be "code" type because will be openned in modal code editor by having ID "html"
			)
		);

		/*--------------------------------------------*/
		/* Icon Box
		/*--------------------------------------------*/

		$this->core_blocks['icon_box'] = array();

		// Information
		$this->core_blocks['icon_box']['info'] = array(
			'name' 		=> __( 'Icon Box', 'themeblvd_builder' ),
			'id'		=> 'icon_box',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['icon_box']['options'] = array(
			'icon' => array(
		    	'id' 		=> 'icon',
				'name'		=> __( 'Icon', 'themeblvd_builder' ),
				'desc'		=> __( 'This can be any FontAwesome vector icon ID.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'vector'
			),
			'size' => array(
		    	'id' 		=> 'size',
				'name'		=> __( 'Icon Size', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how large the icon should be displayed.', 'themeblvd_builder' ),
				'std'		=> '65px',
				'type'		=> 'slide',
				'options'	=> array(
					'min'	=> '10',
					'max'	=> '150',
					'step'	=> '1',
					'units'	=> 'px'
				)
			),
			'location' => array(
		    	'id' 		=> 'location',
				'name'		=> __( 'Icon Placement', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how the icon should be displayed within the block.', 'themeblvd_builder' ),
				'std'		=> 'above',
				'type'		=> 'radio',
				'options'	=> array(
					'above'	=> __('Icon is above title and content.', 'themeblvd_builder'),
					'side'	=> __('Icon is to the side of title and content.', 'themeblvd_builder'),
				)
			),
			'color' => array(
		    	'id' 		=> 'color',
				'name'		=> __( 'Icon Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the color of the icon.', 'themeblvd_builder' ),
				'std'		=> '#666666',
				'type'		=> 'color'
			),
			'subgroup_start' => array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide'
			),
			'circle' => array(
		    	'id' 		=> 'circle',
				'name'		=> null,
				'desc'		=> __( 'Wrap icon in circle.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'circle_color' => array(
		    	'id' 		=> 'circle_color',
				'name'		=> __( 'Circle Background Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the background color of the circle that surrounds the icon.', 'themeblvd_builder' ),
				'std'		=> '#cccccc',
				'type'		=> 'color',
				'class'		=> 'hide receiver'
			),
			'subgroup_end' => array(
				'type'		=> 'subgroup_end'
			),
			'title' => array(
		    	'id' 		=> 'title',
				'name'		=> __( 'Title (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Add the title above your content.', 'themeblvd_builder' ),
				'type'		=> 'text'
			),
			'text' => array(
		    	'id' 		=> 'text',
				'name'		=> __( 'Content (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Add the content for this icon box.', 'themeblvd_builder' ),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
			),
			'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide'
		    )
		);

		/*--------------------------------------------*/
		/* Image
		/*--------------------------------------------*/

		$this->core_blocks['image'] = array();

		// Information
		$this->core_blocks['image']['info'] = array(
			'name' 		=> __( 'Image', 'themeblvd_builder' ),
			'id'		=> 'image',
			'query'		=> 'none',
			'height'	=> 'large'
		);

		// Options
		$this->core_blocks['image']['options'] = array(
			'image' => array(
		    	'id' 		=> 'image',
				'name'		=> __( 'Image URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the image to be used.', 'themeblvd_builder' ),
				'type'		=> 'upload',
				'advanced'	=> true
			),
			'subgroup_start' => array(
				'type' 		=> 'subgroup_start',
				'class'		=> 'show-hide-toggle desc-toggle'
			),
			'link' => array(
				'id' 		=> 'link',
				'name'		=> __( 'Link', 'themeblvd_builder' ),
				'desc'		=> __( 'Select if and how this image should be linked.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'options'	=> array(
			        'none'		=> __( 'No Link', 'themeblvd' ),
			        '_self' 	=> __( 'Link to webpage in same window.', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'Link to webpage in new window.', 'themeblvd_builder' ),
			        'image' 	=> __( 'Link to image in lightbox popup.', 'themeblvd_builder' ),
			        'video' 	=> __( 'Link to video in lightbox popup.', 'themeblvd_builder' )
				),
				'class'		=> 'trigger'
			),
			'link_url' => array(
				'id' 		=> 'link_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> array(
			        '_self' 	=> __( 'Enter a URL to a webpage.<br />Ex: http://yoursite.com/example', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'Enter a URL to a webpage.<br />Ex: http://google.com', 'themeblvd_builder' ),
			        'image' 	=> __( 'Enter a URL to an image file.<br />Ex: http://yoursite.com/uploads/image.jpg', 'themeblvd_builder' ),
			        'video' 	=> __( 'Enter a URL to a YouTube or Vimeo page.<br />Ex: http://vimeo.com/11178250‎</br />Ex: https://youtube.com/watch?v=ginTCwWfGNY', 'themeblvd_builder' )
				),
				'type'		=> 'text',
				'std'		=> '',
				'pholder'	=> 'http://',
				'class'		=> 'receiver receiver-_self receiver-_blank receiver-image receiver-video'
			),
			'subgroup_end' => array(
				'type' 		=> 'subgroup_end'
			),
			'frame' => array(
		    	'id' 		=> 'frame',
				'name'		=> __( 'Image Frame', 'themeblvd_builder' ),
				'desc'		=> __( 'Add frame around the image.', 'themeblvd_builder' ),
				'type'		=> 'checkbox'
			),
			'class' => array(
				'name' 		=> __( 'CSS Class (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'Any CSS classes you\'d like to add.', 'themeblvd_builder' ),
				'id' 		=> 'class',
				'std' 		=> '',
				'type' 		=> 'text'
			)
		);

		/*--------------------------------------------*/
		/* Jumbotron
		/*--------------------------------------------*/

		$this->core_blocks['jumbotron'] = array();

		// Information
		$this->core_blocks['jumbotron']['info'] = array(
			'name' 		=> __( 'Jumbotron', 'themeblvd_builder' ),
			'id'		=> 'jumbotron',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['jumbotron']['options'] = array(
			'title' => array(
				'id' 		=> 'title',
				'name' 		=> __( 'Title', 'themeblvd_builder'),
				'desc'		=> __( 'Enter the text you\'d like to show for a title.', 'themeblvd_builder'),
				'type'		=> 'text'
		    ),
			'content' => array(
				'id' 		=> 'content',
				'name' 		=> __( 'Content', 'themeblvd_builder'),
				'desc'		=> __( 'Enter in the content you\'d like to show. You may use basic HTML, and most shortcodes.', 'themeblvd_builder'),
				'std'		=> '',
				'type'		=> 'editor_modal',
				'class'		=> 'hide'
		    ),
		    'wpautop' => array(
		    	'id' 		=> 'wpautop',
				'name'		=> __( 'Content Formatting', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply WordPress automatic formatting to above content.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
		    'bg_color' => array(
				'id' 		=> 'bg_color',
				'name' 		=> __( 'Background Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select a background color for the Jumbotron unit.', 'themeblvd_builder'),
				'std'		=> '#eeeeee',
				'type'		=> 'color'
		    ),
		    'text_color' => array(
				'id' 		=> 'text_color',
				'name' 		=> __( 'Text Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select a text color for the Jumbotron unit.', 'themeblvd_builder'),
				'std'		=> '#444444',
				'type'		=> 'color'
		    ),
		    'text_align' => array(
				'id' 		=> 'text_align',
				'name' 		=> __( 'Text Alignment', 'themeblvd_builder'),
				'desc'		=> __( 'Select how you\'d like the text within the unit aligned.', 'themeblvd_builder'),
				'std'		=> 'center',
				'type'		=> 'select',
				'options'	=> array(
					'left' 		=> __( 'Left', 'themeblvd_builder' ),
					'right' 	=> __( 'Right', 'themeblvd_builder' ),
					'center' 	=> __( 'Center', 'themeblvd_builder' )
				)
		    ),
		    'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide'
		    ),
			'button' => array(
		    	'id' 		=> 'button',
				'name'		=> __( 'Button', 'themeblvd_builder' ),
				'desc'		=> __( 'Show button at the bottom of unit?', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'hide receiver show-hide-toggle'
		    ),
			'button_color' => array(
				'id' 		=> 'button_color',
				'name'		=> __( 'Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what color you\'d like to use for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'trigger',
				'options'	=> themeblvd_colors()
			),
			'button_custom' => array(
				'id' 		=> 'button_custom',
				'name'		=> __( 'Custom Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Configure a custom style for the button.', 'themeblvd_builder' ),
				'std'		=> array(
					'bg' 				=> '#ffffff',
					'bg_hover'			=> '#ebebeb',
					'border' 			=> '#cccccc',
					'text'				=> '#333333',
					'text_hover'		=> '#333333',
					'include_bg'		=> 1,
					'include_border'	=> 1
				),
				'type'		=> 'button',
				'class'		=> 'receiver receiver-custom'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'button_text' => array(
				'id' 		=> 'button_text',
				'name'		=> __( 'Button Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the button.', 'themeblvd_builder' ),
				'std'		=> 'Get Started Today!',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_size' => array(
				'id' 		=> 'button_size',
				'name'		=> __( 'Button Size', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the size you\'d like used for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'large',
				'class'		=> 'hide receiver',
				'options'	=> array(
					'mini' 		=> __( 'Mini', 'themeblvd_builder' ),
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'default' 	=> __( 'Normal', 'themeblvd_builder' ),
					'large' 	=> __( 'Large', 'themeblvd_builder' ),
					'x-large' 	=> __( 'Extra Large', 'themeblvd_builder' )
				)
			),
			'button_url' => array(
				'id' 		=> 'button_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the full URL where you want the button\'s link to go.', 'themeblvd_builder' ),
				'std'		=> 'http://www.your-site.com/your-landing-page',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_target' => array(
				'id' 		=> 'button_target',
				'name'		=> __( 'Link Target', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you want the button to open the webpage.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'	=> array(
			        '_self' 	=> __( 'Same Window', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'New Window', 'themeblvd_builder' ),
			        'lightbox' 	=> __( 'Lightbox Popup', 'themeblvd_builder' )
				)
			),
			'button_icon_before' => array(
				'id' 		=> 'button_icon_before',
				'name'		=> __( 'Icon Before Button Text (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Icon before text of button. This can be any FontAwesome vector icon ID.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'vector',
				'class'		=> 'hide receiver'
			),
			'button_icon_after' => array(
				'id' 		=> 'button_icon_after',
				'name'		=> __( 'Icon After Button Text (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Icon after text of button. This can be any FontAwesome vector icon ID.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'vector',
				'class'		=> 'hide receiver'
			),
			'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		/*--------------------------------------------*/
		/* Panel
		/*--------------------------------------------*/

		$this->core_blocks['panel'] = array();

		// Information
		$this->core_blocks['panel']['info'] = array(
			'name' 		=> __( 'Panel', 'themeblvd_builder' ),
			'id'		=> 'panel',
			'query'		=> 'none',
			'height'	=> 'large'
		);

		// Options
		$this->core_blocks['panel']['options'] = array(
			'content' => array(
		    	'id' 		=> 'content',
				'name'		=> __( 'Content', 'themeblvd_builder' ),
				'type'		=> 'textarea',
				'class'		=> 'hide'
			),
			'style' => array(
				'name' 		=> __( 'Style', 'themeblvd_builder' ),
				'desc' 		=> __( 'Style of the panel.', 'themeblvd_builder' ),
				'id' 		=> 'style',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default' 	=> __('Default (grey)', 'themeblvd_builder'),
					'primary' 	=> __('Primary (blue)', 'themeblvd_builder'),
					'info' 		=> __('Info (lighter blue)', 'themeblvd_builder'),
					'success' 	=> __('Success (green)', 'themeblvd_builder'),
					'danger' 	=> __('Danger (red)', 'themeblvd_builder'),
					'warning' 	=> __('Warning (yellow)', 'themeblvd_builder')
				)
			),
			'title' => array(
				'name' 		=> __( 'Title (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'The title of the panel.', 'themeblvd_builder' ),
				'id' 		=> 'title',
				'std' 		=> '',
				'type' 		=> 'text'
			),
			'footer' => array(
				'name' 		=> __( 'Footer text (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'Footer text for the panel.', 'themeblvd_builder' ),
				'id' 		=> 'footer',
				'std' 		=> '',
				'type' 		=> 'text'
			),
			'class' => array(
				'name' 		=> __( 'CSS Class (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'Any CSS classes you\'d like to add.', 'themeblvd_builder' ),
				'id' 		=> 'class',
				'std' 		=> '',
				'type' 		=> 'text'
			)
		);

		/*--------------------------------------------*/
		/* Post Grid (paginated)
		/*--------------------------------------------*/

		$this->core_blocks['post_grid_paginated'] = array();

		// Information
		$this->core_blocks['post_grid_paginated']['info'] = array(
			'name' 		=> __( 'Post Grid (paginated)', 'themeblvd_builder' ),
			'id'		=> 'post_grid_paginated',
			'query'		=> 'primary'
		);

		// Options
		$this->core_blocks['post_grid_paginated']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ&numberposts=10<br><br><em>Note: The number of posts displayed is determined from the rows and columns.</em>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'columns' => array(
		    	'id' 		=> 'columns',
				'name'		=> __( 'Columns', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how many posts per row you\'d like displayed.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> '3',
				'options'	=> array(
			        '2' 	=> __( '2 Columns', 'themeblvd_builder' ),
			        '3' 	=> __( '3 Columns', 'themeblvd_builder' ),
			        '4' 	=> __( '4 Columns', 'themeblvd_builder' ),
			        '5' 	=> __( '5 Columns', 'themeblvd_builder' )
				)
			),
			'rows' => array(
		    	'id' 		=> 'rows',
				'name'		=> __( 'Rows per page', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of rows <strong>per page</strong> you\'d like to show. The number you enter here will be multiplied by the amount of columns you selected in the previous option to figure out how many posts should be showed on each page. You can leave this option blank if you\'d like to show all posts from the categories you\'ve selected on a single page.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3'
			),
			'crop' => array(
		    	'id' 		=> 'crop',
				'name'		=> __( 'Custom Image Crop Size (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a custom image crop size. Always leave this blank unless you know what you\'re doing here. When left blank, the theme will generate this crop size for you depending on the amount of columns in your post grid.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> ''
			)
		);

		/*--------------------------------------------*/
		/* Post Grid
		/*--------------------------------------------*/

		$this->core_blocks['post_grid'] = array();

		// Information
		$this->core_blocks['post_grid']['info'] = array(
			'name' 		=> __( 'Post Grid', 'themeblvd_builder' ),
			'id'		=> 'post_grid',
			'query'		=> 'secondary'
		);

		// Options
		$this->core_blocks['post_grid']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'offset' => array(
		    	'id' 		=> 'offset',
				'name'		=> __( 'Offset', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>. Utilizing this option could be useful, for example, if you wanted to have the first post in an element above this one, and then you could offset this set by <em>1</em> so the posts start after that post in the previous element. If that makes no sense, just ignore this option and leave it at <em>0</em>!', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ<br><br><em>Note: You cannot set the number of posts because this is generated in a grid based on the rows and columns.</em>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'columns' => array(
		    	'id' 		=> 'columns',
				'name'		=> __( 'Columns', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how many posts per row you\'d like displayed.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> '3',
				'options'	=> array(
			        '2' 	=> __( '2 Columns', 'themeblvd_builder' ),
			        '3' 	=> __( '3 Columns', 'themeblvd_builder' ),
			        '4' 	=> __( '4 Columns', 'themeblvd_builder' ),
			        '5' 	=> __( '5 Columns', 'themeblvd_builder' )
				)
			),
			'rows' => array(
		    	'id' 		=> 'rows',
				'name'		=> __( 'Rows', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of rows you\'d like to show. The number you enter here will be multiplied by the amount of columns you selected in the previous option to figure out how many posts should be showed. You can leave this option blank if you\'d like to show all posts from the categories you\'ve selected.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3'
			),
			'crop' => array(
		    	'id' 		=> 'crop',
				'name'		=> __( 'Custom Image Crop Size (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a custom image crop size. Always leave this blank unless you know what you\'re doing here. When left blank, the theme will generate this crop size for you depending on the amount of columns in your post grid.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> ''
			)
		);

		/*--------------------------------------------*/
		/* Post Grid Slider
		/*--------------------------------------------*/

		$this->core_blocks['post_grid_slider'] = array();

		// Information
		$this->core_blocks['post_grid_slider']['info'] = array(
			'name' 		=> __( 'Post Grid Slider', 'themeblvd_builder' ),
			'id'		=> 'post_grid_slider',
			'query'		=> 'secondary'
		);

		// Options
		$this->core_blocks['post_grid_slider']['options'] = array(
			'fx' => array(
		    	'id' 		=> 'fx',
				'name'		=> __( 'Transition Effect', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the effect you\'d like used to transition from one slide to the next.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'slide',
				'options'	=> array(
			        'fade' 	=> __( 'Fade', 'themeblvd_builder' ),
					'slide'	=> __( 'Slide', 'themeblvd_builder' )
				)
			),
			'timeout' => array(
		    	'id' 		=> 'timeout',
				'name'		=> __( 'Speed', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of seconds you\'d like in between trasitions. You may use <em>0</em> to disable the slider from auto advancing.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0'
			),
			'nav_standard' => array(
				'id'		=> 'nav_standard',
				'name'		=> __( 'Show standard slideshow navigation?', 'themeblvd_builder' ),
				'desc'		=> __( 'The standard navigation are the little dots that appear below the slider.' , 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show navigation.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
				)
			),
			'nav_arrows' => array(
				'id'		=> 'nav_arrows',
				'name'		=> __( 'Show next/prev slideshow arrows?', 'themeblvd_builder' ),
				'desc'		=> __( 'These arrows allow the user to navigation from one slide to the next.' , 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show arrows.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show them.', 'themeblvd_builder' )
				)
			),
			'pause_play' => array(
				'id'		=> 'pause_play',
				'name'		=> __( 'Show pause/play button?', 'themeblvd_builder' ),
				'desc'		=> __('Note that if you have the speed set to 0, this option will be ignored. ', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show pause/play button.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
				)
			),
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'numberposts' => array(
		    	'id' 		=> 'numberposts',
				'name'		=> __( 'Total Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the maximum number of posts you\'d like to show from the categories selected. You can use <em>-1</em> to show all posts from the selected categories.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '-1',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'offset' => array(
		    	'id' 		=> 'offset',
				'name'		=> __( 'Offset', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>. Utilizing this option could be useful, for example, if you wanted to have the first post in an element above this one, and then you could offset this set by <em>1</em> so the posts start after that post in the previous element. If that makes no sense, just ignore this option and leave it at <em>0</em>!', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'columns' => array(
		    	'id' 		=> 'columns',
				'name'		=> __( 'Columns', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how many posts per row you\'d like displayed.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> '3',
				'options'	=> array(
			        '2' 	=> __( '2 Columns', 'themeblvd_builder' ),
			        '3' 	=> __( '3 Columns', 'themeblvd_builder' ),
			        '4' 	=> __( '4 Columns', 'themeblvd_builder' ),
			        '5' 	=> __( '5 Columns', 'themeblvd_builder' )
				)
			),
			'rows' => array(
		    	'id' 		=> 'rows',
				'name'		=> __( 'Rows per slide', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of rows <strong>per slide</strong> you\'d like to show. The number you enter here will be multiplied by the amount of columns you selected in the previous option to figure out how many posts should be showed on each slide.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3'
			),
			'crop' => array(
		    	'id' 		=> 'crop',
				'name'		=> __( 'Custom Image Crop Size (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a custom image crop size. Always leave this blank unless you know what you\'re doing here. When left blank, the theme will generate this crop size for you depending on the amount of columns in your post grid.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> ''
			)
		);

		/*--------------------------------------------*/
		/* Post List (paginated)
		/*--------------------------------------------*/

		$this->core_blocks['post_list_paginated'] = array();

		// Information
		$this->core_blocks['post_list_paginated']['info'] = array(
			'name' 		=> __( 'Post List (paginated)', 'themeblvd_builder' ),
			'id'		=> 'post_list_paginated',
			'query'		=> 'primary'
		);

		// Options
		$this->core_blocks['post_list_paginated']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'posts_per_page' => array(
		    	'id' 		=> 'posts_per_page',
				'name'		=> __( 'Posts per page', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of posts <strong>per page</strong> you\'d like to show. You can enter <em>-1</em> if you\'d like to show all posts from the categories you\'ve selected on a single page.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '6',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ&posts_per_page=10', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end',
		    ),
			'thumbs' => array(
				'id' 		=> 'thumbs',
				'name' 		=> __( 'Featured Images', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the size of the post list\'s thumbnails or whether you\'d like to hide them all together when posts are listed.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'small'		=> __( 'Show small thumbnails.', 'themeblvd_builder' ),
					'full' 		=> __( 'Show full-width thumbnails.', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide thumbnails.', 'themeblvd_builder' )
				)
			),
			'content' => array(
				'id' 		=> 'content',
				'name' 		=> __( 'Show excerpts of full content?', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Choose whether you want to show full content or post excerpts only.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'content'	=> __( 'Show full content.', 'themeblvd_builder' ),
					'excerpt' 	=> __( 'Show excerpt only.', 'themeblvd_builder' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Post List
		/*--------------------------------------------*/

		$this->core_blocks['post_list'] = array();

		// Information
		$this->core_blocks['post_list']['info'] = array(
			'name' 		=> __( 'Post List', 'themeblvd_builder' ),
			'id'		=> 'post_list',
			'query'		=> 'secondary'
		);

		// Options
		$this->core_blocks['post_list']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'numberposts' => array(
		    	'id' 		=> 'numberposts',
				'name'		=> __( 'Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the <strong>total number</strong> of posts you\'d like to show. You can enter <em>-1</em> if you\'d like to show all posts from the categories you\'ve selected.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '6',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'offset' => array(
		    	'id' 		=> 'offset',
				'name'		=> __( 'Offset', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>. Utilizing this option could be useful, for example, if you wanted to have the first post in an element above this one, and then you could offset this set by <em>1</em> so the posts start after that post in the previous element. If that makes no sense, just ignore this option and leave it at <em>0</em>!', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ&numberposts=10', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'thumbs' => array(
				'id' 		=> 'thumbs',
				'name' 		=> __( 'Featured Images', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Select the size of the post list\'s thumbnails or whether you\'d like to hide them all together when posts are listed.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'small'		=> __( 'Show small thumbnails.', 'themeblvd_builder' ),
					'full' 		=> __( 'Show full-width thumbnails.', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide thumbnails.', 'themeblvd_builder' )
				)
			),
			'content' => array(
				'id' 		=> 'content',
				'name' 		=> __( 'Show excerpts of full content?', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Choose whether you want to show full content or post excerpts only.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'content'	=> __( 'Show full content.', 'themeblvd_builder' ),
					'excerpt' 	=> __( 'Show excerpt only.', 'themeblvd_builder' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Post List Slider
		/*--------------------------------------------*/

		$this->core_blocks['post_list_slider'] = array();

		// Information
		$this->core_blocks['post_list_slider']['info'] = array(
			'name' 		=> __( 'Post List Slider', 'themeblvd_builder' ),
			'id'		=> 'post_list_slider',
			'query'		=> 'secondary'
		);

		// Options
		$this->core_blocks['post_list_slider']['options'] = array(
			'fx' => array(
		    	'id' 		=> 'fx',
				'name'		=> __( 'Transition Effect', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the effect you\'d like used to transition from one slide to the next.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'slide',
				'options'	=> array(
			        'fade' 	=> 'Fade',
					'slide'	=> 'Slide'
				)
			),
			'timeout' => array(
		    	'id' 		=> 'timeout',
				'name'		=> __( 'Speed', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of seconds you\'d like in between trasitions. You may use <em>0</em> to disable the slider from auto advancing.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0'
			),
			'nav_standard' => array(
				'id'		=> 'nav_standard',
				'name'		=> __( 'Show standard slideshow navigation?', 'themeblvd_builder' ),
				'desc'		=> __( 'The standard navigation are the little dots that appear below the slider.' , 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show navigation.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
				)
			),
			'nav_arrows' => array(
				'id'		=> 'nav_arrows',
				'name'		=> __( 'Show next/prev slideshow arrows?', 'themeblvd_builder' ),
				'desc'		=> __( 'These arrows allow the user to navigation from one slide to the next.' , 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show arrows.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show them.', 'themeblvd_builder' )
				)
			),
			'pause_play' => array(
				'id'		=> 'pause_play',
				'name'		=> __( 'Show pause/play button?', 'themeblvd_builder' ),
				'desc'		=> __('Note that if you have the speed set to 0, this option will be ignored. ', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show pause/play button.', 'themeblvd_builder' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd_builder' )
				)
			),
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'source' => array(
		    	'id' 		=> 'source',
				'name'		=> __( 'Where to pull posts from?', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to pull posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options'	=> array(
					'category' 	=> __( 'Category', 'themeblvd_builder' ),
			        'tag' 		=> __( 'Tag', 'themeblvd_builder' ),
			        'query' 	=> __( 'Custom Query', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'categories' => array(
		    	'id' 		=> 'categories',
				'name'		=> __( 'Categories', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the categories you\'d like to pull posts from. Note that selecting "All Categories" will override any other selections.', 'themeblvd_builder' ),
				'std'		=> array( 'all' => 1 ),
				'type'		=> 'multicheck',
				'options'	=> $categories_multicheck,
				'class' 	=> 'hide receiver receiver-category'
			),
			'tag' => array(
		    	'id' 		=> 'tag',
				'name'		=> __( 'Tag', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a single tag, or a comma separated list of tags, to pull posts from.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-tag'
			),
			'numberposts' => array(
		    	'id' 		=> 'numberposts',
				'name'		=> __( 'Total Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the maximum number of posts you\'d like to show from the categories selected. You can use <em>-1</em> to show all posts from the selected categories.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '-1',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'orderby' => array(
		    	'id' 		=> 'orderby',
				'name'		=> __( 'Order By', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what attribute you\'d like the posts ordered by.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'date',
				'options'	=> array(
			        'date' 			=> __( 'Publish Date', 'themeblvd_builder' ),
			        'title' 		=> __( 'Post Title', 'themeblvd_builder' ),
			        'comment_count' => __( 'Number of Comments', 'themeblvd_builder' ),
			        'rand' 			=> __( 'Random', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'order' => array(
		    	'id' 		=> 'order',
				'name'		=> __( 'Order', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the order in which you\'d like the posts displayed based on the previous orderby parameter.<br><br><em>Note that a traditional WordPress setup would have posts ordered by <strong>Publish Date</strong> and be ordered <strong>Descending</strong>.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'DESC',
				'options'	=> array(
			        'DESC' 	=> __( 'Descending (highest to lowest)', 'themeblvd_builder' ),
			        'ASC' 	=> __( 'Ascending (lowest to highest)', 'themeblvd_builder' )
				),
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'offset' => array(
		    	'id' 		=> 'offset',
				'name'		=> __( 'Offset', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>. Utilizing this option could be useful, for example, if you wanted to have the first post in an element above this one, and then you could offset this set by <em>1</em> so the posts start after that post in the previous element. If that makes no sense, just ignore this option and leave it at <em>0</em>!', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ&numberposts=10', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'thumbs' => array(
				'id' 		=> 'thumbs',
				'name' 		=> __( 'Featured Images', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Select the size of the post list\'s thumbnails or whether you\'d like to hide them all together when posts are listed.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'small'		=> __( 'Show small thumbnails.', 'themeblvd_builder' ),
					'full' 		=> __( 'Show full-width thumbnails.', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide thumbnails.', 'themeblvd_builder' )
				)
			),
			'content' => array(
				'id' 		=> 'content',
				'name' 		=> __( 'Show excerpts of full content?', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Choose whether you want to show full content or post excerpts only.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
					'content'	=> __( 'Show full content.', 'themeblvd_builder' ),
					'excerpt' 	=> __( 'Show excerpt only.', 'themeblvd_builder' )
				)
			),
			'posts_per_slide' => array(
		    	'id' 		=> 'posts_per_slide',
				'name'		=> __( 'Posts per slide', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of posts <strong>per slide</strong> you\'d like to show.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3'
			)
		);

		/*--------------------------------------------*/
		/* Quote
		/*--------------------------------------------*/

		$this->core_blocks['quote'] = array();

		// Information
		$this->core_blocks['quote']['info'] = array(
			'name' 		=> __( 'Quote', 'themeblvd_builder' ),
			'id'		=> 'quote',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['quote']['options'] = array(
			'quote' => array(
				'name' 		=> __( 'Quote Text', 'themeblvd_shortcodes' ),
				'desc' 		=> __( 'The main text of the quote. You cannot use HTML here.', 'themeblvd' ),
				'id' 		=> 'quote',
				'std' 		=> 'Quote text...',
				'type' 		=> 'textarea'
			),
			'source' => array(
				'name' 		=> __( 'Quote Source (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'Optional source for the quote.<br />Ex: John Smith', 'themeblvd' ),
				'id' 		=> 'source',
				'std' 		=> '',
				'type' 		=> 'text'
			),
			'source_link' => array(
				'name' 		=> __( 'Quote Source URL (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'Optional website URL to the source you entered in the previous option.<br />Ex: http://google.com', 'themeblvd' ),
				'id' 		=> 'source_link',
				'std' 		=> '',
				'type' 		=> 'text'
			),
			'reverse' => array(
				'name' 		=> __( 'Reverse Orientation', 'themeblvd_shortcodes' ),
				'desc' 		=> __( 'If you choose to reverse the orientation, the text and inner parts of the blockquote will be aligned to the right.', 'themeblvd' ),
				'id' 		=> 'reverse',
				'std' 		=> 'false',
				'type' 		=> 'select',
				'options' 	=> array(
					'false' 	=> __('False', 'themeblvd_builder'),
					'true' 		=> __('True', 'themeblvd_builder')
				)
			),
			'class' => array(
				'name' 		=> __( 'CSS Class (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'Any CSS classes you\'d like to add.', 'themeblvd_builder' ),
				'id' 		=> 'class',
				'std' 		=> '',
				'type' 		=> 'text'
			)
		);

		/*--------------------------------------------*/
		/* Raw Content
		/*--------------------------------------------*/

		$this->core_blocks['raw'] = array();

		// Information
		$this->core_blocks['raw']['info'] = array(
			'name' 		=> __( 'Raw Text', 'themeblvd_builder' ),
			'id'		=> 'raw',
			'query'		=> 'none',
			'height'	=> 'large'
		);

		// Options
		$this->core_blocks['raw']['options'] = array(
			'raw' => array(
		    	'id' 		=> 'raw',
				'name'		=> __( 'Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the content you\'d like to show. You may use basic HTML, and most shortcodes.', 'themeblvd_builder' ),
				'type'		=> 'textarea'
			),
			'raw_format' => array(
		    	'id' 		=> 'raw_format',
				'name'		=> __( 'Raw Content Formatting', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply WordPress automatic formatting.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			)
		);

		/*--------------------------------------------*/
		/* Simple Slider
		/*--------------------------------------------*/

		$this->core_blocks['simple_slider'] = array();

		// Information
		$this->core_blocks['simple_slider']['info'] = array(
			'name' 		=> __( 'Simple Slider', 'themeblvd_builder' ),
			'id'		=> 'simple_slider',
			'query'		=> 'none',
			'height'	=> 'large'
		);

		// Options
		$this->core_blocks['simple_slider']['options'] = array(
			'subgroup_start' => array(
				'type'		=> 'subgroup_start'
			),
			'images' => array(
		    	'id' 		=> 'images',
				'name'		=> null,
				'desc'		=> null,
				'type'		=> 'slider'
			),
			'crop' => array(
				'name' 		=> __( 'Image Crop Size', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the crop size to be used for the images. Remember that the slider will be scaled proportionally to fit within its container.', 'themeblvd' ),
				'id' 		=> 'crop',
				'std' 		=> 'slider-large',
				'type' 		=> 'select',
				'select'	=> 'crop',
				'class'		=> 'match-trigger' // Will send the value of this to hidden crop sizes with class "match" within each slide
			),
			'subgroup_end' => array(
				'type'		=> 'subgroup_end',
			),
			'interval' => array(
				'id'		=> 'interval',
				'name' 		=> __( 'Speed', 'themeblvd_builder' ),
				'desc' 		=> __( 'Seconds in between slider transitions. You can use 0 for the slider to not auto rotate.', 'themeblvd_builder' ),
				'std'		=> '5',
				'type'		=> 'text'
		    ),
			'pause' => array(
				'id'		=> 'pause',
				'desc' 		=> __( 'Pause slider on hover.', 'themeblvd_builder' ),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'wrap' => array(
				'id'		=> 'wrap',
				'desc'		=> __( 'cycle continuously or have hard stops', 'themeblvd_builder' ),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'nav_standard' => array(
				'id'		=> 'nav_standard',
				'desc'		=> __( 'Show standard navigation indicator dots.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'nav_arrows' => array(
				'id'		=> 'nav_arrows',
				'desc'		=> __( 'Whether to show standard navigation arrows.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'nav_thumbs' => array(
				'id'		=> 'nav_thumbs',
				'desc'		=> __( 'Show thumbnail navigation.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			),
			'link' => array(
				'id'		=> 'thumb_link',
				'desc'		=> __( 'Apply hover effect to linked images.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'dark_text'	=> array(
				'id'		=> 'dark_text',
				'desc'		=> __( 'Use dark navigation elements and dark text for any titles and descriptions.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			)
		);

		/*--------------------------------------------*/
		/* Slogan
		/*--------------------------------------------*/

		$this->core_blocks['slogan'] = array();

		// Information
		$this->core_blocks['slogan']['info'] = array(
			'name' 		=> __( 'Slogan', 'themeblvd_builder' ),
			'id'		=> 'slogan',
			'query'		=> 'none',
			'height'	=> 'large'
		);

		// Options
		$this->core_blocks['slogan']['options'] = array(
			'slogan' => array(
				'id' 		=> 'slogan',
				'name' 		=> __( 'Setup Slogan', 'themeblvd_builder'),
				'desc'		=> __( 'Enter the text you\'d like to show.', 'themeblvd_builder'),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
		    ),
		    'text_size' => array(
				'id' 		=> 'text_size',
				'name' 		=> __( 'Slogan Text Size', 'themeblvd_builder'),
				'desc'		=> __( 'Select how large you\'d like the text in the slogan to be.', 'themeblvd_builder'),
				'std'		=> 'large',
				'type'		=> 'select',
				'options'	=> array(
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'default' 	=> __( 'Normal', 'themeblvd_builder' ),
					'medium' 	=> __( 'Medium', 'themeblvd_builder' ),
					'large' 	=> __( 'Large', 'themeblvd_builder' )
				)
		    ),
		    'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide'
		    ),
			'button' => array(
		    	'id' 		=> 'button',
				'name'		=> __( 'Button', 'themeblvd_builder' ),
				'desc'		=> __( 'Show call-to-action button next to slogan?', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'hide receiver show-hide-toggle'
		    ),
			'button_color' => array(
				'id' 		=> 'button_color',
				'name'		=> __( 'Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what color you\'d like to use for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'trigger',
				'options'	=> themeblvd_colors()
			),
			'button_custom' => array(
				'id' 		=> 'button_custom',
				'name'		=> __( 'Custom Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Configure a custom style for the button.', 'themeblvd_builder' ),
				'std'		=> array(
					'bg' 				=> '#ffffff',
					'bg_hover'			=> '#ebebeb',
					'border' 			=> '#cccccc',
					'text'				=> '#333333',
					'text_hover'		=> '#333333',
					'include_bg'		=> 1,
					'include_border'	=> 1
				),
				'type'		=> 'button',
				'class'		=> 'receiver receiver-custom'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'button_text' => array(
				'id' 		=> 'button_text',
				'name'		=> __( 'Button Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the button.', 'themeblvd_builder' ),
				'std'		=> 'Get Started Today!',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_size' => array(
				'id' 		=> 'button_size',
				'name'		=> __( 'Button Size', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the size you\'d like used for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'large',
				'class'		=> 'hide receiver',
				'options'	=> array(
					'mini' 		=> __( 'Mini', 'themeblvd_builder' ),
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'default' 	=> __( 'Normal', 'themeblvd_builder' ),
					'large' 	=> __( 'Large', 'themeblvd_builder' ),
					'x-large' 	=> __( 'Extra Large', 'themeblvd_builder' )
				)
			),
			'button_url' => array(
				'id' 		=> 'button_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the full URL where you want the button\'s link to go.', 'themeblvd_builder' ),
				'std'		=> 'http://www.your-site.com/your-landing-page',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_target' => array(
				'id' 		=> 'button_target',
				'name'		=> __( 'Link Target', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you want the button to open the webpage.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'	=> array(
			        '_self' 	=> __( 'Same Window', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'New Window', 'themeblvd_builder' ),
			        'lightbox' 	=> __( 'Lightbox Popup', 'themeblvd_builder' )
				)
			),
			'button_icon_before' => array(
				'id' 		=> 'button_icon_before',
				'name'		=> __( 'Icon Before Button Text (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Icon before text of button. This can be any FontAwesome vector icon ID.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'vector',
				'class'		=> 'hide receiver'
			),
			'button_icon_after' => array(
				'id' 		=> 'button_icon_after',
				'name'		=> __( 'Icon After Button Text (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Icon after text of button. This can be any FontAwesome vector icon ID.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'vector',
				'class'		=> 'hide receiver'
			),
			'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			unset( $this->core_slogan['slogan']['options']['button_icon_before'] );
			unset( $this->core_slogan['slogan']['options']['button_icon_after'] );
		}

		/*--------------------------------------------*/
		/* Tabs
		/*--------------------------------------------*/

		$this->core_blocks['tabs'] = array();

		// Information
		$this->core_blocks['tabs']['info'] = array(
			'name' 		=> __( 'Tabs', 'themeblvd_builder' ),
			'id'		=> 'tabs',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['tabs']['options'] = array(
			'tabs' => array(
				'id' 		=> 'tabs',
				'name'		=> null,
				'desc'		=> null,
				'type'		=> 'tabs'
			),
			'nav' => array(
				'id' 		=> 'nav',
				'name'		=> __( 'Navigation', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the style of the navigation.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'tabs',
				'options'	=> array(
			        'tabs' 		=> __( 'Tabs', 'themeblvd_builder' ),
			        'pills' 	=> __( 'Pills', 'themeblvd_builder' )
				)
			),
			'style' => array(
				'id' 		=> 'style',
				'name'		=> __( 'Style', 'themeblvd_builder' ),
				'desc'		=> __( 'Select whether you want the content of the tabs to be framed or not.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'framed',
				'options'	=> array(
			        'open' 		=> __( 'Open Style', 'themeblvd_builder' ),
			        'framed' 	=> __( 'Framed Style', 'themeblvd_builder' )
				)
			),
			'height' => array(
				'id' 		=> 'height',
				'name'		=> __( 'Fixed Height', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply automatic fixed height across all tabs.<br><br>This just takes the height of the tallest tab\'s content and applies that across all tabs. This can help with "page jumping" in the case that not all tabs have equal amount of content. It can also help in the case when you\'re getting unwanted scrollbars on the inner content areas of tabs.', 'themeblvd_builder' ),
				'std'		=> 0,
				'type'		=> 'checkbox'
			)
		);

		/*--------------------------------------------*/
		/* Toggles
		/*--------------------------------------------*/

		$this->core_blocks['toggles'] = array();

		// Information
		$this->core_blocks['toggles']['info'] = array(
			'name' 		=> __( 'Toggles', 'themeblvd_builder' ),
			'id'		=> 'toggles',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['toggles']['options'] = array(
			'toggles' => array(
				'id' 		=> 'toggles',
				'name'		=> null,
				'desc'		=> null,
				'type'		=> 'toggles'
			),
			'accordion' => array(
				'id' 		=> 'accordion',
				'name'		=> __( 'Accordion', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply accordion functionality.<br><br>When a group of toggles functions as an accordion, it means that not more than one toggle can be open at any one time.', 'themeblvd_builder' ),
				'std'		=> 0,
				'type'		=> 'checkbox'
			)
		);

		/*--------------------------------------------*/
		/* Video
		/*--------------------------------------------*/

		$this->core_blocks['video'] = array();

		// Information
		$this->core_blocks['video']['info'] = array(
			'name' 		=> __( 'Video', 'themeblvd_builder' ),
			'id'		=> 'video',
			'query'		=> 'none',
			'height'	=> 'medium'
		);

		// Options
		$this->core_blocks['video']['options'] = array(
			'video' => array(
		    	'id' 		=> 'video',
				'name'		=> __( 'Video URL', 'themeblvd_builder' ),
				'desc'		=> __( '<p>Upload a video or enter a video URL compatible with <a href="" target="_blank">WordPress\'s oEmbed</a>.</p><p>Examples:<br />http://vimeo.com/11178250</br />http://youtube.com/watch?v=ginTCwWfGNY</p>', 'themeblvd_builder' ),
				'type'		=> 'upload',
				'video'		=> true
			)
		);

		/*--------------------------------------------*/
		/* Widget Area
		/*--------------------------------------------*/

		$this->core_blocks['widget'] = array();

		// Information
		$this->core_blocks['widget']['info'] = array(
			'name' 		=> __( 'Widget Area', 'themeblvd_builder' ),
			'id'		=> 'widget',
			'query'		=> 'none'
		);

		// Options
		$this->core_blocks['widget']['options'] = array(
			'sidebar' => array(
		    	'id' 		=> 'sidebar',
				'name'		=> __( 'Widget Area', 'themeblvd_builder' ),
				'desc'		=> __( 'Select from your registered widget areas.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'select'	=> 'sidebars_all'
			)
		);

	}

	/**
	 * Set content blocks by combining core elements and client-added
	 * blocks. Then remove any elements that have been set to
	 * be removed. This happens at the "after_setup_theme" hook
	 * with a priority of 1000.
	 *
	 * @since 2.0.0
	 */
	public function set_blocks() {

		// Combine core elements with client elements
		$this->blocks = array_merge( $this->core_blocks, $this->client_blocks );

		// Remove blocks
		if ( $this->remove_blocks ) {
			foreach ( $this->remove_blocks as $block_id ) {
				if ( isset( $this->blocks[$block_id] ) ) {
					unset( $this->blocks[$block_id] );
				}
			}
		}

		// Extend
		$this->blocks = apply_filters( 'themeblvd_blocks', $this->blocks );

	}

	/**
	 * Set original core layouts. These will be later merged
	 * with API client-added elements. WP-Admin only, see constructer.
	 *
	 * @since 1.1.1
	 */
	private function set_core_layouts() {

		$this->core_layouts = array();

		/*--------------------------------------------*/
		/* Business Homepage #1
		/*--------------------------------------------*/

		$this->core_layouts['business_1'] = array(
			'name'				=> __('Business Homepage #1', 'themeblvd_builder'),
			'id'				=> 'business_1',
			'preview' 			=> TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images/sample-business_1.png',
			'import'			=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/layout-business-homepage-1.xml'
		);

		/*--------------------------------------------*/
		/* Business Homepage #2
		/*--------------------------------------------*/

		$this->core_layouts['business_2'] = array(
			'name'				=> __('Business Homepage #2', 'themeblvd_builder'),
			'id'				=> 'business_2',
			'preview'			=> TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images/sample-business_2.png',
			'import'			=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/layout-business-homepage-2.xml'
		);

		/*--------------------------------------------*/
		/* Business Homepage #3
		/*--------------------------------------------*/

		$this->core_layouts['business_3'] = array(
			'name'				=> __('Business Homepage #3', 'themeblvd_builder'),
			'id'				=> 'business_3',
			'preview'			=> TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images/sample-business_3.png',
			'import'			=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/layout-business-homepage-3.xml'
		);

		/*--------------------------------------------*/
		/* Business Homepage #4
		/*--------------------------------------------*/

		$this->core_layouts['business_4'] = array(
			'name'				=> __('Business Homepage #4', 'themeblvd_builder'),
			'id'				=> 'business_4',
			'preview'			=> TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images/sample-business_4.png',
			'import'			=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/layout-business-homepage-4.xml'
		);

		/*--------------------------------------------*/
		/* Classic Magazine #1
		/*--------------------------------------------*/

		$this->core_layouts['magazine_1'] = array(
			'name'				=> __('Classic Magazine #1', 'themeblvd_builder'),
			'id'				=> 'magazine_1',
			'preview'			=> TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images/sample-magazine_1.png',
			'import'			=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/layout-magazine-1.xml'
		);

		/*--------------------------------------------*/
		/* Classic Magazine #2
		/*--------------------------------------------*/

		$this->core_layouts['magazine_2'] = array(
			'name'				=> __('Classic Magazine #2', 'themeblvd_builder'),
			'id'				=> 'magazine_2',
			'preview'			=> TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images/sample-magazine_2.png',
			'import'			=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/layout-magazine-2.xml'
		);

		/*--------------------------------------------*/
		/* Design Agency
		/*--------------------------------------------*/

		$this->core_layouts['agency'] = array(
			'name'				=> __('Design Agency', 'themeblvd_builder'),
			'id'				=> 'agency',
			'preview'			=> TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images/sample-agency.png',
			'import'			=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/layout-agency.xml'
		);

		/*--------------------------------------------*/
		/* Portfolio Homepage
		/*--------------------------------------------*/

		$this->core_layouts['portfolio'] = array(
			'name'				=> __('Portfolio Homepage', 'themeblvd_builder'),
			'id'				=> 'portfolio',
			'preview'			=> TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images/sample-portfolio.png',
			'import'			=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/layout-portfolio-homepage.xml'
		);

		/*--------------------------------------------*/
		/* Showcase Blogger
		/*--------------------------------------------*/

		$this->core_layouts['showcase'] = array(
			'name'				=> __('Showcase Blogger', 'themeblvd_builder'),
			'id'				=> 'showcase',
			'preview'			=> TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images/sample-showcase.png',
			'import'			=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/layout-showcase-blogger.xml'
		);

		/*--------------------------------------------*/
		/* Extend
		/*--------------------------------------------*/

		$this->core_layouts = apply_filters( 'themeblvd_core_layouts', $this->core_layouts );

	}

	/**
	 * Set sample layouts by combining core elements and client-added
	 * layouts. Then remove any layouts that have been set to
	 * be removed. This happens at the "after_setup_theme" hook
	 * with a priority of 1000.
	 *
	 * @since 1.1.1
	 */
	public function set_layouts() {

		// Merge core layouts with client API-added layouts
		$this->layouts = array_merge( $this->core_layouts, $this->client_layouts );

		// Remove layouts
		if ( $this->remove_layouts ) {
			foreach ( $this->remove_layouts as $layout ) {
				unset( $this->layouts[$layout] );
			}
		}

		// Extend
		$this->layouts = apply_filters( 'themeblvd_sample_layouts', $this->layouts );
	}

	/*--------------------------------------------*/
	/* Methods, client API mutators
	/*--------------------------------------------*/

	/**
	 * Manually register an element.
	 *
	 * Note: This won't be used in most cases, as registration
	 * of elements is taken care of automatically when adding
	 * and removing.
	 *
	 * @since 1.1.1
	 *
	 * @param string $id ID of element to register
	 */
	public function register_element( $id ) {
		$this->registered_elements[] = $id;
	}

	/**
	 * Manually register a content block.
	 *
	 * Note: This won't be used in most cases, as registration
	 * of blocks is taken care of automatically when adding
	 * and removing.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id ID of block to register
	 */
	public function register_block( $id ) {
		$this->registered_blocks[] = $id;
	}

	/**
	 * Manually de-register an element.
	 *
	 * Note: This won't be used in most cases, as registration
	 * of elements is taken care of automatically when adding
	 * and removing.
	 *
	 * @since 1.1.1
	 *
	 * @param string $id ID of element to register
	 */
	public function de_register_element( $id ) {

		if ( ! $this->registered_elements ) {
			return;
		}

		foreach ( $this->registered_elements as $key => $element ) {
			if ( $id == $element ) {
				unset( $this->registered_elements[$key] );
			}
		}

	}

	/**
	 * Manually de-register a content block.
	 *
	 * Note: This won't be used in most cases, as registration
	 * of blocks is taken care of automatically when adding
	 * and removing.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id ID of block to register
	 */
	public function de_register_block( $id ) {

		if ( ! $this->registered_blocks ) {
			return;
		}

		foreach ( $this->registered_blocks as $key => $block ) {
			if ( $id == $block ) {
				unset( $this->registered_blocks[$key] );
			}
		}

	}

	/**
	 * Add element to Builder.
	 *
	 * @since 1.1.1
	 *
	 * @param string $element_id ID of element to add
	 * @param string $element_name Name of element to add
	 * @param string $query_type Type of query if any - none, secondary, or primary
	 * @param array $options Options formatted for Options Framework
	 * @param string $callback Function to display element on frontend
	 */
	public function add_element( $element_id, $element_name, $query_type, $options, $callback ) {

		// Register element
		$this->registered_elements[] = $element_id;

		// Add in element
		if ( is_admin() ) {
			$this->client_elements[$element_id] = array(
				'info' => array(
					'name' 		=> $element_name,
					'id'		=> $element_id,
					'query'		=> $query_type,
					'hook'		=> 'themeblvd_'.$element_id,
					'shortcode'	=> null,
					'desc' 		=> null
				),
				'options' => $options
			);
		}

		// Hook in display function on frontend
		add_action( 'themeblvd_'.$element_id, $callback, 10, 3 );

	}

	/**
	 * Remove element from Builder.
	 *
	 * @since 1.1.1
	 *
	 * @param string $element_id ID of element to remove
	 */
	public function remove_element( $element_id ) {

		// Add to removal array, and process in set_elements()
		$this->remove_elements[] = $element_id;

		// De-register Element
		if ( $this->registered_elements ) {
			foreach ( $this->registered_elements as $key => $value ) {
				if ( $value == $element_id ) {
					unset( $this->registered_elements[$key] );
				}
			}
		}
	}

	/**
	 * Add content block to Builder.
	 *
	 * @since 2.0.0
	 */
	public function add_block( $element_id, $element_name, $options, $callback ) {
		// @todo ...
	}

	/**
	 * Remove content block to Builder.
	 *
	 * @since 2.0.0
	 */
	public function remove_block( $element_id ) {
		// @todo ...
	}

	/**
	 * Add sample layout to Builder.
	 *
	 * @since 1.1.1
	 *
	 * @param string $layout_id ID of sample layout to add
	 * @param string $layout_name Name of sample layout to add
	 * @param string $preview Image URL to preview image
	 * @param string $sidebar_layout Default sidebar layout
	 * @param string $import Absolute path to XML file of elements to import
	 */
	public function add_layout( $layout_id, $layout_name, $preview, $sidebar_layout, $import ) {

		// WP-Admin only
		if ( is_admin() ) {
			$this->client_layouts[$layout_id] = array(
				'name' 				=> $layout_name,
				'id' 				=> $layout_id,
				'preview' 			=> $preview,
				'sidebar_layout'	=> $sidebar_layout,
				'import'			=> $import
			);
		}

	}

	/**
	 * Remove sample layout from Builder.
	 *
	 * @since 1.1.1
	 *
	 * @param string $layout_id ID of sample layout to remove
	 */
	public function remove_layout( $layout_id ) {

		// Add to removal array, and process in set_elements()
		if ( is_admin() ) {
			$this->remove_layouts[] = $layout_id;
		}

	}

	/*--------------------------------------------*/
	/* Methods, accessors
	/*--------------------------------------------*/

	/**
	 * Get registered elements.
	 *
	 * @since 1.1.1
	 *
	 * @return array $registered_elements
	 */
	public function get_registered_elements() {
		return $this->registered_elements;
	}

	/**
	 * Get core elements and options.
	 *
	 * @since 1.1.1
	 *
	 * @return array $core_elements
	 */
	public function get_core_elements() {
		return $this->core_elements;
	}

	/**
	 * Get client API-added elements and options.
	 *
	 * @since 1.1.1
	 *
	 * @return array $client_elements
	 */
	public function get_client_elements() {
		return $this->client_elements;
	}

	/**
	 * Get final elements. This is the merged result of
	 * core elements and client API-added elements. This
	 * is available after WP's "after_setup_theme" hook.
	 *
	 * @since 1.1.1
	 *
	 * @return array $elements
	 */
	public function get_elements() {
		return $this->elements;
	}

	/**
	 * Get registered blocks.
	 *
	 * @since 2.0.0
	 *
	 * @return array $registered_blocks
	 */
	public function get_registered_blocks() {
		return $this->registered_blocks;
	}

	/**
	 * Get core blocks and options.
	 *
	 * @since 2.0.0
	 *
	 * @return array $core_blocks
	 */
	public function get_core_blocks() {
		return $this->core_blocks;
	}

	/**
	 * Get client API-added blocks and options.
	 *
	 * @since 2.0.0
	 *
	 * @return array $client_blocks
	 */
	public function get_client_blocks() {
		return $this->client_blocks;
	}

	/**
	 * Get final blocks. This is the merged result of
	 * core blocks and client API-added blocks. This
	 * is available after WP's "after_setup_theme" hook.
	 *
	 * @since 2.0.0
	 *
	 * @return array $blocks
	 */
	public function get_blocks() {
		return $this->blocks;
	}

	/**
	 * Get core sample layouts.
	 *
	 * @since 1.1.1
	 *
	 * @return array $core_layouts
	 */
	public function get_core_layouts() {
		return $this->core_layouts;
	}

	/**
	 * Get client API-added sample layouts.
	 *
	 * @since 1.1.1
	 *
	 * @return array $client_layouts
	 */
	public function get_client_layouts() {
		return $this->client_layouts;
	}

	/**
	 * Get final sample layouts. This is the merged result
	 * of core layouts and client API-added layouts. This
	 * is available after WP's "after_setup_theme" hook.
	 *
	 * @since 1.1.1
	 *
	 * @return array $layouts
	 */
	public function get_layouts() {
		return $this->layouts;
	}

	/*--------------------------------------------*/
	/* Methods, helpers
	/*--------------------------------------------*/

	/**
	 * Check if an element is currently registered.
	 *
	 * @since 1.1.1
	 *
	 * @param string $element_id ID of element to check
	 * @return bool Whether or not the element is registerd
	 */
	public function is_element( $element_id ) {
		return in_array( $element_id, $this->registered_elements );
	}

	/**
	 * Check if a content block is currently registered.
	 *
	 * @since 2.0.0
	 *
	 * @param string $element_id ID of element to check
	 * @return bool Whether or not the element is registerd
	 */
	public function is_block( $block_id ) {
		return in_array( $block_id, $this->registered_blocks );
	}

} // End class Theme_Blvd_Builder_API