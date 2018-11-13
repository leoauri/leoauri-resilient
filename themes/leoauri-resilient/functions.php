<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/


// Autoload all our class definitions
$classfiles = new \FilesystemIterator( get_stylesheet_directory() . '/classes', \FilesystemIterator::SKIP_DOTS );
foreach ( $classfiles as $file )
{
  /** @noinspection PhpIncludeInspection */
  ! $file->isDir() and include $file->getRealPath();
}


function setup() {
  /*
   * Let WordPress manage the document title.
   */
  add_theme_support('title-tag');

  // Add navigation menu
  register_nav_menu('navigation', 'Navigation Menu');

}
add_action( 'after_setup_theme', '\leoauriResilient\setup' );


// add menu order to posts
function add_posts_menu_order() {
  add_post_type_support('post', 'page-attributes');
}
add_action('admin_init', '\leoauriResilient\add_posts_menu_order');


// shorts shortcode and function
function show_shorts($atts) {
  extract(shortcode_atts(array(
    'category' => '',
    'template' => 'short'
  ), $atts));
  global $post;
  $posts = get_posts(array(
    'category_name' => $category,
    'numberposts' => '-1',
    'post_type' => 'any',
    'orderby' => 'menu_order'
  ));
  $output = '';
  foreach ($posts as $post) {
    setup_postdata($post);

    ob_start();
    require('parts/' . $template . '.php');
    $output .= ob_get_clean();
  }
  return $output;
}
// register shortcode
function register_shorts_shortcode() {
  add_shortcode('show_shorts', '\leoauriResilient\show_shorts');
}
add_action('init', '\leoauriResilient\register_shorts_shortcode');


// purge native emoji integration
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

remove_action('wp_head', 'wp_resource_hints', 2);


// Remove discovery services
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head');


// Remove oEmbed
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);


define('KILL_FEEDS', false);
if (KILL_FEEDS) {
  // Remove RSS
  remove_action('wp_head', 'feed_links_extra', 3);
  remove_action('wp_head', 'feed_links', 2);

  function feed_die() {
    // header() NOT WORKING
    header('HTTP/1.0 404 Not Found');
    wp_die( __( 'There are no feeds here. <a href="'. esc_url( home_url( '/' ) ) .'">HOME</a>' ) );
  }

  add_action('do_feed', '\leoauriResilient\feed_die', 1);
  add_action('do_feed_rdf', '\leoauriResilient\feed_die', 1);
  add_action('do_feed_rss', '\leoauriResilient\feed_die', 1);
  add_action('do_feed_rss2', '\leoauriResilient\feed_die', 1);
  add_action('do_feed_atom', '\leoauriResilient\feed_die', 1);
  add_action('do_feed_rss2_comments', '\leoauriResilient\feed_die', 1);
  add_action('do_feed_atom_comments', '\leoauriResilient\feed_die', 1);
}


// Customise RSS
add_theme_support('automatic-feed-links');

// display only main (not comments) feed links
function return_false() {
  return false;
}
add_filter('feed_links_show_comments_feed', '\leoauriResilient\return_false');


// Enqueue scripts
function enqueue_scripts() {
  // shim for js classList
  wp_register_script('shimClassList', get_template_directory_uri() . '/js/classList.min.js', array(), '1.2.20171210', false);
  wp_enqueue_script('shimClassList');

  // navbar is enqueued in its part
  wp_register_script('navbar', get_template_directory_uri() . '/js/navbar.js', array('shimClassList'), '0.2', true);

  // remove oEmbed js
  wp_deregister_script('wp-embed');
}
add_action('wp_enqueue_scripts', '\leoauriResilient\enqueue_scripts');
