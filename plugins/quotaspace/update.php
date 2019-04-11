<?php

if( chdir( dirname( __FILE__) ) )
{
	if( count( $argv ) > 1 )
		$_SERVER['REMOTE_USER'] = $argv[1];
	require_once( "rquota.php" );
	$qt = rQuota::load();
	$qt->update();
}

?>