<?php

define('AUTH_ADMIN', 1);
define('AUTH_RANKED', 2);
define('AUTH_MOD', 4);
define('AUTH_BOT', 8);
define('AUTH_BANNED', 16);


class User
{
	public $username = "";
	public $usertoken = "";
	public $authorization = 0;
	public $line = 0;
}

function loadAuthFile(&$userlist, &$fileClutter)
{
	global $worker, $server;

	// Initialize our array of users
	$userlist = Array();
	
	// Get the auth file
	$res;
	// $remoteServer->request($res, 'request_auth', Array());
	$res = $worker->request_auth($server);
	if(array_key_exists('error', $res))
	{
		fatal_error("Remove server error: {$res[message]}.");
	}
	$filecontents = $res['content'];

	// Split into lines
	$lines = preg_split('/$\R?^/m', $filecontents);
	
	// Temporary variables
	$user = 0;
	$line = '';
	
	// Loop over the file
	foreach($lines as $lineNum => &$buffer)
	{
		++$lineNum;
		
		// No comments or empty lines
		if(empty($buffer) || $buffer[0]==';' || $buffer[0]=='/')
		{
			$fileClutter[] = $buffer;
			continue;
		}
		
		// Scan the line
		$user = new User;
		$num = sscanf($buffer, "%d %s %s", $user->authorization, $user->usertoken, $user->username);
		
		// We need at least 2 results
		if($num<2)
		{
			$fileClutter[] = $buffer;
			continue;
		}
		
		// create new user
		$user->line = $lineNum;
		$user->username = ($num>=3) ? $user->username : '[none]';
		
		// Add the user to the list
		$userlist[] = $user;
	}
}

function saveAuthFile(&$userlist, &$fileClutter)
{
	global $server, $worker;

	// Implode all file clutter
	$filecontents = implode("\n",	$fileClutter);

	// Implode all users
	foreach($userlist as &$user)
	{
		$filecontents .= "\n$user->authorization $user->usertoken " . (($user->username!='[none]') ? str_replace(' ', '_', $user->username) : '');
	}

	$res;
	// $remoteServer->request($res, 'edit_auth', Array($filecontents));
	$res = $worker->edit_auth($server, $filecontents);
	return $res;
}

function authNumToString($auth)
{
	$res = '';
	if($auth & AUTH_ADMIN)
		$res .= 'A';
	if($auth & AUTH_MOD)
		$res .= 'M';
	if($auth & AUTH_RANKED)
		$res .= 'R';
	if($auth & AUTH_BOT)
		$res .= 'B';
	if($auth & AUTH_BANNED)
		$res .= 'X';
	if($res=='')
		$res = '&nbsp;';
	return $res;
}

function authStringToNum($auth)
{
	$res = 0;
	
	if(strpos($auth, 'A')!==FALSE) $res += AUTH_ADMIN;
	if(strpos($auth, 'M')!==FALSE) $res += AUTH_MOD;
	if(strpos($auth, 'R')!==FALSE) $res += AUTH_RANKED;
	if(strpos($auth, 'B')!==FALSE) $res += AUTH_BOT;
	if(strpos($auth, 'X')!==FALSE) $res += AUTH_BANNED;
	
	return $res;
}

if($action=='auth_list')
{
	// Get the userlist
	$fileClutter = Array();
	$userlist = Array();
	loadAuthFile($userlist, $fileClutter);
		
	// Count the amount of users
	$count = count($userlist);

	// Create the output
	$title = 'view userlist';
	$content = <<<EOF
		<div style="width: 100%; text-align: left; font-weight: bold;">Legend</div>
		<ul style="width: 400px; margin: 0; text-align: left;">
			<li><strong>A</strong>: Administrator (red flag, access to !kick, !ban, !unban and !say commands)</li>
			<li><strong>M</strong>: Moderator (blue flag, access to !kick, !ban, !unban and !say commands)</li>
			<li><strong>R</strong>: Ranked (green flag, no special privileges)</li>
			<li><strong>B</strong>: Robot (no flag, no special privileges)</li>
		</ul><br />
		<div style="width: 100%; text-align: right; font-style: italic; color: grey;">Changes only take effect after a server restart</div>
EOF;
	
	
	$content .= <<<EOF
				<div style="position:relative; border-top: 1px solid #000000; text-align: left; height: 29px; text-align: center; padding-top: 10px;">
					EDIT EXISTING USERS
				</div>
EOF;

	// Get the userlist
	$count = 0;
	foreach($userlist as &$user)
	{
		$authStr = authNumToString($user->authorization);
		
		$content .= <<<EOF
					<div id="usertd_{$count}" style="position:relative; border-top: 1px solid #000000; text-align: left; height: 29px;">
						<div id="usertd_{$count}_linenum" style="position: absolute; left: 0; top: 0;  height: 29px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; width: 20px;">$user->line</div>
						<input id="usertd_{$count}_auth" readonly="readonly" value="$authStr" style="position: absolute; left: 25px; top: 0; width: 45px; margin-top: 2px;" onFocus="javascript: this.select();" onKeyUp="javascript: checkAuth($count);" onBlur="javascript: checkAuth($count);" />
						<input type="text" id="usertd_{$count}_name" maxlength="40" readonly="readonly" value="$user->username" onFocus="javascript: this.select();" style="width: 250px; margin-left: 75px; margin-top: 2px;" />
						&nbsp;
						<div style="position: absolute; right: 0; top: 0; margin-top: 2px;">
							<img src="img/Pen.png" title="edit" alt="edit" id="usertd_{$count}_editButton" style="height: 25px; width: 25px;" onclick="javascript: userEditButtonClick($count);" />
							<img src="img/Trash.png" title="delete" alt="delete" style="height: 25px; width: 25px;" onclick="javascript: deleteUser($count);" />
						</div>
						
						<div id="usertd_{$count}_tokenbox" style="position: absolute; left: 75px; top: 29px; display: none;">
							<input id="usertd_{$count}_token" type="text" style="width: 250px;" onKeyUp="javascript: updateEncodedToken($count);" onFocus="javascript: this.select();" value="[unknown]" />
							<input id="usertd_{$count}_encodedtoken" type="text" style="width: 250px; font-family: monospace;" maxlength="40" onFocus="javascript: this.select();" onKeyUp="javascript: checkEncodedToken($count);" onBlur="javascript: checkEncodedToken($count);" value="$user->usertoken" />
						</div>
					</div>
EOF;
		++$count;
	}
	
	// Allow to add new user
	$content .= <<<EOF
				<div style="position:relative; border-top: 1px solid #000000; text-align: left; height: 29px; text-align: center; padding-top: 10px;">
					ADD NEW USERS
				</div>
EOF;
	
	// Print some new user adding rows
	$tempToken = 'new_user_token';
	$tempEncodedToken = sha1($tempToken);
	$authStrings = Array('A', 'M', 'R', 'MR', 'AR', 'B', 'MB', 'AB');
	for($usercount = $count; $count<$usercount+8; ++$count)
	{
		$authStr = $authStrings[$count-$usercount];
		$content .= <<<EOF
					<div id="usertd_{$count}" style="position:relative; border-top: 1px solid #000000; text-align: left; height: 80px;">
						<div id="usertd_{$count}_linenum" style="position: absolute; left: 0; top: 0;  height: 29px; border-right: 1px solid #000000; border-bottom: 1px solid #000000; width: 20px;">&nbsp;</div>
						<input id="usertd_{$count}_auth" value="$authStr" style="position: absolute; left: 25px; top: 0; width: 45px; margin-top: 2px;" onFocus="javascript: this.select();" onKeyUp="javascript: checkAuth($count);" onBlur="javascript: checkAuth($count);" />
						<input type="text" id="usertd_{$count}_name" maxlength="40" value="new username" onFocus="javascript: this.select();" style="width: 250px; margin-left: 75px; margin-top: 2px;" />
						&nbsp;
						<div style="position: absolute; right: 0; top: 0; margin-top: 2px;">
							<img src="img/Checkmark.png" title="Add this user" alt="add" id="usertd_{$count}_editButton" style="height: 25px; width: 25px;" onclick="javascript: userEditButtonClick($count);" />
							<img src="img/Trash.png" title="delete" alt="delete" style="height: 25px; width: 25px;" onclick="javascript: deleteUser($count);" />
						</div>
						
						<div id="usertd_{$count}_tokenbox" style="position: absolute; left: 75px; top: 29px; display: block;">
							<input id="usertd_{$count}_token" type="text" style="width: 250px;" onKeyUp="javascript: updateEncodedToken($count);" onFocus="javascript: this.select();" value="$tempToken" />
							<input id="usertd_{$count}_encodedtoken" type="text" style="width: 250px; font-family: monospace;" maxlength="40" onFocus="javascript: this.select();" onKeyUp="javascript: checkEncodedToken($count);" onBlur="javascript: checkEncodedToken($count);" value="$tempEncodedToken" />
						</div>
					</div>
EOF;
	}
	
	//$content .= '<br />A (admin), M (moderator), R (ranked), B (robot), X (banned).';
	
	// Print footer
	$footer = '<span style="color: red" id="errorBox"></span>';
	$onload = 'usercount = '.$count.';';
	sendOutput();
}
else if($action=='auth_edit')
{
	// Check the arguments
	if(!array_key_exists('name', $_GET)
		|| !array_key_exists('encodedtoken', $_GET)
		|| !array_key_exists('auth', $_GET)
	) {
		fatal_error('ERROR: Not enough parameters.');
	}

	// Get the userlist
	$fileClutter = Array();
	$userlist = Array();
	loadAuthFile($userlist, $fileClutter);
	
	// Create a new user
	$user = new User;
	
	// Get the arguments
	$user->username = addslashes($_GET['name']);
	$user->usertoken = addslashes($_GET['encodedtoken']);
	$user->authorization = authStringToNum(addslashes($_GET['auth']));
	
	// Search for an identical token
	$userFound = false;
	foreach($userlist as &$tmpuser)
	{
		if($tmpuser->usertoken == $user->usertoken)
		{
			$tmpuser = $user;
			$userFound = true;
			break;
		}
	}
	
	// Add the user if he doesn't exist
	if(!$userFound)
		$userlist[] = $user;
	
	// Save the userlist again
	$result = saveAuthFile($userlist, $fileClutter);
	
	if($userFound)
		echo "User edited";
	else
		echo "User added";
}
else if($action=='auth_delete')
{
	// Check the arguments
	if(!array_key_exists('encodedtoken', $_GET)
	) {
		fatal_error('ERROR: Not enough parameters.');
	}

	// Get the userlist
	$fileClutter = Array();
	$userlist = Array();
	loadAuthFile($userlist, $fileClutter);
	
	// Get the argument
	$usertoken = addslashes($_GET['encodedtoken']);
	
	// Delete the user at the correct location in the list
	$userFound = false;
	for($i=0; $i<count($userlist); ++$i)
	{
		if($userlist[$i]->usertoken == $usertoken)
		{
			unset($userlist[$i]);
			$userFound = true;
			break;
		}
	}

	if($userFound)
	{
		$result = saveAuthFile($userlist, $fileClutter);
		echo 'User deleted.';
	}
	else
		echo 'User NOT deleted (ERROR: user not found).';
}

?>