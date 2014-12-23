=== Theme Blvd Layouts to Posts ===
Author URI: http://www.jasonbobich.com
Contributors: themeblvd
Tags: layout builder, builder, custom layouts, themeblvd, theme blvd, jason bobich
Requires at least: 3.2
Stable Tag: 1.0.3

This plugin extends the Theme Blvd Layout Builder so you can assign your custom layouts to standard posts and custom post types.

== Description ==

This plugin extends the Theme Blvd Layout Builder so you can assign your custom layouts to standard posts and custom post types.

**Note: You must have a [Theme Blvd theme](http://themeforest.net/user/ThemeBlvd/portfolio "Theme Blvd WordPress themes") installed with the Layout Builder for this plugin to do anything.**

= Customization =

This is a pretty simple plugin, however it will add the custom layout selection meta box to all post types automatically. If you'd like to exlude the meta box from certain post types, you can unset those post types from the array attached to the filter `themeblvd_ltp_post_types`.

`function my_ltp_post_types( $post_types ) {
	unset( $post_types['post_type_to_remove'] );
	return $post_types;
}
add_filter( 'themeblvd_ltp_post_types', 'my_ltp_post_types' );`

== Installation ==

1. Upload `theme-blvd-layouts-to-posts` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. After installing there will be a new meta box on all of your edit post screen sidebars titled "Custom Layout" where you can optionally choose a custom layout built with the Layout Builder.

== Screenshots ==

1. Meta box added to all post edit screens.

== Changelog ==

= 1.0.3 =

* Added compatibility for Theme Blvd framework v2.5+.

= 1.0.2 =

* Added compatibility for Theme Blvd framework v2.3+.
* Added support for private and password protected posts.
* Added "Dismiss" link for admin framework nag.
* Adjusted Custom Layout selection meta box to list layouts alphabetically.

= 1.0.1 =

* Added compatibility for Theme Blvd framework v2.2.1+.

= 1.0.0 =

* This is the first release.