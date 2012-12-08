//<?php
/**
 * DirectResizeLite
 *
 * Resize Images
 *
 * @category    plugin
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @internal    @events OnWebPagePrerender,OnCacheUpdate
 * @internal    @modx_category Manager and Admin
 * @internal    @properties &config=Конфигурация;string;highslide &clearCache=Очистка кеша;list;0,1,2;0
 */

define(DRLITE_PATH, "assets/plugins/drlite/");
define(DRLITE_CACHE_DIR, "assets/cache/drlite");
define(DRLITE_CACHE_PATH, DRLITE_CACHE_DIR."/");
global $modx;

include_once $modx->config['base_path'].DRLITE_PATH."drlite.php";

$e = &$modx->Event;
switch ($e->name) {
	case "OnWebPagePrerender":
			$modx->documentOutput = RenderOnFrontend($modx->documentOutput, $config);
	break;

	case "OnCacheUpdate":
			ClearDRCache($clearCache);
	break;	
	
	default :
		return;
	break;
}