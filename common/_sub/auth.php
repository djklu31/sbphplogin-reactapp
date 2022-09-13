<?php

//include_once("../common/includes/IXR_Library.inc");
include_once(dirname(__FILE__) . "/../includes/IXR_Library.inc");
include_once(dirname(__FILE__) . "/../includes/breached_pwd_check.php");


if (!isset($ir)) return;
if (!$ir) return;



function _sub_process($ir){
	global $sConfigPath;


	$conn = new IXR_Client('http://localhost:1951/');
	$a = $ir[3];

	//print_r($ir); exit;
	//print_r($a); exit;

	switch($a[0]){

	  case "fetchInfo":
		$r = $conn->query('fetchInfo',$a[1]);
		if (!$r){
			echo "Error:";
			echo $conn->getErrorCode(),":";
			echo $conn->getErrorMessage();
			exit;
		}
		$r = $conn->getResponse();
		echo "OK:", json_encode($r);
		exit;
		break;

	  case "fetchAll":
		$r = $conn->query('fetchAll');
		if (!$r){
			echo "Error:";
			echo $conn->getErrorCode(),":";
			echo $conn->getErrorMessage();
			exit;
		}
		$r = $conn->getResponse();
		echo "OK:", json_encode($r);
		exit;
		break;


	  case "setpwd":
		$uname = $_SESSION['username'];
		#TODO: password length check done only when it's turned on

		if (check_breached_pwd($a[1])){ echo "ERR: breached password"; exit; }
		if (strlen($a[1])<12){echo "ERR:",json_encode(' short password (minimum len: 12 chars)'); exit; }

		$r = $conn->query('setpwd', $uname,  md5($a[1]));
		if (!$r){
			echo "ERR:";
			echo $conn->getErrorCode(),":";
			echo $conn->getErrorMessage();
			exit;
		}
		$r = $conn->getResponse();
		if ($r){
			echo "ERR:", json_encode($r);
		} else {
			echo "OK: password has been changed successfully";
		}
		exit;
		break;

	  case "setexp":
		//if ( ($_SESSION['type'] != "admin") && ($_SESSION['username']!=$plist[3] ){
		if ($_SESSION['type'] != "admin"){
			echo "ERR: not admin";
			exit;
		}
		$wd = intval($a[1]);
		$ud = intval($a[2]);
		$to = intval($a[3]);
		if ($wd<=0 || $ud<=0 || $to<0){
			echo "ERR: duration (days) must be positive value";
			exit;
		}
		if ($wd>=$ud){
			echo "ERR: expiration duration (days) must be greater than warning";
			exit;
		}
		setWarnUexpDays($sConfigPath, $wd, $ud, $to);
		echo "OK: update is done successfully.";

		exit;
		break;


	  case "edituser":
		//if ( ($_SESSION['type'] != "admin") && ($_SESSION['username']!=$plist[3] ){
		if ($_SESSION['type'] != "admin"){
			echo "ERR: not admin";
			exit;
		}

		if (check_breached_pwd($a[4])){ echo "ERR: breached password"; exit; }
		if (strlen($a[4])<12){echo "ERR:",json_encode(' short password (minimum len: 12 chars)'); exit; }

		if ($a[4]) $a[4] = md5($a[4]);

		$r = $conn->query('edituser', $a);
		if (!$r){
			echo "ERR:";
			echo $conn->getErrorCode(),":";
			echo $conn->getErrorMessage();
			exit;
		}
		$r = $conn->getResponse();
		if ($r){
			echo "ERR:", json_encode($r);
		} else {
			echo "OK: user data changed successfully.";
		}
		exit;
		break;

	  case "deleteuser":
		if ($_SESSION['type'] != "admin"){
			echo "ERR: not admin";
			exit;
		}

		$uid = $a[1];
		if (in_array($uid, array('administrator', '  guest  '))){
			echo "ERR: admin/guest account cannot be deleted";
			exit;
		}

		$r = $conn->query('deleteuser', $uid);
		if (!$r){
			echo "ERR:";
			echo $conn->getErrorCode(),":";
			echo $conn->getErrorMessage();
			exit;
		}
		$r = $conn->getResponse();
		if ($r){
			echo "ERR:", json_encode($r);
		} else {
			echo "OK:"; //, json_encode($r);
		}
		exit;
		break;


	  case "adduser":
		if ($_SESSION['type'] != "admin"){
			echo "ERR: not admin";
			exit;
		}

		if (check_breached_pwd($a[4])){ echo "ERR: breached password"; exit; }
		if (strlen($a[4])<12){echo "ERR:",json_encode(' short password (minimum len: 12 chars)'); exit; }

		if ($a[4]) $a[4] = md5($a[4]);

		$r = $conn->query('adduser', $a);
		if (!$r){
			echo "ERR:";
			echo $conn->getErrorCode(),":";
			echo $conn->getErrorMessage();
			exit;
		}
		$r = $conn->getResponse();
		if ($r){
			echo "ERR:", json_encode($r);
		} else {
			echo "OK: New user account has been created";
		}
		exit;
		break;


	  case "setSessTimeout":
		//if ( ($_SESSION['type'] != "admin") && ($_SESSION['username']!=$plist[3] ){
		if ($_SESSION['type'] != "admin"){
			echo "ERR: not admin";
			exit;
		}
		setSessTimeout($sConfigPath, intval($a[1]));
		echo "OK: update is done successfully.";
		exit;
		break;

	}
}


?>
