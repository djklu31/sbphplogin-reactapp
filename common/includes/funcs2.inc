<?php

function startsWith($haystack, $needle)
{
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
	$length = strlen($needle);
	return $length === 0 || 
	(substr($haystack, -$length) === $needle);
}

############### Decoder ###############

$_DECODER_LIST_FILE="/var/lib/avenir/conf/decoders.xml";

if (!function_exists("_addDecoderConf")){
function _addDecoderConf($R, $ip, $port, $ttl, $packetSize, $stf, $name){
	$c = $R->addChild('decoder', $label);
	$c->addAttribute("ip", $ip);
	$c->addAttribute("port", $port);
	$c->addAttribute("ttl", $ttl);
	$c->addAttribute("packetSize", $packetSize);
	$c->addAttribute("stuff", $stf);
	//$c->addAttribute("name", $name);
	$c2 = $c->addChild('name', $name);
}
}

if (!function_exists("_delDecoderAtIndOfList")){
function _delDecoderAtIndOfList($R, $v=-1){
	if ($v<0) return -1;
	unset($R->decoder[$v]);
}
}

if (!function_exists("load_decoder_list")){
function load_decoder_list($f=null){
	global $_DECODER_LIST_FILE;
	if (!$f) $f = $_DECODER_LIST_FILE;
	$d = null;
	if (file_exists($f))
		$d = simplexml_load_file($f);
	if (!$d){
		$d = simplexml_load_string("<decoders>\n</decoders>");
		$d->asXml($f);
	}
	return $d;
}
}

if (!function_exists("save_decoder_list")){
function save_decoder_list($R){
	global $_DECODER_LIST_FILE;
	$dom = new domdocument('1.0','UTF-8');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($R->asXML());
	$dom->save($_DECODER_LIST_FILE);
	chmod($_DECODER_LIST_FILE, 0666);
}
}

if (!function_exists("arrayDecoderListSX")){
function arrayDecoderListSX($R, $_p=0){
	$ll = array();;
	#echo count($R->decoder), "\n";
	for($i=0; $i<100; $i++){
		$r = $R->decoder[$i]; 
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
}




?>
