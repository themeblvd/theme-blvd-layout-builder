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

		// Setup registered elements reference for frontend and
		// admin. This allows us to keep track of elements without
		// consuming as much memory on the frontend.
		$this->set_registered_elements();

		if ( is_admin() ) {

			// Setup framework default elements and sample
			// layouts to build onto for Builder interface.
			$this->set_core_elements();
			$this->set_core_layouts();

			// Establish elements and sample layouts based on
			// client modifications combined with framework defaults.
			add_action( 'after_setup_theme', array( $this, 'set_elements' ), 1000 );
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
			'slogan',
			'tabs'
		);
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
		/* (1) Columns
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

		// Options
		$this->core_elements['columns']['options'] = array(
		   	'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'columns'
		    ),
		   	'setup' => array(
				'id' 		=> 'setup',
				'name'		=> __( 'Setup Columns', 'themeblvd_builder' ),
				'desc'		=> __( 'Choose the number of columns along with the corresponding width configurations.', 'themeblvd_builder' ),
				'type'		=> 'columns',
				'options'	=> 'element'
			),
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

		/*--------------------------------------------*/
		/* (2) Content
		/*--------------------------------------------*/

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

		/*--------------------------------------------*/
		/* (3) Divider
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
		/* (4) Headline
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
		/* (5) Jumbotron
		/*--------------------------------------------*/

		$this->core_elements['jumbotron'] = array();

		// Information
		$this->core_elements['jumbotron']['info'] = array(
			'name'		=> __( 'Jumbotron', 'themeblvd_builder' ),
			'id'		=> 'jumbotron',
			'query'		=> 'none',
			'hook'		=> 'themeblvd_post_grid_paginated',
			'shortcode'	=> '[jumbotron]',
			'desc'		=> __( 'Bootstrap\'s Jumbotron unit, also knows as a "Hero" unit.' , 'themeblvd_builder' )
		);

		// Options
		$this->core_elements['jumbotron']['options'] = array(
			'jumbotron_slider_desc' => array(
				'id' 		=> 'jumbotron_slider_desc',
				'desc' 		=> __( 'This element utilizes the Jumbotron component of Twitter Bootstrap.', 'themeblvd_builder' ),
				'type' 		=> 'info'
			),
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
				'type'		=> 'textarea'
		    ),
		    'wpautop' => array(
		    	'id' 		=> 'wpautop',
				'name'		=> __( 'Content Formatting', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply WordPress automatic formatting to above content.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
		    'text_align' => array(
				'id' 		=> 'text_align',
				'name' 		=> __( 'Text Alignment', 'themeblvd_builder'),
				'desc'		=> __( 'Select how you\'d like the text within the unit aligned.', 'themeblvd_builder'),
				'std'		=> 'left',
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
			'button_text' => array(
				'id' 		=> 'button_text',
				'name'		=> __( 'Button Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the button.', 'themeblvd_builder' ),
				'std'		=> 'Get Started Today!',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_color' => array(
				'id' 		=> 'button_color',
				'name'		=> __( 'Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what color you\'d like to use for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'	=> themeblvd_colors()
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
					'large' 	=> __( 'Large', 'themeblvd_builder' )
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
			'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		/*--------------------------------------------*/
		/* (6) Post Grid (paginated)
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
		/* (7) Post Grid
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
			),
			'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide'
		    ),
			'link' => array(
		    	'id' 		=> 'link',
				'name'		=> __( 'Link', 'themeblvd_builder' ),
				'desc'		=> __( 'Show link after posts to direct visitors somewhere?', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'link_text' => array(
				'id' 		=> 'link_text',
				'name'		=> __( 'Link Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the link.', 'themeblvd_builder' ),
				'std'		=> 'View All Posts',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'link_url' => array(
				'id' 		=> 'link_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the full URL where you want this link to go to.', 'themeblvd_builder' ),
				'std'		=> 'http://www.your-site.com/your-blog-page',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'link_target' => array(
				'id' 		=> 'link_target',
				'name'		=> __( 'Link Target', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you want the link to open.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'		=> array(
			        '_self' 	=> __( 'Same Window', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'New Window', 'themeblvd_builder' )
				)
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		/*--------------------------------------------*/
		/* (8) Post Grid Slider
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
		/* (9) Post List (paginated)
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
		/* (10) Post List
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
		    ),
			'link' => array(
		    	'id' 		=> 'link',
				'name'		=> __( 'Link', 'themeblvd_builder' ),
				'desc'		=> __( 'Show link after posts to direct visitors somewhere?', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'link_text' => array(
				'id' 		=> 'link_text',
				'name'		=> __( 'Link Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the link.', 'themeblvd_builder' ),
				'std'		=> 'View All Posts',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'link_url' => array(
				'id' 		=> 'link_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the full URL where you want this link to go to.', 'themeblvd_builder' ),
				'std'		=> 'http://www.your-site.com/your-blog-page',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'link_target' => array(
				'id' 		=> 'link_target',
				'name'		=> __( 'Link Target', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you want the link to open.', 'themeblvd_builder' ),
				'std'		=> '_self',
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'		=> array(
			        '_self' 	=> __( 'Same Window', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'New Window', 'themeblvd_builder' )
				)
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		/*--------------------------------------------*/
		/* (11) Post List Slider
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
		/* (12) Post Slider
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
		/* (13) Slider
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
		/* (14) Slogan
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

		// Options
		$this->core_elements['slogan']['options'] = array(
			'slogan' => array(
				'id' 		=> 'slogan',
				'name' 		=> __( 'Setup Slogan', 'themeblvd_builder'),
				'desc'		=> __( 'Enter the text you\'d like to show.', 'themeblvd_builder'),
				'type'		=> 'textarea'
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
			'button_text' => array(
				'id' 		=> 'button_text',
				'name'		=> __( 'Button Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the button.', 'themeblvd_builder' ),
				'std'		=> 'Get Started Today!',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_color' => array(
				'id' 		=> 'button_color',
				'name'		=> __( 'Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what color you\'d like to use for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'	=> themeblvd_colors()
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
					'large' 	=> __( 'Large', 'themeblvd_builder' )
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
			'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		/*--------------------------------------------*/
		/* (15) Tabs
		/*--------------------------------------------*/

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

		// Options
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

		/*--------------------------------------------*/
		/* Global element options
		/*--------------------------------------------*/

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
	 * Set original core layouts. These will be later merged
	 * with API client-added elements. WP-Admin only, see constructer.
	 *
	 * @since 1.1.1
	 */
	private function set_core_layouts() {

		$this->core_layouts = array();

		// Path to images used in sample layouts on frontend.
		$imgpath = TB_BUILDER_PLUGIN_URI . '/includes/admin/assets/images';

		/*--------------------------------------------*/
		/* (1) Business Homepage #1
		/*--------------------------------------------*/

		// Information
		$this->core_layouts['business_1'] = array(
			'name'				=> __('Business Homepage #1', 'themeblvd_builder'),
			'id'				=> 'business_1',
			'preview' 			=> $imgpath . '/sample-business_1.png',
			'sidebar_layout' 	=> 'full_width'
		);

		// Featured Elements
		$this->core_layouts['business_1']['featured'] = array(
			'element_1' => array(
				'type'			=> 'slider',
				'query_type'	=> 'secondary',
				'options' 		=> array(
					'slider_id' => null
				)
			)
		);

		// Primary Elements
		$this->core_layouts['business_1']['primary'] = array(
			'element_2' => array(
				'type' 			=> 'slogan',
				'query_type' 	=> 'none',
				'options' 		=> array(
					'slogan'		=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.',
                    'button'		=> 1,
                    'button_text'	=> 'Get Started Today!',
                    'button_color'	=> 'default',
                    'button_url'	=> 'http://www.google.com',
                    'button_target'	=> '_blank'
				)
			),
			'element_3' => array(
                'type'			=> 'columns',
                'query_type'	=> 'none',
                'options'		=> array(
                    'setup' => array(
						'num' => '3',
						'width' => array(
							'2' => 'grid_6-grid_6',
							'3' => 'grid_4-grid_4-grid_4',
							'4' => 'grid_3-grid_3-grid_3-grid_3',
							'5' => 'grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1'
						)
					),
                    'col_1' => array(
						'type'			=> 'raw',
						'page' 			=> null,
						'raw'			=> "<h3>Sample Headline #1</h3>\n\n<img src=\"$imgpath/business_1.jpg\" />\n\nLorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n[button link=\"http://google.com\"]Learn More[/button]",
						'raw_format'	=> 1
					),
                    'col_2' => array(
						'type'			=> 'raw',
						'page'			=> null,
						'raw'			=> "<h3>Sample Headline #2</h3>\n\n<img src=\"$imgpath/business_2.jpg\" />\n\nLorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n[button link=\"http://google.com\"]Learn More[/button]",
						'raw_format'	=> 1
					),
                    'col_3' => array(
						'type'			=> 'raw',
						'page'			=> null,
						'raw'			=> "<h3>Sample Headline #3</h3>\n\n<img src=\"$imgpath/business_3.jpg\" />\n\nLorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n[button link=\"http://google.com\"]Learn More[/button]",
						'raw_format'	=> 1
					),
                    'col_4' => array(
						'type' 			=> null,
						'page'			=> null,
						'raw'			=> null,
						'raw_format' 	=> 1
					),
                    'col_5' => array(
						'type' 			=> null,
						'page'			=> null,
						'raw'			=> null,
						'raw_format'	=> 1
					)
                )
			)
		);

		// Featured Below Elements
		$this->core_layouts['business_1']['featured_below'] = array();

		/*--------------------------------------------*/
		/* (2) Business Homepage #2
		/*--------------------------------------------*/

		// Information
		$this->core_layouts['business_2'] = array(
			'name'				=> __('Business Homepage #2', 'themeblvd_builder'),
			'id'				=> 'business_2',
			'preview'			=> $imgpath . '/sample-business_2.png',
			'sidebar_layout'	=> 'full_width'
		);

		// Featured Elements
		$this->core_layouts['business_2']['featured'] = array(
			'element_1' => array(
				'type'			=> 'slider',
				'query_type' 	=> 'secondary',
				'options'		=> array(
					'slider_id' => null
				)
			)
		);

		// Main Elements
		$this->core_layouts['business_2']['primary'] = array(
			'element_2' => array(
				'type'			=> 'slogan',
				'query_type'	=> 'none',
				'options'		=> array(
					'slogan' 		=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.',
                    'button' 		=> 1,
                    'button_text' 	=> 'Get Started Today!',
                    'button_color' 	=> 'default',
                    'button_url' 	=> 'http://www.google.com',
                    'button_target' => '_blank'
				)
			),
			'element_3' => array(
                'type'			=> 'columns',
                'query_type' 	=> 'none',
                'options' 		=> array(
                    'setup' => array(
						'num' => '4',
						'width' => array(
							'2' => 'grid_6-grid_6',
							'3' => 'grid_4-grid_4-grid_4',
							'4' => 'grid_3-grid_3-grid_3-grid_3',
							'5' => 'grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1'
						)
					),
                    'col_1' => array(
						'type'			=> 'raw',
						'page'			=> null,
						'raw'			=> '[icon image="clock" align="left"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
						'raw_format'	=> 1
					),
                    'col_2' => array(
						'type'			=> 'raw',
						'page'			=> null,
						'raw' 			=> '[icon image="pie_chart" align="left"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
						'raw_format' 	=> 1
					),
                    'col_3' => array(
						'type'			=> 'raw',
						'page'			=> null,
						'raw'			=> '[icon image="coffee_mug" align="left"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
						'raw_format'	=> 1
					),
                    'col_4' => array(
						'type'			=> 'raw',
						'page'			=> null,
						'raw'			=> '[icon image="computer" align="left"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
						'raw_format'	=> 1
					),
                    'col_5' => array(
						'type'			=> null,
						'page'			=> null,
						'raw'			=> null,
						'raw_format'	=> 1
					)
                )
			)
		);

		// Featured Below Elements
		$this->core_layouts['business_2']['featured_below'] = array();

		/*--------------------------------------------*/
		/* (3) Business Homepage #3
		/*--------------------------------------------*/

		// Information
		$this->core_layouts['business_3'] = array(
			'name'				=> __('Business Homepage #3', 'themeblvd_builder'),
			'id'				=> 'business_3',
			'preview'			=> $imgpath . '/sample-business_3.png',
			'sidebar_layout'	=> 'sidebar_right'
		);

		// Featured Elements
		$this->core_layouts['business_3']['featured'] = array(
			'element_1' => array(
				'type'			=> 'slider',
				'query_type'	=> 'secondary',
				'options' 		=> array(
					'slider_id' => null
				)
			),
			'element_2' => array(
				'type' 			=> 'slogan',
				'query_type' 	=> 'none',
				'options' 		=> array(
					'slogan' 		=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.',
                    'button' 		=> 1,
                    'button_text' 	=> 'Get Started Today!',
                    'button_color' 	=> 'default',
                    'button_url' 	=> 'http://www.google.com',
                    'button_target' => '_blank'
				)
			)
		);

		// Main Elements
		$this->core_layouts['business_3']['primary'] = array(
			'element_3' => array(
				'type' 			=> 'content',
				'query_type' 	=> 'none',
				'options' 		=> array(
					'source' 		=> 'raw',
					'page_id' 		=> null,
					'raw_content' 	=> "<h2>Welcome to our fancy-schmancy website.</h2>\n\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>\n\n<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>\n\n<p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.</p>\n\n[one_half]\n<h4>We Rock</h4>\n\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>\n\n[/one_half]\n[one_half last]\n<h4>Hire Us</h4>\n\nLorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n[/one_half]",
					'raw_format' 	=> 0
				)
			),
		);

		// Featured Below Elements
		$this->core_layouts['business_3']['featured_below'] = array();

		/*--------------------------------------------*/
		/* (4) Business Homepage #4
		/*--------------------------------------------*/

		// Information
		$this->core_layouts['business_4'] = array(
			'name'				=> __('Business Homepage #4', 'themeblvd_builder'),
			'id'				=> 'business_4',
			'preview'			=> $imgpath . '/sample-business_4.png',
			'sidebar_layout'	=> 'full_width'
		);

		// Featured Elements
		$this->core_layouts['business_4']['featured'] = array();

		// Main Elements
		$this->core_layouts['business_4']['primary'] = array(
			'element_2' => array(
				'type' 			=> 'headline',
				'query_type' 	=> 'none',
				'options' 		=> array(
					'text' 			=> 'Welcome to our website',
					'tagline' 		=> '',
					'tag' 			=> 'h1',
					'align' 		=> 'left'
				)
			),
			'element_3' => array(
				'type' => 'columns',
                'query_type' => 'none',
                'options' => array(
                    'setup' => array(
						'num' => '3',
						'width' => array(
							'2' => 'grid_6-grid_6',
							'3' => 'grid_6-grid_3-grid_3', // => 50% | 25% | 25%
							'4' => 'grid_3-grid_3-grid_3-grid_3',
							'5' => 'grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1'
						)
					),
                    'col_1' => array(
						'type' 			=> 'raw',
						'page' 			=> null,
						'raw' 			=> "<img src=\"$imgpath/business_4.jpg\" class=\"pretty\" />\n\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla in bibendum enim. Nunc in est vitae leo imperdiet suscipit et sagittis leo. Nullam consectetur placerat sem, vitae feugiat lorem posuere nec. Etiam et magna nunc, et faucibus elit. Integer vitae pretium sem. Duis vitae lorem magna, ac tincidunt dolor. Phasellus justo metus, luctus in hendrerit eu, mattis eget lacus. Donec nulla turpis, euismod aliquam aliquam sed, semper vitae enim. Sed venenatis ligula eu enim tempor eget imperdiet dui pulvinar. Etiam et magna nunc, et faucibus elit. Integer vitae pretium sem.",
						'raw_format' 	=> 1
					),
                    'col_2' => array(
						'type' 			=> 'raw',
						'page' 			=> null,
						'raw' 			=> "[icon image=\"clock\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat\n\n[icon image=\"pie_chart\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
						'raw_format' 	=> 1
					),
                    'col_3' => array(
						'type' 			=> 'raw',
						'page' 			=> null,
						'raw' 			=> "[icon image=\"coffee_mug\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n[icon image=\"computer\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
						'raw_format' 	=> 1
					),
                    'col_4' => array(
						'type' 			=> null,
						'page' 			=> null,
						'raw' 			=> null,
						'raw_format' 	=> 1
					),
                    'col_5' => array(
						'type' 			=> null,
						'page' 			=> null,
						'raw' 			=> null,
						'raw_format' 	=> 1
					)
                )
			),
			'element_4' => array(
				'type' 			=> 'post_grid_slider',
				'query_type' 	=> 'secondary',
				'options' 		=> array(
					'fx' 			=> 'slide',
					'timeout' 		=> 0,
					'nav_standard' 	=> 1,
					'nav_arrows' 	=> 1,
					'pause_play' 	=> 1,
					'categories' 	=> array('all'=>1),
					'columns' 		=> 4,
					'rows' 			=> 1,
					'numberposts' 	=> -1,
					'orderby' 		=> 'post_date',
					'order' 		=> 'DESC',
					'offset' 		=> 0
				)
			)
		);

		// Featured Below Elements
		$this->core_layouts['business_4']['featured_below'] = array();

		/*--------------------------------------------*/
		/* (5) Classic Magazine #1
		/*--------------------------------------------*/

		// Information
		$this->core_layouts['magazine_1'] = array(
			'name'				=> __('Classic Magazine #1', 'themeblvd_builder'),
			'id'				=> 'magazine_1',
			'preview'			=> $imgpath . '/sample-magazine_1.png',
			'sidebar_layout'	=> 'sidebar_right'
		);

		// Featured Elements
		$this->core_layouts['magazine_1']['featured'] = array();

		// Main Elements
		$this->core_layouts['magazine_1']['primary'] = array(
			'element_1' => array(
				'type' 			=> 'slider',
				'query_type' 	=> 'secondary',
				'options' 		=> array(
					'slider_id' => null
				)
			),
			'element_2' => array(
				'type'			=> 'post_grid_paginated',
				'query_type'	=> 'primary',
				'options' 		=> array(
					'categories' 	=> array('all'=>1),
					'columns' 		=> 2,
					'rows' 			=> 3,
					'orderby' 		=> 'post_date',
					'order' 		=> 'DESC',
					'offset' 		=> 0
				)
			)
		);

		// Featured Below Elements
		$this->core_layouts['magazine_1']['featured_below'] = array();

		/*--------------------------------------------*/
		/* (6) Classic Magazine #2
		/*--------------------------------------------*/

		// Information
		$this->core_layouts['magazine_2'] = array(
			'name'				=> __('Classic Magazine #2', 'themeblvd_builder'),
			'id'				=> 'magazine_2',
			'preview'			=> $imgpath . '/sample-magazine_2.png',
			'sidebar_layout'	=> 'sidebar_right'
		);

		// Featured Elements
		$this->core_layouts['magazine_2']['featured'] = array();

		// Main Elements
		$this->core_layouts['magazine_2']['primary'] = array(
			'element_1' => array(
				// 1 post featured above everything else
				'type' 			=> 'post_list',
				'query_type'	=> 'secondary',
				'options' 		=> array(
					'categories' 	=> array('all'=>1),
					'thumbs' 		=> 'full',
					'content' 		=> 'default',
					'numberposts' 	=> 1,
					'orderby' 		=> 'post_date',
					'order' 		=> 'DESC',
					'offset' 		=> 0,
					'link' 			=> 0,
					'link_text' 	=> 'View All Posts',
					'link_url' 		=> 'http://www.your-site.com/your-blog-page',
					'link_target' 	=> '_self'
				)
			),
			'element_2' => array(
				// Continue post with offset = 1
				'type' 			=> 'post_grid',
				'query_type' 	=> 'secondary',
				'options' 		=> array(
					'categories' 	=> array('all'=>1),
					'columns' 		=> 3,
					'rows' 			=> 3,
					'orderby' 		=> 'post_date',
					'order' 		=> 'DESC',
					'offset' 		=> 1,
					'link' 			=> 0,
					'link_text' 	=> 'View All Posts &rarr;',
					'link_url' 		=> 'http://www.your-site.com/your-blog-page',
					'link_target' 	=> '_self'
				)
			)
		);

		// Featured Below Elements
		$this->core_layouts['magazine_2']['featured_below'] = array();

		/*--------------------------------------------*/
		/* (7) Design Agency
		/*--------------------------------------------*/

		// Information
		$this->core_layouts['agency'] = array(
			'name'				=> __('Design Agency', 'themeblvd_builder'),
			'id'				=> 'agency',
			'preview'			=> $imgpath . '/sample-agency.png',
			'sidebar_layout'	=> 'full_width'
		);

		// Featured Elements
		$this->core_layouts['agency']['featured'] = array(
			'element_1' => array(
				'type' => 'slogan',
				'query_type' => 'none',
				'options' => array(
					'slogan' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.',
					'button' => 0,
					'button_text' => 'Get Started Today!',
					'button_color' => 'default',
					'button_url' => 'http://www.your-site.com/your-landing-page',
					'button_target' => '_self'
				)
			),
			'element_2' => array(
				'type' => 'post_grid_slider',
				'query_type' => 'secondary',
				'options' => array(
					'fx' => 'slide',
					'timeout' => 0,
					'nav_standard' => 1,
					'nav_arrows' => 1,
					'pause_play' => 1,
					'categories' => array('all'=>1),
					'columns' => 4,
					'rows' => 2,
					'numberposts' => -1,
					'orderby' => 'post_date',
					'order' => 'DESC',
					'offset' => 0
				)
			)
		);

		// Main Elements
		$this->core_layouts['agency']['primary'] = array(
			'element_3' => array(
				'type'			=> 'columns',
				'query_type' 	=> 'none',
				'options' 		=> array(
                    'setup' => array(
						'num' => '3',
						'width' => array(
							'2' => 'grid_6-grid_6',
							'3' => 'grid_4-grid_4-grid_4',
							'4' => 'grid_3-grid_3-grid_3-grid_3',
							'5' => 'grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1-grid_fifth_1'
						)
					),
                    'col_1' => array(
						'type' 			=> 'raw',
						'page' 			=> null,
						'raw' 			=> "<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"clock\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"computer\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
						'raw_format' 	=> 1
					),
                    'col_2' => array(
						'type' 			=> 'raw',
						'page' 			=> null,
						'raw' 			=> "<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"pie_chart\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"image\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
						'raw_format' 	=> 1
					),
                    'col_3' => array(
						'type' 			=> 'raw',
						'page' 			=> null,
						'raw' 			=> "<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"coffee_mug\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\n<h3>Lorem ipsum dolor sit</h3>\n\n[icon image=\"camera\" align=\"left\"]Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
						'raw_format' 	=> 1
					),
                    'col_4' => array(
						'type' 			=> null,
						'page' 			=> null,
						'raw' 			=> null,
						'raw_format' 	=> 1
					),
                    'col_5' => array(
						'type' 			=> null,
						'page' 			=> null,
						'raw' 			=> null,
						'raw_format' 	=> 1
					)
                )
			)
		);

		// Featured Below Elements
		$this->core_layouts['agency']['featured_below'] = array();

		/*--------------------------------------------*/
		/* (8) Portfolio Homepage
		/*--------------------------------------------*/

		// Information
		$this->core_layouts['portfolio'] = array(
			'name'				=> __('Portfolio Homepage', 'themeblvd_builder'),
			'id'				=> 'portfolio',
			'preview'			=> $imgpath . '/sample-portfolio.png',
			'sidebar_layout'	=> 'full_width'
		);

		// Featured Elements
		$this->core_layouts['portfolio']['featured'] = array(
			'element_1' => array(
				'type' 			=> 'slider',
				'query_type' 	=> 'secondary',
				'options' 		=> array(
					'slider_id' => null
				)
			)
		);

		// Primary Elements
		$this->core_layouts['portfolio']['primary'] = array(
			'element_2' => array(
				'type' 			=> 'post_grid_paginated',
				'query_type' 	=> 'primary',
				'options' 		=> array(
					'type' 			=> 'post_grid_paginated',
					'query_type' 	=> 'primary',
					'options' 		=> array(
						'categories' 	=> array('all'=>1),
						'columns' 		=> 4,
						'rows'			=> 3,
						'orderby'		=> 'post_date',
						'order'			=> 'DESC',
						'offset' 		=> 0
					)
				)
			)
		);

		// Featured Below Elements
		$this->core_layouts['portfolio']['featured_below'] = array();

		/*--------------------------------------------*/
		/* (9) Showcase Blogger
		/*--------------------------------------------*/

		// Information
		$this->core_layouts['showcase'] = array(
			'name'				=> __('Showcase Blogger', 'themeblvd_builder'),
			'id'				=> 'showcase',
			'preview'			=> $imgpath . '/sample-showcase.png',
			'sidebar_layout'	=> 'sidebar_right'
		);

		// Featured Elements
		$this->core_layouts['showcase']['featured'] = array(
			'element_1' => array(
				'type'			=> 'slider',
				'query_type'	=> 'secondary',
				'options'		=> array(
					'slider_id' => null
				)
			),
			'element_2' => array(
				'type' 			=> 'slogan',
				'query_type' 	=> 'none',
				'options' 		=> array(
					'slogan'		=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.',
                    'button'		=> 1,
                    'button_text' 	=> 'Get Started Today!',
                    'button_color'	=> 'default',
                    'button_url' 	=> 'http://www.google.com',
                    'button_target'	=> '_blank'
				)
			)
		);

		// Main Elements
		$this->core_layouts['showcase']['primary'] = array(
			'element_3' => array(
				'type'			=> 'post_list_paginated',
				'query_type'	=> 'primary',
				'options'		=> array(
					'categories'	=> array('all'=>1),
					'columns'		=> 4,
					'rows'			=> 3,
					'orderby'		=> 'post_date',
					'order'			=> 'DESC',
					'offset'		=> 0
				)
			)
		);

		// Featured Below Elements
		$this->core_layouts['showcase']['featured_below'] = array();

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

		// Format client API-added sample layouts
		if ( $this->client_layouts ) {
			foreach ( $this->client_layouts as $id => $layouts ) {

				// Establish areas
				$this->client_layouts[$id]['featured'] = array();
				$this->client_layouts[$id]['primary'] = array();
				$this->client_layouts[$id]['featured_below'] = array();

				// Loop through and format elements, splitting them into
				// their areas -- featured, primary, & featured_below
				if ( $layouts['elements'] ) {
					$i = 1;
					foreach ( $layouts['elements'] as $element ) {

						// Skip if the element isn't registered
						if ( ! $this->is_element( $element['type'] ) ) {
							continue;
						}

						// Setup default option values
						$options = array();
						if ( ! empty( $element['defaults'] ) ) {
							foreach ( $this->elements[$element['type']]['options'] as $option ) {

								// Is this an actual configurable option?
								if ( ! isset( $option['id'] ) ) {
									continue;
								}

								$default_value = null;

								// Did the client put in a default value for this element?
								foreach ( $element['defaults'] as $key => $value ) {
									if ( $key == $option['id'] ) {
										$default_value = $value;
									}
								}

								// Is there a default value for the element in the builder
								// we can use instead if client didn't pass one?
								if ( $default_value === null && isset( $option['std'] ) ) {
									$default_value = $option['std'];
								}

								// Apply value
								$options[$option['id']] = $default_value;

							}

						}

						// Add element to proper area
						$this->client_layouts[$id][$element['location']]['element_'.$i] = array(
							'type' 			=> $element['type'],
							'query_type' 	=> $this->elements[$element['type']]['info']['query'],
							'options'		=> $options
						);

						$i++;
					}
				}

				// Remove overall elements array, now that it's been
				// split into areas.
				unset( $this->client_layouts[$id]['elements'] );
			}
		}

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
	 * Add sample layout to Builder.
	 *
	 * Note: $elements should be setup like --
	 *
	 * $elements = array(
	 *		array(
	 *			'type' => 'slider',
	 *			'location' => 'featured',
	 *			'defaults' => array()
	 *		),
	 *		array(
	 *			'type' => 'slogan',
	 *			'location' => 'featured',
	 *			'defaults' => array()
	 *		)
	 * );
	 *
	 * @since 1.1.1
	 *
	 * @param string $layout_id ID of sample layout to add
	 * @param string $layout_name Name of sample layout to add
	 * @param string $preview Image URL to preview image
	 * @param array $sidebar_layout Default sidebar layout
	 * @param string $elements Elements and their default settings
	 */
	public function add_layout( $layout_id, $layout_name, $preview, $sidebar_layout, $elements ) {

		// WP-Admin only
		if ( is_admin() ) {
			$this->client_layouts[$layout_id] = array(
				'name' 				=> $layout_name,
				'id' 				=> $layout_id,
				'preview' 			=> $preview,
				'sidebar_layout'	=> $sidebar_layout,
				'elements'			=> $elements
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
	 * Check if an elements is currently registered.
	 *
	 * @since 1.1.1
	 *
	 * @param string $element_id ID of element to check
	 * @return bool Whether or not the element is registerd
	 */
	public function is_element( $element_id ) {

		if ( in_array( $element_id, $this->registered_elements ) ) {
			return true;
		}

		return false;
	}

} // End class Theme_Blvd_Builder_API