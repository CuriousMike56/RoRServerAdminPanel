		
		
		var urlprefix = '';
		var servername = '';
		var requestInProgress = false;
		function startServer()
		{
			var xmlhttp;
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				alert("Please wait for previous request to time out.");
				return;
			}
			requestInProgress = true;
			
			// Log the request
			var obj = document.getElementById("msgBox");
			obj.innerHTML = "Starting server...";

			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					// Update the server status
					updateServerStatus(xmlhttp.responseText.substr(0,1));
					
					// Show the raw response in the log window
					var obj = document.getElementById("msgBox");
					obj.innerHTML = "Server start result:<br />"+xmlhttp.responseText.substr(1);
					
					// Request is finished
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					var obj = document.getElementById("msgBox");
					obj.innerHTML = "Error while getting data ("+xmlhttp.status+").<br />Your request may or may not have been processed by the server.";
					updateServerStatus("FAIL");
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET",urlprefix+"&action=server_start&cacheprevention="+Math.random(),true);
			xmlhttp.send();
		}
		
		
		function stopServer()
		{
			var xmlhttp;
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				alert("Please wait for previous request to time out.");
				return;
			}
			requestInProgress = true;
			
			// Log the request
			var obj = document.getElementById("msgBox");
			obj.innerHTML = "Stopping server...";

			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					// Update the server status
					updateServerStatus(xmlhttp.responseText.substr(0,1));
					
					// Show the raw response in the log window
					var obj = document.getElementById("msgBox");
					obj.innerHTML = "Server stop result:<br />"+xmlhttp.responseText.substr(1);
					
					// Request is finished
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					var obj = document.getElementById("msgBox");
					obj.innerHTML = "Error while getting data ("+xmlhttp.status+").<br />Your request may or may not have been processed by the server.";
					updateServerStatus("FAIL");
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET",urlprefix+"&action=server_stop&cacheprevention="+Math.random(),true);
			xmlhttp.send();
		}
		

		function killServer()
		{
			var xmlhttp;
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				alert("Please wait for previous request to time out.");
				return;
			}
			requestInProgress = true;
			
			// Log the request
			var obj = document.getElementById("msgBox");
			obj.innerHTML = "Killing server...";

			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					// Update the server status
					updateServerStatus(xmlhttp.responseText.substr(0,1));
					
					// Show the raw response in the log window
					var obj = document.getElementById("msgBox");
					obj.innerHTML = "Server stop result:<br />"+xmlhttp.responseText.substr(1);
					
					// Request is finished
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					var obj = document.getElementById("msgBox");
					obj.innerHTML = "Error while getting data ("+xmlhttp.status+").<br />Your request may or may not have been processed by the server.";
					updateServerStatus("FAIL");
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET",urlprefix+"&action=server_kill&cacheprevention="+Math.random(),true);
			xmlhttp.send();
		}
		
		function printServerStatus2()
		{
			var xmlhttp;
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				alert("Please wait for previous request to time out.");
				return;
			}
			requestInProgress = true;
			
			// Log the request
			var obj = document.getElementById("msgBox");
			obj.innerHTML = "Getting server status...";

			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					// Update the server status
					updateServerStatus(xmlhttp.responseText.substr(0,1));
					
					// Show the raw response in the log window
					var obj = document.getElementById("msgBox");
					obj.innerHTML = "Server status result:<br />"+xmlhttp.responseText.substr(1);
					
					// Request is finished
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					var obj = document.getElementById("msgBox");
					obj.innerHTML = "Error while getting data ("+xmlhttp.status+").<br />Your request may or may not have been processed by the server.";
					updateServerStatus("FAIL");
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET",urlprefix+"&action=server_status&cacheprevention="+Math.random(),true);
			xmlhttp.send();
		}
		
		function getServerStatus()
		{
			// If the server variable is not set
			if(servername=='')
			{
				updateServerStatus("serverlist");
				return;
			}
			var xmlhttp;
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				return;
			}
			requestInProgress = true;
			
			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					// Update the server status
					updateServerStatus(xmlhttp.responseText.substr(0,1));
					
					// Request is finished
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET",urlprefix+"&action=server_status&cacheprevention="+Math.random(),true);
			xmlhttp.send();
		}
		
		function updateServerStatus(num)
		{
			if(num=="0")
			{
				document.getElementById("statusImg").src = "img/status/Error.png";
				document.getElementById("statusText").innerHTML = "Server is Offline";
			}
			else if(num=="2")
			{
				document.getElementById("statusImg").src = "img/status/Valid.png";
				document.getElementById("statusText").innerHTML = "Server is Online";
			}
			else if(num=="1")
			{
				document.getElementById("statusImg").src = "img/status/Warning.png";
				document.getElementById("statusText").innerHTML = "Conflicted Offline";
			}
			else if(num=="3")
			{
				document.getElementById("statusImg").src = "img/status/Warning.png";
				document.getElementById("statusText").innerHTML = "Conflicted Online";
			}
			else if(num=="serverlist")
			{
				//document.getElementById("statusImg").src = "img/Folder.png";
				//document.getElementById("statusText").innerHTML = "";			
				document.getElementById("statusImg").src = "img/Info.png";
				document.getElementById("statusText").innerHTML = "about";
				
				var statusTd = document.getElementById("statusTd");
				statusTd.className += " tdlink";
				if(statusTd.addEventListener) // Firefox, Opera
					statusTd.addEventListener('click',goToAboutPage,false);
				else if (statusTd.attachEvent)// IE
					statusTd.attachEvent("onclick", goToAboutPage);
				else // ?
					elem['click'] = goToAboutPage;
			}
			else
			{
				document.getElementById("statusImg").src = "img/status/Help.png";
				document.getElementById("statusText").innerHTML = "Unknown Status";
			}
		}
		
		function goToAboutPage()
		{
			window.location.href = '?action=about';
		}
		
		
		
		
		
		
		
		
		
		
		
		
		var previousPage = 0;
		var logFileSize = 0;
		function showPreviousPage()
		{
			var xmlhttp;
			
			// Only do something if previous page exists
			if(previousPage<0)
			{
				alert("No data available.");
				return;
			}
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				alert("Please wait for previous request to time out.");
				return;
			}
			requestInProgress = true;

			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					var obj = document.getElementById("logbox");
					obj.innerHTML = xmlhttp.responseText.substr(20) + obj.innerHTML;
					--previousPage;
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					alert("Error while getting data ("+xmlhttp.status+")");
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET",urlprefix+"&action=log_view&output_type=raw&page="+previousPage+"&cacheprevention="+Math.random(),true);
			xmlhttp.send();
		}
		
		function showNewerData(silent)
		{
			var xmlhttp;
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				if(!silent) alert("Please wait for previous request to time out.");
				return;
			}
			requestInProgress = true;

			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					var obj = document.getElementById("logbox");
					var scroll = ((window.innerHeight + window.pageYOffset)>=document.body.offsetHeight-1); /*document.body.scrollTop + */
					var tmp = xmlhttp.responseText.substr(0,20);
					if(tmp=="99999999999999999999")
					{
						obj.innerHTML += xmlhttp.responseText.substr(20);
						toggleAutoUpdating();
					}
					else
					{
						var txt = xmlhttp.responseText.substr(20);
						if(txt!="NO_DATA")
							obj.innerHTML += txt;
						tmpLogFileSize = parseInt(tmp);
						if(!isNaN(tmpLogFileSize))
							logFileSize = tmpLogFileSize;
					}
					if(scroll)
						window.scrollTo(window.pageXOffset, document.body.scrollHeight);
					
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					if(!silent) alert("Error while getting data ("+xmlhttp.status+")");
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET",urlprefix+"&action=log_view&output_type=raw&start="+logFileSize+"&cacheprevention="+Math.random(),true);
			xmlhttp.send();
		}
		
		var autoUpdate;
		function toggleAutoUpdating()
		{
			if(autoUpdate!=undefined)
			{
				document.getElementById('freezengo').innerHTML = "enable auto-update";
				autoUpdate = clearInterval(autoUpdate);
			}
			else
			{
				document.getElementById('freezengo').innerHTML = "freeze log";
				autoUpdate = setInterval("showNewerData(true);",5000);
			}
		}
		
		
		
		var requestInProgress = false;
		var currentRequestArg;
		function copyServer(num, namefrom)
		{
			var xmlhttp;
			
			var nameto=prompt("Copy '"+namefrom+"' to ...?","");
			if (nameto==null || nameto=="")
			{
				return;
			}
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				alert("Please wait for previous request to time out.");
				return;
			}
			requestInProgress = true;

			addRow(nameto);
			
			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET","?server="+namefrom+"&action=mngmnt_copy_server&namefrom="+namefrom+"&nameto="+nameto+"&cacheprevention="+Math.random(),true);
			xmlhttp.send();
		}
		
		
		function deleteServer(num, name)
		{
			var xmlhttp;
			
			// Are you sure?
			if(confirm("Are you sure that you wish to delete server '"+name+"'?")!=true)
				return;
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				alert("Please wait for previous request to time out.");
				return;
			}
			requestInProgress = true;
			
			// delete the row
			try
			{
				document.getElementById("contentBox").removeChild(document.getElementById("row_"+num));
			}
			catch(e)
			{
				document.getElementById("serverlist_addedServers").removeChild(document.getElementById("row_"+num));
			}

			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET","?server="+name+"&action=mngmnt_delete_server&name="+name+"&cacheprevention="+Math.random(),true);
			xmlhttp.send();
		}
		
		function addRow(name) {
	
			// Get a free num
			var num = 0;
			for(var i=0; i<99999; ++i)
			{
				if(!document.getElementById("row_"+i))
				{
					num = i;
					break;
				}
			}
		
			// Get the content element
			var box = document.getElementById("serverlist_addedServers");
			
			// build the data		
			var data = '<div id="row_'+num+'" class="serverlist_row"> \
							<div class="serverlist_statusfield" onClick="javascript: window.location.href = \'?server='+name+'&action=server_control\';"> \
								<img class="serverlist_statusimg" id="status_'+num+'" src="img/status/Error.png" height="25" title="This server is offline" /> \
								<span class="serverlist_namefield">'+name+'</span> \
							</div> \
							<div class="serverlist_actionfield"> \
								<img  src="img/Search.png"     height="25" title="View this server in more detail"  onClick="javascript: window.location.href = \'?server='+name+'&action=server_control\';" /> \
								<img  src="img/Files_Copy.png" height="25" title="Copy this server configuration"   onClick="javascript: copyServer('+num+', \''+name+'\');" /> \
								<img  src="img/Trash.png"      height="25" title="Delete this server configuration" onClick="javascript: deleteServer('+num+', \''+name+'\');" /> \
							</div> \
						</div> \
			';
			
			box.innerHTML += data;
		}
		
		
		
		
		
		
		
		
		
		
		
		
		var usercount = 0;
		function editUser(num)
		{
			var auth = document.getElementById("usertd_"+num+"_auth").value;
			var name = document.getElementById("usertd_"+num+"_name").value;
			var token = document.getElementById("usertd_"+num+"_token").value;
			var encodedtoken = document.getElementById("usertd_"+num+"_encodedtoken").value;
			var linenum = document.getElementById("usertd_"+num+"_linenum").innerHTML;

			if(checkAuth(num) && checkEncodedToken(num) && name.length!=0)
			{
				var xmlhttp;
				
				// Only 1 request at a time
				if(requestInProgress)
				{
					alert("Please wait for previous request to time out.");
					return;
				}
				requestInProgress = true;
				
				// Disable all fields
				disableUserEditMode(num);

				// Do AJAX request
				if (window.XMLHttpRequest)
				{// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp=new XMLHttpRequest();
				}
				else
				{// code for IE6, IE5
					xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
				}
				
				xmlhttp.onreadystatechange=function()
				{
					if (xmlhttp.readyState==4 && xmlhttp.status==200)
					{
						document.getElementById("errorBox").innerHTML = xmlhttp.responseText;
						alert(xmlhttp.responseText);
						requestInProgress = false;
					}
					else if (xmlhttp.readyState==4)
					{
						alert("Error while getting data ("+xmlhttp.status+")");
						requestInProgress = false;
					}
				}
				xmlhttp.open("GET",urlprefix+"&action=auth_edit&name="+name+"&encodedtoken="+encodedtoken+"&auth="+auth+"&line="+linenum+"&cacheprevention="+Math.random(),true);
				xmlhttp.send();
			}
			else
				alert("A problem was detected with one of the fields.");
		}
		
		function enableUserEditMode(num)
		{
			document.getElementById("usertd_"+num+"_auth").readOnly = false;
			document.getElementById("usertd_"+num+"_name").readOnly = false;
			document.getElementById("usertd_"+num).style.height = "80px"
			document.getElementById("usertd_"+num+"_tokenbox").style.display = "block"
			document.getElementById("usertd_"+num+"_editButton").src = "img/Checkmark.png"
			document.getElementById("usertd_"+num+"_editButton").title = "Apply Changes"
		}
		
		function disableUserEditMode(num)
		{
			document.getElementById("usertd_"+num+"_auth").readOnly = true;
			document.getElementById("usertd_"+num+"_name").readOnly = true;
			document.getElementById("usertd_"+num).style.height = "29px"
			document.getElementById("usertd_"+num+"_tokenbox").style.display = "none"
			document.getElementById("usertd_"+num+"_editButton").src = "img/Pen.png"
			document.getElementById("usertd_"+num+"_editButton").title = "edit"
		}
		
		function userEditButtonClick(num)
		{
			if(document.getElementById("usertd_"+num+"_auth").readOnly==false)
				editUser(num);
			else
				enableUserEditMode(num);
		}
		
		function deleteUser(num)
		{
			var encodedtoken = document.getElementById("usertd_"+num+"_encodedtoken").value;
			
			var xmlhttp;
			
			// Only 1 request at a time
			if(requestInProgress)
			{
				alert("Please wait for previous request to time out.");
				return;
			}
			requestInProgress = true;
			
			// Remove row
			document.getElementById("contentBox").removeChild(document.getElementById("usertd_"+num));

			// Do AJAX request
			if (window.XMLHttpRequest)
			{// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}
			else
			{// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					document.getElementById("errorBox").innerHTML = xmlhttp.responseText;
					alert(xmlhttp.responseText);
					requestInProgress = false;
				}
				else if (xmlhttp.readyState==4)
				{
					alert("Error while getting data ("+xmlhttp.status+")");
					requestInProgress = false;
				}
			}
			xmlhttp.open("GET",urlprefix+"&action=auth_delete&encodedtoken="+encodedtoken+"&cacheprevention="+Math.random(),true);
			xmlhttp.send();
			
		}
		
		function updateEncodedToken(num)
		{
			var token = document.getElementById("usertd_"+num+"_token").value;
			if(token.length==0)
			{
				document.getElementById("usertd_"+num+"_encodedtoken").value = "";
				return;
			}
			
			document.getElementById("usertd_"+num+"_encodedtoken").value = SHA1(token).toUpperCase();
		}
		
		function checkEncodedToken(num)
		{
			var encodedtoken = document.getElementById("usertd_"+num+"_encodedtoken").value;
			var token = document.getElementById("usertd_"+num+"_token").value;
			if(encodedtoken.length==0)
			{
				document.getElementById("errorBox").innerHTML = "";
			}
			else if(encodedtoken.search("[^A-F0-9]")>=0)
			{
				document.getElementById("errorBox").innerHTML = "ERROR: Forbidden characters detected in the encoded usertoken!";
			}
			else if(encodedtoken.length!=40)
			{
				document.getElementById("errorBox").innerHTML = "ERROR: An encoded usertoken should have length 40!";
			}
			else if(SHA1(token).toUpperCase()==encodedtoken)
			{
				document.getElementById("errorBox").innerHTML = "";
				return true;
			}
			else
			{
				document.getElementById("errorBox").innerHTML = "";
				document.getElementById("usertd_"+num+"_token").value = "[unknown]";
				return true;
			}
			return false;
		}
		
		function checkAuth(num)
		{
			var auth = document.getElementById("usertd_"+num+"_auth").value;
			if(auth.length==0)
			{
				document.getElementById("errorBox").innerHTML = "";
			}
			else if(auth.search("[^AMRBX]")>=0)
			{
				document.getElementById("errorBox").innerHTML = "ERROR: Forbidden characters detected in the authorization code!<br />Possible characters: A (admin), M (moderator), R (ranked), B (robot), X (banned).";
			}
			else
			{
				document.getElementById("errorBox").innerHTML = "";
				return true
			}
			return false;
		}
