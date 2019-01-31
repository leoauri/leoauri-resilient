<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/


// Autoload all our class definitions
$classesdir = get_stylesheet_directory() . '/classes';
// Check if classes directory exists
if (is_dir($classesdir)) {
  $classfiles = new \FilesystemIterator($classesdir, \FilesystemIterator::SKIP_DOTS);
  foreach ($classfiles as $file) {
    /** @noinspection PhpIncludeInspection */
    ! $file->isDir() and include $file->getRealPath();
  }
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

  // $post_type = ($tag == 'show_tracks') ? 'tracks'
  global $post;
  $posts = get_posts(array(
    'category_name' => $category,
    'numberposts' => '-1',
    'post_type' => 'any',
    'orderby' => 'menu_order date'
  ));
  $output = '';
  foreach ($posts as $post) {
    setup_postdata($post);
    if ($post->post_type == 'lr_track') {
      ob_start();
      require('parts/bandcamp-standard.php');
      $output .= ob_get_clean();
    } else {
      ob_start();
      require('parts/' . $template . '.php');
      $output .= ob_get_clean();
    }
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


// Remove admin Links section
function customise_admin_menu() {
 remove_menu_page('link-manager.php');
}
add_action('admin_menu', '\leoauriResilient\customise_admin_menu');


// Add track post type
function add_track_post_type() {
  $labels = array(
    'name'                  => 'Tracks',
    'singular_name'         => 'Track',
    'menu_name'             => 'Tracks',
    'name_admin_bar'        => 'Track',
    'archives'              => 'Track Archives',
    'attributes'            => 'Track Attributes',
    'parent_item_colon'     => 'Parent Item:',
    'all_items'             => 'All Tracks',
    'add_new_item'          => 'Add New Track',
    'add_new'               => 'Add New',
    'new_item'              => 'New Track',
    'edit_item'             => 'Edit Track',
    'update_item'           => 'Update Track',
    'view_item'             => 'View Track',
    'view_items'            => 'View Tracks',
    'search_items'          => 'Search Track',
    'not_found'             => 'Not found',
    'not_found_in_trash'    => 'Not found in Trash',
    'featured_image'        => 'Featured Image',
    'set_featured_image'    => 'Set featured image',
    'remove_featured_image' => 'Remove featured image',
    'use_featured_image'    => 'Use as featured image',
    'insert_into_item'      => 'Insert into Track',
    'uploaded_to_this_item' => 'Uploaded to this Track',
    'items_list'            => 'Tracks list',
    'items_list_navigation' => 'Tracks list navigation',
    'filter_items_list'     => 'Filter Track list',
  );
  $args = array(
    'label'                 => 'Track',
    'description'           => 'Individual Bandcamp Tracks',
    'labels'                => $labels,
    'supports'              => array('title', 'page-attributes'),
    'taxonomies'            => array('category', 'post_tag'),
    'hierarchical'          => false,
    'public'                => true,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'menu_position'         => 20,
    'menu_icon'             => 'dashicons-media-audio',
    'show_in_admin_bar'     => true,
    'show_in_nav_menus'     => true,
    'can_export'            => true,
    'has_archive'           => false,
    'exclude_from_search'   => false,
    'publicly_queryable'    => false,
    'capability_type'       => 'page',
  );
  register_post_type('lr_track', $args);
}
add_action('init', '\leoauriResilient\add_track_post_type', 0);

function track_meta_markup($object) {
  wp_nonce_field(basename(__FILE__), 'track-meta-nonce');

  $albumquery = new \WP_Query(array(
    'category_name' => 'albums',
    'posts_per_page' => -1,
  ));
  $albumposts = $albumquery->posts;

  ob_start();
  require(get_stylesheet_directory() . '/admin/parts/track_meta_boxes.php');
  echo ob_get_clean();

}

function track_meta_boxes() {
  add_meta_box('lr_track_settings', 'Track Settings', '\leoauriResilient\track_meta_markup', 'lr_track', 'normal', 'high', null);
}
add_action('add_meta_boxes_lr_track', '\leoauriResilient\track_meta_boxes');


function save_track_meta_boxes($post_id, $post) {
  // Check nonce
  if (! isset($_POST['track-meta-nonce']) || ! wp_verify_nonce($_POST['track-meta-nonce'], basename(__FILE__))) {
    return $post_id;
  }
  // Check if the current user has permission to edit the post.
  if (! current_user_can('edit_post', $post_id)) {
    return $post_id;
  }
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return $post_id;
  }

  $metafields = array(
    'track-ID',
    'album-ID',
    'fallback-link',
    'fallback-text',
    'from-album-post',
  );

  foreach ($metafields as $metafield) {
    $new_meta_value = $_POST[$metafield];
    $current_meta_value = get_post_meta($post_id, $metafield, true);

    /* If a new meta value was added and there was no previous value, add it. */
    if ($new_meta_value && ! $current_meta_value) {
      add_post_meta($post_id, $metafield, $new_meta_value, true);
    }
    /* If there is no new meta value but an old value exists, delete it. */
    elseif (! $new_meta_value && $current_meta_value) {
      delete_post_meta($post_id, $metafield, $current_meta_value);
    }
    /* If the new meta value does not match the old value, update it. */
    elseif ($new_meta_value != $current_meta_value) {
      update_post_meta($post_id, $metafield, $new_meta_value, $current_meta_value);
    }
  }
}
add_action('save_post_lr_track', '\leoauriResilient\save_track_meta_boxes', 10, 2);

function admin_scripts() {
  wp_register_style('lr_track_admin_style', get_template_directory_uri() . '/admin/css/track.css', false, '0.1.1');
  wp_enqueue_style('lr_track_admin_style');
}
add_action('admin_enqueue_scripts', '\leoauriResilient\admin_scripts');
