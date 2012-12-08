<?php

// This is the template to markup your thumbnails. See readme for possible placeholders.
$tpl = <<<HTML
<a href="[+dr.bigPath+]" class="highslide [+dr.class+]" onclick="return hs.expand(this, {captionId: 'caption[+dr.id+]'})">
	<img src="[+dr.thumbPath+]" style="[+dr.style+]" width="[+dr.thumbWidth+]" height="[+dr.thumbHeight+]"  alt="[+dr.alt+]" title="[+dr.title+]" align="[+dr.align+]"  vspace="[+dr.vspace+]" hspace="[+dr.hspace+]" />
</a>
<div class="highslide-caption" id="caption[+dr.id+]">[+dr.title+]</div>
HTML;

// All CSS and JS files and all other code that we need in our <HEAD> tag.
$header  = 	'
<link rel="stylesheet" href="assets/libs/highslide/highslide.css" type="text/css" media="screen" />
<script type="text/javascript" src="assets/libs/highslide/highslide.js"></script>
<script type="text/javascript">
	hs.graphicsDir	= "assets/libs/highslide/graphics/";
	hs.outlineType 	= "rounded-white";
</script>

';


// Very important parameter - paths to folders, where the images will be proccesed. Use comma as separator. You can use remote paths with http://.
$allow_from="assets/images";



?>