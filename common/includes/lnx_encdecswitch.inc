<?php
include_once("IXR_Library.inc");

function switchto($e) {
	$actMsg = "FAIL";
	$_abort = NULL;
	$rs = "";

	$actl5flagfile = "/var/lib/avenir/ACTL5";
	$enclinkabsname3 = '/usr/local/bin/usr/enc';
	$declinkabsname3 = '/usr/local/bin/usr/dec';
	$enclinkabsname5 = '/usr/local/bin/usr/actl5enc';
	$declinkabsname5 = '/usr/local/bin/usr/actl5dec';


	$enclinkabsname = '/usr/local/bin/usr/enc';
	$declinkabsname = '/usr/local/bin/usr/dec';
	$_enc = null;
	$_dec = null;
	$enclinkfname = null;
	$declinkfname = null;

	$MUL_ENC_F = "/var/lib/avenir/MULTI_ENC";
	$MUL_DEC_F = "/var/lib/avenir/MULTI_DEC";

	$linkabsname = '/usr/local/bin/usr/LinuxEncoder.exe';
	$linkfname = "-";
	if (is_link($linkabsname)){
		$linkfname = readlink($linkabsname);
		$linkfname = basename($linkfname);
	}

	if (file_exists($actl5flagfile)) $isACTL5 = 1;
	else $isACTL5 = 0;

	if (file_exists("/var/lib/avenir/APP_IS_SERVICE")){
		// must stop BEFORE link is changed
		$client = new IXR_Client('http://localhost:1912/');
		$r = $client->query('stopEncoder');
		sleep(4);
	}

	if ("ENCODER" == $e){
		if ($isACTL5) $target = $enclinkabsname5;
		else $target = $enclinkabsname3;
		$rs = "encoder";
		if (file_exists($MUL_DEC_F)) rename($MUL_DEC_F, $MUL_ENC_F);
	} elseif ("DECODER" == $e){
		if ($isACTL5) $target = $declinkabsname5;
		else $target = $declinkabsname3;
		$rs = "decoder";
		if (file_exists($MUL_ENC_F)) rename($MUL_ENC_F, $MUL_DEC_F);
	} else {
		$_abort = 1;
		$actMsg = "ERR: wrong app ID" ;
	}

	if (!isset($_abort)){
		if (!is_link($target)){
			$_abort = 1;
			$actMsg = "ERR: app not linked" ;
		} else {
			$f = basename(readlink($target));
			$ll = $linkabsname;
			$actMsg = $rs;
			unlink($ll);
			symlink("../".$f ,$ll);
			$client = new IXR_Client('http://localhost:1912/');
			if (file_exists("/var/lib/avenir/APP_IS_SERVICE")){
				if       ("ENCODER" == $e){
					if (file_exists($MUL_ENC_F)){
						$r = $client->query('kill_dec');
						$r = $client->query('run_menc');
					} else {
						$r = $client->query('resumeEncoder');
					}

				} elseif ("DECODER" == $e){
					if (file_exists($MUL_DEC_F)){
						$r = $client->query('kill_enc');
						$r = $client->query('run_mdec');
					} else {
						$r = $client->query('resumeEncoder');
					}
				}
			} else {
				$r = $client->query('killEncoder');
			}
		}
	}

	return $actMsg;

	if ("ENCODER" == $e){
		$ll = $enclinkabsname;
		if (is_link($ll)){
			$f = basename(readlink($ll));
			$actMsg = "encoder";
			$ll = $linkabsname;
			unlink($ll);
			symlink("../".$f ,$ll);
			$client = new IXR_Client('http://localhost:1912/');
			$r = $client->query('killEncoder');
		}
	}
	elseif ("DECODER" == $e){
		$ll = $declinkabsname;
		if (is_link($ll)){
			$f = basename(readlink($ll));
			$actMsg = "decoder";
			$ll = $linkabsname;
			unlink($ll);
			symlink("../".$f ,$ll);
			$client = new IXR_Client('http://localhost:1912/');
			$r = $client->query('killEncoder');
		}
	}

	return $actMsg;
}

function _killEncoder(){
	$client = new IXR_Client('http://localhost:1912/');
	$r = $client->query('killEncoder');
}

function _rebootOS(){
	$client = new IXR_Client('http://localhost:1912/');
	$r = $client->query('reboot');
}

function switchActl35($to5){

	$_abort = NULL;
	$actMsg = "";
	$isACTL5 = 0;
	$actl5flagfile = "/var/lib/avenir/ACTL5";

	if (file_exists($actl5flagfile)){ $isACTL5 = 1; }

	$enclinkabsname3 = '/usr/local/bin/usr/enc';
	$declinkabsname3 = '/usr/local/bin/usr/dec';
	$enclinkabsname5 = '/usr/local/bin/usr/actl5enc';
	$declinkabsname5 = '/usr/local/bin/usr/actl5dec';

	$linkabsname = '/usr/local/bin/usr/LinuxEncoder.exe';
	$linkfname = "-";
	if (is_link($linkabsname)){
		$linkfname = readlink($linkabsname);
		$linkfname = basename($linkfname);
	}

	$fDE = 0;
	$aa = strpos($linkfname, 'Encoder');
	if (is_int($aa)) $fDE = 1;
	$aa = strpos($linkfname, 'Decoder');
	if (is_int($aa)) $fDE = 2;

	if ($to5){
		$target = $enclinkabsname5;
		if ($fDE===2) $target = $declinkabsname5;
	} else {
		$target = $enclinkabsname3;
		if ($fDE===2) $target = $declinkabsname3;
	}

	if (!isset($_abort)){
		if (!is_link($target)){
			$_abort = 1;
			//$actMsg = "ERR: wrong link";
		} else {
			$f = basename(readlink($target));
			$ll = $linkabsname;
			unlink($ll);
			symlink("../".$f ,$ll);
			$actMsg = "Switched to: " . $f;
			if ($to5) touch($actl5flagfile);
			else unlink($actl5flagfile);
		}
	}

}




?>
