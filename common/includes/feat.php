<?php

include_once("phpsock.inc");


if (!isset($phpSend )){
	//$phpSend = new CSendObject($aConfig['device_ip'], $aDeviceTypes['encoder']);
	$phpSend = new CSendObject("127.0.0.1", 1801);
}


$feaStruct = array(

	array(
		 "label" => "Main"
		,"items" => [ 
			 ["Encoder","Encoder","Encoding"]
			,["Decoder","Decoder","Decoding"]
			,["Duplex","Duplex","Full Duplex"]
		]
	)

	,
	array(
		 "label" => "Video"
		,"items" => [ 
			 ["EnableSD","SD Video","Standard Definition"]
			,["EnableED","ED Video", "Extended Definition (480p, 525p, 640x480p)"]
			//,["", "PC Video","1024x768 etc"]
			,["EnableHD","HD","1920x1080, 1280x720"]
			,["EnableUHD","UHD","3840x2160"]
			,["Enable2K","DCI 2K","2048x1080"]
			,["Enable4K","DCI 4K","4096x2160"]
			,["Enable3G","3G SDI","SDI 3G support"]
			,["Enable12G","12G SDI","SDI 12G support"]
			,["EnableDCI","DCI Legacy","DCI for Legacy S/W"]
			//,["EnableDCI","DCI","Digital Cinema (2048×1080 24fps)"]
			,["Enable3D","3D", "Stereo video"]
		]
	)

	,
	array(
		 "label" => "Color Profile"
		,"items" => [ 
			 ["Enable422", "4:2:2","4:2:2 Color"]
			,["Enable444", "4:4:4","4:4:4 12 bit"]
			,["EnableHDR", "HDR","10-bit ACT-L5"]
			,["DolbyVision", "Dolby Vision","CMU and Tunneling"]
		]
	)

	,
	array(
		 "label" => "Audio"
		,"items" => [ 
			 ["Audio4ch", "4 Channel","4 channel audio"]
			,["Audio8ch", "8 Channel","8 channel audio"]
			,["Audio16ch", "16 Channel","16 channel audio"]
		]
	)

	,
	array(
		 "label" => "Encryption"
		,"items" => [ 
			 ["EnableAES", "AES 128-bit",""]
			,["EnableAES192", "AES 192-bit",""]
			,["EnableAES256", "AES 256-bit",""]
		]
	)

);


class EFeat {
	var $fname = "";
	var $label = "";
	var $desc  = "";
	var $u = 0;
	var $et = 0;
	var $rDays= 0;
	function __construct($fn, $l, $de){
		$this->fname = $fn;
		$this->label = $l;
		$this->desc  = $de;
	}
};


class Featcat {
	var $catname = "";
	var $feats = [];

	function __construct($cn){
		$this->catname = $cn ;
	}

	function addFeat($fn, $l, $de){
		$a = new EFeat($fn, $l, $de);
		$this->feats[] = $a;
	}

	function get(){
		global $phpSend;
		foreach($this->feats as $feat){
			$r = $phpSend->sendCommand("get::FeatureTime::" . $feat->fname);
			$r = $r['result'];
			$rr = preg_match("/^(-?\\d+):(\\d+):(\\d+):(\\d+)$/", $r, $m);
			$t = time();
			$u = 0;
			$expTime = 0;
			$rDays   = 0;
			if ($rr){
				$ifEnabled     = intval($m[1]);
				$enterTime     = intval($m[2]);
				$activatedTime = intval($m[3]);
				$expTime       = intval($m[4]);
				$rDays         = 0;
				$et = $expTime;
				// echo $feat->fname, " ", $et-$t, " ", $t, "\n";
				switch($ifEnabled){
				  case -1:
				  case 0:
					if ($t > $expTime) $u = -1; //expired
					else $u = 0; //disabled
					break;
				  case 1:
					if ($expTime == 4294967295)  $u = 1;  // enabled
					elseif ($t - $et > 0)        $u = -1; // expired
					elseif ($et - $t < 48*3600)  $u = 2;  // almost expires
					elseif ($et - $t < 7*24*3600)$u = 3;  // will expire within week
					else                         $u = 4;  // effective more than a week
				}
				if ($u>=2 || $u==-1)
					$rDays = ceil(($expTime - time()) / (24*60*60) );
			}
			$feat->u = $u;
			$feat->et = $expTime;
			$feat->rDays = $rDays;
		}
	}

};



class FeatMan {
	var $cats  = [];

	function __construct(){
		global $feaStruct;
		foreach($feaStruct as $cat){
			$catlabel = $cat["label"];
			$c = new Featcat($catlabel);
			foreach($cat["items"] as $f){
				$i = new EFeat($f[0], $f[1], $f[2]);
				$c->feats[] = $i;
			}
			$this->cats[] = $c;
		}
	}

	function get(){
		global $phpSend;
		//TODO: check connection
		if (! $phpSend->isConnected()) return;
		foreach($this->cats as $cat){
			$cat->get();
		}
	}

	function dp(){
		foreach($this->cats as $c){
			echo $c->catname , "\n";
			foreach($c->feats as $f){
				printf("\t%12s %14s %d %d \n", $f->fname, $f->label, $f->u, $f->rDays);
			}
		}
	}

	function need_act(){
		$r = 1;
		$c = $this->cats[0];
		foreach($c->feats as $f){
			if ($f->u>=1) $r = 0;
		}
		return $r;
	}

	function list_expired(){
		$r = array();
		foreach($this->cats as $c){
			foreach($c->feats as $f){
				//printf("\t%12s %14s %d %d \n", $f->fname, $f->label, $f->u, $f->rDays);
				if ($f->u==-1) $r[] = $f;
			}
		}
		return $r;
	}

	function list_will_expire_week(){
		$r = array();
		foreach($this->cats as $c){
			foreach($c->feats as $f){
				//printf("\t%12s %14s %d %d \n", $f->fname, $f->label, $f->u, $f->rDays);
				if ($f->u==3) $r[] = $f;
			}
		}
		return $r;
	}

	function list_almost_expires(){
		$r = array();
		foreach($this->cats as $c){
			foreach($c->feats as $f){
				//printf("\t%12s %14s %d %d \n", $f->fname, $f->label, $f->u, $f->rDays);
				if ($f->u==2) $r[] = $f;
			}
		}
		return $r;
	}

	function rep1(){
		$na = $this->need_act();
		$el = $this->list_expired();
		$al = $this->list_almost_expires();
		$wl = $this->list_will_expire_week();
		$el = array_map(function($f){return $f->label;}, $el);
		$al = array_map(function($f){return $f->label;}, $al);
		$wl = array_map(function($f){return $f->label;}, $wl);
		//print_r($el);
		return array($na, $el, $al, $wl);
	}

};


function _get_all_features_status_(){
	global $featureNameList;
	foreach ($featureNameList as $i){
		$r = $phpSend->sendCommand("get::FeatureTime::" . $i);
		$r = $r['result'];
		$rr = preg_match("/^(-?\\d+):(\\d+):(\\d+):(\\d+)$/", $r, $m);
		$t = time();
		$u = 0;
		$expTime = 0;
		$rDays   = 0;
		if ($rr){
			$ifEnabled     = intval($m[1]);
			$enterTime     = intval($m[2]);
			$activatedTime = intval($m[3]);
			$expTime       = intval($m[4]);
			$rDays         = 0;
			$et = $expTime;
			switch($ifEnabled){
			  case -1:
			  case 0:
				if ($t > $expTime) $u = -1; //expired
				else $u = 0; //disabled
				break;
			  case 1:
				if ($expTime == 4294967295)  $u = 1;
				elseif ($t - $et > 0)        $u = -1; // expired
				elseif ($et - $t < 48*3600)  $u = 2;  // almost expires
				elseif ($et - $t < 7*24*3600)$u = 3;  // will expire within week
				else                         $u = 4;  // effective more than a week
			}
			if ($u>=2 || $u==-1)
				$rDays = ceil(($expTime - time()) / (24*60*60) );
		}
		//echo $i, $u, $et, $rDays, "\n";
	}
}




if(php_sapi_name()==="cli"){
	$x = new FeatMan();
	$x->get();
	$x->dp();

	if (0){
		echo "\n";

		$el = $x->list_expired();
		foreach($el as $e){
			printf("  %s expired\n", $e->fname);
		}

		$el = $x->list_almost_expires();
		foreach($el as $e){
			printf("  %s is almost expired\n", $e->fname);
		}

		$el = $x->list_will_expire_week();
		foreach($el as $e){
			printf("  %s will expire within week\n", $e->fname);
		}
	}

	print_r($x->rep1());

}



?>
