=== Theme Blvd Layout Builder ===
Author URI: http://www.themeblvd.com
Contributors: themeblvd
Tags: layouts, custom, homepage, builder, Theme Blvd, themeblvd, Jason Bobich
Stable Tag: 2.0.9.5

When using a Theme Blvd theme, this plugin gives you slick interface to build custom layouts.

== Description ==

**NOTE: This plugin requires Theme Blvd framework v2.2+**

When using a Theme Blvd theme, this plugin gives you slick interface to build custom layouts with the framework's core elements.

You can build these layouts directly into your WordPress pages through the standard Edit Page screen, or you can create templates that can be synced to multiple pages. Additionally, you can use [this plugin](http://wordpress.org/extend/plugins/theme-blvd-layouts-to-posts/) to extend the templates to standard posts and custom post types.

[vimeo https://vimeo.com/70256816]

*Note: To get all features in the above video, you need to be using a theme with Theme Blvd Framework v2.5+, which currently only includes [Jump Start 2](http://themeblvd.com/links/buy-jumpstart) and [Denali](http://themeblvd.com/links/buy-denali). For all other themes, [see this video](https://vimeo.com/112649094).*

= Theme Compatibility =

This plugin works with the following themes only.

**Theme Blvd Framework 2.5+ Themes**

* [Jump Start](http://themeblvd.com/links/buy-jumpstart)
* [Denali](http://themeblvd.com/links/buy-denali)

**Theme Blvd Framework 2.2-2.4 Themes**

* [Akita](http://themeforest.net/item/akita-responsive-wordpress-theme/1530025?ref=themeblvd)
* [Alyeska](http://themeforest.net/item/alyeska-responsive-wordpress-theme/164366?ref=themeblvd)
* [Arcadian](http://themeforest.net/item/the-arcadian-responsive-wordpress-theme/1266406?ref=themeblvd)
* [Barely Corporate](http://themeforest.net/item/barely-corporate-responsive-wordpress-theme/93069?ref=themeblvd)
* [Breakout](http://www.mojo-themes.com/item/breakout-premium-wordpress-theme/?r=themeblvd)
* [Commodore](http://themeforest.net/item/commodore-responsive-wordpress-theme/111713?ref=themeblvd)
* [Swagger](http://themeforest.net/item/swagger-responsive-wordpress-theme/930581?ref=themeblvd)

== Installation ==

1. Upload `theme-blvd-layout-builder` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Overview =

[vimeo https://vimeo.com/70256816]

*Note: To get all features in the above video, you need to be using a theme with Theme Blvd Framework v2.5+, which currently only includes [Jump Start 2](http://themeblvd.com/links/buy-jumpstart) and [Denali](http://themeblvd.com/links/buy-denali). For all other themes, [see this video](https://vimeo.com/112649094).*

== Screenshots ==

1. Edit a page's custom layout.
2. Manage templates page.
3. Editing a template.

== Changelog ==

= 2.0.9.5 - 01/27/2016 =

* New: Added option to pull from pages to Post List element.
* New: Added option to align divider left or right (for framework 2.6+ themes).
* New: Added option to shade slider images for overlaid text readability; this applies to post slider and simple slider elements (for framework 2.6+ themes).
* Improvement: Allow up to six logos per row in Partner Logos element.
* Improvement: Use `add_menu_page` instead of `add_object_page`, which was deprecated in WordPress 4.5.
* Removed: No more icon style option on redesigned Team Member element (for framework 2.6+ themes).

= 2.0.9.4 - 12/11/2015 =

* Improvement: Better RTL support for Layout Builder admin interface.
* Fixed: When custom elements are filtered in, make sure they appear for selection within the "Columns" element.
* Fixed: Hero unit content background color opacity option not working.

= 2.0.9.3 - 10/01/2015 =

* Fixed: Javascript errors in builder from incorrect divider element options (for framework 2.2-2.4 themes).

= 2.0.9.2 - 09/21/2015 =

* Improvements to overall security and sanitization.
* Fixed: Import & Export buttons will only show if [Theme Blvd Importer plugin](https://wordpress.org/plugins/theme-blvd-importer) is installed.

= 2.0.9.1 - 09/14/2015 =

* Fixed: Hero Unit Slider element shouldn't be available to be inserted within a Columns element (for framework 2.5+ themes).
* Fixed: Negative horizontal margin overflow when using "popout" element in section with custom left/right padding set to `0px` (for framework 2.5+ themes).

= 2.0.9 - 08/24/2015 =

* Added: More accuracy for opacity selections (for framework 2.5+ themes).
* Improvement: Elements and sample layouts sorted alphabetically, after merged with client-API.
* Improvement: Parallax background image performance (for framework 2.5+ themes).
* Removed: Parallax intensity options; now it's based on height of image, proportional to container (for framework 2.5+ themes).

** Note: If you're using a theme with framework 2.5.0, you must update it to the latest version containing framework 2.5.1.**

= 2.0.8 - 07/06/2015 =

* Fixed: Fatal error in previous version (for framework 2.2-2.4 themes).

= 2.0.7 - 07/04/2015 =

* Added: Extended custom background support for Hero Unit element (for framework 2.5+ themes).
* Added: Hero unit slider element (for framework 2.5+ themes).

= 2.0.6 - 05/29/2015 =

* Renamed "Jumbotron" to "Hero Unit".
* Improvements to Hero Unit (for framework 2.5+ themes).
* Allow layout's first section padding to adjust automatically to height of theme header, when displaying "Transparent Header" (for framework 2.5+ themes).
* Added more button size selections elements with buttons (for framework 2.5+ themes).
* Added two shop homepage sample layouts for use with WooCommerce (for framework 2.5+ themes).
* Added "Small Desktops" breakpoint for Columns element (for framework 2.5+ themes).

= 2.0.5 - 04/19/2015 =

* Fixed: Errors with inner elements, when duplicating outter "Columns elements" (for framework 2.5+ themes).

= 2.0.4 - 03/27/2015 =

* Added new sample layouts, and improved previous ones (for framework 2.5+ themes).
* Added options to Icon Box element (for framework 2.5+ themes).
* Added options to Content element (for framework 2.5+ themes).
* Added options to Divider element (for framework 2.5+ themes).
* Added "Custom Styling" (background color) options to Jumbotron element (for framework 2.5+ themes).
* Added "Maximum Width" option to Promo Box element (for framework 2.5+ themes).
* Added "Gutters" option to Post Showcase element (for framework 2.5+ themes).
* Added video background support for sections (for framework 2.5+ themes).
* Added responsive visibility options to columns and sections (for framework 2.5+ themes).
* Added Revolution Slider element.
* Modified how you can set the background image sizing with parallax backgrounds for sections (for framework 2.5+ themes).
* Reduced plugin size, by pulling from unified assets directory for sample layouts (for framework 2.5+ themes).
* Fixed Bug: Importing from a sample layout with a slider would import an extra, empty slide (for framework 2.5+ themes).

= 2.0.3 - 01/28/2015 =

* Added "Current Featured Image" element.
* Fixed Bug: "Preview Changes" when inserting current page's content into a custom layout wasn't working right (for framework 2.2-2.4 themes).
* Fixed Bug: When editing a page, sidebar layout option would disappear when applying elements from Template or Sample Layout (for framework 2.2-2.4 themes).

= 2.0.2 - 12/22/2014 =

* Increased limits on Jumbotron font sizes (for framework 2.5+ themes).
* Fix for using Columns in template footer sync feature (for framework 2.5+ themes).
* Fix for saving "HTML" element within "Columns" element (for framework 2.5+ themes).
* Expanded options for Divider element (for framework 2.5+ themes).
* Reduced plugin size by compressing included sample images.
* Fix to ensure hidden Builder is only inserted when editing pages, needed for [Theme Blvd Layouts to Posts](https://wordpress.org/plugins/theme-blvd-layouts-to-posts/) plugin to save properly when editing posts.
* Minor security fixes.

= 2.0.1 - 11/19/2014 =

* Removed the "Builder" tab from Edit Page screen for better compatibility with WP's Visual/Text editors; builder now shows above editor.
* Fixed issues with "Promo Box" (formerly "Slogan") element after last update.
* Fixed some errors with themes, which aren't up-to-date.

= 2.0.0 - 11/17/2014 =

* New interface for editing layouts from the Edit Page screen
* Complete coding overhaul of the Templates (previously "Builder") admin page
* Separation of "layouts" vs "templates"
* GlotPress compatibility (for 2015 wordpress.org release).
* Enhancements for themes with Theme Blvd Framework 2.5+
	* Elements: 32 new elements added to layout builder
	* Elements: Standard set of display options added across all elements
	* Sections: Add unlimited sections for your elements
	* Sections: Apply custom background options to each section
	* Sections: Apply custom border to top or bottom of each section
	* Sections: Apply custom padding to each section, based on viewport
	* Sections: Apply custom CSS class to each section
	* Editing: Use WordPress's Visual editor throughout builder
	* Editing: New sortable options used for many of the elements
	* Columns: Complete overhaul of Columns element
	* Columns: Add unlimited elements within columns
	* Columns: Up to 5 columns, w/unlimited combos of 10-col and 12-col grid system
	* Columns: Select at what responsive viewport columns stack for mobile
	* Columns: Background display options for each individual column
	* Sample Layouts: All new set of sample layouts
	* Footer Syncing: Apply template from Theme Options to replace footer columns
	* Import/Export: Templates can be exported as XML files, and imported
	* Removed: Sidebar layout option removed, use Columns element instead
	* Removed: Paginated post grid and list elements removed; pagination can now be applied from standard post list and grid elements.
	* Removed: Post grid slider removed; standard post grid element can now be set to display as slider.
	* Removed: Post list slider removed; use updated post slider element instead.
	* Edits: Enhancements to previous elements, Content, Divider, Headline, Jumbotron, Post Grid, Post List and Tabs
	* Edits: Slogan element ehanced and renamed "Promo Box"

*Note: Currently, the only theme with Theme Blvd Framework 2.5, is the [Jump Start](http://themeblvd.com/links/buy-jumpstart) 2.0 beta. We're currently working on expanding support.*

= 1.2.3 - 03/18/2014 =

* Added Jumbotron element (requires Theme Blvd Framework 2.4.2+).
* Added support for "element-unstyled" CSS class (requires Theme Blvd Framework 2.4.2+).

= 1.2.2 - 12/11/2013 =

* Admin style updates for WordPress 3.8 and MP6 (requires Theme Blvd Framework 2.4+).

= 1.2.1 - 10/11/2013 =

* Added "Screen Options" tab to Builder interface.
* Added "CSS Classes" advanced option for all elements.

= 1.2.0 - 06/31/2013 =

* Added support for WordPress 3.5 media uploader (requires Theme Blvd framework v2.3+).
* Added support for Theme Blvd framework v2.3's Builder API modifications.
* Fixed issue of homepage layout not displaying after toggling WP Reading settings back and forth.
* Improved Builder API functionality and moved here to the plugin.
* Fixed bug with creating a new layout from Edit Page meta box when no current layout is selected.
* Some minor admin javascript improvements.
* Styled Custom Layout meta box to take up less visual space when no layout is selected (i.e. it isn't being used).
* Fixed Layout Information not saving properly from Builder.
* Fixed custom layout selection not displaying on Edit Page screen when no layouts exist yet.
* Removed "Tweet" element. Use [Tweeple](http://wordpress.org/extend/plugins/tweeple) instead.
* Fixed any conflicts when activated with [Theme Blvd Bundle](http://wordpress.org/extend/plugins/theme-blvd-bundle).

= 1.1.0 - 03/22/2013 =

* Added Meta Box to apply and edit custom layouts directly from Edit page screen.
* Added support for "Post Slider" when used with [Theme Blvd Sliders](http://wordpress.org/extend/plugins/theme-blvd-sliders) plugin.
* Minor internal improvements to cut down on database queries.
* Added check so if user designates a "posts page" under Settings > Reading, the homepage custom layout option will not get applied (many people do this by accident).
* Update requires Theme Blvd framework v2.2.1+.

= 1.0.1 - 12/05/12 =

* Fixed issues with applying custom layouts to as homepage from Appearance > Theme Options > Content > Homepage.

= 1.0.0 - 09/07/12 =

* This is the first release.
