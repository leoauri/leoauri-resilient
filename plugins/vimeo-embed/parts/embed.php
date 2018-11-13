<?php
namespace leoauriVimeoEmbed;
/**
 * leoauri Vimeo Embed
 * responsive Vimeo embeds
 **/
?>
<div class='embed-container'>
<?php switch ($tag): ?>
<?php case 'vimeo': ?>
<iframe src='https://player.vimeo.com/video/<?php echo $id; ?>' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen>
<?php break; ?>
<?php case 'youtube': ?>
<iframe src='https://www.youtube-nocookie.com/embed/<?php echo $id; ?>' frameborder='0' allowfullscreen>
<?php break; ?>
<?php endswitch; ?>
</iframe>
</div>
