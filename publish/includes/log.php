<?php

// Small exception for downloading the log file
if($action=='log_download')
{
	// Get the data
	$res = $worker->get('serverlog', $server);
	
	// Edit the data line endings
	$res = str_replace("\n", "\r\n", $res);
	
	// Strip out user tokens and IP addresses
	// $res = preg_replace('/with token [A-Fa-f0-9]+/', 'with token XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', preg_replace('/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/', 'xxx.xxx.xxx.xxx', htmlentities($res)));
	
	// header for the data
	$header = '';
	$header .= " /!\\ THIS IS A PRIVATE LOG FILE /!\\\r\n";
	$header .= " /!\\ DO NOT SHARE - DO NOT COPY /!\\\r\n";
	$header .= " /!\\ RESPECT EVERYONE'S PRIVACY /!\\\r\n\r\n\r\n";
	$res = $header . $res;
	
	// output
	header('Content-type: text/plain');
	header('Content-Disposition: attachment; filename="'.str_replace(' ', '_', $server).'.log"');
	header('Content-Length: '.strlen($res));
	ob_clean(); 
	flush(); 
	echo $res;
	exit;
}

// page size
define('PAGE_SIZE', 32768); // pages of 32 kB
// define('PAGE_SIZE', 524288); // pages of 512 kB

// Get the page number
$page = -1;
if(array_key_exists('page', $_GET))
{
	// Get input
	$page = addslashes($_GET['page']);
	
	// verify input
	if(!is_numeric($page))
		fatal_error('Malformed parameter: page');
	
	// Get the data
	$res = '';
	// $remoteServer->request($res, 'request_log', Array($page*PAGE_SIZE));
	$res = $worker->request_log($server, $page*PAGE_SIZE, PAGE_SIZE);
	// $res['content'] = preg_replace('/with token [A-Fa-f0-9]+/', 'with token XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', preg_replace('/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/', 'xxx.xxx.xxx.xxx', htmlentities($res['content'])));
	
	// We're going to output text
	header('Content-type: text/plain');
	
	// Echo the data
	printf('%20d%s', $res['cursor'], $res['content']);
	
	exit;
}

// Get the start byte
$start = -1;
if(array_key_exists('start', $_GET))
{
	// Get input
	$start = addslashes($_GET['start']);

	// verify input
	if(!is_numeric($start))
		fatal_error('99999999999999999999 Malformed parameter: start'.$start);
	
	// Get the data
	$res = '';
	// $remoteServer->request($res, 'request_log', Array($start));
	$res = $worker->request_log($server, $start, PAGE_SIZE);
	// $res['content'] = preg_replace('/with token [A-Fa-f0-9]+/', 'with token XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', preg_replace('/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/', 'xxx.xxx.xxx.xxx', htmlentities($res['content'])));
		
	// We're going to output text
	header('Content-type: text/plain');
	
	// Echo the data (if present)
	printf('%20d%s', $res['cursor'], $res['content']);
	
	exit;
}


	
// Get the log file size
$res = '';
// $remoteServer->request($res, 'request_logsize', Array());
$res = $worker->request_logsize($server);
$logfilesize = $res['logsize'];

// Use the logfilesize to get the page number and start byte
$page = floor($logfilesize/PAGE_SIZE);
$start = $page*PAGE_SIZE;

// Get the logfile contents
// $remoteServer->request($res, 'request_log', Array($start));
$res = $worker->request_log($server, $start, PAGE_SIZE);
if($res['content']=='NO_DATA')
{
	// Create the output
	$title = 'logbook';				
	$content = 'No logbook data available.';
	sendOutput();
	exit;
}
else
{
	// $res['content'] = preg_replace('/with token [A-Fa-f0-9]+/', 'with token XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', preg_replace('/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/', 'xxx.xxx.xxx.xxx', $res['content']));
	$logfilesize     = intval($res['cursor']);
	$logfilecontents = htmlentities($res['content']);
	$previousPage = $page-1;
	$logfilesize_str = sprintf("%.2fMB", ceil($logfilesize/1024/1024*100)/100);
}


// Get the request type
$output_type = 'html';
if(array_key_exists('output_type', $_GET))
	$output_type = addslashes($_GET['output_type']);
	
if($output_type=='raw')
{
	header('Content-type: text/plain');
	
	// Echo the size of the file
	printf("%20d", $logfilesize);
	
	// Echo the data
	echo $logfilecontents;
}
else
{
	// Create the output
	$title = 'logbook';				
	$menu = Array(
		Array(
			Array(),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=log_download';",
				'tdparams' => '',
				'icon'     => 'img/Button_Down.png',
				'caption'  => "Download as file ($logfilesize_str)"
			),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=oldlog_list';",
				'tdparams' => '',
				'icon'     => 'img/Box.png',
				'caption'  => "older logs"
			),
			/*Array(
				'class'    => 'tdlink',
				'action'   => 'showPreviousPage();',
				'tdparams' => '',
				'icon'     => 'img/Box.png',
				'caption'  => 'Show earlier data (32KB)'
			),*/
			Array()
		)
	);
	$content = "<pre> /!\\ THIS IS A PRIVATE LOG FILE /!\\\r\n /!\\ DO NOT SHARE - DO NOT COPY /!\\\r\n /!\\ RESPECT EVERYONE'S PRIVACY /!\\</pre>";
	$viewport = '800';
	$footer = <<<EOF
		<div style="border: none; width: 100%; text-align: center;"><a href="javascript: showPreviousPage();">Show earlier data (32KB)</a></div>
		<pre id="logbox" style="text-align: left; display: block;">$logfilecontents</pre>
		<div style="border: none; width: 100%; text-align: center;"><a href="javascript: toggleAutoUpdating();" id="freezengo">freeze log</a> --- <a href="#">back to top</a></div>
EOF;
	$onload = "window.scrollTo(0, document.body.scrollHeight); toggleAutoUpdating(); previousPage = $previousPage; logFileSize = $logfilesize;";
	sendOutput();
}
?>