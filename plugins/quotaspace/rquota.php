<?php

require_once( dirname(__FILE__)."/../../php/xmlrpc.php" );
require_once( $rootPath.'/php/cache.php');
eval(getPluginConf('quotaspace'));

class rQuota
{
	public $hash = "quota.dat";
	public $fileSystem = '/';
	public $quotaFlags = 'ug';
	public $savedDL = null;

	static public function load()
	{
		$cache = new rCache();
		$qt = new rQuota();
		if(!$cache->get($qt))
			$qt->obtain();
		return($qt);
	}

	public function store()
	{
		$cache = new rCache();
		return($cache->set($this));
	}

	public function obtain()
	{
		global $topDirectory;

		$system = strtolower(php_uname('s'));
//		if($system=='linux')
//			$this->quotaFlags = 'ugw';
		$fsColumn = (($system=='freebsd') ? 5 : 0);
		$req = new rXMLRPCRequest( new rXMLRPCCommand( "execute_capture", array( 
			"sh", "-c", "df -Pk ".escapeshellarg($topDirectory)." | grep %" )));
		if($req->success())
		{
			$tokens = preg_split("/\s+/",$req->val[0]);
			if(count($tokens)>$fsColumn)
			{
				$this->fileSystem = trim($tokens[$fsColumn]);
				return($this->store());
			}
		}
		return(false);
	}

	public function check()
	{
	        $arr = $this->getSpace();
		return( !$arr || ($arr["free"]>0));
	}

	public function update()
	{
		if(!$this->check())
			$this->stopAllLeechers();
		else
			$this->restoreDL();
	}

	public function restoreDL()
	{
		if(!is_null($this->savedDL))
		{
			$req = new rXMLRPCRequest( new rXMLRPCCommand("set_download_rate", $this->savedDL) );
			if($req->success())
			{
				$this->savedDL = null;
				$this->store();
			}
		}
	}

	public function stopAllLeechers()
	{
		$req = new rXMLRPCRequest(  
			new rXMLRPCCommand("d.multicall", array("started",getCmd("d.get_hash="), getCmd("d.get_connection_current=") )) );
		$leeches = array();
		if($req->success())
		{
		        $action = new rXMLRPCRequest();
			for($i=0; $i<count($req->val); $i+=2)
			{
				if($req->val[$i+1]=='leech')
					$action->addCommand(new rXMLRPCCommand( "d.stop", $req->val[$i] ));
			}
			if(is_null($this->savedDL))
			{
				$req = new rXMLRPCRequest( new rXMLRPCCommand("get_download_rate") );
				if($req->success())
				{
					$this->savedDL = floatval($req->val[0]);
					$this->store();
				}
			}
			$action->addCommand(new rXMLRPCCommand( "set_download_rate", 1024 ) );
			$action->run();
		}
	}

	public function getSpace()
	{
		global $topDirectory;
		$quota = getExternal('quota');
		$req = new rXMLRPCRequest( new rXMLRPCCommand( "execute_capture", array( 
			"sh", "-c", $quota." -".$this->quotaFlags." | grep ".escapeshellarg($this->fileSystem)."; exit 0" )));
		$used = null;
		$total = null;
		if($req->success())
		{
			$lines = explode("\n",$req->val[0]);
			foreach( $lines as $ndx=>$line )
			{
				$tokens = preg_split("/\s+/", trim($line));
				if(count($tokens)>2)
				{
					$used = is_null($used) ? floatval($tokens[1]) : min($used,floatval($tokens[1]));
					$total = is_null($total) ? floatval($tokens[2]) : min($total,floatval($tokens[2]));
				}
			}
			if(is_null($used))
			{
				$req = new rXMLRPCRequest( new rXMLRPCCommand( "execute_capture", array( 
					"sh", "-c", "df -Pk ".escapeshellarg($topDirectory)." | grep %" )));
				if($req->success())
				{
					$tokens = preg_split("/\s+/",$req->val[0]);
					if(count($tokens)>3)
					{
						$total = floatval($tokens[1])*1024;
						$avail = floatval($tokens[3])*1024;
						$used = $total-$avail;
					}
				}
			}
			else
			{
				$used*=1024;
				$total*=1024;
			}
		}
		return( is_null($used) ? false : array( "total"=>$total, "free"=>$total-$used ) );
	}
}

?>