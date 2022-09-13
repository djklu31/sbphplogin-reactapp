<?php

//include_once("../common/includes/IXR_Library.inc");
include_once(dirname(__FILE__) . "/../includes/IXR_Library.inc");
include_once(dirname(__FILE__) . "/../includes/breached_pwd_check.php");


if (!isset($ir)) return;
if (!$ir) return;


function _sub_process($ir){
	global $sConfigPath;
	global $aConfig;
	global $aDeviceTypes;

	$a = $ir[3];

	$phpSend = new CSendObject($aConfig['device_ip'], $aDeviceTypes['encoder']);

	switch($a[0]){
	  case "set":
		$t = $a[1];
		if ( in_array($t, array("0","1","2","3","4"))){
			$reply = $phpSend->sendCommand("set::EncryptionMode::" . $t); 
		}
		echo "t=". $t;
		//sleep(2); //need to wait for propagation to reload current mode

		$NOT_TOUCH = "_use_existing_pwd_";
		$ekey = $a[2];
		if ($ekey != $NOT_TOUCH){
			$phpSend->sendCommand("set::EncryptionKey::".$ekey); 
		}
		echo "OK:";
		//echo "ERR:";
		exit;
		break;
	}

}


?>
