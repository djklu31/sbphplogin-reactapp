<?php
require_once('funcs2.inc');
require_once('cls_sbsecure.php');
$_WEBUI_VER = '1.1.135';
$sbsecure = new SBSECURE;
$sConfigPath = __DIR__ . '/../../data/config.xml';
$accounts_xml_path = __DIR__ . '/../../data/accounts.xml';
$encoder_activation_path = '../data/encoder_activation.tmp';
//1803 => parent
//1802 => slave
$aDeviceTypes = array('encoder'=>'1802', 'decoder'=>'1801', 'sms'=>'1803', 'sms1'=>'1802', 'sfl'=>'8081');
$aReturn = array('msg'=>'', 'err'=>array(), 'debug'=>array());
$aHeader = array('online'=>false, 'bin_ver'=>'');
$aParam = array();

include_once(dirname(__FILE__) . "/IXR_Library.inc");


# patch
$_dec_id = 0;
$_pull_dec_filename = "/var/lib/avenir/PULL_DEC.xml";
if (isset($_SERVER['REQUEST_URI'])){
	if       (startsWith($_SERVER['REQUEST_URI'],'/decoder1/')){ //now used when multi-dec mode
		$aDeviceTypes['decoder'] = '1801';
		$_dec_id = 0;
		$_pull_dec_filename = "/var/lib/avenir/PULL_DEC_${_dec_id}.xml"; //req'd
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/decoder2/')){
		$aDeviceTypes['decoder'] = '1802';
		$_dec_id = 1;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/decoder3/')){
		$aDeviceTypes['decoder'] = '1803';
		$_dec_id = 2;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/decoder4/')){
		$aDeviceTypes['decoder'] = '1804';
		$_dec_id = 3;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/decoder5/')){
		$aDeviceTypes['decoder'] = '1805';
		$_dec_id = 4;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/decoder6/')){
		$aDeviceTypes['decoder'] = '1806';
		$_dec_id = 5;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/decoder7/')){
		$aDeviceTypes['decoder'] = '1807';
		$_dec_id = 6;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/decoder8/')){
		$aDeviceTypes['decoder'] = '1808';
		$_dec_id = 7;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/decoder9/')){
		$aDeviceTypes['decoder'] = '1809';
		$_dec_id = 8;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/decoder10/')){
		$aDeviceTypes['decoder'] = '1810';
		$_dec_id = 9;
	}
	if ($_dec_id>0) $_pull_dec_filename = "/var/lib/avenir/PULL_DEC_${_dec_id}.xml";
}

# patch
$_enc_id = 0;
if (isset($_SERVER['REQUEST_URI'])){
	if       (startsWith($_SERVER['REQUEST_URI'],'/encoder1/')){
		$aDeviceTypes['encoder'] = '1802';
		$_enc_id = 0;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/encoder2/')){
		$aDeviceTypes['encoder'] = '1803';
		$_enc_id = 1;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/encoder3/')){
		$aDeviceTypes['encoder'] = '1804';
		$_enc_id = 2;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/encoder4/')){
		$aDeviceTypes['encoder'] = '1805';
		$_enc_id = 3;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/encoder5/')){
		$aDeviceTypes['encoder'] = '1806';
		$_enc_id = 4;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/encoder6/')){
		$aDeviceTypes['encoder'] = '1807';
		$_enc_id = 5;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/encoder7/')){
		$aDeviceTypes['encoder'] = '1808';
		$_enc_id = 6;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/encoder8/')){
		$aDeviceTypes['encoder'] = '1809';
		$_enc_id = 7;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/encoder9/')){
		$aDeviceTypes['encoder'] = '1810';
		$_enc_id = 8;
	} elseif (startsWith($_SERVER['REQUEST_URI'],'/encoder10/')){
		$aDeviceTypes['encoder'] = '1811';
		$_enc_id = 9;
	}
}


function getSystemConfig($sFilePath){
	global $_WEBUI_VER;
	$dom = new domdocument();
	$aConfig = array();
	$webui_ver = $_WEBUI_VER;
	//Create config file if not exist.
	if(!file_exists($sFilePath)){
		$dom = new domdocument('1.0','UTF-8');
		$dom->formatOutput = true;
		$root = $dom->createElement('config');
		$root->setAttribute('sb_install_dir','/usr/local/bin');
		$root->setAttribute('actl3_default','/var/www/avenir/ACTL3');
		$root->setAttribute('device_ip','127.0.0.1');
		$root->setAttribute('platform','windows'); //Choices are 'osx' | 'windows' | 'unknown'
		$root->setAttribute('webui_ver', $webui_ver);
		$root->setAttribute('is3D','0');
		$dom->appendChild($root);

		$decoder = $dom->createElement('decoder');
		$decoder->setAttribute('sb_session_timeout','300'); //in seconds
		
		$webui = $dom->createElement('webui');
		$webui->setAttribute('login_autofill', false);
		$root->appendChild($decoder);
		$root->appendChild($webui);
		if($dom->save($sFilePath) === false){
			print 'Cannot create file at '.$sFilePath;
			exit(0);
		}
	}

	if($dom->load($sFilePath)) {
		$nConfig = $dom->getElementsByTagName('config')->item(0);
		$nDecoder = $dom->getElementsByTagName('decoder')->item(0);
		$nWebUI = $dom->getElementsByTagName('webui')->item(0);
		$aConfig['sb_install_dir'] = $nConfig->getAttribute('sb_install_dir');
		$aConfig['actl3_default'] = $nConfig->getAttribute('actl3_default');
		$aConfig['device_ip'] = $nConfig->getAttribute('device_ip');
		$aConfig['platform'] = $nConfig->getAttribute('platform');
		$aConfig['webui_ver'] = $webui_ver;
		$aConfig['warn30'] = intVal($nConfig->getAttribute('warn30'));
		$aConfig['uexp90'] = intVal($nConfig->getAttribute('uexp90'));
		$warndays = intval($nConfig->getAttribute('warndays'));
		$uexpdays = intval($nConfig->getAttribute('uexpdays'));
		if ($warndays<=0) $warndays = 30;
		if ($uexpdays<=0) $uexpdays = 90;
		$aConfig['warndays'] = $warndays;
		$aConfig['uexpdays'] = $uexpdays;
		$aConfig['is3D'] = (bool) intval($nConfig->getAttribute('is3D'));
		$aConfig['sb_session_timeout'] = intval($nDecoder->getAttribute('sb_session_timeout'));
		$aConfig['login_autofill'] = $nWebUI ? $nWebUI->getAttribute('login_autofill') : "";
		$aConfig['is_enc_dec_only'] = file_exists("/var/lib/avenir/ENCODER_ONLY") ? 1 : NULL;
		$aConfig['is_enc_dec_only'] = file_exists("/var/lib/avenir/DECODER_ONLY") ? 2 : $aConfig['is_enc_dec_only'] ;

	} else {
		print 'Cannot load config file at '.$sFilePath;
		exit(0);
	}

	// patch for remoteNode
	$aConfig['device_ip'] = _getDeviceIP();


	return $aConfig;
}

function setSCLoginAutofill($configPath, $state) {
	$dom = new domdocument();
	if($dom->load($configPath)) {
		$dom->formatOutput = true;
		$webui = $dom->getElementsByTagName('webui')->item(0);
		$webui->setAttribute('login_autofill', $state);
		$dom->save($configPath);
	}
}

function setUexp90($configPath, $state) {
	$dom = new domdocument();
	if($dom->load($configPath)) {
		$dom->formatOutput = true;
		$conf = $dom->getElementsByTagName('config')->item(0);
		if ($state) $conf->setAttribute('uexp90', '1');
		else        $conf->setAttribute('uexp90', '0');
		$dom->save($configPath);
	}
}

function setWarn30($configPath, $state) {
	$dom = new domdocument();
	if($dom->load($configPath)) {
		$dom->formatOutput = true;
		$conf = $dom->getElementsByTagName('config')->item(0);
		if ($state) $conf->setAttribute('warn30', '1');
		else        $conf->setAttribute('warn30', '0');
		$dom->save($configPath);
	}
}
function setWarnUexpDays($configPath, $w, $u, $to){
	$w = intval($w); $u = intval($u); if(0>=$w)$w=30;if(0>=$u)$u=90;
	$dom = new domdocument();
	if($dom->load($configPath)) {
		$dom->formatOutput = true;
		$conf = $dom->getElementsByTagName('config')->item(0);
		$conf->setAttribute('warndays', "$w");
		$conf->setAttribute('uexpdays', "$u");

		if ($to>=0){
			$dec = $dom->getElementsByTagName('decoder')->item(0);
			$dec->setAttribute('sb_session_timeout', "".$to); //in seconds
		}

		$dom->save($configPath);
	}
}

function setSessTimeout($configPath, $to){
	$to = intval($to);
	$dom = new domdocument();
	if($dom->load($configPath)) {
		$dom->formatOutput = true;
		$conf = $dom->getElementsByTagName('config')->item(0);
		if ($to>=0){
			$dec = $dom->getElementsByTagName('decoder')->item(0);
			$dec->setAttribute('sb_session_timeout', "".$to); //in seconds
		}
		$dom->save($configPath);
	}
}

function setSystemConfig($configPath, $aConfigs){
	$dom = new domdocument('1.0','UTF-8');
	$dom->formatOutput = true;
	if($dom->load($configPath)){
		$config = $dom->getElementsByTagName('config')->item(0);
		$config->setAttribute('actl3_default', $aConfigs['actl3_default']);
		$config->setAttribute('warn30'       , $aConfigs['warn30']);
		$config->setAttribute('uexp90'       , $aConfigs['uexp90']);
		//$aConfig['uexp90'] = $nConfig->getAttribute('uexp90');
		$config->setAttribute('warndays'       , $aConfigs['warndays']);
		$config->setAttribute('uexpdays'       , $aConfigs['uexpdays']);
		$webui = $dom->getElementsByTagName('webui')->item(0);
		$webui->setAttribute('login_autofill', $aConfigs['login_autofill']);
		if($dom->save($configPath)){
			$attrs = $config->attributes;
			$result = array();
			foreach ($attrs as $i => $attr)  {
				$result[$attr->name] = $attr->value;
			}
			return true;
		} else {
			echo 'Failed to overwrite device config for this machine.';
		}
	} else {
		echo 'Failed to read config file at '.$configPath;
	}
	return false;
}


function outJson($obj){
	header("content-type: text/javascript");
	$jsonpCallback = $_REQUEST['jsonp_callback']; //index name must match what's on client side jsonp url
	echo $jsonpCallback. '(' . json_encode($obj) . ');';
}


function setHeader($phpSend){
	global $aHeader;
	$bOnline = $phpSend->isConnected();
	if($bOnline) {
		$aHeader['online'] = $bOnline;
		$reply = $phpSend->sendCommand("get::info::version");
		$aHeader['bin_ver'] = isset($reply['result'])? $reply['result'] : $aHeader['bin_ver'];
	}
	return $bOnline;
}

function isAuth($username, $password){
	global $accounts_xml_path;
	$aResult = false;
	$dom = new domdocument();
	if($dom->load($accounts_xml_path)) {
		$users = $dom->getElementsByTagName('user');
		foreach($users as $user) {
			if($aResult === false && strtolower($user->getAttribute('username')) == strtolower($username) && $user->getAttribute('pass') == $password) {
				$aResult['name'] = $user->getAttribute('name');
				$aResult['username'] = $user->getAttribute('username');
				$aResult['type'] = $user->getAttribute('type');
				$now = date('Y.m.d H:i:s');
				$user->setAttribute('lastaccess',$now);
				$dom->save($accounts_xml_path);
			}
		}
	} else {
		doLog('fail to load accounts at '.$accounts_xml_path);
	}
	return $aResult;
}

function doLog($sMsg){
	global $aReturn;
	$aReturn['debug'][] = array('time'=>timestamp(), 'msg'=>$sMsg);
}

function out($obj, $key = 'msg'){
	global $aReturn;
	$aReturn[$key] = $obj;
}

function err($intCode, $sMsg){
	global $aReturn;
	$aReturn['err'][] = array('code'=>$intCode, 'msg'=>$sMsg);
}

function errFatal($intCode, $sMsg){
	global $aReturn;
	err($intCode, $sMsg);
	outJson($aReturn);
}

function iniParam(){
	global $aParam;
	foreach($_REQUEST as $key=>$value){
		$aParam[$key] = $value;
	}
}

function getParam($sKey){
	global $aParam;
	return (isset($aParam[$sKey]) ? $aParam[$sKey] : false);
}

function timestamp(){
	return date('Y-m-d H:i:s');
}

/* Return a list installed products.
 * If $bReturnAll is true then return a
 * list all supported products whether they are installed or not.
 */
function getInstalledProds($strInstallPath = '/usr/local/bin/', $bReturnAll = false){
	$aReturn = array();
	$aProds = array();
	$aProds['decoder'] = array();

	$aProds['decoder'][] = array('key'=>'DECODER', 'webpath'=>'decoder/', 'filename'=>'/usr/dec', 'label'=>'Decoder', 'installed'=>false, 'format'=>'');
	$aProds['encoder'][] = array('key'=>'ENCODER', 'webpath'=>'encoder/', 'filename'=>'/usr/enc', 'label'=>'Encoder', 'installed'=>false, 'format'=>'');
	
	foreach($aProds as $type=>&$aProd){
		foreach($aProd as $key=>&$aItem){
			$strFullPath = $strInstallPath.$aItem['filename'];
			$aItem['installed'] = file_exists($strFullPath);
			if($aItem['installed']){
				$aReturn[$type][] = $aItem;
			}
		}
	}

	if($bReturnAll) {
		return $aProds;
	} else {
		#return $aReturn;
		return $aProds;
	}
}

function killCmdDaemon(){
	$output = exe_wshshell('taskkill /im cmd.exe /f');
	return $output;
}

function killEDProcesses(){
	$output = exe_wshshell('taskkill /im Encoder* /f');
	$output = exe_wshshell('taskkill /im transport* /f');
}

function restartFD($batchFilePath, $strDeviceIp){
	global $aDeviceTypes;
	if(is_file($batchFilePath)){
		//Kill running processes of encoders and decoders
		killEDProcesses();
		sleep(2);
		$output = exe_wshshell('cmd.exe','/c start '. $batchFilePath);
		return $output;
	} else {
		echo 'Required batch file is missing: '.$batchFilePath;
		exit(0);
	}
}

function getFDStatus(){
	$aWini = get_win_ini();
	$bFound = false;
	if($aWini !== false){
		foreach($aWini as $catName=>$aCat){
			foreach($aCat as $key=>$value){
				if($key == 'FullDuplex'){
					if(trim($value) == '1') {
						$bFound = true;
						break 2;
					}
				}
			}
		}
	}
	return $bFound;
}

function setFDStatus($value){
	$aWini = get_win_ini();
	foreach($aWini as $catName=>&$aCat){
		$aCat['FullDuplex'] = $value;
	}
	set_win_ini($aWini);
}

function get_win_ini($file = 'C:/WINDOWS/win.ini'){

    // if cannot open file, return false
    if (!is_file($file))
        return false;

    $ini = file($file);

    // to hold the categories, and within them the entries
    $cats = array();

    foreach ($ini as $i) {
        if (@preg_match('/\[(.+)\]/', $i, $matches)) {
            $last = $matches[1];
        } elseif (@preg_match('/(.+)=(.+)/', $i, $matches)) {
            $cats[$last][$matches[1]] = trim($matches[2]);
        }
    }

    return $cats;

}

function set_win_ini($aIni, $file = 'C:/WINDOWS/win.ini'){
	$output = '';

	foreach($aIni as $catName=>$aCat){
		$output .= "[".$catName . "]\r\n";
		foreach($aCat as $key=>$value){
			$output .= $key."=".$value."\r\n";
		}
		$output .= "\r\n";
	}
	file_put_contents($file, $output);
	return $output;
}

/*
 * Return device webpath
 * if 2000.txt is successfully updated with the strId
 * else return empty string
 */
function setDeviceMode($strDeviceIp, $strInstallDir, $strId){
	//return 'restarting .....'.$strId;
	global $aDeviceTypes;
	set_time_limit(60);
	$maxWaitTime = 40;
	$waitTime = 3;
	$strReturn = '';
	$bSuccess = false;
	$str2kTextPath = $strInstallDir.'\2000.txt';
	//Build Retart Commands for all devices
	$aCmds = array();
	$aCmds['encoder'] = array(
		'ENCODER'=>'',
		'SD_ENCODER'=>'set::action::1903',
		'HD_ENCODER'=>'set::action::1902',
		'3D_ENCODER'=>'',
		'SMS'=>'setsmsmode',
		'DECODER'=>'set::action::1702',
		'SD_DECODER'=>'set::action::1905',
		'HD_DECODER'=>'set::action::1904',
		'3D_DECODER'=>'set::action::1906',
		'SD_FD'=>'\runfx.bat',
		'HD_FD'=>'\runfxhd.bat');
	$aCmds['decoder'] = array(
		'ENCODER'=>'set::action::4108',
		'SD_ENCODER'=>'set::action::4110',
		'HD_ENCODER'=>'set::action::4109',
		'3D_ENCODER'=>'',
		'SMS'=>'setsmsmode',
		'DECODER'=>'',
		'SD_DECODER'=>'set::action::4112',
		'HD_DECODER'=>'set::action::4111',
		'3D_DECODER'=>'',
		'SD_FD'=>'\runfx.bat',
		'HD_FD'=>'\runfxhd.bat');
	$aCmds['sms'] = array(
		'SMS'=>'setsmsmode',
		'DECODER'=>'setdecodermode',
		'SD_DECODER'=>'setdecodermode',
		'HD_DECODER'=>'setdecodermode',
		'3D_DECODER'=>'',
		'SD_FD'=>'\runfx.bat',
		'HD_FD'=>'\runfxhd.bat');
	$aCmds['fd'] = array(
		'ENCODER'=>'\runhx.bat',
		'SD_ENCODER'=>'\runhx.bat',
		'HD_ENCODER'=>'\runhx.bat',
		'3D_ENCODER'=>'\runhx.bat',
		'SMS'=>'\runhx.bat',
		'DECODER'=>'\runhx.bat',
		'SD_DECODER'=>'\runhx.bat',
		'HD_DECODER'=>'\runhx.bat',
		'3D_DECODER'=>'\runhx.bat',
		'SD_FD'=>'\runfx.bat',
		'HD_FD'=>'\runfxhd.bat');
	$aPorts = $aDeviceTypes;

	//Get Current Device Mode
	$str2kContent = trim(file_get_contents($str2kTextPath));
	$a2kTextContent = explode('_', $str2kContent);
	$strCurrMode = '';
	if($str2kContent != '' && count($a2kTextContent) > 0){
		$strCurrMode = strtolower($a2kTextContent[count($a2kTextContent)-1]);
	} else {
		return "cannot determine current running mode";
	}
	//Exception: 2000Text doesn't represent current state on SMS machines
	//Overriding currMode if this is a SMS machine
	$objSms1 = new CSendObject($strDeviceIp, $aPorts['sms']);
	$objDecoder = new CSendObject($strDeviceIp, $aPorts['decoder']);
	$strSmsPreviousMode = $strCurrMode;
	if($objSms1->isConnected() && !$objDecoder->isConnected()){
		if(file_put_contents($str2kTextPath, $strId) === false){
			return "could not set device mode, please try again.";
		}
		$strCurrMode = 'sms';
	}
	//Override currMode if this is a FullDuplex machine
	if(getFDStatus()){
		$strCurrMode = 'fd';
	}

	//Send Set As commands to device

	//Special handle to switch to a Full Duplex machine
	if($strId == 'SD_FD' || $strId == 'HD_FD'){
		$path = $strInstallDir.$aCmds['fd'][$strId];
		setFDStatus('1');
		killCmdDaemon();
		restartFD($path, $strDeviceIp);
		$objEncoder = new CSendObject($strDeviceIp, $aDeviceTypes['encoder']);
		$objDecoder = new CSendObject($strDeviceIp, $aDeviceTypes['decoder']);
		$timeStart = time();
		while(!($objEncoder->isConnected() && $objDecoder->isConnected())){
			if((time() - $timeStart) <= $maxWaitTime){
				sleep($waitTime);
			} else {
				return "Something went wrong. Encoder and Decoder did not start within $maxWaitTime seconds.";
			}
		}
		$bSuccess = true;

	//Handling device switching for Half Duplex machine
	} else if(isset($aCmds[$strCurrMode][$strId])){
		if(getFDStatus()){
			$path = $strInstallDir.$aCmds['fd'][$strId];
			if($strId == 'SD_FD' || $strId == 'HD_FD'){
				setFDStatus('1');
			} else {
				if(file_put_contents($str2kTextPath, $strId) !== false){
					setFDStatus('0');
				} else {
					return 'could not set value for 2000.txt';
				}
			}
			killCmdDaemon();
			restartFD($path, $strDeviceIp);
			$objEncoder = new CSendObject($strDeviceIp, $aDeviceTypes['encoder']);
			$objDecoder = new CSendObject($strDeviceIp, $aDeviceTypes['decoder']);
			$timeStart = time();
			while(!($objEncoder->isConnected() || $objDecoder->isConnected())){
				if((time() - $timeStart) <= $maxWaitTime){
					sleep($waitTime);
				} else {
					return "Something went wrong. Encoder or Decoder did not start within $maxWaitTime seconds.";
				}
			}
			$bSuccess = true;
		} else if($aCmds[$strCurrMode][$strId] != ''){
			$sleep = false;
			$phpSend = new CSendObject($strDeviceIp, $aPorts[$strCurrMode]);
			if($aCmds[$strCurrMode][$strId] == 'setsmsmode' || $aCmds[$strCurrMode][$strId] ==  'setdecodermode'){
				$phpSend = new CSendObject($strDeviceIp, $aPorts['sms']);
				$sleep = true;
			}
			if($phpSend->isConnected()){
				$phpSend->sendCommand($aCmds[$strCurrMode][$strId]);
				if($sleep && $aCmds[$strCurrMode][$strId] == 'setdecodermode'){
					$objDecoder = new CSendObject($strDeviceIp, $aDeviceTypes['decoder']);
					$timeStart = time();
					while(!$objDecoder->isConnected()){
						if((time() - $timeStart) <= $maxWaitTime){
							sleep($waitTime);
							$phpSend->sendCommand($aCmds[$strCurrMode][$strId]);
							$bSuccess = true;
						} else {
							$strReturn = "Something went wrong. Decoder did not start within $maxWaitTime seconds.";
							$bSuccess = false;
						}
					}

				}  else {
					$bSuccess = true;
				}

			} else {
				$strReturn = "device is offline";
			}
		} else {
			if(file_put_contents($str2kTextPath, $strId) !== false){
				$phpSend = new CSendObject($strDeviceIp, $aPorts[$strCurrMode]);
				if($phpSend->isConnected()){
					$phpSend->sendCommand('restart');
					$bSuccess = true;
				}
			} else {
				$strReturn = "could not set device mode, please try again.";
			}
		}
	}
	if($bSuccess){
		if(strstr($strId, 'ENCODER') !== false){
			$strReturn = 'encoder';
		} else if(strstr($strId, 'DECODER') !== false){
			$strReturn = 'decoder';
		} else if(strstr($strId, 'SMS') !== false){
			$strReturn = 'sms';
		} else if(strstr($strId, '_FD') !== false){
			$strReturn = 'fx';
		} else {
			$strReturn = 'Not Supported';
		}
	} else if(!$bSuccess && $strReturn == '') {
		$strReturn = "device mode switching failed";
	}

	return $strReturn;
}


function parseCSV($strCSV){
	$aReturn = array();
	if(strlen($strCSV) == 0){
		return false;
	}

	$tmp_lines = split("\n", trim($strCSV));
	for($i = 0; $i < count($tmp_lines); $i++){
		$keywords = preg_split('/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/', $tmp_lines[$i], -1, PREG_SPLIT_DELIM_CAPTURE);
		$aTasks = array();
		foreach ($keywords as $col) {
			array_push($aTasks, trim(trim($col), '"'));
		}
		array_push($aReturn, $aTasks);
	}
	return $aReturn;

}

function myPassthru($cmd){
	$default = ini_get ('display_errors');
	ini_set('display_errors', 1);
	ob_start();
	session_write_close();
	passthru($cmd);
	session_start();
	$strResult=ob_get_contents();
	ob_end_clean();
	ini_set('display_errors', $default);

	return $strResult;
}

/**
 *
 * @return array list of writable paths
 */
function getWhiteListPaths($bGetVolumesOnly = false){
	$strRaw = myPassthru('fbwfmgr /displayconfig');
	/* Response Sample:
	 File-based write filter configuration for the current session:
	filter state: enabled.
	overlay cache data compression state: disabled.
	overlay cache threshold: 128 MB.
	overlay cache pre-allocation: disabled.
	size display: actual mode.
	protected volume list:
	\Device\HarddiskVolume4 (C:)
	write through list of each protected volume:
	\Device\HarddiskVolume4:
	\Documents and Settings
	\Streambox
	\WINDOWS\system32\config
	\WINDOWS\win.ini
	\Apache2.2\htdocs\data

	File-based write filter configuration for the next session:
    filter state: enabled.
    overlay cache data compression state: disabled.
    overlay cache threshold: 128 MB.
    overlay cache pre-allocation: disabled.
    size display: actual mode.
    protected volume list:
      \Device\HarddiskVolume2 (C:)
      \Device\HarddiskVolume8 (E:)
    write through list of each protected volume:
      \Device\HarddiskVolume2:
        \Documents and Settings
        \Streambox
        \WINDOWS\system32\config
        \WINDOWS\win.ini
        \Apache2.2\htdocs\data
      \Device\HarddiskVolume8:
        \my_fun_dir2\readwrite.txt
	*/

	$aResult = array();
	$aLine = explode("\n", $strRaw);
	$bHeaderFound = false;
	$bVolumeDone = false;
	$aVolume = array();
	$aDriveLetters = array();
	$iCurrVolume = false;
	$slash = '\\';
	foreach($aLine as $strLine){
		error_log($strLine);
		$strLine = trim($strLine);
		if(!$bHeaderFound && stripos($strLine, 'protected volume list:') !== false){
			$bHeaderFound = true;
		} else if($bHeaderFound && !$bVolumeDone){

			//Collect all volumes
			if(!$bVolumeDone){
				//Is it the end of volume list?
				if(stripos($strLine, 'write through list of each protected volume:') !== false){
					$bVolumeDone = true;

				} else if(stripos($strLine, 'Device') == 1) {
					$aVolume[] = $strLine;
				}
			}
		} else if($bVolumeDone && !empty($aVolume)){
			if(stripos($strLine, $slash) === 0){
				//match volume
				if($iCurrVolume === false || stripos($strLine, ':') !== false){
					$iCurrVolume = false;
					for($i = 0; $i < count($aVolume); $i++){
						if(stripos($aVolume[$i], rtrim($strLine, ':')) !== false){
							$iCurrVolume = $i;
						}
					}
					//Format is different, terminate
					if($iCurrVolume === false){
						break;
					}
				} else{
					$strDriveLetter = substr($aVolume[$iCurrVolume], stripos($aVolume[$iCurrVolume], '(') + 1 , 1);
					$aDriveLetters[] = strtolower($strDriveLetter);
					$aResult[] = $strDriveLetter.':'.$strLine;
				}
			} else if(!empty($aResult)){
				//Last path entry. Stop here because the next set is "next session" instead of "current session"
				break;
			}
		}
	}
	if($bGetVolumesOnly){
		return $aDriveLetters;
	} else {
		return $aResult;
	}

}

function exe_wshshell($cmd, $strParam = '', $winStyle = 0, $waitToFinish = false ){
	$WshShell = new COM("WScript.Shell");
	$strCmd = "$cmd $strParam";
	$winStyle = 0;
	$waitToFinish = false;
	$WshShell->Run(trim($strCmd), $winStyle, $waitToFinish);
	return $strCmd;
}


function decode($data){
	global $sbsecure;
	return $sbsecure->decode($data);
}

function encode($data){
	global $sbsecure;
	return $sbsecure->encode($data);
}

function getpage()  {
	$path = $_SERVER["SCRIPT_NAME"];
	$page = @array_pop(explode("/", $path));
	return $page;
}

function createXmlAccounts($accounts_xml_path){
	$dom = new domdocument('1.0','UTF-8');
	$dom->formatOutput = true;
	$root = $dom->createElement('accounts');
	$dom->appendChild($root);
	// create default admin user with default password
	$user = $dom->createElement('user');
	$user->setAttribute('name','Administrator');
	$user->setAttribute('username','administrator');
	$user->setAttribute('pass','fe01ce2a7fbac8fafaed7c982a04e229');
	$user->setAttribute('type','admin');
	$user->setAttribute('lastaccess','n/a');
	$user->setAttribute('lastaccessip', getRemoteIp());

	// Automatic log view uptate state to save between logout and next login
	$log_state = $dom->createElement('log_display_state');
	$log_state->setAttribute('encoder_log_autoupdate', '0');
	$log_state->setAttribute('decoder_log_autoupdate', '0');
	$user->appendChild($log_state);

	// Preview auto-update state NAB2017 encoder feature
	$encoder_preview_node = $dom->createElement('encoder_preview');
	$encoder_preview_node->setAttribute('state', 0);
	$encoder_preview_node->setAttribute('update_period', 3000);
	$user->appendChild($encoder_preview_node);

	// create default operator user with default password
	$user2 = $dom->createElement('user');
	$user2->setAttribute('name','Guest');
	$user2->setAttribute('username','guest');
	$user2->setAttribute('pass','fe01ce2a7fbac8fafaed7c982a04e229');
	$user2->setAttribute('type','guest');
	$user2->setAttribute('lastaccess','n/a');
	$user2->setAttribute('lastaccessip', getRemoteIp());

	// Automatic log view uptate state to save between logout and next login
	$log_state2 = $dom->createElement('log_display_state');
	$log_state2->setAttribute('encoder_log_autoupdate', '0');
	$log_state2->setAttribute('decoder_log_autoupdate', '0');
	$user2->appendChild($log_state2);
	
	// Preview auto-update state NAB2017 encoder feature
	$encoder_preview_node2 = $dom->createElement('encoder_preview');
	$encoder_preview_node2->setAttribute('state', 0);
	$encoder_preview_node2->setAttribute('update_period', 3000);
	$user2->appendChild($encoder_preview_node2);
	
	$root->appendChild($user);
	$root->appendChild($user2);
	$dom->save($accounts_xml_path);
	return $dom;
}

function addExtraUserElement($dom, $user){
	// Automatic log view uptate state to save between logout and next login
	$log_state = $dom->createElement('log_display_state');
	$log_state->setAttribute('encoder_log_autoupdate', '0');
	$log_state->setAttribute('decoder_log_autoupdate', '0');
	$user->appendChild($log_state);

	// Preview auto-update state NAB2017 encoder feature
	$encoder_preview_node = $dom->createElement('encoder_preview');
	$encoder_preview_node->setAttribute('state', 0);
	$encoder_preview_node->setAttribute('update_period', 3000);
	$user->appendChild($encoder_preview_node);
}

function makeNewUserNode($dom, $name, $un, $pwd, $t){
	global $accounts_xml_path;
	if (0){
		$dom = new domdocument('1.0','UTF-8');
		if (!$dom->load($accounts_xml_path)) return NULL;
		$dom->formatOutput = true;
		$dom->load($accounts_xml_path);

		// check if username already exists
		$users = $dom->getElementsByTagName('user');
		foreach($users as $user){
			if($user->getAttribute('username') == $plist[2]){
				echo "<font color=\"red\">Username already exists, please use a different username.</font>";
				exit;
			}
		}
	}

	$user = $dom->createElement('user');
	$user->setAttribute('name', $name);
	$user->setAttribute('username', $un);
	$user->setAttribute('pass', md5($pwd));
	$user->setAttribute('type',$t);
	$user->setAttribute('lastaccess','n/a');

	addExtraUserElement($dom, $user);

	return $user;
}


function getUserConfigNode() {
	global $accounts_xml_path;
	$auth_defined = array_key_exists('auth', $_SESSION);
	// Check if session is active and user is authenticated
	if (!isset($auth_defined) || !$auth_defined || !$_SESSION['auth'])
		return NULL;
	$username = $_SESSION['username'];
	$dom = new domdocument();
	if (!$dom->load($accounts_xml_path))
		return NULL;
	$users = $dom->getElementsByTagName('user');
	foreach ($users as $l_user)
		if(strtolower($l_user->getAttribute('username')) == strtolower($username)) {
			$user = $l_user;
			break;
		}
	// User not found (just in case)
	if (!isset($user))
		return NULL;
	return $user;
}

function getLogAutoUpdateNode() {
	$user_node = getUserConfigNode();
	if (NULL != $user_node)
		$uau_node = $user_node->getElementsByTagName('log_display_state')->item(0);
	else
		$uau_node = NULL;
	return $uau_node;
}

function getEncoderLogAutoUpdate() {
	$enc_log_aupdate = getLogAutoUpdateNode();
	if (NULL == $enc_log_aupdate)
		return 0;
	return $enc_log_aupdate->getAttribute('encoder_log_autoupdate');
}

function getDecoderLogAutoUpdate() {
	$dec_log_aupdate = getLogAutoUpdateNode();
	if (NULL == $dec_log_aupdate)
		return 0;
	return $dec_log_aupdate->getAttribute('decoder_log_autoupdate');
}

function setEncoderLogAutoUpdate($newstate) {
	global $accounts_xml_path;
	$enc_log_aupdate = getLogAutoUpdateNode();
	if (NULL != $enc_log_aupdate) {
		$enc_log_aupdate->setAttribute('encoder_log_autoupdate', $newstate);
		$enc_log_aupdate->ownerDocument->save($accounts_xml_path);
	}
}

function setDecoderLogAutoUpdate($newstate) {
	global $accounts_xml_path;
	$dec_log_aupdate = getLogAutoUpdateNode();
	if (NULL != $dec_log_aupdate) {
		$dec_log_aupdate->setAttribute('decoder_log_autoupdate', $newstate);
		$dec_log_aupdate->ownerDocument->save($accounts_xml_path);
	}
}

function setSession($username, $password, $sessionName = 'streambox'){
	global $accounts_xml_path;
	startSession($sessionName);
	$password = md5($password);
	$isValid = false;

	if (webauth_available()){

		$conn = new IXR_Client('http://localhost:1951/');
		$r = $conn->query('auth', $username, $password, getRemoteIp());
		if (!$r){
			//echo "Error:";
			//echo $conn->getErrorCode(),":";
			//echo $conn->getErrorMessage();
			return $isValid;
		}
		$r = $conn->getResponse();
		if ($r){
			if ($r[0]==0){
				$isValid = true;
				$_SESSION['auth'] = $isValid;
				$_SESSION['username'] = $r[1][0];
				$_SESSION['name'] = $r[1][1];
				$_SESSION['type'] = $r[1][2];
				$_SESSION['bitrate'] = 0; // just to provide initial bitrate value displayed at top (not important)
				$_SESSION['buffer'] = 0; // just to provide initial buffer value displayed at top (not important)
				$now = date('Y.m.d H:i:s', time());
				$_SESSION['lastaccess'] = $now;
				$_SESSION['sessionip'] = getRemoteIp();
				unset($_SESSION['lockstate']);
				unset($_SESSION['lockstate_msg']);
				unset($_SESSION['emsg']);
				$_SESSION['remaindays'] = $r[2];
				$_SESSION['lastacttime'] = time();
			} elseif ($r[0]==-4){
				$_SESSION['lockstate'] = $r[1];
				$_SESSION['lockstate_msg'] = $r[2];
				unset($_SESSION['emsg']);
			} elseif ($r[0]==-6){
				unset($_SESSION['lockstate']);
				unset($_SESSION['lockstate_msg']);
				$_SESSION['remaindays'] = -1;
				unset($_SESSION['emsg']);
			} elseif ($r[0]==-5){
				unset($_SESSION['lockstate']);
				unset($_SESSION['lockstate_msg']);
				unset($_SESSION['remaindays']);
				$_SESSION['emsg'] = $r[1];
			} else {
				unset($_SESSION['lockstate']);
				unset($_SESSION['lockstate_msg']);
				unset($_SESSION['remaindays']);
				unset($_SESSION['emsg']);
			}
		}

	} else {
		$dom = new domdocument();
		if(!$dom->load($accounts_xml_path)) {
			$dom = createXmlAccounts($accounts_xml_path);
		}
		$users = $dom->getElementsByTagName('user');
		foreach($users as $user) 	{
			if(strtolower($user->getAttribute('username')) == strtolower($username) && $user->getAttribute('pass') == $password) {
				$isValid = true;
				$_SESSION['auth'] = $isValid;
				$_SESSION['name'] = $user->getAttribute('name');
				$_SESSION['username'] = $user->getAttribute('username');
				$_SESSION['type'] = $user->getAttribute('type');
				$_SESSION['bitrate'] = 0; // just to provide initial bitrate value displayed at top (not important)
				$_SESSION['buffer'] = 0; // just to provide initial buffer value displayed at top (not important)
				$now = date('Y.m.d H:i:s', time());
				$_SESSION['lastaccess'] = $now;
				$_SESSION['sessionip'] = getRemoteIp();
				$user->setAttribute('lastaccess',$now);
				$user->setAttribute('sessionip',getRemoteIp());
				$dom->save($accounts_xml_path);
			}
		}
	}


	return $isValid;
}

function startSession($name = 'streambox'){
	session_name($name);
	session_start();
}

function redirect($location){
	header('Location: '.$location);
}

function get2kText(){
	global $aConfig;
	$str2kText = @file_get_contents($aConfig['sb_install_dir'].'\2000.txt');
	if($str2kText === false){
		$str2kText = '';
	}
	return $str2kText;
}

function getRemoteIp(){
	$REMOTE_ADDR = '';
	if (isset($_SERVER['REMOTE_ADDR'])) $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else $HTTP_X_FORWARDED_FOR = '';

	if (!empty($HTTP_X_FORWARDED_FOR)) {
		$match = array();
		if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $HTTP_X_FORWARDED_FOR, $match)) {
			$REMOTE_ADDR = preg_replace($privateIpList, $REMOTE_ADDR, $match[1]);
			#$REMOTE_ADDR = preg_replace(self::$privateIpList, $REMOTE_ADDR, $match[1]);
		}
	}

	// darwin fix
	if ($REMOTE_ADDR == '::1' || $REMOTE_ADDR == 'fe80::1') {
		$REMOTE_ADDR = '127.0.0.1';
	}

	return $REMOTE_ADDR;
}


function getUserSessionIp($username){
	global $accounts_xml_path;
	$strResult = '';
	$dom = new domdocument();
	if(!$dom->load($accounts_xml_path)) {
		$dom = createXmlAccounts($accounts_xml_path);
	}

	$users = $dom->getElementsByTagName('user');
	foreach($users as $user) 	{
		if($user->getAttribute('username') == strtolower($username)) {
			$strResult = $user->getAttribute('sessionip');
		}
	}

	return $strResult;
}


function _getDeviceIP(){
	#global $aConfig;
	global $aDeviceTypes;
	$r = "127.0.0.1";
	$phpSend = new CSendObject('127.0.0.1', $aDeviceTypes['encoder']);
	if (startsWith($_SERVER['REQUEST_URI'],'/remoteNode/')){
		if($phpSend->isConnected()){
			$reply = $phpSend->sendCommand("get::decoderip");
			return $reply['result'];
		}
	}
	return $r;
}

function get_app_head_logo_fname(){
	if (file_exists("/var/lib/avenir/conf/HAS_CDI"))
		return "common/images/streambox_spectra_vm_mc.png" ;
	else
		return "common/images/chromaLogoS.png" ;
}

function get_mc_head_logo_fname(){
	if (file_exists("/var/lib/avenir/conf/HAS_CDI"))
		return "common/images/streambox_spectra_vm_mc.png" ;
	else
		return "common/images/streambox_multichannel_banner.png";
}

function webauth_available(){
	if (!file_exists("/etc/systemd/system/webui-auth.service")) return 0;
	#if (!file_exists("/etc/systemd/system/multi-user.target.wants/webui-auth.service")) return 0;
	$x = shell_exec("systemctl is-active webui-auth");
	#if ($x === "active") return 1;
	$conn = new IXR_Client('http://localhost:1951/');
	$r = $conn->query('app_ver');
	if ($r) return 1;
	return 0;
}

function _update_lastacttime(){
	if (!empty($_SESSION['auth'])){
		$_SESSION['lastacttime'] = time();
	}
}
function _inactive_dur(){
	if (!empty($_SESSION['lastacttime'])){
		return time() - $_SESSION['lastacttime'];
	}
	return -1;
}


?>
