<?php

$tpl = <<<HTML
<a href="[+dr.bigPath+]" rel="lightbox" title="[+dr.title+]">
	<img src="[+dr.thumbPath+]" width="[+dr.thumbWidth+]" height="[+dr.thumbHeight+]" />
</a>
HTML;

$header  = 	'
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script type="text/javascript" src="assets/libs/slimbox-2.04/js/slimbox2.js"></script>
<link rel="stylesheet" href="assets/libs/slimbox-2.04/css/slimbox2.css" type="text/css" media="screen" />
';

$allow_from="assets/images";

?>