<?php

$result_put;
$result_get;
$resultmsg = '';
$mycontent = '';

// post?
if(array_key_exists('submit', $_POST))
{
	if(!array_key_exists('cfgtxt', $_POST))
	{
		fatal_error('Missing parameter.');
	}
	
	// $remoteServer->request($result_put, 'edit_motd', Array($_POST['cfgtxt']));
	$result_put = $worker->edit_motd($server, $_POST['cfgtxt']);
	if(array_key_exists('error', $result_put))
		$resultmsg = $result_put['message'];
	else
	{
		$mycontent = $result_put['content'];
		$resultmsg = 'OK - Updated without error.';
	}
}
else
{
	// Get the contents of the config file
	// $remoteServer->request($result_get, 'request_motd', Array());
	$result_get = $worker->request_motd($server);
	if(array_key_exists('error', $result_get))
		$resultmsg = $result_get['message'];
	else
		$mycontent = $result_get['content'];
}

// Create the output
$title = 'edit message of the day';
$content = <<<EOF
<form method="post" action="$url[current]">
	<textarea name="cfgtxt" id="cfgtxt" style="width: 400px; height: 300px; margin: 0 0 0 0; border: none;">$mycontent</textarea><br />
	<input type="submit" name="submit" id="submit" value="apply changes" style="width: 100%; height: 50px;" />
</form>
EOF;
if($resultmsg!='')
	$content .= '<br />Server result: '.$resultmsg;
sendOutput();
?>