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
	
	// $remoteServer->request($result_put, 'edit_config', Array($_POST['cfgtxt']));
	$result_put = $worker->edit_config($server, $_POST['cfgtxt']);
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
	// $remoteServer->request($result_get, 'request_config', Array());
	$result_get = $worker->request_config($server);
	if(array_key_exists('error', $result_get))
		$resultmsg = $result_get['message'];
	else
		$mycontent = $result_get['content'];
}

// Get the possible values for binfile
$res = $worker->list_binfiles();
// $remoteServer->request($res, 'list_binfiles', Array());
$binlist_str = "";
foreach($res as &$file)
{
	$binlist_str .= "$file\n";
}

// Create the output
$title = 'edit server configuration';
$content = <<<EOF
<form method="post" action="$url[current]">
	<label for="binfiles" style="display: block; width: 100%; text-align: left; font-weight: bold;">Possible binfile values</label>
	<textarea name="binfiles" id="binfiles" readonly="readonly" style="height: 50px; width: 99%;">$binlist_str</textarea>
	<br /><br />
	
	<label for="cfgtxt" style="display: block; width: 100%; text-align: left; font-weight: bold;">Config file</label>
	<textarea name="cfgtxt" id="cfgtxt" style="height: 500px; width: 99%;">$mycontent</textarea><br />
	
	<input type="submit" name="submit" id="submit" value="apply changes" style="width: 100%; height: 50px;" />
	<p style="color: grey;">(Changes only become active after a server restart)</p>
</form>
EOF;
if($resultmsg!='')
	$content .= '<br />Server result: '.$resultmsg;
sendOutput();
?>