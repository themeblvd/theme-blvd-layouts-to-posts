<?php
/*
Plugin Name: Theme Blvd Layouts to Posts
Description: This plugin extends the Theme Blvd Layout Builder to allow you to assign your custom layouts to standard posts and custom post types.
Version: 1.0.2
Author: Jason Bobich
Author URI: http://jasonbobich.com
License: GPL2

    Copyright 2012  Jason Bobich

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

define( 'TB_LTP_PLUGIN_VERSION', '1.0.2' );
define( 'TB_LTP_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_LTP_PLUGIN_URI', plugins_url( '' , __FILE__ ) );

/**
 * Run Layouts to Posts
 *
 * @since 1.0.0
 */
function themeblvd_ltp_init() {

	// Check to make sure Theme Blvd Framework is running
	if( ! defined( 'TB_FRAMEWORK_VERSION' ) ) {
		add_action( 'admin_notices', 'themeblvd_ltp_notice' );
		add_action( 'admin_init', 'themeblvd_ltp_disable_nag' );
		return;
	}

	// Add meta box
	add_action( 'add_meta_boxes', 'themeblvd_ltp_add_meta_box' );

	// Save meta box
	add_action( 'save_post', 'themeblvd_ltp_save_meta_box' );

	// Modify TB framework's primary config array to look for
	// custom layout from our custom option.
	add_filter( 'themeblvd_frontend_config', 'themeblvd_ltp_frontend_config' );

	// Also, if there was a custom layout assigned with our option,
	// we need to redirect to the template_builder.php file.
	add_action( 'template_redirect', 'themeblvd_ltp_redirect' );

}
add_action( 'after_setup_theme', 'themeblvd_ltp_init' );

/**
 * Register text domain for localization.
 *
 * @since 1.0.0
 */
function themeblvd_ltp_textdomain() {
	load_plugin_textdomain( 'themeblvd_ltp', false, TB_LTP_PLUGIN_DIR . '/lang' );
}
add_action( 'plugins_loaded', 'themeblvd_ltp_textdomain' );

/**
 * Display warning telling the user they must have a
 * theme with Theme Blvd framework v2.2+ installed in
 * order to run this plugin.
 *
 * @since 1.0.0
 */
function themeblvd_ltp_notice() {

	global $current_user;

	// DEBUG: delete_user_meta( $current_user->ID, 'tb_sliders_no_framework' );
	if ( ! get_user_meta( $current_user->ID, 'tb_ltp_no_framework' ) ){
		echo '<div class="updated">';
		echo '<p>'.__( 'You currently have the "Theme Blvd Layouts to Posts" plugin activated, however you are not using a theme with Theme Blvd Framework v2.0+, and so this plugin will not do anything.', 'themeblvd_ltp' ).'</p>';
		echo '<p><a href="'.themeblvd_ltp_disable_url('tb_ltp_no_framework').'">'.__('Dismiss this notice', 'themeblvd_ltp').'</a> | <a href="http://www.themeblvd.com" target="_blank">'.__('Visit ThemeBlvd.com', 'themeblvd_ltp').'</a></p>';
		echo '</div>';
	}
}

/**
 * Dismiss an admin notice.
 *
 * @since 1.0.2
 */
function themeblvd_ltp_disable_nag() {

	global $current_user;

    if ( isset( $_GET['tb_nag_ignore'] ) ) {
         add_user_meta( $current_user->ID, $_GET['tb_nag_ignore'], 'true', true );
	}
}

/**
 * Disable a nag message URL.
 *
 * @since 1.0.2
 */
function themeblvd_ltp_disable_url( $id ) {

	global $pagenow;

	$url = admin_url( $pagenow );

	if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
		$url .= sprintf( '?%s&tb_nag_ignore=%s', $_SERVER['QUERY_STRING'], $id );
	} else {
		$url .= sprintf( '?tb_nag_ignore=%s', $id );
	}

	return $url;
}

/**
 * Add Meta Box
 *
 * @since 1.0.0
 */
function themeblvd_ltp_add_meta_box() {

	// Get post types
	$post_types = get_post_types( array( 'public' => true ) );
	$post_types = apply_filters( 'themeblvd_ltp_post_types', $post_types );

	// Add meta box for each post type
	foreach( $post_types as $type ) {
		if( $type != 'attachment' && $type != 'page' ) {
			add_meta_box( 'themeblvd_ltp', __('Custom Layout', 'themeblvd_ltp'), 'themeblvd_ltp_display_meta_box', $type, 'side' );
		}
	}
}

/**
 * Display Meta Box
 *
 * @since 1.0.0
 */
function themeblvd_ltp_display_meta_box(){

	global $post;

	// Current Value
	$value = get_post_meta( $post->ID, '_tb_custom_layout', true );
	$settings = array( '_tb_custom_layout' => $value );

	// Custom Layouts for options array
	$select = array( '' => '-- '.__( 'No Custom Layouts', 'themeblvd_ltp' ).' --' );
	$layouts = get_posts('post_type=tb_layout&numberposts=-1');

	if( $layouts ){

		$select = array( '' => '-- '.__( 'None', 'themeblvd_ltp' ).' --' );

		foreach( $layouts as $layout ) {
			$select[$layout->post_name] = $layout->post_title;
		}
	}

	// Setup Options array
	$options = array(
		array(
			'id'		=> '_tb_custom_layout',
			'desc' 		=> __( 'If you\'d like to replace this post with a custom layout from the builder, you can select one from the dropdown menu.', 'themeblvd_ltp' ),
			'type' 		=> 'select',
			'options'	=> $select
		)
	);

	// Start output
    echo '<div class="tb-meta-box">';

	// Display options form
	// @todo - After framework v2.2 is released, we can
	// clean this up and not to have to check for the
	// different functions, simply requiring the user
	// to update their theme.
	if( function_exists( 'themeblvd_option_fields' ) ){

		// Options form for TB framework v2.2+
    	$form = themeblvd_option_fields( 'themeblvd_ltp', $options, $settings, false );
    	echo $form[0];

	} elseif( function_exists( 'optionsframework_fields' ) ) {

		// Options form for TB framework v2.0 - v2.1
		$form = optionsframework_fields( 'themeblvd_ltp', $options, $settings, false );
		echo $form[0];

	}

	// End output
	echo '</div><!-- .tb-meta-box (end) -->';

}

/**
 * Save Meta Box
 *
 * @since 1.0.0
 */
function themeblvd_ltp_save_meta_box( $post_id ) {
	// If our meta box's value was sent, let's save it.
	if( isset( $_POST['themeblvd_ltp']['_tb_custom_layout'] ) ){
		$vaue = apply_filters( 'themeblvd_sanitize_text', $_POST['themeblvd_ltp']['_tb_custom_layout'] ); // @todo - This technically won't do anything until TB framework v2.2
		update_post_meta( $post_id, '_tb_custom_layout', $vaue );
	}
}

/**
 * Filter TB Framework's frontend config.
 *
 * Whenever a page loads, there is global primary config
 * array that gets generated. This sets up many things when
 * determining the structure of every page WP outputs.
 * So, within this array, we want to add a filter that
 * will now modify the following.
 *
 * (1) Current custom layout ID
 * (2) Whether the featured areas show, based on if we found a custom layout
 * (3) What the sidebar layout is, if we found a custom layout
 *
 * @since 1.0.0
 */
function themeblvd_ltp_frontend_config( $config ) {

	global $post;

	// If any single post type
	if( is_single() ) {

		// Get layout name if its been saved to this post.
		$layout_name = get_post_meta( $post->ID, '_tb_custom_layout', true );

		// Only continue if a custom layout was selected
		if( $layout_name ) {

			if ( post_password_required() || ( 'private' == get_post_status() && ! current_user_can( 'edit_posts' ) ) ) {

				// Password is currently required or status
				// is private and this isn't an admin. So the
				// custom layout doesn't get used.
				$layout_name = 'wp-private';

			} else {

				// Get custom layout's settings and elements
				$config['builder_post_id'] = themeblvd_post_id_by_name( $layout_name, 'tb_layout' ); // Needed in framework v2.2.1+

				if ( $config['builder_post_id'] ) {

					// Setup featured area classes
					$layout_elements = get_post_meta( $config['builder_post_id'], 'elements', true );

					if ( function_exists( 'themeblvd_featured_builder_classes' ) ) {

						// Theme Blvd Framework v2-2.2
						$config['featured'] = themeblvd_featured_builder_classes( $layout_elements, 'featured' );
						$config['featured_below'] = themeblvd_featured_builder_classes( $layout_elements, 'featured_below' );

					} else {

						// Theme Blvd Framework v2.3+
						$frontent_init = Theme_Blvd_Frontend_Init::get_instance();
						$config['featured'] = $frontent_init->featured_builder_classes( $layout_elements, 'featured' );
						$config['featured_below'] = $frontent_init->featured_builder_classes( $layout_elements, 'featured_below' );

					}

					// Sidebar Layout
					$layout_settings = get_post_meta( $config['builder_post_id'], 'settings', true );
					$config['sidebar_layout'] = $layout_settings['sidebar_layout'];

					if( 'default' == $config['sidebar_layout'] ) {
						$config['sidebar_layout'] = themeblvd_get_option( 'sidebar_layout', null, apply_filters( 'themeblvd_default_sidebar_layout', 'sidebar_right' ) );
					}

				}

			}

			// Set layout name
			$config['builder'] = $layout_name;

		}

	}
	return $config;
}

/**
 * Redirect to theme's Builder template.
 *
 * If the user selected a custom layout for the current
 * post, it means we caught it with our global config
 * filter in the previous function. Now, we just need
 * to check for that on single posts and then manually
 * forward to the custom layout page template.
 *
 * @since 1.0.0
 */
function themeblvd_ltp_redirect( $config ) {
	// Include page template and exit if this is a
	// single post AND the global config says there
	// is a custom layout
	if( is_single() && themeblvd_config( 'builder' ) ) {
		include_once( locate_template( 'template_builder.php' ) );
		exit;
	}
}