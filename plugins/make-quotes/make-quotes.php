<?php
namespace leoauriMakeQuotes;
/*
Plugin Name: Make quotes
*/

function make_quote($atts) {
  extract(shortcode_atts(array(
    'quote' => '',
    'who' => '',
    ), $atts));

  ob_start();
  require('parts/quote.php');
  return ob_get_clean();
}

// register shortcode
function register_shortcode() {
  add_shortcode('quote', '\leoauriMakeQuotes\make_quote');
}
add_action('init', '\leoauriMakeQuotes\register_shortcode');
