<?php

// Initializations
$server  = '';
$content = '';
$footer = '';
$parentText = 'back to control panel';
$parentLink = 'server_control';
$onload = '';
$menu = Array();
$title = '';
$viewport = '420';
$serverstatus = 'unknown';

// The main output generation function
function sendOutput() {
global $server, $content, $header, $footer, $parentText, $parentLink, $onload, $menu, $title, $url, $serverstatus, $worker;

// Get the server status
if(isset($worker) && $worker!=null && $serverstatus=='unknown')
{
	$res = $worker->server_status($server);
	$serverstatus = $res['status'];
}
$statusTD = <<<EOF
		<td class="tdmain mainmenu" id="statusTd">
			<img src="img/status/Help.png" id="statusImg" alt="status" /><br />
			<span id="statusText">Checking Status...</span>
		</td>
EOF;
if($serverstatus==='empty')
{
	$statusTD = '<td class="tdmain mainmenu">&nbsp;</td>';
}

// Create the back button
$backButton = <<<EOF
			<td class="tdmain tdlink mainmenu" onClick="javascript: window.location.href = '$url[prefix]&amp;action=$parentLink';" title="$parentText">
				<img src="img/Button_Rewind.png" alt="go back" /><br />
				$parentText
			</td>
EOF;
if($parentLink=='' && $parentText=='')
	$backButton = '<td class="tdmain mainmenu">&nbsp;</td>';

// Calculate the amount of columns
// We need this to be html5 confomant and to display correctly in old browsers
$colnum = 4;
if(count($menu)==0) $colnum = 3;
$headcols = $colnum-2;


echo <<<EOF
<!DOCTYPE html>
<html>
<head>
	<title>Rigs of Rods Multiplayer Server Administration Panel - $title</title>

	<meta charset="UTF-8">
	<meta name="viewport" content="width=404, target-densitydpi=high-dpi" />
	
	<!-- Dependency for sha1 hash -->
	<script type="text/javascript" src="js/crypto-sha1.js"></script>
	
	<!-- other scripts -->
	<script type="text/javascript" src="js/scripts.js"></script>
	
	<link rel="stylesheet" type="text/css" href="style.css" />
	
	<!-- page specific -->
	$header
	
	
</head>
<body onLoad="javascript: urlprefix='$url[prefix]'; servername='$server'; updateServerStatus('$serverstatus'); $onload "><table class="maintable" style="border-spacing: 0;">
	
		<!--header-->
		<tr>
			<th colspan="$colnum" style="width: 400px; text-align: center; border-bottom: 3px double #000000;">Rigs of Rods Multiplayer Server Administration</th>
		</tr>
		
		<!--<tr>
			<th colspan="$colnum" style="font-size: 35px; text-align: center; background-color: #AAAAAA;">$server</th>
		</tr>-->
	
		<!--main menu-->
		<tr>
			$backButton
			<td class="tdmain mainmenu" colspan="$headcols" style="width: 200px; font-weight: bold; vertical-align: middle; padding-top: 0px; font-weight: bold;"><span style="font-size: x-large">$server</span><br />$title</td>
			$statusTD
		</tr>
EOF;


// Build the menu
foreach($menu as &$row)
{
	echo '<tr>';
	foreach($row as &$item)
	{
		if(empty($item))
		{
			echo '<td class="tdmain">&nbsp;</td>';
		}
		else
		{
			echo <<<EOF
				<td class="tdmain $item[class]" onClick="javascript: $item[action]" $item[tdparams]>
					<img src="$item[icon]" width="32" height="32" alt="icon"/><br />
					$item[caption]
				</td>
EOF;
		}
	}
	echo '</tr>';
}

// content
if(!empty($content))
{
	echo <<<EOF
		<tr>
			<td id="contentBox" class="tdmain" colspan="$colnum" style="padding-top: 0; background-color: #FFFFFF; height: auto; width: 400px;">$content</td>
		</tr>
EOF;
}

// footer
echo <<<EOF
	</table>
	$footer
</body>
</html>
EOF;
}
