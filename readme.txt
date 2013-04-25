=== Theme Blvd Layout Builder ===
Author URI: http://www.jasonbobich.com
Contributors: themeblvd
Tags: layouts, custom, homepage, builder, Theme Blvd, themeblvd, Jason Bobich
Stable Tag: 1.1.1

When using a Theme Blvd theme, this plugin gives you slick interface to build custom layouts.

== Description ==

**NOTE: This plugin requires Theme Blvd framework v2.2.1+**

When using a Theme Blvd theme, this plugin gives you slick interface to build custom layouts with the framework's core element functions. These custom layouts can then be applied to individual pages or your homepage. Additionally, you can use [this plugin](http://wordpress.org/extend/plugins/theme-blvd-layouts-to-posts/) to extend this fuctionality to standard posts and custom post types.

== Installation ==

1. Upload `theme-blvd-layout-builder` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to *Builder* in your WordPress admin panel to to use the Layout Builder.
4. Custom layouts can then be applied to pages by selecing the "Custom Layout" page template and then selecting the desired custom layout from the dropdown that then appears.

= Using a custom layout for your Homepage =

There are two separate methods for applying a custom layouts as your theme's homepage. Which method you use just depends on what you feel is most logical for how you're setting up your homepage.

Method 1: Create your custom layout, apply it to a static page, and then assign that page as your static frontpage under *Settings > Reading > Frontpage Displays*.

Method 2: Under *Settings > Reading > Frontpage Displays*, select "your latest posts." Create your custom layout. Then, go to *Appearance > Theme Options > Content > Homepage*, and select your new custom layout as the content for the homepage. 

*Note: If you're using an element in your custom layout that uses post pagination, make sure you use method #2.*

== Screenshots ==

1. Manage your custom layouts.
2. Add a new custom layout.
3. Edit a custom layout with the Builder interface.

== Changelog ==

= 1.1.1 =

* Added support for WordPress 3.5 media uploader (requires Theme Blvd framework v2.2.2+).

= 1.1.0 =

* Added Meta Box to apply and edit custom layouts directly from Edit page screen.
* Added support for "Post Slider" when used with [Theme Blvd Sliders](http://wordpress.org/extend/plugins/theme-blvd-sliders) plugin.
* Minor internal improvements to cut down on database queries.
* Added check so if user designates a "posts page" under Settings > Reading, the homepage custom layout option will not get applied (many people do this by accident).
* Update requires Theme Blvd framework v2.2.1+.

= 1.0.1 =

* Fixed issues with applying custom layouts to as homepage from Appearance > Theme Options > Content > Homepage.

= 1.0.0 =

* This is the first release.