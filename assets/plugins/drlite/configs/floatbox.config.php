<?php

// This is the template to markup your thumbnails. See readme for possible placeholders.
$tpl = <<<HTML
<a href="[+dr.bigPath+]" class="floatbox">
	<img src="[+dr.thumbPath+]" width="[+dr.thumbWidth+]" height="[+dr.thumbHeight+]" /></a>
HTML;

// All CSS and JS files and all other code that we need in our <HEAD> tag.
$header  = 	'
<link type="text/css" rel="stylesheet" href="assets/libs/floatbox/floatbox.css" />
<script type="text/javascript" src="assets/libs/floatbox/floatbox.js"></script>
<script type="text/javascript" src="assets/libs/floatbox/options.js"></script>
';


// Very important parameter - paths to folders, where the images will be proccesed. Use comma as separator. You can use remote paths with http://.
$allow_from="assets/images";



?>