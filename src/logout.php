<?php
	include_once 'inc/secure_session_start.php';

	// Now it's safe to call session_destroy()
	$_SESSION = [];
	session_destroy();

	header("Location: index.php");
	exit;
?>