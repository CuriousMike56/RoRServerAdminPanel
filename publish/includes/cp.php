<?php

$result;
$mycontent = '';


// $remoteServer->request($result, 'get_cpurl', Array());
$result = $worker->get_cpurl($server);
if(array_key_exists('error', $result))
	$content = $result['message'];
else if(array_key_exists('result', $result) && $result['result']!=1)
	$content = $result['content'];
else if(array_key_exists('status', $result) && ($result['status']==STATUS_OFFLINE || $result['status']==STATUS_CONFLICT_OFFLINE) )
	$content = 'Server is offline. Status view unavailable.';
else
{
	$mycontent = $result['content'];
	$content = '';
	
	$footer = <<<EOF
		<iframe src="http://$mycontent" style="border: none;  width: 100%; height: 1000px; margin: 0 0 0 0;" ></iframe>
	
EOF;
	
}

// Create the output
$title = 'Server Status View';
//if(!empty($mycontent)) $header = '<meta http-equiv="Refresh" content="1; url=http://'.$mycontent.'" />';
sendOutput();
?>