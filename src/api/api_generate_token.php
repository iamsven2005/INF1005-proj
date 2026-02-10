<?php 
// Generate token to access booking page
session_start();
// random 32 byte string
$token = bin2hex(random_bytes(32));
$_SESSION['allow_booking'] = $token;
?>
