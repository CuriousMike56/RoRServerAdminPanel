<?php
if($action=='mngmnt_copy_server')
{
	// Check the arguments
	if(!array_key_exists('nameto', $_GET))
	{
		fatal_error('ERROR: Not enough parameters.');
	}
	
	// Get the arguments
	$nameto = addslashes($_GET['nameto']);
	
	// Send the request
	$res = $worker->mngmnt_copy_server($server, $nameto);
	echo $res['result'];
	exit;
}
elseif($action=='mngmnt_delete_server')
{	
	// Send the request
	$res = $worker->mngmnt_delete_server($server);
	echo $res['result'];
	exit;
}
else
{
	// Get serverlist
	$res = $worker->mngmnt_list_servers();
	ksort($res);
	
	// Get daemon motd
	$motd = $worker->get_daemon_message();
	$motd = htmlentities(str_replace('\\n', "\n", $motd['content']));
	
	// Create the output
	$title = 'manage servers';
	$parentText = 'Log out';
	$parentLink = 'logout';
	$content = '';
	$serverstatus = 'serverlist';
	
	// Add the msg.txt file
	$content .= '<div style="text-align: left;"><pre>'.$motd.'</pre><strong>Servers:</strong></div>';

	$count = count($res);
	$num = 0;
	foreach($res as $servername => &$statuscode)
	{		
		// Get the status of the server
		$status = '';
		if($statuscode==STATUS_OFFLINE)
			$status = '<img class="serverlist_statusimg" id="status_'.$num.'" src="img/status/Error.png" height="25" title="This server is offline" alt="offline" />';
		else if($statuscode==STATUS_CONFLICT_OFFLINE)
			$status = '<img class="serverlist_statusimg" id="status_'.$num.'" src="img/status/Warning.png" height="25" title="Conflicted state: This server should be online, but instead, it\'s offline" alt="conflict" />';
		else if($statuscode==STATUS_ONLINE)
			$status = '<img class="serverlist_statusimg" id="status_'.$num.'" src="img/status/Valid.png" height="25" title="This server is online" alt="online" />';
		else if($statuscode==STATUS_CONFLICT_ONLINE)
			$status = '<img class="serverlist_statusimg" id="status_'.$num.'" src="img/status/Warning.png" height="25" title="Conflicted state: This server should be offline, but instead, it\'s online" alt="conflict" />';
		
		// You're not to delete online servers, or to delete the last existing server
		$deleteArg = ($statuscode!='0' || $count==1) ? 'true' : 'false';
		
		// Output the result
		$content .= <<<EOF
			<div id="row_$num" class="serverlist_row">
				<div class="serverlist_statusfield" onClick="javascript: window.location.href = '?server=$servername&amp;action=server_control';">
					$status
					<span class="serverlist_namefield">$servername</span>
				</div>
				<div class="serverlist_actionfield">
					<img  src="img/Search.png"       height="25" title="View this server in more detail"  alt="view"   onClick="javascript: window.location.href = '?server=$servername&amp;action=server_control';" />
					<img  src="img/Files_Copy.png"   height="25" title="Copy this server configuration"   alt="copy"   onClick="javascript: copyServer($num, '$servername');" />
					<img  src="img/Trash.png"        height="25" title="Delete this server configuration" alt="delete" onClick="javascript: if(!$deleteArg) deleteServer($num, '$servername'); else alert('Cannot delete running servers!');" />
				</div>
			</div>
EOF;

		++$num;
	}
	$content .= '<div id="serverlist_addedServers"></div>';
	//$content .= '<div class="serverlist_row">&nbsp;</div>';
	//$content .= '<div style="text-align: left;">'.@file_get_contents('includes/msg.txt').'</div>';
	sendOutput();
}
?>