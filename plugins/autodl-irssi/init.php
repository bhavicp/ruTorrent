<?php
$user = posix_getpwuid(posix_geteuid())['name'];
$Success = false;
if (file_exists('/home/'.$user.'/.autodl/autodl.cfg') && is_readable('/home/'.$user.'/.autodl/autodl.cfg')) {
	$Cfg = file_get_contents('/home/'.$user.'/.autodl/autodl.cfg');

	$M = array();
	if (preg_match('/gui-server-port\s*=\s*(\d+)/',$Cfg,$M)) {
		$autodlPort = $M[1];

		$M = array();
		if (preg_match('/gui-server-password\s*=\s*(\S+)/',$Cfg,$M)) {
			$autodlPassword = $M[1];
			$Success = true;
		}
	}
}

if (!$Success) {
	$jResult .= "plugin.disable();";
}

$theSettings->registerPlugin($plugin["name"],$pInfo["perms"]);
