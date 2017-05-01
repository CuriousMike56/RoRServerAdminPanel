<?php

$result_put;
$result_get;
$resultmsg = '';
$mycontent = '';


// $remoteServer->request($result_get, 'get_cpurl', Array($_POST['cfgtxt']));
$result_get = $worker->get_cpurl($server);

// post?
if(array_key_exists('submit', $_POST))
{
	if(!array_key_exists('cfgtxt', $_POST))
	{
		fatal_error('Missing parameter.');
	}
	
	// $remoteServer->request($result_put, 'server_say', Array($_POST['cfgtxt']));
	$result_put = $worker->server_say($server, $_POST['cfgtxt']);
	if(array_key_exists('error', $result_put))
		$resultmsg = $result_put['message'];
	else
	{
		$mycontent = $result_put['content'];
		$resultmsg = 'OK - Updated without error.';
	}
}
else
	$mycontent = '';

// Create the output
$title = 'send a chat message';
if($result_get['result']==1 && ($result_get['status']==STATUS_ONLINE || $result_get['status']==STATUS_CONFLICT_ONLINE) )
{
$content = <<<EOF
<form method="post" action="$url[current]">
	<textarea name="cfgtxt" id="cfgtxt" style="width: 400px; height: 100px; margin: 0 0 0 0; border: none;" placeholder="[write your message here]" >$mycontent</textarea><br />
	<input type="submit" name="submit" id="submit" value="Send chat message!" style="width: 100%; height: 50px;" />
</form>
EOF;
}
else
{
$content = "Server is offline or webserver is disabled.";
}

if($resultmsg!='')
	$content .= '<br />Server result: '.$resultmsg;
sendOutput();
?>