<?php
header('Access-Control-Allow-Origin: *');

$accounts_xml_path = "../data/accounts.xml";
$sessions_xml_path = "../data/sessions.xml";
$fromreact = 0;
$islogout = 0;

$username = $_POST['username'];
if (!empty($_POST['token'])) {
	$token = $_POST['token'];
} else {
	$password = $_POST['password'];
}
if (!empty($_POST['fromreact'])) {
	$fromreact = $_POST['fromreact'];
}
if (!empty($_POST['islogout'])) {
	$islogout = $_POST['islogout'];
}

function getRemoteIp()
{
	$REMOTE_ADDR = '';
	if (isset($_SERVER['REMOTE_ADDR'])) $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];

	// darwin fix
	if ($REMOTE_ADDR == '::1' || $REMOTE_ADDR == 'fe80::1') {
		$REMOTE_ADDR = '127.0.0.1';
	}

	return $REMOTE_ADDR;
}
function createXmlSessions($sessions_xml_path)
{
	$dom = new domdocument('1.0', 'UTF-8');
	$dom->formatOutput = true;
	$root = $dom->createElement('sessions');
	$dom->appendChild($root);

	// $session = $dom->createElement('session');
	// $session->setAttribute('username', 'butts');
	// $session->setAttribute('token', 'randomtoken');

	// $root->appendChild($session);

	echo 'Wrote: ' . $dom->save($sessions_xml_path) . ' bytes';
	return $dom;
}

function createXmlAccounts($accounts_xml_path)
{
	$dom = new domdocument('1.0', 'UTF-8');
	$dom->formatOutput = true;
	$root = $dom->createElement('accounts');
	$dom->appendChild($root);
	// create default admin user with default password
	$user = $dom->createElement('user');
	$user->setAttribute('name', 'Administrator');
	$user->setAttribute('username', 'administrator');
	$user->setAttribute('pass', 'fe01ce2a7fbac8fafaed7c982a04e229');
	$user->setAttribute('type', 'admin');
	$user->setAttribute('lastaccess', 'n/a');
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
	$user2->setAttribute('name', 'Guest');
	$user2->setAttribute('username', 'guest');
	$user2->setAttribute('pass', 'fe01ce2a7fbac8fafaed7c982a04e229');
	$user2->setAttribute('type', 'guest');
	$user2->setAttribute('lastaccess', 'n/a');
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

	echo 'Wrote: ' . $dom->save($accounts_xml_path) . ' bytes';
	return $dom;
}

function createSession($sessions_xml_path, $username)
{
	$dom = new domdocument();
	$dom->load($sessions_xml_path);
	$sessionsRoot = $dom->getElementsByTagName('sessions');
	$session = $dom->createElement('session');
	$token = bin2hex(random_bytes(32));
	$session->setAttribute('username', $username);
	$session->setAttribute('token', $token);
	$now = date('Y-m-d H:i:s', time());
	$session->setAttribute('lastaccess', $now);
	$sessionsRoot[0]->appendChild($session);
	$dom->save($sessions_xml_path);

	return $token;
}

function clearSessionsOverOneDay($sessions_xml_path)
{
	$dom = new domdocument();
	$dom->load($sessions_xml_path);
	$sessionsRoot = $dom->getElementsByTagName('sessions')[0];
	$sessions = $dom->getElementsByTagName('session');
	foreach ($sessions as $session) {
		$sessionLastAccess = new DateTime($session->getAttribute('lastaccess'));
		$now = new DateTime("now");
		$interval = $sessionLastAccess->diff($now);
		$daysSince = intval($interval->format("%a"));
		if ($daysSince > 0) {
			$sessionsRoot->removeChild($session);
			error_log("Token Expired: " . $session->getAttribute('token'));
		}
	}
	$dom->save($sessions_xml_path);
}

function validateToken($username, $token, $sessions_xml_path)
{
	$dom = new domdocument();
	$isValid = false;
	clearSessionsOverOneDay($sessions_xml_path);

	$dom = new domdocument();
	$dom->load($sessions_xml_path);
	$sessions = $dom->getElementsByTagName('session');
	foreach ($sessions as $session) {
		error_log("Token: " . $token . " Username: " . $username);
		if ($session->getAttribute("token") === $token && $session->getAttribute("username") === $username) {
			$isValid = true;
			break;
		}
	}
	return array($isValid, $token);
}

function validateUser($username, $password)
{
	global $accounts_xml_path;
	global $sessions_xml_path;
	$password = md5($password);
	$isValid = false;

	$dom = new domdocument();
	$sessionDom = new domdocument();


	if (!is_file($accounts_xml_path)) {
		fopen($accounts_xml_path, "w");
		chmod($accounts_xml_path, 0777);
	}

	if (!is_file($sessions_xml_path)) {
		fopen($sessions_xml_path, "w");
		chmod($sessions_xml_path, 0777);
	}

	clearSessionsOverOneDay($sessions_xml_path);

	if (!$dom->load($accounts_xml_path)) {
		$dom = createXmlAccounts($accounts_xml_path);
	}
	if (!$sessionDom->load($sessions_xml_path)) {
		$sessionDom = createXmlSessions($sessions_xml_path);
	}
	$users = $dom->getElementsByTagName('user');
	foreach ($users as $user) {
		if (strtolower($user->getAttribute('username')) == strtolower($username) && $user->getAttribute('pass') == $password) {
			$isValid = true;
			$now = date('Y.m.d H:i:s', time());
			$user->setAttribute('lastaccess', $now);
			$user->setAttribute('sessionip', getRemoteIp());
			$dom->save($accounts_xml_path);
			$token = createSession($sessions_xml_path, strtolower($username));
		}
	}
	return array($isValid, $token);
}

function logoutToken($token, $sessions_xml_path)
{
	$dom = new domdocument();
	$dom->load($sessions_xml_path);
	$sessionsRoot = $dom->getElementsByTagName('sessions')[0];
	$sessions = $dom->getElementsByTagName('session');
	foreach ($sessions as $session) {
		if ($session->getAttribute('token') === $token) {
			$sessionsRoot->removeChild($session);
			error_log("Token Logged Out (token destroyed): " . $session->getAttribute('token'));
		}
	}
	$dom->save($sessions_xml_path);

	return array(true);
}

if ($fromreact == 1) {
	if ($islogout == 1) {
		$result = logoutToken($token, $sessions_xml_path);
	} else {
		$result = validateToken($username, $token, $sessions_xml_path);
	}
} else {
	$result = validateUser($username, $password);
}

$resultSuccess = $result[0];
if ($islogout == 0) {
	$resToken = $result[1];
}

if ($resultSuccess) {
	// if ($fromreact === "false") {
	// 	$hashedPass = md5($password);
	// } else {
	// 	$hashedPass = $password;
	// }
	if ($islogout == 1) {
		echo json_encode(['logout success', $token]);
	} else {
		echo json_encode(['login success', $resToken]);
	}
} else {
	echo json_encode(['login failure', ""]);
}
