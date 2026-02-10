<?php
	// include this at start of protected pages/processors
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params([
		'lifetime' => 0, // until browser close
		'path' => $cookieParams['path'],
		'domain' => $cookieParams['domain'],
		'secure' => isset($_SERVER['HTTPS']), // true when using HTTPS
		'httponly' => true,
		'samesite' => 'Lax'
	]);

	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
?>