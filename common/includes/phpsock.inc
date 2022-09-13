<?php
	class CSendObject {
		function sendMessage($message) {
			//$this->String2File($message, '../data/log_cmd.txt', 'a');
			$sock = @fsockopen($this->fHost, $this->fPort, $e1, $e2, 0.5);
			if ($sock != false) {
				$ans = fwrite($sock, $message);
				$l = 1024;
				$message = "";
				$message1 = "";
				$len = $l;

				while(!feof($sock)) {
					$message1 = "";
					$message1 = fread($sock, $l);
					if ($message1 == FALSE)
						break;
					$len = strlen($message1);
					$message .= $message1;
				}
				fclose($sock);
			}
			else
				$message = "";

			return $message;
		}

		function CSendObject($host, $port)
		{
			$this->fHost = $host;
			$this->fPort = $port;
		}

		function sendCommand($command)
		{

			$reply = $this->sendMessage($command);
			//error_log('SendCommand Org'. $reply);
			$reply = explode("&", $reply);
			foreach ($reply as $pair)
			{
				$pair1 = explode("=", $pair);
				if(count($pair1) >= 2){
					$r[$pair1[0]] = $pair1[1];
				} else {
					$r[] = $pair1[0];
				}
			}
			return $r;
		}
		
		function sendCommand2($command)
		{
			return $this->sendMessage($command);
		}

		function processReplyAmp($reply){
			//error_log('Amp Org : '.$reply);
			//Treat Delimiter as '='
			//Replace all '&' with '%26' except the last one or the only one.
			//merge pairs back with '=' being delimiter
			//$reply = str_replace('::', ';', $reply);
			$reply = explode("=", $reply);
			$tmp = $reply;
			if(count($reply) >= 2){
				$tmp = array();
				foreach ($reply as $strReply){

					//Escape all & chars but the last one when there's two or more
					$ocr = substr_count($strReply, '&');
					if($ocr >= 2){
						$strReply = preg_replace('/&/', '%26', $strReply, $ocr -1);
					}

					//Escape all & chars within :: separated values except the ending &
					$aReply = explode('::', $strReply);
					for($i = 0; $i < count($aReply); $i++){
						if($i != (count($aReply) - 1)){
							$aReply[$i] = preg_replace('/&/', '%26', $aReply[$i]);
						}
					}
					$strReply = implode('::', $aReply);

					$tmp[] = $strReply;
				}
			}
			$reply = implode('=', $tmp);

			//error_log('Amp Converted: '.$reply);
			$reply = explode("&", $reply);
			foreach ($reply as $pair) {
				$pair1 = explode("=", $pair);
				if(count($pair1) >= 2){
					//Earlier, all '&' are converted to '%26' and converting back.
					$pair1[1] = str_replace('%26', '&', $pair1[1]);
					$r[$pair1[0]] = $pair1[1];
				} else {
					$r[] = $pair1[0];
				}
			}
			return $r;
		}

		function sendCommandPlaylist($command)
		{
			$reply = $this->sendMessage($command);
			return $this->processReplyAmp($reply);

		}

		function isConnected()
		{
			$sock = @fsockopen($this->fHost, $this->fPort, $e1, $e2, 0.5);
			if ($sock != false){
				fclose($sock);
				return true;
			}
			return false;
		}

		function getSystemInfo()
		{
			$aSysInfo = array();
			$cmdReply = array();
			$cmdReply = $this->sendCommand("get::ishdtv");
			$aSysInfo['ishdtv'] = $cmdReply['result'];
			$cmdReply = $this->sendCommand("get::is3D");
			$aSysInfo['is3D'] = $cmdReply['result'];
			if ('unknown' == $cmdReply['result'])
				$aSysInfo['is3D'] = '0';
			$cmdReply = $this->sendCommand("get::systemname");
			$aSysInfo['name'] = $cmdReply['result'];
			$cmdReply = $this->sendCommand("get::systemlocation");
			$aSysInfo['location'] = $cmdReply['result'];
			$cmdReply = $this->sendCommand("get::systemserial");
			$aSysInfo['serial'] = $cmdReply['result'];
			$cmdReply = $this->sendCommand('get::info::version');
			$aSysInfo['version'] = $cmdReply['result'];
			$cmdReply = $this->sendCommand('get::info::bd');
			$aSysInfo['build_date'] = $cmdReply['result'];

			$cmdReply = $this->sendCommand('get::app_version');
			$r = $cmdReply['result'];
			if ($r!="unknown" && $r!="unsupported") $aSysInfo['appversion'] = $r;

			$cmdReply = $this->sendCommand('get::video-bits');
			$rr = $cmdReply['result'];
			if ($rr=="unsupported" || $rr=="unknown" || $rr==""){
				$aSysInfo['bitdepth_ok'] = '0';
			} else $aSysInfo['bitdepth_ok'] = '1';
			return $aSysInfo;
		}

		function String2File($sIn, $sFileOut, $writeMethod = 'wa+') {
		  $rc = false;
		  do {
			if (!($f = fopen($sFileOut, $writeMethod))) {
			  $rc = 1; break;
			}
			date_default_timezone_set('UTC');
			if (!fwrite($f, date('m/d h:i:s',time()).' '.$sIn."\r\n")) {
			  $rc = 2; break;
			}
			$rc = true;
		  } while (0);
		  if ($f) {
			fclose($f);
		  }
		  return ($rc);
		}
	};



?>
