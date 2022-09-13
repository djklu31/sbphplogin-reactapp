<?php

$PWD_LIST_FILES = array(
	 "/usr/share/streambox/misc/toppwdlist.txt"
	,"/usr/share/avenir/www/toppwdlist.txt"
	,"10-million-password-list-top-100000.txt"
	,"10-million-password-list-top-10000.txt"
	,"toppwdlist.txt"
);
$PWD_LIST_DICT = Array();

function load_bpwd_list(){
	global $PWD_LIST_DICT;
	global $PWD_LIST_FILES;
	foreach ($PWD_LIST_FILES as $fn){
		if (!file_exists($fn)){
			#echo "not exists: $fn not\n";
			continue;
		}
		$f = fopen($fn, "r");
	}
	while (1){
		$l = fgets($f);
		if ($l=="") break;
		$l = trim($l);
		$PWD_LIST_DICT[$l] = 1;
		#echo trim($l), "\n";
	}
	fclose($f);
}

function check_breached_pwd($pwd){
	global $PWD_LIST_DICT;
	return isset($PWD_LIST_DICT[$pwd]);
}

function test_breached_pwd_list_load(){
	global $PWD_LIST_DICT;
	for ($i; $i<1*1000*1000; $i++){
		$a = check_breached_pwd('test');
		#print_r($a);
		#var_dump($a);
	}
}

load_bpwd_list();

#test_breached_pwd_list_load();

?>
