<?php

	// CONFIG
	
	// The web admin password. It must be the same as the password in your "config_daemon.ini"'s "[users]" section.
	define('PASSWORD', 'password');
	
	// The password of the daemon on the remote server (This has to be exactly 40 characters).
	// This has to match the user/password in your "config_daemon.ini"'s "[users]" section.
	define('DAEMON_URI', 'http://user:password@example.com:12999');

	// Error reporting?
	error_reporting(E_ALL);
	
?>
