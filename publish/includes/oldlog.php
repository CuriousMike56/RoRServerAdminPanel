<?php

// Do not cache anything here, as these are log files, which are subject to change
header('Pragma: no-cache');

// Small exception for downloading the log file
if($action=='oldlog_download')
{

	// Get the filename
	if(!array_key_exists('file', $_GET))
		fatal_error("Missing parameter: file");
	$filename = addslashes($_GET['file']);

	// Get the data
	$res = $worker->get('logfile', $filename);

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
	header('Pragma: public');
	header('Content-type: text/plain');
	header("Content-Disposition: attachment; filename=\"$filename\";");
	header('Content-Length: '.strlen($res));;
	ob_clean(); 
	flush(); 
	echo $res;
	exit;
}

// Small exception for viewing the log file
if($action=='oldlog_view')
{
	// Get the filename
	if(!array_key_exists('file', $_GET))
		fatal_error("Missing parameter: file");
	$filename = addslashes($_GET['file']);

	// Get the data
	$res = $worker->get('logfile', $filename);
	
	// Get the filesize
	$logfilesize = strlen($res);
	$logfilesize_str = sprintf("%.2fMB", ceil($logfilesize/1024/1024*100)/100);
		
	// Create the output
	$title = 'old logbook';				
	$menu = Array(
		Array(
			Array(),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=oldlog_download&amp;file=$filename&amp;size=$logfilesize';",
				'tdparams' => '',
				'icon'     => 'img/Button_Down.png',
				'caption'  => "Download as file ($logfilesize_str)"
			),
			Array(
				'class'    => 'tdlink',
				'action'   => "window.location.href = '$url[prefix]&amp;action=oldlog_list';",
				'tdparams' => '',
				'icon'     => 'img/Box.png',
				'caption'  => "other log files"
			),
			Array()
		)
	);
	$parentText = 'old logbooks';
	$parentLink = 'oldlog_list';
	$content = "<pre> /!\\ THIS IS A PRIVATE LOG FILE /!\\\r\n /!\\ DO NOT SHARE - DO NOT COPY /!\\\r\n /!\\ RESPECT EVERYONE'S PRIVACY /!\\</pre>";
	$viewport = '800';
	$footer = <<<EOF
		<pre id="logbox" style="text-align: left; display: block;">$res</pre>
		<div style="border: none; width: 100%; text-align: center;"><a href="#">back to top</a></div>
EOF;
	sendOutput();
	
	exit;
}

// Get the show var
$showall = false;
$showStr = 'min';
if(array_key_exists('show', $_GET))
{
	$a = addslashes($_GET['show']);
	if($a=='all')
	{
		$showall = true;
		$showStr = 'all';
	}
}

// Initialize sorting
$sortcol = 'date';
$sortord = 'dec';
$sortord_inversed = 'asc';

if(array_key_exists('sort', $_GET))
{
	$a = addslashes($_GET['sort']);
	if($a=='size') $sortcol = 'size';
}

if(array_key_exists('dir', $_GET))
{
	$a = addslashes($_GET['dir']);
	if($a=='asc')
	{
		$sortord = 'asc';
		$sortord_inversed = 'dec';
	}
}

function sortResults(&$res)
{
	global $sortcol, $sortord;
	
	if($sortcol=='date' && $sortord=='dec')
		krsort($res);
	else if($sortcol=='date' && $sortord=='asc')
		ksort($res);
	else if($sortcol=='size' && $sortord=='asc')
		asort($res);
	else if($sortcol=='size' && $sortord=='dec')
		arsort($res);
}

// Get logfile list
$res = '';
// $remoteServer->request($res, 'list_oldlog', Array());
$res = $worker->list_oldlog($server);

// Create the output
$title = 'old logbooks';				
$content = '<div style="width: 100%; text-align: center; font-style: italic; color: grey;">'.date("d M Y H:i:s").'</div><div style="width: 100%; margin: 0; padding: 0; text-align: left;">';
$content .= <<<EOF
		<div style="cursor: default; vertical-align: middle; text-align: center;">
			<input 
				type="text"
				readonly="readonly"
				style="
					width: 80px;
					background-color: transparent;
					border: none;
					border-top: 1px solid #000000;
					border-right: 1px solid #999999;
					text-align: left;
					color: #000000;
					margin: 0;
					margin-top: 5px;
					margin-bottom: 5px;
					padding-left: 20px;
					font-weight: bold;
					cursor: default;
				"
				value="Timestamp"
			/><input 
				type="text"
				readonly="readonly"
				style="
					width: 138px;
					background-color: transparent;
					border: none;
					border-top: 1px solid #000000;
					border-right: 1px solid #999999;
					text-align: center;
					color: #0000FF;
					margin: 0;
					margin-top: 5px;
					margin-bottom: 5px;
					padding-right: 20px;
					font-weight: bold;
					cursor: pointer;
				"
				value="    Date"
				onClick="javascript: window.location = '$url[prefix]&amp;action=oldlog_list&amp;sort=date&amp;dir=$sortord_inversed&amp;show=$showStr'"
			/><input 
				type="text"
				readonly="readonly"
				style="
					width: 68px; /*110*/
					background-color: transparent;
					border: none;
					border-top: 1px solid #000000;
					text-align: right;
					color: #0000FF;
					margin: 0;
					margin-top: 5px;
					margin-bottom: 5px;
					padding-right: 20px;
					font-weight: bold;
					cursor: pointer;
				"
				value="Filesize"
				onClick="javascript: window.location = '$url[prefix]&amp;action=oldlog_list&amp;sort=size&amp;dir=$sortord_inversed&amp;show=$showStr'"
			/><input 
				type="text"
				readonly="readonly"
				style="
					width: 54px;
					background-color: transparent;
					border: none;
					border-top: 1px solid #000000;
					text-align: right;
					color: #0000FF;
					margin: 0;
					font-weight: bold;
					cursor: default;
				"
				value=""
			/>
		</div>
EOF;

if($res['count']>0)
{
	unset($res['count']);
	sortResults($res);
	
	//var_dump($res);
	
	foreach($res as $filename => $filesize)
	{	
		if(!$showall && $filesize<10000)
			continue;
	
		$filesize_str = sprintf("%.2fMB", ceil($filesize/1024/1024*100)/100);
		
		preg_match('/\.([0-9]+)\.log/', $filename, $matches);
		$timestamp = $matches[1];
		$date = date("d M Y H:i:s", $timestamp);

		$content .= <<<EOF
			<!--onClick="javascript: window.location = '$url[prefix]&amp;action=oldlog_download&amp;file=$filename&amp;size=$filesize'"-->
			<div class="oldlog_row" style="cursor: pointer; vertical-align: middle; text-align: center;">
				<input 
					type="text"
					readonly="readonly"
					style="
						width: 80px;
						background-color: transparent;
						border: none;
						border-top: 1px solid #000000;
						border-right: 1px solid #999999;
						text-align: left;
						color: #000000;
						margin: 0;
						margin-top: 5px;
						margin-bottom: 5px;
						cursor: pointer;
						padding-left: 20px;
					"
					value="{$timestamp}"
					onClick="javascript: window.location = '$url[prefix]&amp;action=oldlog_download&amp;file=$filename&amp;size=$filesize'"
				/><input 
					type="text"
					readonly="readonly"
					style="
						width: 138px;
						background-color: transparent;
						border: none;
						border-top: 1px solid #000000;
						border-right: 1px solid #999999;
						text-align: right;
						color: #000000;
						margin: 0;
						margin-top: 5px;
						margin-bottom: 5px;
						cursor: pointer;
						padding-right: 20px;
					"
					value="{$date}"
					onClick="javascript: window.location = '$url[prefix]&amp;action=oldlog_download&amp;file=$filename&amp;size=$filesize'"
				/><input 
					type="text"
					readonly="readonly"
					style="
						width: 68px; /*110*/
						background-color: transparent;
						border: none;
						border-top: 1px solid #000000;
						border-right: 1px solid #999999;
						text-align: right;
						color: #000000;
						margin: 0;
						margin-top: 5px;
						margin-bottom: 5px;
						cursor: pointer;
						padding-right: 20px;
					"
					value="{$filesize_str}"
					onClick="javascript: window.location = '$url[prefix]&amp;action=oldlog_download&amp;file=$filename&amp;size=$filesize'"
				/><img
					src="img/Search.png"
					height="23"
					title="View this log file"
					alt="view"
					onClick="javascript: window.location.href = '$url[prefix]&amp;action=oldlog_view&amp;file=$filename&amp;size=$filesize';"
					style="
						vertical-align: bottom;
						border: none;
						border-top: 1px solid #000000;
						padding-left: 5px;
					"
				/><img
					src="img/Button_Down.png"
					height="23"
					title="Download this log file"
					alt="download"
					onClick="javascript: window.location.href = '$url[prefix]&amp;action=oldlog_download&amp;file=$filename&amp;size=$filesize';"
					style="
						vertical-align: bottom;
						border: none;
						border-top: 1px solid #000000;
						padding-left: 3px;
					"
				/>
			</div>
EOF;

	}
}
else
{
	$content .= <<<EOF
		<div class="oldlog_row" style="vertical-align: middle; text-align: center;">
			No data available.
		</div>
EOF;
}

$content .= '</div>';



		
$menu = Array(
	Array(
		Array(),
		Array(
			'class'    => 'tdlink',
			'action'   => "window.location.href = '$url[prefix]&amp;action=log_download';",
			'tdparams' => '',
			'icon'     => 'img/Button_Down.png',
			'caption'  => "Download Latest"
		),
		Array(
			'class'    => 'tdlink',
			'action'   => "window.location.href = '$url[prefix]&amp;action=log_view';",
			'tdparams' => '',
			'icon'     => 'img/Project_2.png',
			'caption'  => 'Current Logbook'
		),
		($showall) ? 
		Array()
		:
		Array(
			'class'    => 'tdlink',
			'action'   => "window.location.href = '$url[prefix]&amp;action=oldlog_list&amp;sort=$sortcol&amp;dir=$sortord&amp;show=all';",
			'tdparams' => '',
			'icon'     => 'img/Add_Symbol.png',
			'caption'  => "Show all files"
		)
	)
);
	

sendOutput();

?>