/**
 * template
 *
 * Шаблон сайта
 *
 * @category	template
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@lock_template 0
 * @internal 	@modx_category Template
 * @internal    @installset sample
 */
<!DOCTYPE html>
<html>
<head>
	{{head}}
	<link rel="stylesheet" href="[(site_template)]style.css" />
</head>

<body>
<div id="wrapper">
	[*content*]
</div><!-- #wrapper -->

{{scripts}}
</body>
</html>