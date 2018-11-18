<?php
namespace leoauriBandcampEmbed;
/**
 * leoauri Bandcamp Embed
 * responsive Bandcamp embeds
 **/

/*
Plugin Name:  leoauri Bandcamp Embed
Plugin URI:   https://leoauri.com
Description:  responsive Bandcamp embeds
Version:      0.1
Author:       Leo Auri
Author URI:   https://leoauri.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

// Create the Bandcamp embed
function bandcamp_embed($atts) {
  extract(shortcode_atts(array(
    'albumid' => '',
    'trackid' => '',
    'id' => '',
    'link' => '',
    'title' => '',
    ), $atts));

  // Is it a track?
  if (! $trackid) {
    $trackid = get_post_custom_values('trackid')[0];
  }
  if ($trackid) {
    $albumid = $trackid;
    $type = 'track';
  } else {
    $type = 'album';
  }

  // Try to get id from alternate attribute
  if (! $albumid) {
    $albumid = $id;
  }

  // Try to get values from custom fields
  if (! $albumid) {
    $albumid = get_post_custom_values('albumid')[0];
  }
  if (! $link) {
    $link = get_post_custom_values('albumlink')[0];
  }
  if (! $title) {
    $title = get_post_custom_values('albumalt')[0];
  }

  ob_start();
  require('parts/embed.php');
  return ob_get_clean();
}


// register shortcode
function register_shortcode() {
  add_shortcode('bandcamp', '\leoauriBandcampEmbed\bandcamp_embed');
}
add_action('init', '\leoauriBandcampEmbed\register_shortcode');
