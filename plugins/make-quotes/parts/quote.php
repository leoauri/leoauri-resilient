<?php
namespace leoauriMakeQuotes;
/*
Plugin Name: Make quotes
*/
?>

<?php if ($quote or $who) : ?>
<figure>
<?php if ($quote) : ?>
<blockquote>
<p>
<?php echo $quote; ?>
</p>
</blockquote>
<?php endif; ?>
<?php if ($who) : ?>
<figcaption>
<?php echo $who; ?>
</figcaption>
<?php endif; ?>
</figure>
<?php endif; ?>
