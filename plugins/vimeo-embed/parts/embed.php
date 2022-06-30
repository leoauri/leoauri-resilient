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
<iframe src='https://www.youtube-nocookie.com/embed/<?php echo $id; ?>?rel=0' frameborder='0' allowfullscreen>
<?php break; ?>
<?php case 'youtube_skiprel': ?>
<iframe src='https://www.youtube-nocookie.com/embed/<?php echo $id; ?>' frameborder='0' allowfullscreen>
<?php break; ?>
<?php case 'youtube_playlist': ?>
<iframe src='https://www.youtube-nocookie.com/embed/videoseries?list=<?php echo $id; ?>' frameborder='0' allowfullscreen>
<?php break; ?>
<?php endswitch; ?>
</iframe>
</div>
