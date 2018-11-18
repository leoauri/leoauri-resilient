<?php
namespace leoauriBandcampEmbed;
/**
 * leoauri Bandcamp Embed
 * responsive Bandcamp embeds
 **/
?>
<p>
<iframe style="border: 0; width: 100%; height: calc(95vw + 217px); max-height: 1000px;" src="https://bandcamp.com/EmbeddedPlayer/<?php echo $type . '=' . $albumid; ?>/size=large/bgcol=ffffff/linkcol=EE3D00/<?php if ($trackid) {echo "tracklist=false/"; } ?>transparent=true/" seamless>
<a href="<?php echo $link; ?>">
<?php echo $title; ?>
</a>
</iframe>
</p>
