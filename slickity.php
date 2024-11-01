<?php

/*
Plugin Name: Slickity
Plugin URI: https://wordpress.org/plugins/slickity/
Description: Slickity is <strong>the last WordPress carousel plugin you'll ever need!</strong> Easily add fully customizable carousels and sliders to your theme using a simple shortcode. Fully responsive and loaded with a ton of customizable features. Uses Key Wheeler's hugely popular <a href="http://kenwheeler.github.io/slick/">slick</a> library.
Version: 2.3.2
Author: Ben Marshall
Author URI: https://benmarshall.me
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: slickity
Domain Path: /languages

Slickity is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Slickity is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Slickity. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

/**
 * Include the plugin helper functions
 */
require_once dirname( __FILE__ ) . '/includes/helpers.php';

if ( !function_exists( 'slickity_setup_post_type' ) ) {
  function slickity_setup_post_type() {
    // Register the slideshow post type.
    $labels = array(
      'name'                  => _x( 'Slideshows', 'Post Type General Name', 'slickity' ),
      'singular_name'         => _x( 'Slideshow', 'Post Type Singular Name', 'slickity' ),
      'menu_name'             => __( 'Slideshows', 'slickity' ),
      'name_admin_bar'        => __( 'Slideshows', 'slickity' ),
      'archives'              => __( 'Slideshow Archives', 'slickity' ),
      'attributes'            => __( 'Slideshow Attributes', 'slickity' ),
      'parent_item_colon'     => __( 'Parent Slideshow:', 'slickity' ),
      'all_items'             => __( 'All Slideshows', 'slickity' ),
      'add_new_item'          => __( 'Add New Slideshow', 'slickity' ),
      'add_new'               => __( 'Add New', 'slickity' ),
      'new_item'              => __( 'New Slideshow', 'slickity' ),
      'edit_item'             => __( 'Edit Slideshow', 'slickity' ),
      'update_item'           => __( 'Update Slideshow', 'slickity' ),
      'view_item'             => __( 'View Slideshow', 'slickity' ),
      'view_items'            => __( 'View Slideshows', 'slickity' ),
      'search_items'          => __( 'Search Slideshow', 'slickity' ),
      'not_found'             => __( 'Not found', 'slickity' ),
      'not_found_in_trash'    => __( 'Not found in Trash', 'slickity' ),
      'featured_image'        => __( 'Featured Image', 'slickity' ),
      'set_featured_image'    => __( 'Set featured image', 'slickity' ),
      'remove_featured_image' => __( 'Remove featured image', 'slickity' ),
      'use_featured_image'    => __( 'Use as featured image', 'slickity' ),
      'insert_into_item'      => __( 'Insert into slideshow', 'slickity' ),
      'uploaded_to_this_item' => __( 'Uploaded to this slideshow', 'slickity' ),
      'items_list'            => __( 'Slideshows list', 'slickity' ),
      'items_list_navigation' => __( 'Slideshows list navigation', 'slickity' ),
      'filter_items_list'     => __( 'Filter slideshows list', 'slickity' ),
    );
    $args = array(
      'label'                 => __( 'Slideshow', 'slickity' ),
      'description'           => __( 'Used to create embeddable slideshows.', 'slickity' ),
      'labels'                => $labels,
      'supports'              => array( 'title', 'revisions' ),
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 5,
      'menu_icon'             => 'dashicons-slides',
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'has_archive'           => false,
      'exclude_from_search'   => true,
      'publicly_queryable'    => true,
      'capability_type'       => 'post',
      'show_in_rest'          => false,
    );
    register_post_type( 'slickity_slideshow', $args );
  }
}
add_action( 'init', 'slickity_setup_post_type' );

if ( !function_exists( 'slickity_install' ) ) {
  function slickity_install() {
    // Register the custom post type.
    slickity_setup_post_type();

    // Clear permalinks after the post type has been registered to avoid 404 errors.
    // @TODO - Getting a 'Fatal error: Call to a member function flush_rules() on a non-object in /wp-includes/rewrite.php on line 273'
    //flush_rewrite_rules();
  }
}
register_activation_hook( __FILE__, 'slickity_install' );

if ( !function_exists( 'slickity_deactivation' ) ) {
  // Clear the permalinks to remove the post type's rules.
  // @TODO - Getting a 'Fatal error: Call to a member function flush_rules() on a non-object in /wp-includes/rewrite.php on line 273'
  //flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'slickity_deactivation' );

/**
 * Load plugin textdomain.
 *
 * @since 2.0.0
 */
function slickity_load_textdomain() {
  load_plugin_textdomain( 'slickity', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'slickity_load_textdomain' );

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname( __FILE__ ) . '/includes/class-tgm-plugin-activation.php';

/**
 * Include the config for Advanced Custom Fields.
 */
if ( !defined( 'SLICKITY_DEV' ) || ( defined( 'SLICKITY_DEV' ) && !SLICKITY_DEV ) ) {
  require_once dirname( __FILE__ ) . '/includes/acf.php';
}

/**
 * Register the required plugins for this theme.
 */
if ( !function_exists( 'slickity_register_required_plugins' ) ) {
  function slickity_register_required_plugins() {
    $plugins = array(
      array(
        'name'               => 'Advanced Custom Fields PRO',
        'slug'               => 'advanced-custom-fields-pro',
        'source'             => dirname( __FILE__ ) . '/includes/advanced-custom-fields-pro.zip',
        'required'           => true,
        'version'            => '5.6.7',
        'force_activation'   => true,
      ),
    );

    $config = array(
      'id'           => 'slickity',
      'menu'         => 'slickity-install-plugins',
      'parent_slug'  => 'plugins.php',
      'capability'   => 'manage_options',
      'has_notices'  => true,
      'dismissable'  => false,
      'is_automatic' => true,
      'strings'      => array(
        'page_title'                      => __( 'Install Required Plugins for Slickity', 'slickity' ),
        'notice_can_install_required'     => _n_noop(
          /* translators: 1: plugin name(s). */
          'Slickity requires the following plugin: %1$s.',
          'Slickity requires the following plugins: %1$s.',
          'slickity'
        ),
        'notice_ask_to_update'            => _n_noop(
          /* translators: 1: plugin name(s). */
          'The following plugin needs to be updated to its latest version to ensure maximum compatibility with Slickity: %1$s.',
          'The following plugins need to be updated to their latest version to ensure maximum compatibility with Slickity: %1$s.',
          'slickity'
        ),
        /* translators: 1: plugin name. */
        'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for Slickity. Please update the plugin.', 'slickity' ),
      ),
    );

    tgmpa( $plugins, $config );
  }
}
add_action( 'tgmpa_register', 'slickity_register_required_plugins' );

/**
 * Enqueue scripts and styles.
 */
if ( !function_exists( 'slickity_scripts' ) ) {
  function slickity_scripts() {
    global $post;

    // Register Slick JS
    wp_register_script( 'slickity-slick', plugin_dir_url( __FILE__ ) . 'public/js/slick.min.js', array( 'jquery' ), '1.8.0', true );
    wp_register_script( 'slickity', plugin_dir_url( __FILE__ ) . 'public/js/slickity.js', array( 'jquery', 'slickity-slick' ), '2.2.0', true );

    // Register Slick CSS
    wp_register_style( 'slickity-slick', plugin_dir_url( __FILE__ ) . 'public/css/slick.css', array(), '1.8.0' );
    wp_register_style( 'slickity-theme', plugin_dir_url( __FILE__ ) . 'public/css/slick-theme.css', array( 'slickity-slick' ), '1.8.0' );
    wp_register_style( 'slickity-templates', plugin_dir_url( __FILE__ ) . 'public/css/slick-templates.css', array( 'slickity-theme' ) );
    wp_register_style( 'slickity-lightbox', plugin_dir_url( __FILE__ ) . 'public/css/slick-lightbox.css' );

    // @TODO - Find a better way to load scripts only if a slideshow is present on the page.
    wp_enqueue_script( 'slickity' );
    wp_enqueue_style( 'slickity-templates' );
    wp_enqueue_style( 'slickity-lightbox' );
  }
}
add_action( 'wp_enqueue_scripts', 'slickity_scripts' );

if ( !function_exists( 'slickity_shortcode_init' ) ) {
  function slickity_shortcode_init() {
    function slickity_shortcode( $atts = [], $content = null ) {
      $attr = shortcode_atts( array(
        'id' => false, // Post ID
      ), $atts );

      // Check if post ID supplied
      if ( $attr['id'] ) {
        $query = new WP_Query( array(
          'post_type' => 'slickity_slideshow',
          'p'         => $attr['id'],
        ));

        if ( $query->have_posts() ) {
          ob_start();
          while ( $query->have_posts() ):
            $query->the_post();

            // Get the slides
            $slides = get_field( 'slickity_slides' );

            if ( $slides ):
              $hasThumbnail = get_field( 'slickity_thumbnail' );
              $hasLightbox = get_field( 'slickity_lightbox' );

              // Generate a random ID to allow the slideshow to placed on the page more than once
              $uniqueID = uniqid();
              $slideshowID = get_the_ID() . '-' . $uniqueID;

              echo slickity_slideshow(
                $slideshowID,
                $slides,
                'main',
                $hasThumbnail,
                $hasLightbox
              );

              if ( $hasThumbnail ) {
                echo slickity_slideshow(
                  $slideshowID,
                  $slides,
                  'thumbnail',
                  $hasThumbnail,
                  $hasLightbox
                );
              }

              if ( $hasLightbox ) {
                echo slickity_slideshow(
                  $slideshowID,
                  $slides,
                  'lightbox',
                  $hasThumbnail,
                  $hasLightbox
                );
              }
            endif;
          endwhile;

          // Restore original post data
          wp_reset_postdata();

          return ob_get_clean();
        }
      }
    }
    add_shortcode( 'slickity', 'slickity_shortcode' );
  }
}
add_action( 'init', 'slickity_shortcode_init' );
