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
