<?php

if( chdir( dirname( __FILE__) ) )
{
	if( count( $argv ) > 3 )
		$_SERVER['REMOTE_USER'] = $argv[3];
	if(( count( $argv ) > 2 ) && empty($argv[2]))
	{
		require_once( "rquota.php" );
		$qt = rQuota::load();
		if(!$qt->check())
		{
			$action = new rXMLRPCRequest(new rXMLRPCCommand( "d.stop", $argv[1] ));
			$action->run();
		}
		else
			$qt->restoreDL();
	}
}

?>