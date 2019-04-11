<?php

$req = new rXMLRPCRequest( array(
	$theSettings->getOnResumedCommand(array('quotapauser'.getUser(), getCmd('cat='))),
	new rXMLRPCCommand("schedule_remove", 'quotaspace'.getUser())));
$req->run();

?>