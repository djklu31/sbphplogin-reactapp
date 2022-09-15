<?php
header('Access-Control-Allow-Origin: *');

//link to existing accounts.xml on remote server
$accounts_xml_path = __DIR__ . '/accounts.xml';

$username = $_POST['username'];
$password = $_POST['password'];
$fromreact = $_POST['fromreact'];

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

function setSession($username, $password, $fromreact)
{
	global $accounts_xml_path;
	// startSession($username);
	if ($fromreact === "false") {
		$password = md5($password);
	}

	$isValid = false;

	$dom = new domdocument();
	if (!$dom->load($accounts_xml_path)) {
		$dom = createXmlAccounts($accounts_xml_path);
	}
	$users = $dom->getElementsByTagName('user');
	foreach ($users as $user) {
		if (strtolower($user->getAttribute('username')) == strtolower($username) && $user->getAttribute('pass') == $password) {
			$isValid = true;
			// $_SESSION['auth'] = $isValid;
			// $_SESSION['name'] = $user->getAttribute('name');
			// $_SESSION['username'] = $user->getAttribute('username');
			// $_SESSION['type'] = $user->getAttribute('type');
			// $_SESSION['bitrate'] = 0; // just to provide initial bitrate value displayed at top (not important)
			// $_SESSION['buffer'] = 0; // just to provide initial buffer value displayed at top (not important)
			$now = date('Y.m.d H:i:s', time());
			// $_SESSION['lastaccess'] = $now;
			// $_SESSION['sessionip'] = getRemoteIp();
			$user->setAttribute('lastaccess', $now);
			$user->setAttribute('sessionip', getRemoteIp());
			$dom->save($accounts_xml_path);
		}
	}

	return $isValid;
}

function startSession($username)
{
	session_name($username);
	session_start();
}

$credentialSuccess = setSession($username, $password, $fromreact);

if ($credentialSuccess) {
	if ($fromreact === "false") {
		$hashedPass = md5($password);
	} else {
		$hashedPass = $password;
	}
	echo json_encode(['login success', $hashedPass]);
} else {
	echo json_encode(['login failure', ""]);
}
