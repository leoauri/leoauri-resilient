<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/


?>
<div>
  <label>
    <span>Track ID</span>
    <input type="text" name="track-ID" value="<?php echo get_post_meta($object->ID, "track-ID", true); ?>">
  </label>
</div>
<div>
  <label>
    <span>Album ID</span>
    <input type="text" name="album-ID" value="<?php echo get_post_meta($object->ID, "album-ID", true); ?>">
  </label>
</div>
<div>
  <label>
    <span>Fallback link</span>
    <input type="text" name="fallback-link" value="<?php echo get_post_meta($object->ID, "fallback-link", true); ?>">
  </label>
</div>
<div>
  <label>
    <span>Fallback text</span>
    <input type="text" name="fallback-text" value="<?php echo get_post_meta($object->ID, "fallback-text", true); ?>">
  </label>
</div>
<div>
  <label>
    <span>From Album Post</span>
    <select name="from-album-post">
      <?php foreach ($albumposts as $post) : ?>
      <option value="<?php echo $post->ID; ?>"<?php if ($post->ID == get_post_meta($object->ID, "from-album-post", true)) { echo " selected"; } ?>><?php echo $post->post_title; ?></option>
      <?php endforeach; ?>
    </select>
  </label>
</div>
