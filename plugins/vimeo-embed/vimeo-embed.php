<?php
namespace leoauriVimeoEmbed;
/**
 * leoauri Vimeo Embed
 * responsive Vimeo embeds
 **/

/*
Plugin Name:  leoauri Vimeo Embed
Plugin URI:   https://leoauri.com
Description:  responsive Vimeo embeds
Version:      0.1
Author:       Leo Auri
Author URI:   https://leoauri.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

// Create the Vimeo embed
function vimeo_embed($atts, $content, $tag) {
  extract(shortcode_atts(array(
    'id' => '',
    ), $atts));

  ob_start();
  require('parts/embed.php');
  return ob_get_clean();
}


// register shortcode
function register_shortcode() {
  add_shortcode('vimeo', '\leoauriVimeoEmbed\vimeo_embed');
  add_shortcode('youtube', '\leoauriVimeoEmbed\vimeo_embed');
}
add_action('init', '\leoauriVimeoEmbed\register_shortcode');


// enqueue stylesheet
function enqueue_style() {
  wp_enqueue_style('leoauriVimeoEmbed', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/vimeo-embed.css', basename(__FILE__)));
}
add_action('wp_enqueue_scripts', '\leoauriVimeoEmbed\enqueue_style');
