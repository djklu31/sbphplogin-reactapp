<?php

$_ENCODER_LABEL_FILE = "/var/lib/avenir/conf/enc_labels.xml";


function load_encoder_labels($f=null){
	global $_ENCODER_LABEL_FILE;
	$d = null;
	if (is_null($f)) $f = $_ENCODER_LABEL_FILE;
	if (file_exists($f))
		$d = simplexml_load_file($f);
	
	if (!$d){
		$d = simplexml_load_string("<encoders>\n</encoders>");
		$d->asXml($f);
	}
	return $d;
}


function save_encoder_labels($R, $f=null){
	global $_ENCODER_LABEL_FILE;
	if (is_null($f)) $f = $_ENCODER_LABEL_FILE;
	$dom = new domdocument('1.0','UTF-8');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($R->asXML());
	$dom->save($f);
	chmod($f, 0666);
}


function _addEncoderLabel($R, $idx, $label){
	$c = $R->addChild('encoder'. $idx, $label);
	if (0){
		$c->addAttribute("ip", $ip);
		$c->addAttribute("port", $port);
		$c->addAttribute("ttl", $ttl);
		$c->addAttribute("packetSize", $packetSize);
		$c->addAttribute("stuff", $stf);
		//$c->addAttribute("name", $name);
	}
	return $c;
}

function setEncoderLabel($R, $idx, $label){
	$c =  $R->xpath("encoder" . strval($idx));
	if ($c){
		$c[0][0] =  $label;
	} else {
		_addEncoderLabel($R, $idx, $label);
	}
}


function arrayEncoderLabels($R, $_p=0){
	$ll = array();;

	foreach ($R->children() as $k => $v){
		//echo "exe:", $k, "\n";
		$rr = preg_match("/^encoder(\\d+)$/", $k, $m);
		if (!$rr) continue;
		$idx = $m[1];
		//echo $idx, ":", $v, "\n";
		$ll[intval($idx)] = $v;
	}
	return $ll;

	for($i=0; $i<100; $i++){


		$r = $R->encoder[$i]; 
		if (!$r) break;
		#print $i . "\n";
		$ip      = (string) $r['ip'];
		$port    = (int)    $r['port'];
		$ttl     = (int)    $r['ttl'];
		$psize   = (int)    $r['packetSize'];
		$stf     = (int)    $r['stuff'];
		$name    = (string) $r->name ; // N/A
		$name = trim($name);
		$ll[] = array($ip, $port, $ttl, $psize, $stf, $name);
	}
	#print_r($ll);
	return $ll;
}

function get_enc_label_from_dict($R, $idx, $d=null){
	if (!isset($R[$idx])) return $d;
	return trim($R[$idx]);
}

function get_enc_label($R, $idx, $d=null){
	$c =  $R->xpath("encoder" . strval($idx));
	if (!$c) return $d;
	$r = (string)$c[0];
	if (!$r) return $d;
	return $r;
}


class EncLabels {
	var $R;
	var $fname;

	function EncLabels($f=null){
		$this->fname = $f;
		$this->R = load_encoder_labels($f);
		return $this;
	}

	function getLabel($idx, $d=null){
		return get_enc_label($this->R, $idx, $d);
	}
	function save(){
		save_encoder_labels($this->R, $this->fname);
	}
	function getDict(){
		return arrayEncoderLabels($this->R);
	}

	function setLabel($idx, $v){
		if ($v) $v = trim($v);
		if (!$v){
			$this->unsetLabel($idx);
			return;
		}
		return setEncoderLabel($this->R, $idx, $v);
	}

	function unsetLabel($idx){
		$c =  $this->R->xpath("encoder" . strval($idx));
		if ($c) unset($c[0][0]);
	}

};




function test_enc_label_func2(){
	global $_ENCODER_LABEL_FILE;
	$_ENCODER_LABEL_FILE = "enc_labels.xml";
	$DL = new EncLabels();
	print_r($DL->getDict());
	for($i=0; $i<10; $i++){
		printf("%d: [%s]\n", $i, $DL->getLabel($i, "(null)"));
		$c = $DL->getLabel($i);
		//if (is_null($c)) printf( "%d is null\n", $i);
	}
	$tt = strftime("%Y-%m-%d-%H:%M:%S");
	$DL->setLabel(6, $tt);
	$DL->setLabel(5, $tt);
	for($i=0; $i<10; $i++){
		printf("%d: [%s]\n", $i, $DL->getLabel($i));
	}
	$DL->unsetLabel(6);
	$DL->unsetLabel(6);
	$DL->save();
}
//test_enc_label_func2();


function test_enc_label_func1(){
	global $_ENCODER_LABEL_FILE;
	$_ENCODER_LABEL_FILE = "enc_labels.xml";

	//echo strftime("%Y-%m-%d-%H:%M:%S"), "\n";

	$a = load_encoder_labels();

	$dldict = arrayEncoderLabels($a);
	print_r($dldict);
	echo "\n";
	echo $dldict[0], "\n";
	echo $dldict[3], "\n";
	echo $dldict[4], "\n";
	echo $dldict[5], "\n";
	echo "\n\n";


	$c = $a->addChild("test3");
	$c = $a->addChild("test", "aaa");
	print $a->asXML();
	echo "\n\n";

	print_r( $a->children("test"));

	echo "\n";
	$c =  $a->children("","encoder3");
	$c =  $a->xpath("encoder4");
	print_r($c);
	print (string)$c[0];
	if ($c) print "Test ok \n";
	else print "Test not ok\n";
	echo "\n";

	print_r( $a->children("test2"));
	print_r( $a->children());
	$b = $a->children();


	echo "\n";
	foreach ($b as $k) {
		echo trim((string)($k)), " <= ",  $k->getName(), "\n";
	}

	echo "\n";
	foreach ($b as $k => $v) {
		echo $k, " => ", $v, "\n";
	}
}

?>
