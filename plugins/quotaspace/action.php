<?php

require_once( 'rquota.php' );

$qt = rQuota::load();
$cmd = isset($_REQUEST["cmd"]) ? $_REQUEST["cmd"] : "get";

switch($cmd)
{
	case "get":
	{
        	$arr = $qt->getSpace();
		if($arr)
			$content = '{ "total": '.$arr["total"].', "free": '.$arr["free"].' }';
		break;
	}
	case "stop":
	{
		$qt->update();
		break;
	}
}

if(!isset($content))
	$content = '{ "total": 0, "free": 0 }';
cachedEcho($content,"application/json");

?>