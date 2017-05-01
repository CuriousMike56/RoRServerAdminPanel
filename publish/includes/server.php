<?php
if($action=='server_start')
{
	//rename(LOG_FILE_LOCATION, "$ROOT/logs/$NAME.".date('Y-m-d-H-i-s').'.log');
	$res = $worker->server_start($server);
	// $remoteServer->request($res, 'server_start', Array());
	echo $res['status'].$res['message'];
}
elseif($action=='server_stop')
{
	$res = $worker->server_stop($server);
	// $remoteServer->request($res, 'server_stop', Array());
	echo $res['status'].$res['message'];
}
elseif($action=='server_kill')
{
	$res = $worker->server_kill($server);
	// $remoteServer->request($res, 'server_kill', Array());
	echo $res['status'].$res['message'];
}
elseif($action=='server_status')
{
	$res = $worker->server_status($server);
	// $remoteServer->request($res, 'server_status', Array());
	echo $res['status'].$res['message'];
}
elseif($action=='server_control')
{
	// Create the output
	$title = 'Server Control Panel';
	$menu = Array(
		Array(
			/*Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=send_chat';",
				'tdparams' => '',
				'icon'     => 'img/Comment.png',
				'caption'  => 'Send Chat Message'
			),*/
			Array(),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=redirect_server_status';",
				'tdparams' => '',
				'icon'     => 'img/Window_App_Results.png',
				'caption'  => 'Server Status View'
			),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=log_view';",
				'tdparams' => '',
				'icon'     => 'img/Project_2.png',
				'caption'  => 'Current Logbook'
			),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=oldlog_list';",
				'tdparams' => '',
				'icon'     => 'img/Box.png',
				'caption'  => 'Older Logbooks'
			)
		),
		Array(
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=config_edit';",
				'tdparams' => '',
				'icon'     => 'img/Gear.png',
				'caption'  => 'Configuration'
			),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=auth_list';",
				'tdparams' => '',
				'icon'     => 'img/User.png',
				'caption'  => 'Authorizations'
			),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=motd_edit';",
				'tdparams' => '',
				'icon'     => 'img/Mail.png',
				'caption'  => 'Message of the Day'
			),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=rules_edit';",
				'tdparams' => '',
				'icon'     => 'img/Book.png',
				'caption'  => 'Rules'
			)
		),
		Array(
			Array(
				'class'    => 'tdlink',
				'action'   => 'startServer();',
				'tdparams' => 'style="border-top: 1px dotted #000000;"',
				'icon'     => 'img/Button_Play_Pause.png',
				'caption'  => 'Start Server'
			),
			Array(
				'class'    => 'tdlink',
				'action'   => 'printServerStatus2();',
				'tdparams' => 'style="border-top: 1px dotted #000000;"',
				'icon'     => 'img/Info_Light.png',
				'caption'  => 'Refresh Server Status'
			),
			Array(
				'class'    => 'tdlink',
				'action'   => 'stopServer();',
				'tdparams' => 'style="border-top: 1px dotted #000000;"',
				'icon'     => 'img/Button_Stop.png',
				'caption'  => 'Stop Server'
			),
			Array(
				'class'    => 'tdlink',
				'action'   => 'killServer();',
				'tdparams' => 'style="border-top: 1px dotted #000000;"',
				'icon'     => 'img/Denided.png',
				'caption'  => 'Kill Server (do not use this!)'
			)
		)
	);
	$content = '<div id="msgBox"></div>';
	$parentText = 'back to serverlist';
	$parentLink = 'mngmnt_list_servers';
	sendOutput();
}
?>