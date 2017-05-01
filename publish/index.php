<?php
	// Get the start time
	$scriptStartTime = microtime(true);

	// Definitions
	define('STATUS_OFFLINE', 0);
	define('STATUS_CONFLICT_OFFLINE', 1);
	define('STATUS_ONLINE', 2);
	define('STATUS_CONFLICT_ONLINE', 3);
	
	// config
	require('config_web.php');
	
	// Include the output handler
	require('includes/output.php');
	
	// Include the RPC client
	require('includes/RPCClient.php');

	// Set up the RPC client
	$worker = new RemoteClass(DAEMON_URI);

	// General function
	function fatal_error($txt)
	{
		header('HTTP/1.0 500 Internal Server Error');
		echo <<<EOF
				<html>
					<head>
						<title>Rigs of Rods Multiplayer Server Administration Panel - Fatal error</title>
					</head>
					<body>
						<h1>Admin panel</h1>
						<h2>Internal Server Error</h2>
						<pre>$txt</pre>
					</body>
				</html>
EOF;
		error_log('ADMIN_PANEL_FATAL_ERROR '.$txt);
		exit;
	}
	
try
{
	
	// Get the action argument
	$action = '';
	if(!array_key_exists('action', $_GET))
		$action = 'index';
	else
		$action = addslashes($_GET['action']);
		
	// CHECK AUTH
	if(PASSWORD!='')
	{
		if(!array_key_exists('admin_panel_password', $_COOKIE) || addslashes($_COOKIE['admin_panel_password'])!=sha1(PASSWORD))
		{
			if($action=='login' && array_key_exists('password', $_POST) && addslashes($_POST['password'])==PASSWORD)
			{
				setcookie('admin_panel_password', sha1(PASSWORD), time()+60*60*24*365, dirname($_SERVER['PHP_SELF']), $_SERVER['HTTP_HOST']);
				$action = 'index';
			}
			else
			{
				$title = 'Authorization Required';
				$content = '<form method="post" action="?action=login" style="padding: 25px;">
									<label for="password">password: </label>
									<input type="password" name="password" autofocus />
									<input type="submit" name="submit" />
								</form>';
				$parentText = '';
				$parentLink = '';
				$serverstatus = 'empty';
				sendOutput();
				exit;
			}
		}
		
		// logout?
		if($action=='logout')
		{
			setcookie('admin_panel_password', '', time()-3600, '/admin', $_SERVER['HTTP_HOST']);
			$title = 'Authorization Required';
			$content = '<form method="post" action="?action=login" style="padding: 25px;">
								<label for="password">password: </label>
								<input type="password" name="password" autofocus />
								<input type="submit" name="submit" />
							</form>';
			$parentText = '';
			$parentLink = '';
			$serverstatus = 'empty';
			sendOutput();
			exit;
		}
	}
		
	// Get the server argument
	$server = '';
	if(array_key_exists('server', $_GET))
		$server = addslashes($_GET['server']);
	if($action=='mngmnt_list_servers' || $action=='about')
		$server = '';
	$url = Array('current' => "?action=$action&amp;server=$server", 'prefix' => "?server=$server");
	
	// Include the correct file
	switch($action)
	{	
		case 'log_view':
		case 'log_download':
			(include('includes/log.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'oldlog_list':
		case 'oldlog_download':
		case 'oldlog_view':
			(include('includes/oldlog.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'auth_list':
		case 'auth_add':
		case 'auth_edit':
		case 'auth_delete':
			(include('includes/auth.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'server_control':
		case 'server_status':
		case 'server_start':
		case 'server_stop':
		case 'server_kill':
			(include('includes/server.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'config_edit':
			(include('includes/config.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'motd_edit':
			(include('includes/motd.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'rules_edit':
			(include('includes/rules.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'login':
		case 'index':
		case 'mngmnt_list_servers':
		case 'mngmnt_delete_server':
		case 'mngmnt_copy_server':
			(include('includes/manage.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'about':
		case 'reload_config':
			(include('includes/about.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'send_chat':
			(include('includes/chat.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		case 'redirect_server_status':
			(include('includes/cp.php')) or fatal_error('Internal exception: Failed to include 1 or more files.');
		break;
		
		default:
			fatal_error('Internal exception: Unhandled action.');
		break;
	}
}
catch(RemoteClassOffline $e)
{
	fatal_error($e->getMessage());
}

	// echo '<tt style="display: block; text-align: right; width: 100%;">Page generated in '.(microtime(true)-$scriptStartTime).' seconds.</tt>';
?>
