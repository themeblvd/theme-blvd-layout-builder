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
	public $core_elements = array();

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
	public $core_layouts = array();

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

		// Setup registered element reference for frontend and
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
			'content',
			'columns',
			'divider',
			'headline',
			'jumbotron',
			'post_grid',
			'post_list',
			'slogan', // Promo Box
			'tabs'
		);

		// Elements requiring framework 2.5
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) {
			$this->registered_elements[] = 'alert';
			$this->registered_elements[] = 'author_box';
			$this->registered_elements[] = 'blog';
			$this->registered_elements[] = 'breadcrumbs';
			$this->registered_elements[] = 'chart_bar';
			$this->registered_elements[] = 'chart_line';
			$this->registered_elements[] = 'chart_pie';
			$this->registered_elements[] = 'contact';
			$this->registered_elements[] = 'current';
			$this->registered_elements[] = 'custom_field';
			$this->registered_elements[] = 'external';
			$this->registered_elements[] = 'map'; // Google map
			$this->registered_elements[] = 'html';
			$this->registered_elements[] = 'icon_box';
			$this->registered_elements[] = 'image';
			$this->registered_elements[] = 'milestone';
			$this->registered_elements[] = 'milestone_ring';
			$this->registered_elements[] = 'mini_post_grid';
			$this->registered_elements[] = 'mini_post_list';
			$this->registered_elements[] = 'panel';
			$this->registered_elements[] = 'partners';
			$this->registered_elements[] = 'post_showcase';
			$this->registered_elements[] = 'post_slider';
			$this->registered_elements[] = 'post_slider_popout';
			$this->registered_elements[] = 'pricing_table';
			$this->registered_elements[] = 'progress_bars';
			$this->registered_elements[] = 'quote';
			$this->registered_elements[] = 'simple_slider';
			$this->registered_elements[] = 'simple_slider_popout';
			$this->registered_elements[] = 'team_member';
			$this->registered_elements[] = 'testimonial';
			$this->registered_elements[] = 'testimonial_slider';
			$this->registered_elements[] = 'toggles';
			$this->registered_elements[] = 'video';
			$this->registered_elements[] = 'widget';
		}

		// Elements @deprecated as of framework 2.5, but being
		// added for old theme compat
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			$this->registered_elements[] = 'post_grid_paginated';
			$this->registered_elements[] = 'post_grid_slider';
			$this->registered_elements[] = 'post_list_paginated';
			$this->registered_elements[] = 'post_list_slider';
		}
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

		// Setup array for categories select
		//$categories_select = array();
		$categories_select = themeblvd_get_select( 'categories' );

		// Setup array for categories group of checkboxes
		$categories_multicheck = $categories_select;
		unset( $categories_multicheck['null'] );

		/*--------------------------------------------*/
		/* Alert
		/*--------------------------------------------*/

		$this->core_elements['alert'] = array();

		// Information
		$this->core_elements['alert']['info'] = array(
			'name' 		=> __('Alert', 'themeblvd_builder'),
			'id'		=> 'alert',
			'hook'		=> 'themeblvd_alert',
			'shortcode'	=> '[alert]',
			'desc' 		=> __( 'A boostrap styled alert.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['alert']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['alert']['options'] = array(
			'content' => array(
		    	'id' 		=> 'content',
				'name'		=> __( 'Content', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the content of the alert.', 'themeblvd_builder' ),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
			),
			'style' => array(
				'name' 		=> __( 'Style', 'themeblvd_shortcodes' ),
				'desc' 		=> __( 'The style of the alert.', 'themeblvd_builder' ),
				'id' 		=> 'style',
				'std' 		=> 'info',
				'type' 		=> 'select',
				'options' 	=> array(
					'info' 		=> __('Info (blue)', 'themeblvd_builder'),
					'success' 	=> __('Success (green)', 'themeblvd_builder'),
					'danger' 	=> __('Danger (red)', 'themeblvd_builder'),
					'warning' 	=> __('Warning (yellow)', 'themeblvd_builder')
				)
			)
		);

		/*--------------------------------------------*/
		/* Author Box
		/*--------------------------------------------*/

		$this->core_elements['author_box'] = array();

		// Information
		$this->core_elements['author_box']['info'] = array(
			'name' 		=> __('Author Box', 'themeblvd_builder'),
			'id'		=> 'author_box',
			'hook'		=> 'themeblvd_author_box',
			'shortcode'	=> false,
			'desc' 		=> __( 'The author box setup from the user\'s profile settings.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['author_box']['support'] = array(
			'popout'		=> false,
			'padding'		=> false
		);

		// Options
		$this->core_elements['author_box']['options'] = array(
			'user' => array(
		    	'id' 		=> 'user',
				'name'		=> __( 'Author', 'themeblvd_builder' ),
				'desc'		=> __( 'Select a WordPress user to pull the author box for. The individual author box settings can be set from the user\'s profile settings.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'select'	=> 'authors'
			)
		);

		/*--------------------------------------------*/
		/* Blog (primary posts display)
		/*--------------------------------------------*/

		$this->core_elements['blog'] = array();

		// Information
		$this->core_elements['blog']['info'] = array(
			'name'		=> __( 'Blog', 'themeblvd_builder' ),
			'id'		=> 'blog',
			'hook'		=> 'themeblvd_blog',
			'shortcode'	=> false,
			'desc'		=> __( 'Primary posts display', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['blog']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['blog']['options'] = array(
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
				'class' 	=> 'hide receiver receiver-category select-categories'
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
				'name'		=> __( 'Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of posts you\'d like to show. If your post list is paginated, this will be the number of posts per page, and if not, it will be the total number of posts. You can enter <em>-1</em> if you don\'t want there to be a limit.', 'themeblvd_builder' ),
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
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>.<br><br><em>Note: Offset will not take effect if you\'re using pagination for this post list.</em>', 'themeblvd_builder' ),
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
		    'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'display' => array(
		    	'id' 		=> 'display',
				'name'		=> __( 'Display', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to display the posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'list',
				'options'	=> array(
					'blog' 		=> __( 'Blog', 'themeblvd_builder' ),
					'paginated' => __( 'Blog, with pagination', 'themeblvd_builder' )
					//'ajax' 		=> __( 'Blog, with Ajax "Load More"', 'themeblvd_builder' ) // ... @TODO future feature
				),
				'class' 	=> 'tb-query-check trigger'
			),
			'paginated_hide' => array(
		    	'id' 		=> 'paginated_hide',
				'name'		=> null,
				'desc'		=> __( 'Hide other elements of the layout after page 1 of the posts.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'hide receiver receiver-paginated'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'thumbs' => array(
				'id' 		=> 'thumbs',
				'name' 		=> __( 'Featured Images', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Select the size of the post list\'s thumbnails or whether you\'d like to hide them all together when posts are listed.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default blog display setting', 'themeblvd_builder' ),
					'full' 		=> __( 'Show featured images', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide featured images', 'themeblvd_builder' )
					/*
					'default'	=> __( 'Use default primary posts display setting', 'themeblvd_builder' ),
					'small'		=> __( 'Show small thumbnails', 'themeblvd_builder' ),
					'full' 		=> __( 'Show full-width thumbnails', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide thumbnails', 'themeblvd_builder' )
					*/
				)
			),
			'meta' => array(
				'name' 		=> __( 'Meta Information', 'themeblvd' ),
				'desc' 		=> __( 'Select if you\'d like the meta information (like date posted, author, etc) to show for each post.', 'themeblvd' ),
				'id' 		=> 'meta',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default blog display setting', 'themeblvd_builder' ),
					'show'		=> __( 'Show meta info', 'themeblvd' ),
					'hide' 		=> __( 'Hide meta info', 'themeblvd' )
				)
			),
			'content' => array(
				'id' 		=> 'content',
				'name' 		=> __( 'Show excerpts of full content?', 'themeblvd_builder' ), /* Required by Framework */
				'desc' 		=> __( 'Choose whether you want to show full content or post excerpts only.', 'themeblvd_builder' ),
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default blog display setting', 'themeblvd_builder' ),
					'content'	=> __( 'Show full content', 'themeblvd_builder' ),
					'excerpt' 	=> __( 'Show excerpt only', 'themeblvd_builder' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Breadcrumbs
		/*--------------------------------------------*/

		$this->core_elements['breadcrumbs'] = array();

		// Information
		$this->core_elements['breadcrumbs']['info'] = array(
			'name'		=> __( 'Breadcrumbs', 'themeblvd_builder' ),
			'id'		=> 'breadcrumbs',
			'hook'		=> 'themeblvd_breadcrumbs',
			'shortcode'	=> false,
			'desc'		=> __( 'Breadcrumb trail for the current page.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['breadcrumbs']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		$this->core_elements['breadcrumbs']['options'] = array(
			// ...
		);

		/*--------------------------------------------*/
		/* Chart (Bar)
		/*--------------------------------------------*/

		$this->core_elements['chart_bar'] = array();

		// Information
		$this->core_elements['chart_bar']['info'] = array(
			'name' 		=> __( 'Chart (bar)', 'themeblvd_builder' ),
			'id'		=> 'chart_bar',
			'hook'		=> 'themeblvd_chart_bar',
			'shortcode'	=> false,
			'desc' 		=> __( 'A bar graph using chart.js plugin.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['chart_bar']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['chart_bar']['options'] = array(
			'data' => array(
		    	'id' 		=> 'data',
				'name'		=> __( 'Line Chart Data Sets', 'themeblvd_builder' ),
				'desc'		=> __( 'Each data set will represent a set of bars on your chart. Within each data set, you\'ll be inputting a list of values; make sure that you use the same amount of values for each data set.', 'themeblvd_builder' ),
				'type'		=> 'datasets'
			),
			'labels' => array(
		    	'id' 		=> 'labels',
				'name'		=> __( 'Chart Labels (x-axis)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a comma separated list of labels for the x-axis of the chart. The number of labels should match number of values you ented in each data set.<br>Ex: Jan, Feb, March, April, May, June', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> ''
			),
			'width' => array(
		    	'id' 		=> 'width',
				'name'		=> __( 'Chart Width', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a width in pixels for the chart.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '400'
			),
			'height' => array(
		    	'id' 		=> 'height',
				'name'		=> __( 'Chart Height', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a height in pixels for the chart.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '200'
			),
			'zero' => array(
		    	'id' 		=> 'zero',
				'name'		=> null,
				'desc'		=> __( 'Always start scale (y-axis) at 0.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
			'tooltips' => array(
		    	'id' 		=> 'tooltips',
				'name'		=> null,
				'desc'		=> __( 'Display labels when data points are hovered over.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '0'
			),
			'legend' => array(
		    	'id' 		=> 'legend',
				'name'		=> null,
				'desc'		=> __( 'Display chart legend.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '0'
			)
		);

		/*--------------------------------------------*/
		/* Chart (Line)
		/*--------------------------------------------*/

		$this->core_elements['chart_line'] = array();

		// Information
		$this->core_elements['chart_line']['info'] = array(
			'name' 		=> __( 'Chart (line)', 'themeblvd_builder' ),
			'id'		=> 'chart_line',
			'hook'		=> 'themeblvd_chart_line',
			'shortcode'	=> false,
			'desc' 		=> __( 'A line graph using chart.js plugin.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['chart_line']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['chart_line']['options'] = array(
			'data' => array(
		    	'id' 		=> 'data',
				'name'		=> __( 'Line Chart Data Sets', 'themeblvd_builder' ),
				'desc'		=> __( 'Each data set will represent a line on your chart. Within each data set, you\'ll be inputting a list of values; make sure that you use the same amount of values for each data set.', 'themeblvd_builder' ),
				'type'		=> 'datasets'
			),
			'labels' => array(
		    	'id' 		=> 'labels',
				'name'		=> __( 'Chart Labels (x-axis)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a comma separated list of labels for the x-axis of the chart. The number of labels should match number of values you ented in each data set.<br>Ex: Jan, Feb, March, April, May, June', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> ''
			),
			'width' => array(
		    	'id' 		=> 'width',
				'name'		=> __( 'Chart Width', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a width in pixels for the chart.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '400'
			),
			'height' => array(
		    	'id' 		=> 'height',
				'name'		=> __( 'Chart Height', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a height in pixels for the chart.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '200'
			),
			'curve' => array(
		    	'id' 		=> 'curve',
				'name'		=> null,
				'desc'		=> __( 'Curve line in between points.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
			'fill' => array(
		    	'id' 		=> 'fill',
				'name'		=> null,
				'desc'		=> __( 'Fill each dataset with color.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
			'dot' => array(
		    	'id' 		=> 'dot',
				'name'		=> null,
				'desc'		=> __( 'Show dot for each data point.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
			'zero' => array(
		    	'id' 		=> 'zero',
				'name'		=> null,
				'desc'		=> __( 'Always start scale (y-axis) at 0.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
			'tooltips' => array(
		    	'id' 		=> 'tooltips',
				'name'		=> null,
				'desc'		=> __( 'Display labels when data points are hovered over.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '0'
			),
			'legend' => array(
		    	'id' 		=> 'legend',
				'name'		=> null,
				'desc'		=> __( 'Display chart legend.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '0'
			)
		);

		/*--------------------------------------------*/
		/* Chart (Pie)
		/*--------------------------------------------*/

		$this->core_elements['chart_pie'] = array();

		// Information
		$this->core_elements['chart_pie']['info'] = array(
			'name' 		=> __( 'Chart (pie)', 'themeblvd_builder' ),
			'id'		=> 'chart_pie',
			'hook'		=> 'themeblvd_chart_pie',
			'shortcode'	=> false,
			'desc' 		=> __( 'A pie graph using chart.js plugin.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['chart_pie']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		$this->core_elements['chart_pie']['options'] = array(
			'data' => array(
		    	'id' 		=> 'data',
				'name'		=> __( 'Pie Chart Sectors', 'themeblvd_builder' ),
				'desc'		=> null,
				'type'		=> 'sectors'
			),
			'width' => array(
		    	'id' 		=> 'width',
				'name'		=> __( 'Chart Width', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a width in pixels for the chart.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '200'
			),
			'height' => array(
		    	'id' 		=> 'height',
				'name'		=> __( 'Chart Height', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a height in pixels for the chart.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '200'
			),
			'doughnut' => array(
		    	'id' 		=> 'doughnut',
				'name'		=> null,
				'desc'		=> __( 'Display chart as doughnut.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '0'
			),
			'tooltips' => array(
		    	'id' 		=> 'tooltips',
				'name'		=> null,
				'desc'		=> __( 'Display labels when sectors are hovered over.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
			'legend' => array(
		    	'id' 		=> 'legend',
				'name'		=> null,
				'desc'		=> __( 'Display chart legend.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '0'
			)
		);

		/*--------------------------------------------*/
		/* Columns
		/*--------------------------------------------*/

		$this->core_elements['columns'] = array();

		// Information
		$this->core_elements['columns']['info'] = array(
			'name' 		=> __('Columns', 'themeblvd_builder'),
			'id'		=> 'columns',
			'hook'		=> 'themeblvd_columns',
			'shortcode'	=> false,
			'desc' 		=> __( 'Row of columns with custom content', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['columns']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['columns']['options'] = array(
		   	'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'columns-setup hide'
		    ),
		   	'subgroup_start_2' => array(
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
			'stack' => array(
		    	'id' 		=> 'stack',
				'name'		=> __( 'Column Stack', 'themeblvd_builder' ),
				'desc'		=> __( 'When viewing your site responsively, at what point should your columns stack on top of each other? Be careful when selecting "Never stack"; this can break your mobile layout, depending on the content in your columns.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'md',
				'options'	=> array(
					'md'		=> __('Tablet (992px and below)', 'themeblvd_builder'),
					'sm'		=> __('Mobile (767px and below)', 'themeblvd_builder'),
					'xs'		=> __('Never stack', 'themeblvd_builder')
				),
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'subgroup_start_3' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'cshow-hide column-height'
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
			'subgroup_end_3' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'subgroup_end' => array(
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

			$this->core_elements['columns']['options']['subgroup_start']['class'] = 'columns';
			$this->core_elements['columns']['options']['setup']['desc'] = __( 'Choose the number of columns along with the corresponding width configurations.', 'themeblvd_builder' );

			unset( $this->core_elements['columns']['options']['subgroup_start_2'] );
			unset( $this->core_elements['columns']['options']['stack'] );
			unset( $this->core_elements['columns']['options']['subgroup_end_2'] );
			unset( $this->core_elements['columns']['options']['height'] );
			unset( $this->core_elements['columns']['options']['align'] );
			unset( $this->core_elements['columns']['options']['subgroup_end'] );

			$this->core_elements['columns']['options'] = array_merge( $this->core_elements['columns']['options'], $column_legacy_options );

		}

		/*--------------------------------------------*/
		/* Contact Bar
		/*--------------------------------------------*/

		$this->core_elements['contact'] = array();

		// Information
		$this->core_elements['contact']['info'] = array(
			'name' 		=> __( 'Contact Bar', 'themeblvd_builder' ),
			'id'		=> 'contact',
			'hook'		=> 'themeblvd_contact',
			'shortcode'	=> false,
			'desc' 		=> __( 'Set of contact social icons.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['contact']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['contact']['options'] = array(
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
				'options'	=> array(
					'flat'	=> __('Flat Color', 'themeblvd_builder'),
					'grey'	=> __('Flat Grey', 'themeblvd_builder'),
					'dark'	=> __('Flat Dark', 'themeblvd_builder'),
					'light'	=> __('Flat Light', 'themeblvd_builder'),
					'color'	=> __('Color', 'themeblvd_builder')
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
		/* Content
		/*--------------------------------------------*/

		$this->core_elements['content'] = array();

		// Information
		$this->core_elements['content']['info'] = array(
			'name' 		=> __('Content', 'themeblvd_builder'),
			'id'		=> 'content',
			'hook'		=> null,
			'shortcode'	=> false,
			'desc'		=> __( 'Content from external page or current page', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['content']['support'] = array(
			'popout'		=> true,
			'padding'		=> false
		);

		// Options
		$this->core_elements['content']['options'] = array(
			'content' => array(
		    	'id' 		=> 'content',
				'name'		=> __( 'Content', 'themeblvd_builder' ),
				'desc'		=> null,
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html',
				'class'		=> 'full-width'
			),
			'format' => array(
		    	'id' 		=> 'format',
				'name'		=> null,
				'desc'		=> __( 'Apply WordPress automatic formatting.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
			'subgroup_start' => array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide-toggle'
		    ),
			'style'	=> array(
				'id' 		=> 'style',
				'name' 		=> __( 'Styling', 'themeblvd_builder'),
				'desc'		=> __( 'Select if you\'d like to apply any special styling for this block.', 'themeblvd_builder'),
				'std'		=> 'none',
				'type'		=> 'select',
				'options'	=> apply_filters('themeblvd_promo_classes', array(
					'none'		=> __('None', 'themeblvd_builder'),
					'custom'	=> __('Custom BG color', 'themeblvd_builder')
				)),
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
				'class'		=> 'hide receiver receiver-custom'
			),
		    'bg_color' => array(
				'id' 		=> 'bg_color',
				'name' 		=> __( 'Background Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select a background color for the content block.', 'themeblvd_builder'),
				'std'		=> '#eeeeee',
				'type'		=> 'color',
				'class'		=> 'hide receiver receiver-custom'
		    ),
		    'bg_opacity' => array(
				'id'		=> 'bg_opacity',
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
				'class'		=> 'hide receiver receiver-custom'
			),
			'subgroup_end' => array(
				'type'		=> 'subgroup_end'
		    )
		);

		// Options for using this element with theme framework prior to 2.5
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

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
					'desc'		=> __( 'Enter the slug of a page you\'d like to pull from.', 'themeblvd_builder' ),
					'type'		=> 'text',
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
					'name'		=> __( 'Widget Area', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter the ID of a widget area.', 'themeblvd_builder' ),
					'type'		=> 'text',
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
		/* Current Page
		/*--------------------------------------------*/

		$this->core_elements['current'] = array();

		// Information
		$this->core_elements['current']['info'] = array(
			'name'		=> __( 'Current Page', 'themeblvd_builder' ),
			'id'		=> 'current',
			'hook'		=> 'themeblvd_current',
			'shortcode'	=> false,
			'desc'		=> __( 'Content from the current page.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['current']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['current']['options'] = array(
			// ...
		);

		/*--------------------------------------------*/
		/* Custom Field
		/*--------------------------------------------*/

		$this->core_elements['custom_field'] = array();

		// Information
		$this->core_elements['custom_field']['info'] = array(
			'name'		=> __( 'Custom Field', 'themeblvd_builder' ),
			'id'		=> 'custom_field',
			'hook'		=> 'themeblvd_custom_field',
			'shortcode'	=> false,
			'desc'		=> __( 'Grab a custom field from the current page.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['custom_field']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['custom_field']['options'] = array(
			'key' => array(
				'id' 		=> 'key',
				'name'		=> __( 'Name', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the name of the custom field you want to pull from for the current page.', 'themeblvd_builder' ),
				'type'		=> 'text'
			),
			'wpautop' => array(
		    	'id' 		=> 'wpautop',
				'desc'		=> __( 'Apply WordPress automatic formatting to custom field output.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '0'
			)
		);

		/*--------------------------------------------*/
		/* Divider
		/*--------------------------------------------*/

		$this->core_elements['divider'] = array();

		// Information
		$this->core_elements['divider']['info'] = array(
			'name' 		=> __( 'Divider', 'themeblvd_builder' ),
			'id'		=> 'divider',
			'hook'		=> 'themeblvd_divider',
			'shortcode'	=> '[divider]',
			'desc' 		=> __( 'Simple divider line to break up content', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['divider']['support'] = array(
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
					'shadow' 		=> __( 'Shadow Line', 'themeblvd_builder' ),
					'solid' 		=> __( 'Solid Line', 'themeblvd_builder' ),
			        'dashed' 		=> __( 'Dashed Line', 'themeblvd_builder' ),
					'double-solid' 	=> __( 'Double Solid Lines', 'themeblvd_builder' ),
					'double-dashed' => __( 'Double Dashed Lines', 'themeblvd_builder' )
				)
			),
			'width' => array(
				'id' 		=> 'width',
				'name'		=> __( 'Divider Width', 'themeblvd_builder' ),
				'desc'		=> __( 'If you\'d like to restrict the width of the divider enter an integer in pixels.<br>Ex: 100', 'themeblvd_builder' ),
				'type'		=> 'text'
			)
		);

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			unset( $this->core_elements['divider']['options']['type']['options']['double-solid'] );
			unset( $this->core_elements['divider']['options']['type']['options']['double-dashed'] );
			unset( $this->core_elements['divider']['options']['width'] );
		}

		/*--------------------------------------------*/
		/* External Post/Page Content
		/*--------------------------------------------*/

		$this->core_elements['external'] = array();

		// Information
		$this->core_elements['external']['info'] = array(
			'name' 		=> __( 'External Post/Page', 'themeblvd_builder' ),
			'id'		=> 'external',
			'hook'		=> 'themeblvd_external',
			'shortcode'	=> false,
			'desc' 		=> __( 'Content from external page or post', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['external']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['external']['options'] = array(
			'post_id' => array(
		    	'id' 		=> 'post_id',
				'name'		=> __( 'Post or Page ID', 'themeblvd_builder' ),
				'desc'		=> __( 'Insert a post or page ID to pull the content from. Remember that you are only pulling in the post\'s content and no other attributes.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'icon'		=> 'post_id'
			)
		);

		/*--------------------------------------------*/
		/* Google Map
		/*--------------------------------------------*/

		$this->core_elements['map'] = array();

		// Information
		$this->core_elements['map']['info'] = array(
			'name' 		=> __('Google Map', 'themeblvd_builder'),
			'id'		=> 'map',
			'hook'		=> null,
			'shortcode'	=> false,
			'desc'		=> __( 'Map from Google Maps', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['map']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['map']['options'] = array(
			'markers' => array(
				'id' 		=> 'markers',
				'name'		=> __( 'Map Location Markers', 'themeblvd_builder' ),
				'desc'		=> null,
				'type'		=> 'locations',
			),
			'subgroup_start' => array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide-toggle'
			),
			'center_type' => array(
		    	'id' 		=> 'center_type',
				'name'		=> __( 'Map Center', 'themeblvd_builder' ),
				'desc'		=> __( 'When the map is initially displayed, where do you want the center point to be?', 'themeblvd_builder' ),
				'type'		=> 'select',
				'options'		=> array(
					'default' 	=> __( 'Use first location marker', 'themeblvd_builder' ),
					'custom' 	=> __( 'Enter custom location', 'themeblvd_builder' )
				),
				'class'		=> 'trigger'
			),
			'center' => array(
		    	'id' 		=> 'center',
				'name'		=> __( 'Map Center Point', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter custom coordinates for the center point of the map. ', 'themeblvd_builder' ),
				'type'		=> 'geo',
				'class'		=> 'hide receiver receiver-custom'
			),
			'subgroup_end' => array(
				'type'		=> 'subgroup_end'
			),
			'height' => array(
		    	'id' 		=> 'height',
				'name'		=> __( 'Map Height', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the height of the map in pixels.', 'themeblvd_builder' ),
				'std'		=> '400',
				'type'		=> 'text'
			),
			'zoom' => array(
		    	'id' 		=> 'zoom',
				'name'		=> __( 'Map Zoom', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the zoom of the initially loaded map from 1 (very far) to 20 (very close).<br><br><em>Note: Depending on the distance of your location markers from the center point, you may need to adjust the zoom in order for all markers to be visable.</em>', 'themeblvd_builder' ),
				'std'		=> '15',
				'type'		=> 'slide',
				'options'	=> array(
					'min'	=> '1',
					'max'	=> '20',
					'step'	=> '1'
				)
			),
			'lightness' => array(
		    	'id' 		=> 'lightness',
				'name'		=> __( 'Map Color Brightness', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the map\'s color brightness.', 'themeblvd_builder' ),
				'std'		=> '0',
				'type'		=> 'slide',
				'options'	=> array(
					'min'	=> '-100',
					'max'	=> '100',
					'step'	=> '1'
				)
			),
			'saturation' => array(
		    	'id' 		=> 'saturation',
				'name'		=> __( 'Map Color Saturation', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the map\'s color saturation.', 'themeblvd_builder' ),
				'std'		=> '0',
				'type'		=> 'slide',
				'options'	=> array(
					'min'	=> '-100',
					'max'	=> '100',
					'step'	=> '1'
				)
			),
			'subgroup_start_1' => array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide'
			),
			'has_hue' => array(
		    	'id' 		=> 'has_hue',
				'name'		=> null,
				'desc'		=> __( 'Apply overlay color to map.', 'themeblvd_builder' ),
				'std'		=> '',
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'hue' => array(
		    	'id' 		=> 'hue',
				'name'		=> __( 'Map Overlay Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select a custom color for your map. The map will be tinted with this color.', 'themeblvd_builder' ),
				'std'		=> '#ff0000',
				'type'		=> 'color',
				'class'		=> 'hide receiver'
			),
			'subgroup_end_2' => array(
				'type'		=> 'subgroup_end'
			),
			'zoom_control' => array(
				'id' 		=> 'zoom_control',
				'name'		=> null,
				'desc'		=> __( 'Give user zoom control of map.', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'checkbox'
			),
			'pan_control' => array(
				'id' 		=> 'pan_control',
				'name'		=> null,
				'desc'		=> __( 'Give user pan control of map.', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'checkbox'
			),
			'draggable' => array(
				'id' 		=> 'draggable',
				'name'		=> null,
				'desc'		=> __( 'Map is draggable by the user.', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'checkbox'
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
			'hook'		=> 'themeblvd_headline',
			'shortcode'	=> false,
			'desc'		=> __( 'Simple &lt;H&gt; header title', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['headline']['support'] = array(
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
			'hook'		=> 'themeblvd_html',
			'shortcode'	=> '',
			'desc'		=> __( 'A block of HTML/JavaScript code.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['html']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['html']['options'] = array(
			'html' => array(
				'id' 		=> 'html',
				'type'		=> 'code',
				'lang'		=> 'html',
				'class'		=> 'block-hide tight' // "tight" CSS class will remove margin from bottom of option so it looks nicer alone w/ no description or following options
		    )
		);

		/*--------------------------------------------*/
		/* Icon Box
		/*--------------------------------------------*/

		$this->core_elements['icon_box'] = array();

		// Information
		$this->core_elements['icon_box']['info'] = array(
			'name' 		=> __( 'Icon Box', 'themeblvd_builder' ),
			'id'		=> 'icon_box',
			'hook'		=> 'themeblvd_icon_box',
			'shortcode'	=> false,
			'desc'		=> __( 'A box with an icon and text.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['icon_box']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['icon_box']['options'] = array(
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
			'badge' => array(
		    	'id' 		=> 'badge',
				'name'		=> null,
				'desc'		=> __( 'Display icon as a badge (in a circle).', 'themeblvd_builder' ),
				'std'		=> '0',
				'type'		=> 'checkbox'
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
				'class'		=> 'show-hide-toggle'
		    ),
			'style'	=> array(
				'id' 		=> 'style',
				'name' 		=> __( 'Styling', 'themeblvd_builder'),
				'desc'		=> __( 'Select if you\'d like to apply any special styling for this block.', 'themeblvd_builder'),
				'std'		=> 'none',
				'type'		=> 'select',
				'options'	=> apply_filters('themeblvd_promo_classes', array(
					'none'		=> __('None', 'themeblvd_builder'),
					'custom'	=> __('Custom BG color', 'themeblvd_builder')
				)),
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
				'class'		=> 'hide receiver receiver-custom'
			),
		    'bg_color' => array(
				'id' 		=> 'bg_color',
				'name' 		=> __( 'Background Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select a background color for the content block.', 'themeblvd_builder'),
				'std'		=> '#eeeeee',
				'type'		=> 'color',
				'class'		=> 'hide receiver receiver-custom'
		    ),
		    'bg_opacity' => array(
				'id'		=> 'bg_opacity',
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
				'class'		=> 'hide receiver receiver-custom'
			),
			'subgroup_end' => array(
				'type'		=> 'subgroup_end'
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
			'hook'		=> 'themeblvd_image',
			'shortcode'	=> false,
			'desc'		=> __( 'An image, which can be linked or framed to look like a "featured" image.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['image']['support'] = array(
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
			'frame' => array(
				'id' 		=> 'frame',
				'name'		=> null,
				'desc'		=> __( 'Add frame around the image.', 'themeblvd_builder' ),
				'type'		=> 'checkbox'
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
					'none'		=> __( 'No Link', 'themeblvd_builder' ),
					'_self' 	=> __( 'Link to webpage in same window.', 'themeblvd_builder' ),
					'_blank' 	=> __( 'Link to webpage in new window.', 'themeblvd_builder' ),
					'full'		=> __( 'Link to full image in lightbox.' ),
					'image' 	=> __( 'Link to another image in lightbox.', 'themeblvd_builder' ),
					'video' 	=> __( 'Link to video in lightbox.', 'themeblvd_builder' )
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
				'class'		=> 'hide desc-receiver receiver receiver-_self receiver-_blank receiver-image receiver-video'
			),
			'subgroup_end' => array(
				'type' 		=> 'subgroup_end'
			),
			'align' => array(
				'id' 		=> 'align',
				'name'		=> __( 'Alignment', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like the image aligned. This may not really be applicable if your image fills the entire horizontal space of where you\'ve placed it.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'options'	=> array(
					'none'		=> __( 'None', 'themeblvd_builder' ),
					'left' 		=> __( 'Left', 'themeblvd_builder' ),
					'center' 	=> __( 'Center', 'themeblvd_builder' ),
					'right'		=> __( 'Right', 'themeblvd_builder' )
				)
			),
			'title' => array(
				'id' 		=> 'title',
				'name'		=> __( 'Image Descriptive Text (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a brief description for the image, which can be beneficial for SEO and accessibility purposes.', 'themeblvd_builder' ),
				'type'		=> 'text'
			),
			'width' => array(
		    	'id' 		=> 'width',
		    	'name'		=> __( 'Display Width (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a width in pixels if you\'d like to force a width for the image. This can be useful for displaying retina-optimized images by entering a display width that is half the actual width of the image.<br>Ex: 200', 'themeblvd_builder' ),
				'type'		=> 'text'
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
			'hook'		=> 'themeblvd_jumbotron',
			'shortcode'	=> '[jumbotron]',
			'desc'		=> __( 'Bootstrap\'s Jumbotron unit, also knows as a "Hero" unit.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['jumbotron']['support'] = array(
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
				'code'		=> 'html',
				'class'		=> 'full-width'
		    ),
		    'wpautop' => array(
		    	'id' 		=> 'wpautop',
				'name'		=> __( 'Content Formatting', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply WordPress automatic formatting to above content.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
			'title_size' => array(
				'id' 		=> 'title_size',
				'name' 		=> __( 'Title Size', 'themeblvd_builder'),
				'desc'		=> __( 'Select the size of the title text.', 'themeblvd_builder'),
				'std'		=> '30px',
				'type'		=> 'slide',
				'options'	=> array(
					'units'	=> 'px',
					'min'	=> '10',
					'max'	=> '50',
					'step'	=> '1'
				)
		    ),
			'title_color' => array(
				'id' 		=> 'title_color',
				'name' 		=> __( 'Title Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select a text color for the title text.', 'themeblvd_builder'),
				'std'		=> '#444444',
				'type'		=> 'color'
			),
			'text_size' => array(
				'id' 		=> 'text_size',
				'name' 		=> __( 'Text Size', 'themeblvd_builder'),
				'desc'		=> __( 'Select the size of the content text.', 'themeblvd_builder'),
				'std'		=> '18px',
				'type'		=> 'slide',
				'options'	=> array(
					'units'	=> 'px',
					'min'	=> '10',
					'max'	=> '50',
					'step'	=> '1'
				)
		    ),
			'text_color' => array(
				'id' 		=> 'text_color',
				'name' 		=> __( 'Text Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select a text color for the content text.', 'themeblvd_builder'),
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
			'max' => array(
				'id' 		=> 'max',
				'name'		=> __( 'Maximum Width', 'themeblvd_builder' ),
				'desc'		=> __( 'If you\'d like to limit the width of the unit, give it a maximum width in pixels or as a percentage.<br>Ex: 400px, 50%, etc.', 'themeblvd_builder' ),
				'std'		=> '',
				'type'		=> 'text'
			),
			'align' => array(
				'name' 		=> __( 'Unit Alignment', 'themeblvd_shortcodes' ),
				'desc' 		=> __( 'If you\'ve set a maximum width, how you\'d like to align the entire unit on the page.', 'themeblvd_shortcodes' ),
				'id' 		=> 'align',
				'std' 		=> 'center',
				'type' 		=> 'select',
				'options' 	=> array(
					'left' 		=> __('Left', 'themeblvd_builder'),
					'right' 	=> __('Right', 'themeblvd_builder'),
					'center' 	=> __('Center', 'themeblvd_builder')
				),
				'class'		=> 'trigger'
			),
		    'buttons' => array(
		    	'id' 		=> 'buttons',
				'name'		=> __( 'Buttons (optional)', 'themeblvd_builder' ),
				'desc'		=> null,
				'type'		=> 'buttons'
			),
			'buttons_stack' => array(
				'id' 		=> 'buttons_stack',
				'name'		=> null,
				'desc'		=> __('Stack buttons on top of each other (if multiple buttons).', 'themeblvd_builder'),
				'type'		=> 'checkbox'
			),
			'buttons_block' => array(
				'id' 		=> 'buttons_block',
				'name'		=> null,
				'desc'		=> __('Display buttons as block-level elements (will also result in stacking).', 'themeblvd_builder'),
				'type'		=> 'checkbox'
			)
		);

		// Modified options for older themes
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
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
		}

		/*--------------------------------------------*/
		/* Milestone
		/*--------------------------------------------*/

		$this->core_elements['milestone'] = array();

		// Information
		$this->core_elements['milestone']['info'] = array(
			'name' 		=> __( 'Milestone', 'themeblvd_builder' ),
			'id'		=> 'milestone',
			'hook'		=> 'themeblvd_milestone',
			'shortcode'	=> '[milestone]',
			'desc'		=> __( 'Display a number as a milestone.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['milestone']['support'] = array(
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['milestone']['options'] = array(
			'milestone' => array(
				'id' 		=> 'milestone',
				'name'		=> __('Milestone', 'themeblvd_builder'),
				'desc'		=> __('Enter the accomplished milestone Optionally, you may include symbols before and/or after the number.<br>Ex: 500, $500, 500+, etc', 'themeblvd_builder'),
				'type'		=> 'text'
			),
			'color' => array(
				'id' 		=> 'color',
				'name'		=> __('Milestone Color', 'themeblvd_builder'),
				'desc'		=> __('Text color for the milestone number.', 'themeblvd_builder'),
				'std'		=> '#0c9df0',
				'type'		=> 'color'
			),
			'text' => array(
				'id' 		=> 'text',
				'name'		=> __('Description', 'themeblvd_builder'),
				'desc'		=> __('Enter a very simple description for the milestone number.', 'themeblvd_builder'),
				'type'		=> 'text'
			),
			'boxed' => array(
				'id' 		=> 'boxed',
				'name'		=> null,
				'desc'		=> __('Wrap milestone block in a box.', 'themeblvd_builder'),
				'type'		=> 'checkbox'
			)
		);

		/*--------------------------------------------*/
		/* Milestone Ring
		/*--------------------------------------------*/

		$this->core_elements['milestone_ring'] = array();

		// Information
		$this->core_elements['milestone_ring']['info'] = array(
			'name' 		=> __( 'Milestone Ring', 'themeblvd_builder' ),
			'id'		=> 'milestone_ring',
			'hook'		=> 'themeblvd_milestone_ring',
			'shortcode'	=> '[milestone_ring]',
			'desc'		=> __( 'Display a percentage-based milestone, with a ring representing the progress.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['milestone_ring']['support'] = array(
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['milestone_ring']['options'] = array(
			'percent' => array(
				'id' 		=> 'percent',
				'name'		=> __('Milestone Percent', 'themeblvd_builder'),
				'desc'		=> __('Enter an integer that is a fraction of 100. This will be represented as a visual percentage.<br>Ex: 25, 50, 75, etc.', 'themeblvd_builder'),
				'type'		=> 'text'
			),
			'color' => array(
				'id' 		=> 'color',
				'name'		=> __('Milestone Color', 'themeblvd_builder'),
				'desc'		=> __('This is the color of the milestone ring, which is a visual representation of the percentage.', 'themeblvd_builder'),
				'std'		=> '#0c9df0',
				'type'		=> 'color'
			),
			'display' => array(
				'id' 		=> 'display',
				'name'		=> __('Display', 'themeblvd_builder'),
				'desc'		=> __('Enter the text to display in the middle of the block.<br>Ex: 25%, 50%, 75%, etc.', 'themeblvd_builder'),
				'type'		=> 'text'
			),
			'title' => array(
				'id' 		=> 'title',
				'name'		=> __('Title (optional)', 'themeblvd_builder'),
				'desc'		=> __('Enter a short title to display below the milestone.', 'themeblvd_builder'),
				'type'		=> 'text'
			),
			'text' => array(
				'id' 		=> 'text',
				'name'		=> __('Description (optional)', 'themeblvd_builder'),
				'desc'		=> __('Enter a short description to display below the milestone.', 'themeblvd_builder'),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
			),
			'text_align' => array(
				'id' 		=> 'text_align',
				'name'		=> __('Text Alignment', 'themeblvd_builder'),
				'desc'		=> __('If you\'ve entered a title and/or description, select how would you like the text aligned.', 'themeblvd_builder'),
				'std'		=> 'center',
				'type'		=> 'select',
				'options'	=> array(
					'left' 		=> __( 'Left', 'themeblvd_builder' ),
					'right' 	=> __( 'Right', 'themeblvd_builder' ),
					'center' 	=> __( 'Center', 'themeblvd_builder' )
				)
			),
			'boxed' => array(
				'id' 		=> 'boxed',
				'name'		=> null,
				'desc'		=> __('Wrap milestone block in a box.', 'themeblvd_builder'),
				'type'		=> 'checkbox'
			)
		);

		/*--------------------------------------------*/
		/* Mini Post Grid
		/*--------------------------------------------*/

		$this->core_elements['mini_post_grid'] = array();

		// Information
		$this->core_elements['mini_post_grid']['info'] = array(
			'name'		=> __( 'Mini Post Grid', 'themeblvd_builder' ),
			'id'		=> 'mini_post_grid',
			'hook'		=> 'themeblvd_mini_post_grid',
			'shortcode'	=> '[mini_post_grid]',
			'desc'		=> __( 'Mini post grid', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['mini_post_grid']['support'] = array(
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['mini_post_grid']['options'] = array(
			'title' => array(
				'name' 		=> __( 'Title (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'The title of the mini post grid.', 'themeblvd_builder' ),
				'id' 		=> 'title',
				'std' 		=> '',
				'type' 		=> 'text'
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
			        'gallery' 	=> __( 'Gallery', 'themeblvd_builder' ),
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
				'class' 	=> 'hide receiver receiver-category select-categories'
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
				'name'		=> __( 'Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of posts you\'d like to show. If your post list is paginated, this will be the number of posts per page, and if not, it will be the total number of posts. You can enter <em>-1</em> if you don\'t want there to be a limit.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '9',
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
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>.<br><br><em>Note: Offset will not take effect if you\'re using pagination for this post list.</em>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'gallery' => array(
		    	'id' 		=> 'gallery',
				'name'		=> __( 'Gallery', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter an instance of the [gallery] shortcode.<br>Ex: [gallery ids="1,2,3"]', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-gallery'
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
				'name' 		=> __( 'Thumbnails', 'themeblvd_builder' ),
				'desc' 		=> __( 'Choosed the size of thumbnails in the mini post grid.', 'themeblvd_builder' ),
				'id' 		=> 'thumbs',
				'std' 		=> 'smaller',
				'type' 		=> 'select',
				'options' => array(
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'smaller'	=> __( 'Smaller', 'themeblvd_builder' ),
					'smallest' 	=> __( 'Smallest', 'themeblvd_builder' )
				)
			),
			'align' => array(
				'name' 		=> __( 'Thumbnail Alignment', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select how you\'d like the thumbnails aligned.', 'themeblvd_builder' ),
				'id' 		=> 'align',
				'std' 		=> 'left',
				'type' 		=> 'select',
				'options' 	=> array(
					'left'		=> __( 'Left', 'themeblvd_builder' ),
					'center' 	=> __( 'Center', 'themeblvd_builder' ),
					'right' 	=> __( 'Right', 'themeblvd_builder' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Mini Post List
		/*--------------------------------------------*/

		$this->core_elements['mini_post_list'] = array();

		// Information
		$this->core_elements['mini_post_list']['info'] = array(
			'name'		=> __( 'Mini Post List', 'themeblvd_builder' ),
			'id'		=> 'mini_post_list',
			'hook'		=> 'themeblvd_mini_post_list',
			'shortcode'	=> '[mini_post_list]',
			'desc'		=> __( 'Mini post list', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['mini_post_list']['support'] = array(
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['mini_post_list']['options'] = array(
			'title' => array(
				'name' 		=> __( 'Title (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'The title of the mini post list.', 'themeblvd_builder' ),
				'id' 		=> 'title',
				'std' 		=> '',
				'type' 		=> 'text'
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
				'class' 	=> 'hide receiver receiver-category select-categories'
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
				'name'		=> __( 'Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of posts you\'d like to show. If your post list is paginated, this will be the number of posts per page, and if not, it will be the total number of posts. You can enter <em>-1</em> if you don\'t want there to be a limit.', 'themeblvd_builder' ),
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
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>.<br><br><em>Note: Offset will not take effect if you\'re using pagination for this post list.</em>', 'themeblvd_builder' ),
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
				'name' 		=> __( 'Thumbnails', 'themeblvd_builder' ),
				'desc' 		=> __( 'Choosed the size of featured images in the mini post list, or if you want them hidden.', 'themeblvd_builder' ),
				'id' 		=> 'thumbs',
				'std' 		=> 'smaller',
				'type' 		=> 'select',
				'options' => array(
					'0' 		=> __( 'Hide', 'themeblvd_builder' ),
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'smaller'	=> __( 'Smaller', 'themeblvd_builder' ),
					'smallest' 	=> __( 'Smallest', 'themeblvd_builder' ),
					'date'		=> __( 'Date Block', 'themeblvd_builder' ),
				)
			),
			'meta' => array(
				'name' 		=> __( 'Meta Information', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select if you\'d like the meta information to show for each post.', 'themeblvd_builder' ),
				'id' 		=> 'meta',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'1'			=> __( 'Show meta info', 'themeblvd_builder' ),
					'0' 		=> __( 'Hide meta info', 'themeblvd_builder' )
				)
			),
			'columns' => array(
				'name' 		=> __( 'Column Spread', 'themeblvd_builder' ),
				'desc' 		=> __( 'Here, you can choose to list out your posts separated into columns.<br><br><em>Note: For best results, set your "Number of Posts" option above to a number divisable by the number of columns.</em>', 'themeblvd_builder' ),
				'id' 		=> 'columns',
				'std' 		=> '1',
				'type' 		=> 'select',
				'options' 	=> array(
					'1'			=> __( 'Don\'t spread across multiple columns', 'themeblvd_builder' ),
					'2'			=> __( '2 Columns', 'themeblvd_builder' ),
					'3' 		=> __( '3 Columns', 'themeblvd_builder' ),
					'4' 		=> __( '4 Columns', 'themeblvd_builder' ),
					'5' 		=> __( '5 Columns', 'themeblvd_builder' ),
					'6' 		=> __( '6 Columns', 'themeblvd_builder' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Panel
		/*--------------------------------------------*/

		$this->core_elements['panel'] = array();

		// Information
		$this->core_elements['panel']['info'] = array(
			'name' 		=> __( 'Panel', 'themeblvd_builder' ),
			'id'		=> 'panel',
			'hook'		=> 'themeblvd_panel',
			'shortcode'	=> '[panel]',
			'desc'		=> __( 'Display a boostrap-styled panel.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['panel']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['panel']['options'] = array(
			'content' => array(
		    	'id' 		=> 'content',
				'name'		=> __( 'Content', 'themeblvd_builder' ),
				'desc' 		=> __( 'Enter the content to show in the panel\'s main section.', 'themeblvd_builder' ),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
			),
			'style' => array(
				'name' 		=> __( 'Style', 'themeblvd_builder' ),
				'desc' 		=> __( 'Style of the panel.', 'themeblvd_builder' ),
				'id' 		=> 'style',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default' 	=> __('Default', 'themeblvd_builder'),
					'primary' 	=> __('Primary', 'themeblvd_builder'),
					'info' 		=> __('Info (light blue)', 'themeblvd_builder'),
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
			)
		);

		/*--------------------------------------------*/
		/* Partners
		/*--------------------------------------------*/

		$this->core_elements['partners'] = array();

		// Information
		$this->core_elements['partners']['info'] = array(
			'name' 		=> __( 'Partner Logos', 'themeblvd_builder' ),
			'id'		=> 'partners',
			'hook'		=> 'themeblvd_partners',
			'shortcode'	=> false,
			'desc'		=> __( 'Display a grid or slider of partner logos.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['partners']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['partners']['options'] = array(
			'logos' => array(
				'id' 		=> 'logos',
				'name'		=> __( 'Logos', 'themeblvd_builder' ),
				'desc'		=> null,
				'type'		=> 'logos'
			),
			'title' => array(
				'id' 		=> 'title',
				'name'		=> __('Title (optional)', 'themeblvd_builder'),
				'desc'		=> __('If you want, you can give this set of partner logos a title.', 'themeblvd_builder'),
				'std'		=> 'Partners',
				'type'		=> 'text'
			),
			'height' => array(
				'id' 		=> 'height',
				'name'		=> __('Height', 'themeblvd_builder'),
				'desc'		=> __('Give your logo blocks a common height. This will help for all of your logos to appear better aligned with each other.', 'themeblvd_builder'),
				'std'		=> '100',
				'type'		=> 'text'
			),
			'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'display' => array(
		    	'id' 		=> 'display',
				'name'		=> __( 'Logo Display', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to display the logos.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'slider',
				'options'	=> array(
					'slider' 	=> __( 'Slider', 'themeblvd_builder' ),
			        'grid' 		=> __( 'Grid', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger'
			),
			'slide' => array(
				'id' 		=> 'slide',
				'name'		=> __( 'Logos per slide', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the amount of logos to display for each slide of the slider.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> '4',
				'options'	=> array(
					'2' 		=> __( '2 logos per slide', 'themeblvd_builder' ),
			        '3' 		=> __( '3 logos per slide', 'themeblvd_builder' ),
			        '4' 		=> __( '4 logos per slide', 'themeblvd_builder' ),
			        '5' 		=> __( '5 logos per slide', 'themeblvd_builder' )
				),
				'class'		=> 'hide receiver receiver-slider'
			),
			'timeout' => array(
		    	'id' 		=> 'timeout',
				'name'		=> __( 'Speed', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of seconds you\'d like in between trasitions. You may use <em>0</em> to disable the slider from auto advancing.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class'		=> 'hide receiver receiver-slider'
			),
			'nav' => array(
				'id' 		=> 'nav',
				'name'		=> null,
				'desc'		=> __( 'Display slider navigation.', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'checkbox',
				'class'		=> 'hide receiver receiver-slider'
			),
			'grid' => array(
				'id' 		=> 'grid',
				'name'		=> __( 'Logos per row', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the amount of logos to display for each row of the grid.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> '4',
				'options'	=> array(
					'2' 		=> __( '2 logos per row', 'themeblvd_builder' ),
			        '3' 		=> __( '3 logos per row', 'themeblvd_builder' ),
			        '4' 		=> __( '4 logos per row', 'themeblvd_builder' ),
			        '5' 		=> __( '5 logos per row', 'themeblvd_builder' )
				),
				'class'		=> 'hide receiver receiver-grid'
			),
		    'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'boxed' => array(
				'id' 		=> 'boxed',
				'name'		=> null,
				'desc'		=> __('Wrap each logo in a box.', 'themeblvd_builder'),
				'std'		=> '1',
				'type'		=> 'checkbox'
			),
			'greyscale' => array(
				'id' 		=> 'greyscale',
				'name'		=> null,
				'desc'		=> __( 'Display logos as black and white until hovered on.', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'checkbox'
			)
		);

		/*--------------------------------------------*/
		/* Post Grid (paginated)
		/*--------------------------------------------*/

		// As of framework v2.5, standard post grid can be displayed as paginated.
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			$this->core_elements['post_grid_paginated'] = array();

			// Information
			$this->core_elements['post_grid_paginated']['info'] = array(
				'name'		=> __( 'Post Grid (paginated)', 'themeblvd_builder' ),
				'id'		=> 'post_grid_paginated',
				'hook'		=> 'themeblvd_post_grid_paginated',
				'shortcode'	=> '[post_grid]',
				'desc'		=> __( 'Full paginated grid of posts', 'themeblvd_builder' )
			);

			// Support
			$this->core_elements['post_grid_paginated']['support'] = array(
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
		}

		/*--------------------------------------------*/
		/* Post Grid
		/*--------------------------------------------*/

		$this->core_elements['post_grid'] = array();

		// Information
		$this->core_elements['post_grid']['info'] = array(
			'name'		=> __( 'Post Grid', 'themeblvd_builder' ),
			'id'		=> 'post_grid',
			'hook'		=> 'themeblvd_post_grid',
			'shortcode'	=> '[post_grid]',
			'desc'		=> __( 'Grid of posts followed by optional link', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_grid']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_grid']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'title' => array(
				'id' 		=> 'title',
				'name'		=> __('Title (optional)', 'themeblvd_builder'),
				'desc'		=> __('If you want, you can give this set of posts a title.', 'themeblvd_builder'),
				'std'		=> '',
				'type'		=> 'text'
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
			        'pages' 	=> __( 'Pages', 'themeblvd_builder' ),
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
				'class' 	=> 'hide receiver receiver-category select-categories'
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
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>.<br><br><em>Note: Offset will not take effect if you\'re using pagination for this post grid.</em>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'pages' => array(
		    	'id' 		=> 'pages',
				'name'		=> __( 'Pages', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a comma-separated list of page slugs.<br>Ex: page-1, page-2, page-3', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-pages'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ<br><br><em>Note: You cannot set the number of posts because this is generated in a grid based on the rows and columns, except when using masonry.</em>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'display' => array(
		    	'id' 		=> 'display',
				'name'		=> __( 'Display', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to display the posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'grid',
				'options'	=> array(
					'grid' 				=> __( 'Grid', 'themeblvd_builder' ),
					'paginated' 		=> __( 'Grid, with pagination', 'themeblvd_builder' ),
					'filter' 			=> __( 'Grid, with filtering', 'themeblvd_builder' ),
					'masonry' 			=> __( 'Grid Masonry', 'themeblvd_builder' ),
					'masonry_paginated' => __( 'Grid Masonry, with pagination', 'themeblvd_builder' ),
					'masonry_filter' 	=> __( 'Grid Masonry, with filtering', 'themeblvd_builder' ),
					//'ajax' 			=> __( 'Grid, with Ajax "Load More"', 'themeblvd_builder' ), // ... @TODO future feature
					'slider' 			=> __( 'Grid Slider', 'themeblvd_builder' )
				),
				'class' 	=> 'trigger tb-query-check'
			),
			'columns' => array(
		    	'id' 		=> 'columns',
				'name'		=> __( 'Columns', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how many posts per row (or slide) you\'d like displayed.', 'themeblvd_builder' ),
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
				'name'		=> __( 'Maximum Number of Rows', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the maximum number of rows you\'d like to show. The number you enter here will be multiplied by the amount of columns you selected in the previous option to figure out how many posts should be showed. You can leave this option blank if you\'d like to show all posts from your configured query.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3',
				'class'		=> 'hide receiver receiver-grid receiver-paginated'
			),
			'paginated_hide' => array(
		    	'id' 		=> 'paginated_hide',
				'name'		=> null,
				'desc'		=> __( 'Hide other elements of the layout after first page of posts.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'hide receiver receiver-paginated'
			),
			'filter' => array(
		    	'id' 		=> 'filter',
				'name'		=> __( 'Filtering: Filter by', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how the the posts can be filtered by the website visitor.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options' => array(
					'category'	=> __( 'Filtered by category', 'themeblvd_builder' ),
					'post_tag'	=> __( 'Filtered by tag', 'themeblvd_builder' )
				),
				'class'		=> 'hide receiver receiver-filter receiver-masonry_filter'
			),
			'filter_max' => array(
		    	'id' 		=> 'filter_max',
				'name'		=> __( 'Filtering: Max Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'By using <code>-1</code>, it means all posts for the queried criteria will be pulled, and this works great for filtering. However, performance issues can arrise if you have a large volume of posts you\'re pulling from. If this is an issue, you can set a maximum here. Ex: <code>50</code>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '-1',
				'class'		=> 'hide receiver receiver-filter receiver-masonry_filter'
			),
			'posts_per_page' => array(
		    	'id' 		=> 'posts_per_page',
				'name'		=> __( 'Masonry: Number of posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the maximum number of posts, or posts per page, if using pagination.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '12',
				'class'		=> 'hide receiver receiver-masonry receiver-masonry_paginated'
			),
			'slides' => array(
		    	'id' 		=> 'slides',
				'name'		=> __( 'Grid Slider: Maximum Number of Slides', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the maximum number of slides you\'d like to show. The number you enter here will be multiplied by the amount of columns you selected in the previous option to figure out how many posts should be showed in the slider. You can leave this option blank if you\'d like to show all posts from your configured query.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3',
				'class' 	=> 'hide receiver receiver-slider'
			),
			'timeout' => array(
		    	'id' 		=> 'timeout',
				'name'		=> __( 'Grid Slider: Speed', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of seconds you\'d like in between trasitions. You may use <em>0</em> to disable the slider from auto advancing.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3',
				'class'		=> 'hide receiver receiver-slider'
			),
			'nav' => array(
				'id' 		=> 'nav',
				'name'		=> null,
				'desc'		=> __( 'Grid Slider: Display slider navigation.', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'checkbox',
				'class'		=> 'hide receiver receiver-slider'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'thumbs' => array(
				'name' 		=> __( 'Featured Images', 'themeblvd_builder' ),
				'desc' 		=> __( 'Choose whether or not you want featured images to show for each post.', 'themeblvd_builder' ),
				'id' 		=> 'thumbs',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' => array(
					'default'	=> __( 'Use default post grid setting', 'themeblvd_builder' ),
					'full'		=> __( 'Show featured images', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide featured images', 'themeblvd_builder' )
				)
			),
			'meta' => array(
				'name' 		=> __( 'Meta Information', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select if you\'d like the meta information (like date posted, author, etc) to show for each post.', 'themeblvd_builder' ),
				'id' 		=> 'meta',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default post grid setting', 'themeblvd_builder' ),
					'show'		=> __( 'Show meta info', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide meta info', 'themeblvd_builder' )
				)
			),
			'excerpt' => array(
				'name' 		=> __( 'Excerpt', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select if you\'d like to show the excerpt or not for each post.', 'themeblvd_builder' ),
				'id' 		=> 'excerpt',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default post grid setting', 'themeblvd_builder' ),
					'show'		=> __( 'Show excerpts', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide excerpts', 'themeblvd_builder' )
				)
			),
			'sub_group_start_3' => array(
				'type' 		=> 'subgroup_start',
				'class'		=> 'show-hide-toggle'
			),
			'more' => array(
				'name' 		=> __( 'Read More', 'themeblvd_builder' ),
				'desc' 		=> __( 'What would you like to show for each post to lead the reader to the full post?', 'themeblvd_builder' ),
				'id' 		=> 'more',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default post grid setting', 'themeblvd_builder' ),
					'text' 		=> __( 'Show text link', 'themeblvd_builder' ),
					'button'	=> __( 'Show button', 'themeblvd_builder' ),
					'none'		=> __( 'Show no button or text link', 'themeblvd_builder' )
				),
				'class'		=> 'trigger'
			),
			'more_text' => array(
				'name' 		=> __( 'Read More Text', 'themeblvd_builder' ),
				'desc' 		=> __( 'Enter the text you\'d like to use to lead the reader to the full post.', 'themeblvd_builder' ),
				'id' 		=> 'more_text',
				'std' 		=> 'Read More',
				'type' 		=> 'text',
				'class'		=> 'hide receiver receiver-text receiver-button'
			),
			'sub_group_end_3' => array(
				'type' 		=> 'subgroup_end'
			),
			'crop' => array(
		    	'id' 		=> 'crop',
				'name'		=> __( 'Featured Image Crop Size', 'themeblvd_builder' ),
				'desc'		=> __( 'Select a custom crop size to be used for the images in the grid. If you select a crop size that doesn\'t have a consistent height, then you may want to use one of the "Masonry" display options above.<br><br><em>Note: Images are scaled proportionally to fit within their current containers.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'select'	=> 'crop',
				'std'		=> 'tb_grid'
			)
		);

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			unset( $this->core_elements['post_grid']['options']['title'] );
			unset( $this->core_elements['post_grid']['options']['pages'] );
			unset( $this->core_elements['post_grid']['options']['subgroup_start_2'] );
			unset( $this->core_elements['post_grid']['options']['display'] );
			unset( $this->core_elements['post_grid']['options']['rows']['class'] );
			unset( $this->core_elements['post_grid']['options']['paginated_hide'] );
			unset( $this->core_elements['post_grid']['options']['filter'] );
			unset( $this->core_elements['post_grid']['options']['filter_max'] );
			unset( $this->core_elements['post_grid']['options']['posts_per_page'] );
			unset( $this->core_elements['post_grid']['options']['slides'] );
			unset( $this->core_elements['post_grid']['options']['timeout'] );
			unset( $this->core_elements['post_grid']['options']['nav'] );
			unset( $this->core_elements['post_grid']['options']['subgroup_end_2'] );
			unset( $this->core_elements['post_grid']['options']['thumbs'] );
			unset( $this->core_elements['post_grid']['options']['meta'] );
			unset( $this->core_elements['post_grid']['options']['excerpt'] );
			unset( $this->core_elements['post_grid']['options']['subgroup_start_3'] );
			unset( $this->core_elements['post_grid']['options']['more'] );
			unset( $this->core_elements['post_grid']['options']['more_text'] );
			unset( $this->core_elements['post_grid']['options']['subgroup_end_3'] );
			unset( $this->core_elements['post_grid']['options']['crop'] );

			$this->core_elements['post_grid']['options']['crop'] = array(
				'id' 		=> 'crop',
				'name'		=> __( 'Custom Image Crop Size (optional)', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a custom image crop size. Always leave this blank unless you know what you\'re doing here. When left blank, the theme will generate this crop size for you depending on the amount of columns in your post grid.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> ''
			);

			$this->core_elements['post_grid']['options']['subgroup_start_4'] = array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide'
			);

			$this->core_elements['post_grid']['options']['link'] = array(
				'id' 		=> 'link',
				'name'		=> __( 'Link', 'themeblvd_builder' ),
				'desc'		=> __( 'Show link after posts to direct visitors somewhere?', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			);

			$this->core_elements['post_grid']['options']['link_text'] = array(
				'id' 		=> 'link_text',
				'name'		=> __( 'Link Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the link.', 'themeblvd_builder' ),
				'std'		=> 'View All Posts',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			);

			$this->core_elements['post_grid']['options']['link_url'] = array(
				'id' 		=> 'link_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the full URL where you want this link to go to.', 'themeblvd_builder' ),
				'std'		=> 'http://www.your-site.com/your-blog-page',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			);

			$this->core_elements['post_grid']['options']['link_target'] = array(
				'id' 		=> 'link_target',
				'name'		=> __( 'Link Target', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you want the link to open.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'	=> array(
			        '_self' 	=> __( 'Same Window', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'New Window', 'themeblvd_builder' )
				)
			);

			$this->core_elements['post_grid']['options']['subgroup_end_4'] = array(
				'type'		=> 'subgroup_end'
			);

		}

		/*--------------------------------------------*/
		/* Post Grid Slider
		/*--------------------------------------------*/

		// With Theme Blvd framework v2.5+, post grid slider
		// can be achieved through the standard "Post Grid" element.
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			$this->core_elements['post_grid_slider'] = array();

			// Information
			$this->core_elements['post_grid_slider']['info'] = array(
				'name'		=> __( 'Post Grid Slider', 'themeblvd_builder' ),
				'id'		=> 'post_grid_slider',
				'hook'		=> 'themeblvd_post_grid_slider',
				'shortcode'	=> '[post_grid_slider]',
				'desc'		=> __( 'Slider of posts in a grid', 'themeblvd_builder' )
			);

			// Support
			$this->core_elements['post_grid_slider']['support'] = array(
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
		}

		/*--------------------------------------------*/
		/* Post List (paginated)
		/*--------------------------------------------*/

		// With Theme Blvd framework v2.5+, paginated post list
		// can be achieved through the standard "Post List" element.
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			$this->core_elements['post_list_paginated'] = array();

			// Information
			$this->core_elements['post_list_paginated']['info'] = array(
				'name' 		=> __( 'Post List (paginated)', 'themeblvd_builder' ),
				'id'		=> 'post_list_paginated',
				'hook'		=> 'themeblvd_post_list_paginated',
				'shortcode'	=> '[post_list]',
				'desc'		=> __( 'Full paginated list of posts', 'themeblvd_builder' )
			);

			// Support
			$this->core_elements['post_list_paginated']['support'] = array(
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
		}

		/*--------------------------------------------*/
		/* Post List
		/*--------------------------------------------*/

		$this->core_elements['post_list'] = array();

		// Information
		$this->core_elements['post_list']['info'] = array(
			'name'		=> __( 'Post List', 'themeblvd_builder' ),
			'id'		=> 'post_list',
			'hook'		=> 'themeblvd_post_list',
			'shortcode'	=> '[post_list]',
			'desc'		=> __( 'Standard post list', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_list']['support'] = array(
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
				'class' 	=> 'hide receiver receiver-category select-categories'
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
				'name'		=> __( 'Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the number of posts you\'d like to show. If your post list is paginated, this will be the number of posts per page, and if not, it will be the total number of posts. You can enter <code>-1</code> if you don\'t want there to be a limit, which can be helpful if you\'re using the "List, with filtering" display option below.', 'themeblvd_builder' ),
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
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>.<br><br><em>Note: Offset will not take effect if you\'re using pagination for this post list.</em>', 'themeblvd_builder' ),
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
		    'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'display' => array(
		    	'id' 		=> 'display',
				'name'		=> __( 'Display', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to display the posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'list',
				'options'	=> array(
					'list' 		=> __( 'List', 'themeblvd_builder' ),
					'paginated' => __( 'List, with pagination', 'themeblvd_builder' ),
					'filter' 	=> __( 'List, with filtering', 'themeblvd_builder' )
					//'ajax' 		=> __( 'List, with Ajax "Load More"', 'themeblvd_builder' ) // ... @TODO future feature
				),
				'class' 	=> 'tb-query-check trigger'
			),
			'paginated_hide' => array(
		    	'id' 		=> 'paginated_hide',
				'name'		=> null,
				'desc'		=> __( 'Hide other elements of the layout after page 1 of the posts.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'hide receiver receiver-paginated'
			),
			'filter' => array(
		    	'id' 		=> 'filter',
				'name'		=> __( 'Filtering', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how the the posts can be filtered by the website visitor.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options' => array(
					'category'	=> __( 'Filtered by category', 'themeblvd_builder' ),
					'post_tag'	=> __( 'Filtered by tag', 'themeblvd_builder' )
				),
				'class'		=> 'hide receiver receiver-filter'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'thumbs' => array(
				'name' 		=> __( 'Featured Images', 'themeblvd_builder' ),
				'desc' 		=> __( 'Choose whether or not you want featured images to show for each post.', 'themeblvd_builder' ),
				'id' 		=> 'thumbs',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' => array(
					'default' 	=> __( 'Use default post list setting', 'themeblvd_builder' ),
					'full'		=> __( 'Show featured images', 'themeblvd_builder' ),
					'date'		=> __( 'Show date block', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide featured images', 'themeblvd_builder' )
				)
			),
			'meta' => array(
				'name' 		=> __( 'Meta Information', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select if you\'d like the meta information (like date posted, author, etc) to show for each post.', 'themeblvd_builder' ),
				'id' 		=> 'meta',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default' 	=> __( 'Use default post list setting', 'themeblvd_builder' ),
					'show'		=> __( 'Show meta info', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide meta info', 'themeblvd_builder' )
				)
			),
			'subgroup_start_3' => array(
				'type' 		=> 'subgroup_start',
				'class'		=> 'show-hide-toggle'
			),
			'more' => array(
				'name' 		=> __( 'Read More', 'themeblvd_builder' ),
				'desc' 		=> __( 'What would you like to show for each post to lead the reader to the full post?', 'themeblvd_builder' ),
				'id' 		=> 'more',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default' 	=> __( 'Use default post list setting', 'themeblvd_builder' ),
					'text' 		=> __( 'Show text link', 'themeblvd_builder' ),
					'button'	=> __( 'Show button', 'themeblvd_builder' ),
					'none'		=> __( 'Show no button or text link', 'themeblvd_builder' )
				),
				'class'		=> 'trigger'
			),
			'more_text' => array(
				'name' 		=> __( 'Read More Text', 'themeblvd_builder' ),
				'desc' 		=> __( 'Enter the text you\'d like to use to lead the reader to the full post.', 'themeblvd_builder' ),
				'id' 		=> 'more_text',
				'std' 		=> 'Read More <i class="fa fa-long-arrow-right"></i>',
				'type' 		=> 'text',
				'class'		=> 'hide receiver receiver-text receiver-button'
			),
			'subgroup_end_3' => array(
				'type' 		=> 'subgroup_end'
			)
		);

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			$legacy = array();

			foreach ( $this->core_elements['post_list']['options'] as $key => $value ) {

				if ( $key == 'posts_per_page' ) {

					$legacy['numberposts'] = array(
				    	'id' 		=> 'numberposts',
						'name'		=> __( 'Number of Posts', 'themeblvd_builder' ),
						'desc'		=> __( 'Enter in the <strong>total number</strong> of posts you\'d like to show. You can enter <em>-1</em> if you\'d like to show all posts from the categories you\'ve selected.', 'themeblvd_builder' ),
						'type'		=> 'text',
						'std'		=> '6',
						'class' 	=> 'hide receiver receiver-category receiver-tag'
					);

					continue;
				}

				if ( $key == 'thumbs') {

					$legacy['thumbs'] = array(
						'id' 		=> 'thumbs',
						'name' 		=> __( 'Featured Images', 'themeblvd_builder' ),
						'desc' 		=> __( 'Select the size of the post list\'s thumbnails or whether you\'d like to hide them all together when posts are listed.', 'themeblvd_builder' ),
						'std' 		=> 'default',
						'type' 		=> 'select',
						'options' 	=> array(
							'default'	=> __( 'Use default primary posts display setting', 'themeblvd_builder' ),
							'small'		=> __( 'Show small thumbnails', 'themeblvd_builder' ),
							'full' 		=> __( 'Show full-width thumbnails', 'themeblvd_builder' ),
							'hide' 		=> __( 'Hide thumbnails', 'themeblvd_builder' )
						)
					);

					$legacy['content'] = array(
						'id' 		=> 'content',
						'name' 		=> __( 'Show excerpts of full content?', 'themeblvd_builder' ),
						'desc' 		=> __( 'Choose whether you want to show full content or post excerpts only.', 'themeblvd_builder' ),
						'std' 		=> 'default',
						'type' 		=> 'select',
						'options' 	=> array(
							'default'	=> __( 'Use default primary posts display setting.', 'themeblvd_builder' ),
							'content'	=> __( 'Show full content.', 'themeblvd_builder' ),
							'excerpt' 	=> __( 'Show excerpt only.', 'themeblvd_builder' )
						)
					);

					continue;
				}

				if ( in_array( $key, array('title', 'display', 'meta', 'subgroup_start_3', 'more', 'more_text', 'subgroup_end_3', 'paginated_hide', 'filter') ) ) {
					continue;
				}

				$legacy[$key] = $value;

			}

			$legacy['subgroup_start_3'] = array(
				'type'		=> 'subgroup_start',
				'class'		=> 'show-hide'
			);

			$legacy['link'] = array(
				'id' 		=> 'link',
				'name'		=> __( 'Link', 'themeblvd_builder' ),
				'desc'		=> __( 'Show link after posts to direct visitors somewhere?', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			);

			$legacy['link_text'] = array(
				'id' 		=> 'link_text',
				'name'		=> __( 'Link Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the link.', 'themeblvd_builder' ),
				'std'		=> 'View All Posts',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			);

			$legacy['link_url'] = array(
				'id' 		=> 'link_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the full URL where you want this link to go to.', 'themeblvd_builder' ),
				'std'		=> 'http://www.your-site.com/your-blog-page',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			);

			$legacy['link_target'] = array(
				'id' 		=> 'link_target',
				'name'		=> __( 'Link Target', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you want the link to open.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'class'		=> 'hide receiver',
				'options'	=> array(
			        '_self' 	=> __( 'Same Window', 'themeblvd_builder' ),
			        '_blank' 	=> __( 'New Window', 'themeblvd_builder' )
				)
			);

			$legacy['subgroup_end_3'] = array(
				'type'		=> 'subgroup_end'
			);

			$this->core_elements['post_list']['options'] = $legacy;

		}

		/*--------------------------------------------*/
		/* Post List Slider
		/*--------------------------------------------*/

		// With Theme Blvd framework v2.5+, post list slider is no more.
		// Use "Post Slider" instead.
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

			$this->core_elements['post_list_slider'] = array();

			// Information
			$this->core_elements['post_list_slider']['info'] = array(
				'name'		=> __( 'Post List Slider', 'themeblvd_builder' ),
				'id'		=> 'post_list_slider',
				'hook'		=> 'themeblvd_post_list_slider',
				'shortcode'	=> '[post_list_slider]',
				'desc'		=> __( 'Slider of posts listed out', 'themeblvd_builder' )
			);

			// Support
			$this->core_elements['post_list_slider']['support'] = array(
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
		}

		/*--------------------------------------------*/
		/* Post Showcase
		/*--------------------------------------------*/

		$this->core_elements['post_showcase'] = array();

		// Information
		$this->core_elements['post_showcase']['info'] = array(
			'name'		=> __( 'Post Showcase', 'themeblvd_builder' ),
			'id'		=> 'post_showcase',
			'hook'		=> 'themeblvd_post_showcase',
			'shortcode'	=> '[post_showcase]',
			'desc'		=> __( 'Showcase of posts', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_showcase']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_showcase']['options'] = array(
			'subgroup_start_1' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'title' => array(
				'id' 		=> 'title',
				'name'		=> __('Title (optional)', 'themeblvd_builder'),
				'desc'		=> __('If you want, you can give this set of posts a title.', 'themeblvd_builder'),
				'std'		=> '',
				'type'		=> 'text'
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
			        'pages' 	=> __( 'Pages', 'themeblvd_builder' ),
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
				'class' 	=> 'hide receiver receiver-category select-categories'
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
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>.<br><br><em>Note: Offset will not take effect if you\'re using pagination for this post showcase.</em>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '0',
				'class' 	=> 'hide receiver receiver-category receiver-tag'
			),
			'pages' => array(
		    	'id' 		=> 'pages',
				'name'		=> __( 'Pages', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter a comma-separated list of page slugs.<br>Ex: page-1, page-2, page-3', 'themeblvd_builder' ),
				'type'		=> 'text',
				'class' 	=> 'hide receiver receiver-pages'
			),
			'query' => array(
		    	'id' 		=> 'query',
				'name'		=> __( 'Custom Query String', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in a <a href="http://codex.wordpress.org/Class_Reference/WP_Query#Parameters">custom query string</a>. This will override any other query-related options.<br><br>Ex: tag=cooking<br>Ex: post_type=XYZ<br><br><em>Note: You cannot set the number of posts because this is generated in a showcase based on the rows and columns, except when using masonry.</em>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '',
				'class' 	=> 'hide receiver receiver-query'
			),
			'subgroup_end_1' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    'display' => array(
		    	'id' 		=> 'display',
				'name'		=> __( 'Display', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how you\'d like to display the posts.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'showcase',
				'options'	=> array(
					'showcase' 			=> __( 'Showcase', 'themeblvd_builder' ),
					'paginated' 		=> __( 'Showcase, with pagination', 'themeblvd_builder' ),
					'filter' 			=> __( 'Showcase, with filtering', 'themeblvd_builder' ),
					'masonry' 			=> __( 'Showcase Masonry', 'themeblvd_builder' ),
					'masonry_paginated' => __( 'Showcase Masonry, with pagination', 'themeblvd_builder' ),
					'masonry_filter' 	=> __( 'Showcase Masonry, with filtering', 'themeblvd_builder' )
					//'ajax' 			=> __( 'Grid, with Ajax "Load More"', 'themeblvd_builder' ), // ... @TODO future feature
				),
				'class' 	=> 'trigger tb-query-check'
			),
			'columns' => array(
		    	'id' 		=> 'columns',
				'name'		=> __( 'Columns', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how many posts per row (or slide) you\'d like displayed.', 'themeblvd_builder' ),
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
				'name'		=> __( 'Maximum Number of Rows', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the maximum number of rows you\'d like to show. The number you enter here will be multiplied by the amount of columns you selected in the previous option to figure out how many posts should be showed. You can leave this option blank if you\'d like to show all posts from your configured query.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3',
				'class'		=> 'hide receiver receiver-showcase receiver-paginated'
			),
			'paginated_hide' => array(
		    	'id' 		=> 'paginated_hide',
				'name'		=> null,
				'desc'		=> __( 'Hide other elements of the layout after first page of posts.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'hide receiver receiver-paginated'
			),
			'filter' => array(
		    	'id' 		=> 'filter',
				'name'		=> __( 'Filtering: Filter by', 'themeblvd_builder' ),
				'desc'		=> __( 'Select how the the posts can be filtered by the website visitor.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'category',
				'options' => array(
					'category'	=> __( 'Filtered by category', 'themeblvd_builder' ),
					'post_tag'	=> __( 'Filtered by tag', 'themeblvd_builder' )
				),
				'class'		=> 'hide receiver receiver-filter receiver-masonry_filter'
			),
			'filter_max' => array(
		    	'id' 		=> 'filter_max',
				'name'		=> __( 'Filtering: Max Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'By using <code>-1</code>, it means all posts for the queried criteria will be pulled, and this works great for filtering. However, performance issues can arrise if you have a large volume of posts you\'re pulling from. If this is an issue, you can set a maximum here. Ex: <code>50</code>', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '-1',
				'class'		=> 'hide receiver receiver-filter receiver-masonry_filter'
			),
			'posts_per_page' => array(
		    	'id' 		=> 'posts_per_page',
				'name'		=> __( 'Masonry: Number of posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the maximum number of posts, or posts per page, if using pagination.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '12',
				'class'		=> 'hide receiver receiver-masonry receiver-masonry_paginated'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'titles' => array(
				'name' 		=> __( 'Titles', 'themeblvd' ),
				'desc' 		=> __( 'Select if you\'d like to show the title or not for each post.', 'themeblvd' ),
				'id' 		=> 'titles',
				'std' 		=> 'show',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default post showcase setting', 'themeblvd_builder' ),
					'show'		=> __( 'Show titles', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide titles', 'themeblvd_builder' )
				)
			),
			'excerpt' => array(
				'name' 		=> __( 'Excerpt', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select if you\'d like to show the excerpt or not for each post.', 'themeblvd_builder' ),
				'id' 		=> 'excerpt',
				'std' 		=> 'default',
				'type' 		=> 'select',
				'options' 	=> array(
					'default'	=> __( 'Use default post showcase setting', 'themeblvd_builder' ),
					'show'		=> __( 'Show excerpts', 'themeblvd_builder' ),
					'hide' 		=> __( 'Hide excerpts', 'themeblvd_builder' )
				)
			),
			'crop' => array(
		    	'id' 		=> 'crop',
				'name'		=> __( 'Featured Image Crop Size', 'themeblvd_builder' ),
				'desc'		=> __( 'Select a custom crop size to be used for the images in the showcase. If you select a crop size that doesn\'t have a consistent height, then you may want to use one of the "Masonry" display options above.<br><br><em>Note: Images are scaled proportionally to fit within their current containers.</em>', 'themeblvd_builder' ),
				'type'		=> 'select',
				'select'	=> 'crop',
				'std'		=> 'tb_large'
			)
		);

		/*--------------------------------------------*/
		/* Post Slider
		/*--------------------------------------------*/

		$this->core_elements['post_slider'] = array();

		// Information
		$this->core_elements['post_slider']['info'] = array(
			'name'		=> __( 'Post Slider', 'themeblvd_builder' ),
			'id'		=> 'post_slider',
			'hook'		=> 'themeblvd_post_slider',
			'shortcode'	=> '[post_slider]',
			'desc'		=> __( 'Bootstrap carousel slider generated from group of posts', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_slider']['support'] = array(
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_slider']['options'] = array(
			 'style' => array(
				'name' 		=> __( 'Display Style', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select one of the preset style for how the post slider displays. When referring to "included elements" it\'s referring to post titles, meta, excerpts, and buttons configured in the following options.', 'themeblvd_builder' ),
				'id' 		=> 'style',
				'std' 		=> 'style-1',
				'type' 		=> 'radio',
				'options'	=> apply_filters('themeblvd_post_slider_styles', array(
					'style-1'	=> __('<strong>Style #1:</strong> Display included elements open and center on each slide.', 'themeblvd_builder'),
					'style-2'	=> __('<strong>Style #2:</strong> Display included elements in a shaded content area positioned to the side of each slide.', 'themeblvd_builder'),
					'style-3'	=> __('<strong>Style #3:</strong> An open, more magazine-style post slider.', 'themeblvd_builder')
				))
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
				'class' 	=> 'hide receiver receiver-category select-categories'
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
				'name'		=> __( 'Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the maximum number of posts you\'d like to show. If your post list is paginated, this will be the number of posts per page, and if not, it will be the total number of posts. You can enter <em>-1</em> if you don\'t want there to be a limit.', 'themeblvd_builder' ),
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
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>.', 'themeblvd_builder' ),
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
		    'crop' => array(
				'name' 		=> __( 'Image Crop Size', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the crop size to be used for the images. Remember that the slider will be scaled proportionally to fit within its container.', 'themeblvd_builder' ),
				'id' 		=> 'crop',
				'std' 		=> 'slider-large',
				'type' 		=> 'select',
				'select'	=> 'crop'
			),
			'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'slide_link' => array(
				'name' 		=> __( 'Link Handling', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select how the user interacts with each slide and where they\'re directed to.', 'themeblvd_builder' ),
				'id' 		=> 'slide_link',
				'std' 		=> 'button',
				'type' 		=> 'select',
				'options'	=> array(
					'none'			=> __('No linking', 'themeblvd_builder'),
					'image_post'	=> __('Images link to posts', 'themeblvd_builder'),
					'image_link'	=> __('Images link to each post\'s featured image link setting', 'themeblvd_builder'),
					'button'		=> __('Slides have buttons linking to posts', 'themeblvd_builder'),
				),
				'class' => 'trigger'
			),
			'subgroup_start_3' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'hide receiver receiver-button show-hide-toggle'
		    ),
			'button_color' => array(
				'id' 		=> 'button_color',
				'name'		=> __( 'Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what color you\'d like to use for this button.', 'themeblvd_builder' ),
				'std'		=> 'custom',
				'type'		=> 'select',
				'class'		=> 'trigger',
				'options'	=> themeblvd_colors()
			),
			'button_custom' => array(
				'id' 		=> 'button_custom',
				'name'		=> __( 'Custom Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Configure a custom style for the button.', 'themeblvd_builder' ),
				'std'		=> array(
					'bg' 				=> '',
					'bg_hover'			=> '#ffffff',
					'border' 			=> '#ffffff',
					'text'				=> '#ffffff',
					'text_hover'		=> '#333333',
					'include_bg'		=> 0,
					'include_border'	=> 1
				),
				'type'		=> 'button',
				'class'		=> 'hide receiver receiver-custom'
			),
			'subgroup_end_3' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'button_text' => array(
				'id' 		=> 'button_text',
				'name'		=> __( 'Button Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the button.', 'themeblvd_builder' ),
				'std'		=> 'View Post',
				'type'		=> 'text',
				'class'		=> 'hide receiver receiver-button'
			),
			'button_size' => array(
				'id' 		=> 'button_size',
				'name'		=> __( 'Button Size', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the size you\'d like used for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'default',
				'options'	=> array(
					'mini' 		=> __( 'Mini', 'themeblvd_builder' ),
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'default' 	=> __( 'Normal', 'themeblvd_builder' ),
					'large' 	=> __( 'Large', 'themeblvd_builder' ),
					'x-large' 	=> __( 'Extra Large', 'themeblvd_builder' )
				),
				'class'		=> 'hide receiver receiver-button'
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
			'dark_text'	=> array(
				'id'		=> 'dark_text',
				'desc'		=> __( 'Use dark navigation elements and dark text for any titles and descriptions.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			),
			'thumb_link' => array(
				'id'		=> 'thumb_link',
				'desc'		=> __( 'Apply hover effect to linked images.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox',
				'class'		=> 'hide receiver receiver-image_post receiver-image_link'
			),
			'title'	=> array(
				'id'		=> 'title',
				'desc'		=> __( 'Display title for each post.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'meta'	=> array(
				'id'		=> 'meta',
				'desc'		=> __( 'Display meta info for each post.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'excerpts'	=> array(
				'id'		=> 'excerpts',
				'desc'		=> __( 'Display excerpt for each post.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		// For themes with framework prior to 2.5, we use a different set of options
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

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

			// If using theme with framework prior to 2.5, we need
			// the Sliders plugin for this element.
			if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) && ! defined( 'TB_SLIDERS_PLUGIN_VERSION' ) ) {
				unset( $this->core_elements['post_slider'] );
			}
		}

		/*--------------------------------------------*/
		/* Post Slider (Full Width)
		/*--------------------------------------------*/

		$this->core_elements['post_slider_popout'] = array();

		// Information
		$this->core_elements['post_slider_popout']['info'] = array(
			'name'		=> __( 'Post Slider (Full Width)', 'themeblvd_builder' ),
			'id'		=> 'post_slider_popout',
			'hook'		=> 'themeblvd_post_slider_popout',
			'shortcode'	=> '[post_slider]',
			'desc'		=> __( 'Bootstrap carousel slider generated from group of posts', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['post_slider_popout']['support'] = array(
			'popout'		=> 'force',
			'padding'		=> true
		);

		// Options
		$this->core_elements['post_slider_popout']['options'] = array(
			'style' => array(
				'name' 		=> __( 'Display Style', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select one of the preset style for how the post slider displays. When referring to "included elements" it\'s referring to post titles, meta, excerpts, and buttons configured in the following options.', 'themeblvd_builder' ),
				'id' 		=> 'style',
				'std' 		=> 'style-1',
				'type' 		=> 'radio',
				'options'	=> apply_filters('themeblvd_post_slider_styles', array(
					'style-1'	=> __('<strong>Style #1:</strong> Display included elements open and center on each slide.', 'themeblvd_builder'),
					'style-2'	=> __('<strong>Style #2:</strong> Display included elements in a shaded content area positioned to the side of each slide.', 'themeblvd_builder'),
					'style-3'	=> __('<strong>Style #3:</strong> An open, more magazine-style post slider.', 'themeblvd_builder')
				))
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
				'class' 	=> 'hide receiver receiver-category select-categories'
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
				'name'		=> __( 'Number of Posts', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter in the maximum number of posts you\'d like to show. If your post list is paginated, this will be the number of posts per page, and if not, it will be the total number of posts. You can enter <em>-1</em> if you don\'t want there to be a limit.', 'themeblvd_builder' ),
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
				'desc'		=> __( 'Enter the number of posts you\'d like to offset the query by. In most cases, you will just leave this at <em>0</em>.', 'themeblvd_builder' ),
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
		    'crop' => array(
				'name' 		=> __( 'Image Crop Size', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select the crop size to be used for the images. Remember that the slider will be scaled proportionally to fit within its container.', 'themeblvd_builder' ),
				'id' 		=> 'crop',
				'std' 		=> 'full',
				'type' 		=> 'select',
				'select'	=> 'crop'
			),
			'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'slide_link' => array(
				'name' 		=> __( 'Link Handling', 'themeblvd_builder' ),
				'desc' 		=> __( 'Select how the user ineracts with each slide and where they\'re directed to.', 'themeblvd_builder' ),
				'id' 		=> 'slide_link',
				'std' 		=> 'button',
				'type' 		=> 'select',
				'options'	=> array(
					'none'			=> __('No linking', 'themeblvd_builder'),
					'image_post'	=> __('Images link to posts', 'themeblvd_builder'),
					'image_link'	=> __('Images link to each post\'s featured image link setting', 'themeblvd_builder'),
					'button'		=> __('Slides have buttons linking to posts', 'themeblvd_builder'),
				),
				'class' => 'trigger'
			),
			'subgroup_start_3' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'hide receiver receiver-button show-hide-toggle'
		    ),
			'button_color' => array(
				'id' 		=> 'button_color',
				'name'		=> __( 'Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Select what color you\'d like to use for this button.', 'themeblvd_builder' ),
				'std'		=> 'custom',
				'type'		=> 'select',
				'class'		=> 'trigger',
				'options'	=> themeblvd_colors()
			),
			'button_custom' => array(
				'id' 		=> 'button_custom',
				'name'		=> __( 'Custom Button Color', 'themeblvd_builder' ),
				'desc'		=> __( 'Configure a custom style for the button.', 'themeblvd_builder' ),
				'std'		=> array(
					'bg' 				=> '',
					'bg_hover'			=> '#ffffff',
					'border' 			=> '#ffffff',
					'text'				=> '#ffffff',
					'text_hover'		=> '#333333',
					'include_bg'		=> 0,
					'include_border'	=> 1
				),
				'type'		=> 'button',
				'class'		=> 'hide receiver receiver-custom'
			),
			'subgroup_end_3' => array(
		    	'type'		=> 'subgroup_end'
		    ),
			'button_text' => array(
				'id' 		=> 'button_text',
				'name'		=> __( 'Button Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the button.', 'themeblvd_builder' ),
				'std'		=> 'View Post',
				'type'		=> 'text',
				'class'		=> 'hide receiver receiver-button'
			),
			'button_size' => array(
				'id' 		=> 'button_size',
				'name'		=> __( 'Button Size', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the size you\'d like used for this button.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'default',
				'options'	=> array(
					'mini' 		=> __( 'Mini', 'themeblvd_builder' ),
					'small' 	=> __( 'Small', 'themeblvd_builder' ),
					'default' 	=> __( 'Normal', 'themeblvd_builder' ),
					'large' 	=> __( 'Large', 'themeblvd_builder' ),
					'x-large' 	=> __( 'Extra Large', 'themeblvd_builder' )
				),
				'class'		=> 'hide receiver receiver-button'
			),
		    'interval' => array(
				'id'		=> 'interval',
				'name' 		=> __( 'Speed', 'themeblvd_builder' ),
				'desc' 		=> __( 'Seconds in between slider transitions. You can use 0 for the slider to not auto rotate.', 'themeblvd_builder' ),
				'std'		=> '5',
				'type'		=> 'text'
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
			'dark_text'	=> array(
				'id'		=> 'dark_text',
				'desc'		=> __( 'Use dark navigation elements and dark text for any titles and descriptions.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			),
			'link' => array(
				'id'		=> 'thumb_link',
				'desc'		=> __( 'Apply hover effect to linked images.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox',
				'class'		=> 'hide receiver receiver-image_post receiver-image_link'
			),
			'title'	=> array(
				'id'		=> 'title',
				'desc'		=> __( 'Display title for each post.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'meta'	=> array(
				'id'		=> 'meta',
				'desc'		=> __( 'Display meta info for each post.', 'themeblvd_builder'),
				'std'		=> true,
				'type'		=> 'checkbox'
			),
			'excerpts'	=> array(
				'id'		=> 'excerpts',
				'desc'		=> __( 'Display excerpt for each post.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox'
			),
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'subgroup_start_4' => array(
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
			'subgroup_end_4' => array(
				'type'		=> 'subgroup_end'
			)
		);

		/*--------------------------------------------*/
		/* Pricing Table
		/*--------------------------------------------*/

		$this->core_elements['pricing_table'] = array();

		// Information
		$this->core_elements['pricing_table']['info'] = array(
			'name' 		=> __('Pricing Table', 'themeblvd_builder'),
			'id'		=> 'pricing_table',
			'hook'		=> 'themeblvd_pricing_table',
			'shortcode'	=> '[pricing_table]',
			'desc' 		=> __( 'A boostrap styled pricing_table.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['pricing_table']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['pricing_table']['options'] = array(
			'columns' => array(
		    	'id' 		=> 'columns',
				'name'		=> null,
				'desc'		=> null,
				'type'		=> 'price_cols'
			),
			'currency' => array(
				'name' 		=> __( 'Currency Symbol', 'themeblvd_shortcodes' ),
				'desc' 		=> __( 'Enter a currency symbol to be used with the prices in each column.', 'themeblvd_builder' ),
				'id' 		=> 'currency',
				'std' 		=> '$',
				'type' 		=> 'text'
			),
			'currency_placement' => array(
				'name' 		=> __( 'Currency Symbol Placement', 'themeblvd_shortcodes' ),
				'desc' 		=> __( 'Select if you want the currency symbol to come before or after the prices in each column.', 'themeblvd_builder' ),
				'id' 		=> 'currency_placement',
				'std' 		=> '$',
				'type' 		=> 'select',
				'options'	=> array(
					'before' 	=> __('Before price', 'themeblvd_builder'),
					'after' 	=> __('After price', 'themeblvd_builder')
				)
			)
		);

		/*--------------------------------------------*/
		/* Progress Bars
		/*--------------------------------------------*/

		$this->core_elements['progress_bars'] = array();

		// Information
		$this->core_elements['progress_bars']['info'] = array(
			'name' 		=> __( 'Progress Bars', 'themeblvd_builder' ),
			'id'		=> 'progress_bars',
			'hook'		=> 'themeblvd_progress_bars',
			'shortcode'	=> false,
			'desc'		=> __( 'A set of horizontal progress bars.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['progress_bars']['support'] = array(
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['progress_bars']['options'] = array(
			'bars' => array(
				'id' 		=> 'bars',
				'name'		=> __( 'Progress Bars', 'themeblvd_builder' ),
				'desc'		=> null,
				'type'		=> 'bars'
			),
			'striped' => array(
				'id' 		=> 'striped',
				'name'		=> null,
				'desc'		=> __( 'Display striped effect over progress bars.', 'themeblvd_builder' ),
				'type'		=> 'checkbox'
			)
		);

		/*--------------------------------------------*/
		/* Promo Box (slogan)
		/*--------------------------------------------*/

		$this->core_elements['slogan'] = array();

		// Information
		$this->core_elements['slogan']['info'] = array(
			'name'		=> __( 'Promo Box', 'themeblvd_builder' ),
			'id'		=> 'slogan',
			'hook'		=> 'themeblvd_slogan',
			'shortcode'	=> '[slogan]',
			'desc'		=> __( 'Slogan with optional button', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['slogan']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['slogan']['options'] = array(
			'headline' => array(
				'id' 		=> 'headline',
				'name' 		=> __( 'Promo Headline', 'themeblvd_builder'),
				'desc'		=> __( 'Enter any text you\'d like to be displayed with large text.', 'themeblvd_builder'),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
		    ),
			'desc' => array(
				'id' 		=> 'desc',
				'name' 		=> __( 'Promo Description', 'themeblvd_builder'),
				'desc'		=> __( 'If you\'d like smaller text below the headline, enter it here.', 'themeblvd_builder'),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
		    ),
		    'wpautop' => array(
				'id' 		=> 'wpautop',
				'name'		=> __( 'Content Formatting', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply WordPress automatic formatting to content.', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'std'		=> '1'
			),
		    'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'style'	=> array(
				'id' 		=> 'style',
				'name' 		=> __( 'Styling', 'themeblvd_builder'),
				'desc'		=> __( 'Select if you\'d like to apply any special styling for this block.', 'themeblvd_builder'),
				'std'		=> 'none',
				'type'		=> 'select',
				'options'	=> apply_filters('themeblvd_promo_classes', array(
					'none'		=> __('None', 'themeblvd_builder'),
					'custom'	=> __('Custom BG color + Text color', 'themeblvd_builder')
				)),
				'class'		=> 'trigger'
			),
		    'bg_color' => array(
				'id' 		=> 'bg_color',
				'name' 		=> __( 'Background Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select a background color for the promo box.', 'themeblvd_builder'),
				'std'		=> '#eeeeee',
				'type'		=> 'color',
				'class'		=> 'hide receiver receiver-custom'
		    ),
		    'bg_opacity' => array(
				'id'		=> 'bg_opacity',
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
				'class'		=> 'hide receiver receiver-custom'
			),
		    'text_color' => array(
				'id' 		=> 'text_color',
				'name' 		=> __( 'Text Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select a text color for the promo box.', 'themeblvd_builder'),
				'std'		=> '#444444',
				'type'		=> 'color',
				'class'		=> 'hide receiver receiver-custom'
		    ),
			'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'subgroup_start_2' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide'
		    ),
			'button' => array(
		    	'id' 		=> 'button',
				'name'		=> __( 'Button', 'themeblvd_builder' ),
				'desc'		=> __( 'Show call-to-action button next to promo content?', 'themeblvd_builder' ),
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'subgroup_start_3' => array(
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
				'class'		=> 'hide receiver receiver-custom'
			),
			'subgroup_end_3' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		    'button_text' => array(
				'id' 		=> 'button_text',
				'name'		=> __( 'Button Text', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the text for the button.', 'themeblvd_builder' ),
				'std'		=> 'Purchase Now',
				'type'		=> 'text',
				'class'		=> 'hide receiver'
			),
			'button_url' => array(
				'id' 		=> 'button_url',
				'name'		=> __( 'Link URL', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the full URL where you want the button\'s link to go.', 'themeblvd_builder' ),
				'std'		=> 'http://www.your-site.com/your-landing-page',
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
			'button_placement' => array(
				'id' 		=> 'button_placement',
				'name'		=> __( 'Button Placement', 'themeblvd_builder' ),
				'desc'		=> __( 'Select where you\'d like the button placed.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'right',
				'class'		=> 'hide receiver',
				'options'	=> array(
					'right' 	=> __( 'Floated Right', 'themeblvd_builder' ),
					'left' 		=> __( 'Floated Left', 'themeblvd_builder' ),
					'below' 	=> __( 'Below Headline and Description', 'themeblvd_builder' )
				)
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
			'subgroup_end_2' => array(
		    	'type'		=> 'subgroup_end'
		    )
		);

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			unset( $this->core_elements['slogan']['options']['button_icon_before'] );
			unset( $this->core_elements['slogan']['options']['button_icon_after'] );
		}

		/*--------------------------------------------*/
		/* Quote
		/*--------------------------------------------*/

		$this->core_elements['quote'] = array();

		// Information
		$this->core_elements['quote']['info'] = array(
			'name' 		=> __( 'Quote', 'themeblvd_builder' ),
			'id'		=> 'quote',
			'shortcode'	=> '[blockquote]',
			'desc'		=> __( 'Standard HTML blockquote', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['quote']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['quote']['options'] = array(
			'quote' => array(
				'name' 		=> __( 'Quote Text', 'themeblvd_shortcodes' ),
				'desc' 		=> __( 'The main text of the quote. You cannot use HTML here.', 'themeblvd_builder' ),
				'id' 		=> 'quote',
				'std' 		=> 'Quote text...',
				'type' 		=> 'textarea'
			),
			'source' => array(
				'name' 		=> __( 'Quote Source (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'Optional source for the quote.<br />Ex: John Smith', 'themeblvd_builder' ),
				'id' 		=> 'source',
				'std' 		=> '',
				'type' 		=> 'text'
			),
			'source_link' => array(
				'name' 		=> __( 'Quote Source URL (optional)', 'themeblvd_builder' ),
				'desc' 		=> __( 'Optional website URL to the source you entered in the previous option.<br />Ex: http://google.com', 'themeblvd_builder' ),
				'id' 		=> 'source_link',
				'std' 		=> '',
				'type' 		=> 'text'
			),
			'reverse' => array(
				'name' 		=> __( 'Reverse Orientation', 'themeblvd_shortcodes' ),
				'desc' 		=> __( 'If you choose to reverse the orientation, the text and inner parts of the blockquote will be aligned to the right.', 'themeblvd_builder' ),
				'id' 		=> 'reverse',
				'std' 		=> 'false',
				'type' 		=> 'select',
				'options' 	=> array(
					'false' 	=> __('False', 'themeblvd_builder'),
					'true' 		=> __('True', 'themeblvd_builder')
				)
			)
		);

		/*--------------------------------------------*/
		/* Simple Slider
		/*--------------------------------------------*/

		$this->core_elements['simple_slider'] = array();

		// Information
		$this->core_elements['simple_slider']['info'] = array(
			'name'		=> __( 'Simple Slider', 'themeblvd_builder' ),
			'id'		=> 'simple_slider',
			'hook'		=> 'themeblvd_simple_slider',
			'shortcode'	=> false,
			'desc'		=> __( 'Simple slider, constructed within the Layout Builder.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['simple_slider']['support'] = array(
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
				'desc' 		=> __( 'Select the crop size to be used for the images. Remember that the slider will be scaled proportionally to fit within its container.', 'themeblvd_builder' ),
				'id' 		=> 'crop',
				'std' 		=> 'slider-large',
				'type' 		=> 'select',
				'select'	=> 'crop'
				// 'class'		=> 'match-trigger' // Will send the value of this to hidden crop sizes with class "match" within each slide
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
			'caption_bg'	=> array(
				'id'		=> 'caption_bg',
				'desc'		=> __( 'Shade caption with background color.', 'themeblvd_builder'),
				'std'		=> false,
				'type'		=> 'checkbox',
				'class'		=> 'trigger'
			),
			'caption_bg_color' => array(
				'id'		=> 'caption_bg_color',
				'name'		=> __( 'Caption Background Color', 'themeblvd_builder'),
				'desc'		=> __( 'Select the background color to show behind the text of the captions of the slider.', 'themeblvd_builder'),
				'std'		=> '#000000',
				'type'		=> 'color',
				'class'		=> 'hide receiver'
			),
			'caption_bg_opacity' => array(
				'id'		=> 'caption_bg_opacity',
				'name'		=> __( 'Caption Background Color Opacity', 'themeblvd_builder'),
				'desc'		=> __( 'And for that background color you\'ve selected, set the opacity of how that shows through to the images of the slider.', 'themeblvd_builder'),
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
					'0.9'	=> '0.9',
					'1'		=> '1.0'
				),
				'class'		=> 'hide receiver'
			),
			'subgroup_end_2' => array(
				'type'		=> 'subgroup_end'
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
			'hook'		=> 'themeblvd_simple_slider_popout',
			'shortcode'	=> false,
			'desc'		=> __( 'Simple slider, constructed within the Layout Builder.', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['simple_slider_popout']['support'] = array(
			'popout'		=> 'force', // Will be checked, and not allow user to uncheck
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
				'desc' 		=> __( 'Select the crop size to be used for the images. Remember that the slider will be scaled proportionally to fit within its container.', 'themeblvd_builder' ),
				'id' 		=> 'crop',
				'std' 		=> 'slider-x-large',
				'type' 		=> 'select',
				'select'	=> 'crop'
				// 'class'		=> 'match-trigger' // Will send the value of this to hidden crop sizes with class "match" within each slide
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
			)
		);

		/*--------------------------------------------*/
		/* Slider
		/*--------------------------------------------*/

		if ( defined( 'TB_SLIDERS_PLUGIN_VERSION' ) ) {

			$this->core_elements['slider'] = array();

			// Information
			$this->core_elements['slider']['info'] = array(
				'name'		=> __( 'Slider (Custom)', 'themeblvd_builder' ),
				'id'		=> 'slider',
				'hook'		=> 'themeblvd_slider',
				'shortcode'	=> '[slider]',
				'desc'		=> __( 'User-built slideshow', 'themeblvd_builder' )
			);

			// Support
			$this->core_elements['slider']['support'] = array(
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
					'select'	=> 'sliders'
				)
			);

			if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '<') ) {
				$this->core_elements['slider']['options']['slider_id']['options'] = themeblvd_get_select('sliders');
			}

		}

		/*--------------------------------------------*/
		/* Tabs
		/*--------------------------------------------*/

		$this->core_elements['tabs'] = array();

		// Information
		$this->core_elements['tabs']['info'] = array(
			'name' 		=> __( 'Tabs', 'themeblvd_builder' ),
			'id'		=> 'tabs',
			'hook'		=> 'themeblvd_tabs',
			'shortcode'	=> '[tabs]',
			'desc'		=> __( 'Tabbed content display', 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['tabs']['support'] = array(
			'popout'		=> false,
			'padding'		=> true
		);

		// Options
		$this->core_elements['tabs']['options'] = array(
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
				'desc'		=> __( 'Select the style of the tabs.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'framed',
				'options'	=> apply_filters('themeblvd_tab_styles', array(
			        'open' 		=> __( 'Open Style', 'themeblvd_builder' ),
			        'framed' 	=> __( 'Framed Style', 'themeblvd_builder' )
				))
			),
			'height' => array(
				'id' 		=> 'height',
				'name'		=> __( 'Fixed Height', 'themeblvd_builder' ),
				'desc'		=> __( 'Apply automatic fixed height across all tabs.<br><br>This just takes the height of the tallest tab\'s content and applies that across all tabs. This can help with "page jumping" in the case that not all tabs have equal amount of content. It can also help in the case when you\'re getting unwanted scrollbars on the inner content areas of tabs.', 'themeblvd_builder' ),
				'std'		=> 0,
				'type'		=> 'checkbox'
			)
		);

		// Deprecated options set for older themes
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

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
		/* Team Member
		/*--------------------------------------------*/

		$this->core_elements['team_member'] = array();

		// Information
		$this->core_elements['team_member']['info'] = array(
			'name' 		=> __( 'Team Member', 'themeblvd_builder' ),
			'id'		=> 'team_member',
			'hook'		=> 'themeblvd_team_member',
			'shortcode'	=> false,
			'desc'		=> __( 'A display to represent a person, including name, description, and contact info.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['team_member']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['team_member']['options'] = array(
			'image' => array(
				'id' 		=> 'image',
				'name' 		=> __( 'Image', 'themeblvd_builder'),
				'desc'		=> __( 'Select an image for this person.', 'themeblvd_builder'),
				'type'		=> 'upload',
				'advanced'	=> true
			),
			'name' => array(
				'id' 		=> 'name',
				'name' 		=> __( 'Name', 'themeblvd_builder'),
				'desc'		=> __( 'Enter the name of this person.', 'themeblvd_builder'),
				'type'		=> 'text'
		    ),
		    'tagline' => array(
				'id' 		=> 'tagline',
				'name' 		=> __( 'Tagline', 'themeblvd_builder'),
				'desc'		=> __( 'Enter a brief tagline for this person.<br>Ex: Founder and CEO', 'themeblvd_builder'),
				'type'		=> 'text'
		    ),
			'icons' => array(
				'id' 		=> 'icons',
				'name'		=> __( 'Social Media Icons', 'themeblvd_builder' ),
				'desc'		=> __( 'Configure the social media and contact icons for this person.', 'themeblvd_builder' ),
				'type'		=> 'social_media'
			),
			'icons_style' => array(
				'id' 		=> 'icons_style',
				'name'		=> __( 'Social Media Icon Style', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the style of social media icons.', 'themeblvd_builder' ),
				'std'		=> 'grey',
				'type'		=> 'select',
				'options'	=> array(
					'flat'	=> __('Flat Color', 'themeblvd_builder'),
					'grey'	=> __('Flat Grey', 'themeblvd_builder'),
					'dark'	=> __('Flat Dark', 'themeblvd_builder'),
					'light'	=> __('Flat Light', 'themeblvd_builder'),
					'color'	=> __('Color', 'themeblvd_builder')
				)
			),
			'text' => array(
				'id' 		=> 'text',
				'name' 		=> __( 'Content', 'themeblvd_builder'),
				'desc'		=> __( 'Enter any content you\'d like displayed about this person.', 'themeblvd_builder'),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
		    )
		);

		/*--------------------------------------------*/
		/* Testimonial
		/*--------------------------------------------*/

		$this->core_elements['testimonial'] = array();

		// Information
		$this->core_elements['testimonial']['info'] = array(
			'name' 		=> __( 'Testimonial', 'themeblvd_builder' ),
			'id'		=> 'testimonial',
			'hook'		=> 'themeblvd_testimonial',
			'shortcode'	=> '[testimonial]',
			'desc'		=> __( 'A display of a testimonial from a customer.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['testimonial']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['testimonial']['options'] = array(
			'text' => array(
				'id' 		=> 'text',
				'name' 		=> __( 'Testimonial Text', 'themeblvd_builder'),
				'desc'		=> __( 'Enter any text of the testimonial.', 'themeblvd_builder'),
				'type'		=> 'textarea',
				'editor'	=> true,
				'code'		=> 'html'
		    ),
			'name' => array(
				'id' 		=> 'name',
				'name' 		=> __( 'Name', 'themeblvd_builder'),
				'desc'		=> __( 'Enter the name of the person giving the testimonial.', 'themeblvd_builder'),
				'type'		=> 'text'
		    ),
		    'tagline' => array(
				'id' 		=> 'tagline',
				'name' 		=> __( 'Tagline (optional)', 'themeblvd_builder'),
				'desc'		=> __( 'Enter a tagline for the person giving the testimonial.<br>Ex: Founder and CEO', 'themeblvd_builder'),
				'type'		=> 'text'
		    ),
		    'company' => array(
				'id' 		=> 'company',
				'name' 		=> __( 'Company (optional)', 'themeblvd_builder'),
				'desc'		=> __( 'Enter the company the person giving the testimonial belongs to.', 'themeblvd_builder'),
				'type'		=> 'text'
		    ),
		    'company_url' => array(
				'id' 		=> 'company_url',
				'name' 		=> __( 'Company URL (optional)', 'themeblvd_builder'),
				'desc'		=> __( 'Enter the website URL for the company or the person giving the testimonial.', 'themeblvd_builder'),
				'type'		=> 'text',
				'pholder'	=> 'http://'
		    ),
		    'image' => array(
				'id' 		=> 'image',
				'name' 		=> __( 'Image (optional)', 'themeblvd_builder'),
				'desc'		=> __( 'Select a small image for the person giving the testimonial. This will look best if you select an image size that is square.', 'themeblvd_builder'),
				'type'		=> 'upload',
				'advanced'	=> true
		    ),
		    'display' => array(
		    	'id' 		=> 'display',
				'name'		=> __( 'Display Style', 'themeblvd_builder' ),
				'desc'		=> __( 'The "Standard" style will fit well with other content, while the "Showcase" style will work better as a stand-alone element, displaying the testimonial much larger.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'fade',
				'options'	=> array(
			        'standard' 	=> __( 'Standard', 'themeblvd_builder' ),
					'showcase'	=> __( 'Showcase', 'themeblvd_builder' )
				),
				'class'		=> 'trigger'
			)
		);

		/*--------------------------------------------*/
		/* Testimonial Slider
		/*--------------------------------------------*/

		$this->core_elements['testimonial_slider'] = array();

		// Information
		$this->core_elements['testimonial_slider']['info'] = array(
			'name' 		=> __( 'Testimonial Slider', 'themeblvd_builder' ),
			'id'		=> 'testimonial_slider',
			'hook'		=> 'themeblvd_testimonial_slider',
			'shortcode'	=> false,
			'desc'		=> __( 'A slider of testimonials.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['testimonial_slider']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['testimonial_slider']['options'] = array(
			'testimonials' => array(
				'id' 		=> 'testimonials',
				'name'		=> __( 'Testimonials', 'themeblvd_builder' ),
				'desc'		=> null,
				'type'		=> 'testimonials'
			),
			'subgroup_start' => array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			'display' => array(
		    	'id' 		=> 'display',
				'name'		=> __( 'Display Style', 'themeblvd_builder' ),
				'desc'		=> __( 'The "Standard" style will fit well with other content, while the "Showcase" style will work better as a stand-alone element, displaying the testimonials much larger.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'fade',
				'options'	=> array(
			        'standard' 	=> __( 'Standard', 'themeblvd_builder' ),
					'showcase'	=> __( 'Showcase', 'themeblvd_builder' )
				),
				'class'		=> 'trigger'
			),
			'title' => array(
				'id' 		=> 'title',
				'name'		=> __('Title (optional)', 'themeblvd_builder'),
				'desc'		=> __('If you want, you can give this set of testimonials a title.', 'themeblvd_builder'),
				'std'		=> 'Testimonials',
				'type'		=> 'text',
				'class'		=> 'hide receiver receiver-standard'
			),
			/*
			'fx' => array(
		    	'id' 		=> 'fx',
				'name'		=> __( 'Transition Effect', 'themeblvd_builder' ),
				'desc'		=> __( 'Select the effect you\'d like used to transition from one slide to the next.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'std'		=> 'fade',
				'options'	=> array(
			        'fade' 	=> __( 'Fade', 'themeblvd_builder' ),
					'slide'	=> __( 'Slide', 'themeblvd_builder' )
				),
				'class'		=> 'hide receiver receiver-standard'
			),
			*/
			'timeout' => array(
		    	'id' 		=> 'timeout',
				'name'		=> __( 'Speed', 'themeblvd_builder' ),
				'desc'		=> __( 'Enter the number of seconds you\'d like in between trasitions. You may use <em>0</em> to disable the slider from auto advancing.', 'themeblvd_builder' ),
				'type'		=> 'text',
				'std'		=> '3'
			),
			'nav' => array(
				'id' 		=> 'nav',
				'name'		=> null,
				'desc'		=> __( 'Display slider navigation.', 'themeblvd_builder' ),
				'std'		=> '1',
				'type'		=> 'checkbox'
			),
			'subgroup_end' => array(
		    	'type'		=> 'subgroup_end'
		    ),
		);

		/*--------------------------------------------*/
		/* Toggles
		/*--------------------------------------------*/

		$this->core_elements['toggles'] = array();

		// Information
		$this->core_elements['toggles']['info'] = array(
			'name' 		=> __( 'Toggles', 'themeblvd_builder' ),
			'id'		=> 'toggles',
			'hook'		=> 'themeblvd_toggles',
			'shortcode'	=> '[accordion]',
			'desc'		=> __( 'A display toggles in an accordion.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['toggles']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['toggles']['options'] = array(
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

		$this->core_elements['video'] = array();

		// Information
		$this->core_elements['video']['info'] = array(
			'name'		=> __( 'Video', 'themeblvd_builder' ),
			'id'		=> 'video',
			'hook'		=> 'themeblvd_video',
			'shortcode'	=> false,
			'desc'		=> __( 'A responsive, full-width video.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['video']['support'] = array(
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
		/* Widget Area
		/*--------------------------------------------*/

		$this->core_elements['widget'] = array();

		// Information
		$this->core_elements['widget']['info'] = array(
			'name' 		=> __( 'Widget Area', 'themeblvd_builder' ),
			'id'		=> 'widget',
			'hook'		=> 'themeblvd_widget',
			'shortcode'	=> false,
			'desc'		=> __( 'A WordPress-regsitered widget area.' , 'themeblvd_builder' )
		);

		// Support
		$this->core_elements['widget']['support'] = array(
			'popout'		=> true,
			'padding'		=> true
		);

		// Options
		$this->core_elements['widget']['options'] = array(
			'sidebar' => array(
		    	'id' 		=> 'sidebar',
				'name'		=> __( 'Widget Area', 'themeblvd_builder' ),
				'desc'		=> __( 'Select from your registered widget areas.', 'themeblvd_builder' ),
				'type'		=> 'select',
				'select'	=> 'sidebars_all'
			)
		);

		/*--------------------------------------------*/
		/* Global element options
		/*--------------------------------------------*/

		// As of framework 2.5, this is @deprecated -- These options have been moved to display options.
		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {

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

				// CSS Classes
				$this->core_elements[$id]['options']['classes'] = array(
			    	'id' 		=> 'classes',
					'name'		=> __( 'CSS Classes', 'themeblvd_builder' ),
					'desc'		=> __( 'Enter any CSS classes you\'d like attached to the element.', 'themeblvd_builder' ),
					'type'		=> 'text',
					'class'		=> 'section-classes'
				);

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

		/*--------------------------------------------*/
		/* Example
		/*--------------------------------------------*/

		$this->core_layouts['about-the-agency'] = array(
			'name'				=> __('About the Agency', 'themeblvd_builder'),
			'id'				=> 'about-the-agency',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/about-the-agency/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/about-the-agency/'
		);

		$this->core_layouts['about-the-company'] = array(
			'name'				=> __('About the Company', 'themeblvd_builder'),
			'id'				=> 'about-the-company',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/about-the-company/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/about-the-company/'
		);

		$this->core_layouts['business-homepage-1'] = array(
			'name'				=> __('Business Homepage #1', 'themeblvd_builder'),
			'id'				=> 'business-homepage-1',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/business-homepage-1/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/business-homepage-1/'
		);

		$this->core_layouts['business-homepage-2'] = array(
			'name'				=> __('Business Homepage #2', 'themeblvd_builder'),
			'id'				=> 'business-homepage-2',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/business-homepage-2/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/business-homepage-2/'
		);

		$this->core_layouts['business-homepage-3'] = array(
			'name'				=> __('Business Homepage #3', 'themeblvd_builder'),
			'id'				=> 'business-homepage-3',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/business-homepage-3/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/business-homepage-3/'
		);

		$this->core_layouts['business-homepage-4'] = array(
			'name'				=> __('Business Homepage #4', 'themeblvd_builder'),
			'id'				=> 'business-homepage-4',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/business-homepage-4/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/business-homepage-4/'
		);

		$this->core_layouts['business-homepage-5'] = array(
			'name'				=> __('Business Homepage #5', 'themeblvd_builder'),
			'id'				=> 'business-homepage-5',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/business-homepage-5/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/business-homepage-5/'
		);

		$this->core_layouts['business-homepage-6'] = array(
			'name'				=> __('Business Homepage #6', 'themeblvd_builder'),
			'id'				=> 'business-homepage-6',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/business-homepage-6/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/business-homepage-6/'
		);

		$this->core_layouts['contact-us'] = array(
			'name'				=> __('Contact Us', 'themeblvd_builder'),
			'id'				=> 'contact-us',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/contact-us/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/contact-us/'
		);

		$this->core_layouts['showcase-blogger'] = array(
			'name'				=> __('Showcase Blogger', 'themeblvd_builder'),
			'id'				=> 'showcase-blogger',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/showcase-blogger/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/showcase-blogger/'
		);

		$this->core_layouts['the-team'] = array(
			'name'				=> __('The Team', 'themeblvd_builder'),
			'id'				=> 'the-team',
			'dir'				=> TB_BUILDER_PLUGIN_DIR . '/includes/admin/sample/the-team/',
			'uri'				=> TB_BUILDER_PLUGIN_URI . '/includes/admin/sample/the-team/'
		);

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

		// For 2.5- themes, merge client added layouts with core layouts -- @deprecated
		if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '<') ) {

			if ( $this->client_layouts ) {
				foreach ( $this->client_layouts as $id => $layouts ) {

					// Establish areas
					$this->client_layouts[$id]['featured'] = array();
					$this->client_layouts[$id]['primary'] = array();
					$this->client_layouts[$id]['featured_below'] = array();

					// Loop through and format elements, splitting them into
					// their areas -- featured, primary, & featured_below
					if ( $layouts['import'] ) {
						$i = 1;
						foreach ( $layouts['import'] as $element ) {

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
								// 'query_type' 	=> $this->elements[$element['type']]['info']['query'],
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
	 * @param string All arguments to create element
	 */
	public function add_element( $args ) {

		$defaults = array(
			'id'		=> '',
			'name'		=> '',
			'options'	=> array(),
			'callback'	=> '',
			'support'	=> array()
		);
		$args = wp_parse_args( $args, $defaults );

		// Register element
		$this->registered_elements[] = $args['id'];

		// Add in element
		if ( is_admin() ) {

			$support_defaults = array(
				'background'	=> true,
				'popout'		=> true,
				'padding'		=> true
			);
			$args['support'] = wp_parse_args( $args['support'], $support_defaults );

			$this->client_elements[$element_id] = array(
				'info' => array(
					'name' 		=> $args['name'],
					'id'		=> $args['id'],
					'hook'		=> 'themeblvd_'.$args['id'],
					'shortcode'	=> false,
					'desc' 		=> null
				),
				'support' => $args['support'],
				'options' => $args['options']
			);
		}

		// Hook in display function on frontend
		add_action( 'themeblvd_'.$args['id'], $args['callback'], 10, 3 ); // Should pass only 2 params passed into callback, leaving as 3 here for backwards compat ($location no longer relevant)

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
					break;
				}
			}
		}
	}

	/**
	 * Add sample layout to Builder.
	 *
	 * @since 1.1.1
	 *
	 * @param string $layout_id ID of sample layout to add
	 * @param string $layout_name Name of sample layout to add
	 * @param string $preview Image URL to preview image
	 * @param string $sidebar_layout Default sidebar layout -- in 2.5+ themes needs to always be "full_width"
	 * @param string $import Absolute path to XML file of elements to import
	 */
	public function add_layout( $layout_id, $layout_name, $preview, $sidebar_layout, $import ) {

		// WP-Admin only
		if ( is_admin() ) {
			$this->client_layouts[$layout_id] = array(
				'name' 				=> $layout_name,
				'id' 				=> $layout_id,
				'preview' 			=> $preview,
				'sidebar_layout'	=> $sidebar_layout, // @deprecated
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
		return apply_filters( 'themeblvd_registered_elements', $this->registered_elements );
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

		$blocks = $this->registered_elements;

		$remove = apply_filters( 'themeblvd_remove_elem_for_blocks', array('columns', 'post_slider_popout', 'simple_slider_popout') );

		if ( $remove ) {
			foreach ( $remove as $elem ) {

				$key = array_search($elem, $blocks);

				if ( $key !== false ) {
					unset( $blocks[$key] );
				}

			}
		}

		return apply_filters( 'themeblvd_registered_blocks', $blocks );
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
		return in_array( $element_id, $this->get_registered_elements() );
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
		return in_array( $block_id, $this->get_registered_blocks() );
	}

} // End class Theme_Blvd_Builder_API