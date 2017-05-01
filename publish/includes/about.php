<?php
	
$reloaded_config = "";
if($action=='reload_config')
{
	$worker->reload_config();
	$reloaded_config = 'disabled="disabled"';
}

// Request some statistics of the daemon
$stats;
// $remoteServer->request($stats, 'get_statistics', Array());
$stats = $worker->get_statistics();

// Format them for output
$stats['megaBytesDown'] = sprintf("%.2fMB", $stats['bytesDown']/1024/1024);
$stats['megaBytesUp']   = sprintf("%.2fMB", $stats['bytesUp']/1024/1024);
$stats['startTime_str'] = date("d M Y H:i:s", $stats['startTime']);
$stats['currTime_str']  = date("d M Y H:i:s", $stats['currTime']);
$stats['runTime_str']   = sprintf("%.2f hours", ($stats['currTime']-$stats['startTime'])/60/60);
$stats['logDirSizeMB']  = sprintf("%.2fMB", $stats['logDirSize']/1024/1024);
$stats['cfgDirSizeMB']  = sprintf("%.2fMB", $stats['cfgDirSize']/1024/1024);

// Get the changelog
$changelog;
// $remoteServer->request($changelog, 'get_changelog', Array());
$changelog = $worker->get_changelog();
$changelog_str = implode("\r\n", $changelog);

// Get the errorlog
$errorlog;
// $remoteServer->request($errorlog, 'get_errorlog', Array());
$errorlog = $worker->get_errorlog();
$errorlog_str = implode("\r\n=== === === ===\r\n", $errorlog);

// Get round trip time
$res;
// $remoteServer->request($res, 'echo', Array(microtime(true)));
$res = $worker->echo(microtime(true));
$utime2 = microtime(true);
$rtt = ceil((microtime(true)-$res['content'])*1000);

// Create the output
$title = 'About';
$serverstatus = 'serverlist';
$content = <<<EOF
This Rigs of Rods Multiplayer Server Administration Panel was made in the hope that it will be useful, but without any warranty.<br />
Usage of the panel is at your own risk.<br />
<br />
<fieldset style="text-align: left;">
	<legend>Authors</legend>
	<ul>
		<li><strong>Admin panel</strong><br /><span style="margin-left: 25px;">neorej16 (neorej16@rigsofrods.com)</span></li>
		<li><strong>Icons</strong><br /><span style="margin-left: 25px;">Andy Gongea (http://graphicrating.com)</span></li>
		<li><strong>JavaScript Secure Hash Algorithm (SHA1)</strong><br /><span style="margin-left: 25px;">http://www.webtoolkit.info</span></li>
	</ul>
</fieldset>
<fieldset style="text-align: left;">
	<legend>Daemon Statistics since start</legend>
	<table style="border-spacing: 0; width: 100%;">
		<tr>
			<td style="border-bottom: 1px solid #555555;">Current time:</td>
			<td style="border-bottom: 1px solid #555555;">$stats[currTime_str]</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #555555;">Current timestamp:</td>
			<td style="border-bottom: 1px solid #555555;">$stats[currTime]</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #555555;">Start time:</td>
			<td style="border-bottom: 1px solid #555555;">$stats[startTime_str]</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #555555;">Running time:</td>
			<td style="border-bottom: 1px solid #555555;">$stats[runTime_str]</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #555555;">Round Trip Delay:</td>
			<td style="border-bottom: 1px solid #555555;">{$rtt}ms</td>
		</tr>
		<!--<tr>
			<td style="border-bottom: 1px solid #555555;">Connection count:</td>
			<td style="border-bottom: 1px solid #555555;">$stats[connCount]</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #555555;">Uploaded:</td>
			<td style="border-bottom: 1px solid #555555;">$stats[megaBytesUp]</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #555555;">Downloaded:</td>
			<td style="border-bottom: 1px solid #555555;">$stats[megaBytesDown]</td>
		</tr>-->
		<tr>
			<td style="border-bottom: 1px solid #555555;">Log folder size:</td>
			<td style="border-bottom: 1px solid #555555;">$stats[logDirSizeMB] ($stats[logDirCount] files)</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #555555;">Config folder size:</td>
			<td style="border-bottom: 1px solid #555555;">$stats[cfgDirSizeMB] ($stats[cfgDirCount] files)</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align: right;"><button onClick="window.location = '$url[prefix]&amp;action=reload_config';" $reloaded_config>reload daemon config</button></td>
		</tr>
	</table>
</fieldset>
<fieldset style="text-align: left;">
	<legend>Changelog</legend>
	<textarea style="width: 100%; height: 200px;">$changelog_str</textarea>
</fieldset>
<!--<fieldset style="text-align: left;">
	<legend>Errorlog</legend>
	<textarea style="width: 100%; height: 200px;">$errorlog_str</textarea>
</fieldset>-->
EOF;
$parentText = 'back to serverlist';
$parentLink = 'mngmnt_list_servers';
$serverstatus = 'empty';
sendOutput();

?>