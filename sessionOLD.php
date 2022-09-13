<?php
include_once(__DIR__ . "/../common/includes/inc_common.php");
include_once(__DIR__ . "/../common/includes/phpsock.inc");
$aConfig = getSystemConfig($sConfigPath);

startSession();
if (!array_key_exists('auth', $_SESSION) || (array_key_exists('auth', $_SESSION) && ($_SESSION['auth'] != true))) {
	redirect('_ind/login.php');
}
else
	$_SESSION['is_linux_encoder'] = file_exists('../encoder/islinux');
?>
