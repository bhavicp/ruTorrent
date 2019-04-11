<?php

require_once( 'rquota.php' );

$qt = new rQuota();
$tm = getdate();
$startAt = mktime($tm["hours"],
	((integer)($tm["minutes"]/$quotaUpdateInterval))*$quotaUpdateInterval+$quotaUpdateInterval,
	0,$tm["mon"],$tm["mday"],$tm["year"])-$tm[0];
if($startAt<0)
	$startAt = 0;
$interval = $quotaUpdateInterval*60;
$req = new rXMLRPCRequest( array(
        new rXMLRPCCommand("schedule", array('quotaspace'.getUser(),$startAt."",$interval."",
		getCmd('execute').'={sh,-c,'.escapeshellarg(getPHP()).' '.escapeshellarg($rootPath.'/plugins/quotaspace/update.php').' '.escapeshellarg(getUser()).' & exit 0}' )),
	$theSettings->getOnResumedCommand( array(
		'quotapauser'.getUser(), getCmd('execute').'={sh,'.$rootPath.'/plugins/quotaspace/run.sh'.','.getPHP().',$'.getCmd("d.get_hash").'=,$'.getCmd("d.get_complete").'=,'.getUser().'}'))
	));
if($qt->obtain() && $req->success())
	$theSettings->registerPlugin("quotaspace");
else
	$jResult.="plugin.disable(); log('quotaspace: '+theUILang.pluginCantStart);";

?>