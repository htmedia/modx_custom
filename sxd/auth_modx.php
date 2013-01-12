<?php
// Sypex Dumper 2 authorization file for MODx 1.0.x
$path = '../manager/includes/config.inc.php';
include($path);
session_name($site_sessionname);
session_start();

if(isset($_SESSION['modx.session.created.time']) && 
	isset($_SESSION['modx.mgr.session.cookie.lifetime']) && 
	($_SESSION['modx.mgr.session.cookie.lifetime'] == 0 || time() < $_SESSION['modx.session.created.time'] + $_SESSION['modx.mgr.session.cookie.lifetime'])  && 
	!empty($_SESSION['mgrPermissions']['bk_manager'])){
	
	if($this->connect($database_server, '', $database_user, $database_password)){
		// Проверяем юзера
		$this->CFG['my_db'] = trim($dbase, '`');
		$this->CFG['exitURL'] = '../manager/';
		$auth = 1;
	}
}
?>