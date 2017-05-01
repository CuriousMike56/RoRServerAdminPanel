#!/bin/env python

import subprocess, os, shutil, time, fnmatch, re, collections, httplib, urllib, ConfigParser, sys, string
import RPCServer

# Vars
CONFIG_FILE    = ""
HOST           = ""
PORT           = 0
BIN_FILES      = []
LOG_DIR        = ""
CFG_DIR        = ""
RES_DIR        = ""
OLDLOG_DIR     = ""
DELETEDCFG_DIR = ""
AUTO_START     = []
USERS          = {}
MOTD           = ""

# Functions
def logChangeEvent(eventName, server):
    changelog.popleft()
    changelog.append("%d ### %s ### %s" % (time.time(), server, eventName))

def logError(eventName, server, errorMessage):
    errorlog.popleft()
    errorlog.append("%d ### %s ### %s ### %s" % (time.time(), server, eventName, errorMessage))
        
def loadConfig():
    global CONFIG_FILE, HOST, PORT, BIN_FILES, LOG_DIR, CFG_DIR, RES_DIR, OLDLOG_DIR, DELETEDCFG_DIR, AUTO_START, USERS, MOTD
    
    # Get config file
    __CONFIG_FILE = ""
    for arg in sys.argv: 
        if arg[-3:] == 'cfg' or arg[-3:] == 'ini':
            __CONFIG_FILE = arg
            break
    if __CONFIG_FILE == "":
        raise Exception("Missing parameter: config file")

    # read config
    config = ConfigParser.RawConfigParser(allow_no_value=True)
    config.read(__CONFIG_FILE)

    if not config.has_section('daemon'):
        print "Couldn't load config file or section '[daemon]' is missing"
        sys.exit()

    __HOST = config.get('daemon', 'listen_interface')
    __PORT = config.getint('daemon', 'port')   

    # No trailing slashes anywhere!
    __BIN_FILES   = config.get('daemon', 'bin_files').split(',')
    __BIN_FILES[:] = [file.strip() for file in __BIN_FILES]
    
    __AUTO_START   = config.get('daemon', 'auto_start').split(',')
    __AUTO_START[:] = [file.strip() for file in __AUTO_START]
    
    __LOG_DIR     = config.get('daemon', 'log_directory')
    if __LOG_DIR[-1] == '/':
        __LOG_DIR = __LOG_DIR[:-1]
        
    __CFG_DIR     = config.get('daemon', 'config_directory')
    if __CFG_DIR[-1] == '/':
        __CFG_DIR = __CFG_DIR[:-1]
        
    __RES_DIR     = config.get('daemon', 'resource_directory')

    __OLDLOG_DIR     = config.get('daemon', 'oldlog_directory')
    if __OLDLOG_DIR[-1] == '/':
        __OLDLOG_DIR = __OLDLOG_DIR[:-1]
        
    __DELETEDCFG_DIR = config.get('daemon', 'oldconfig_directory')
    if __DELETEDCFG_DIR[-1] == '/':
        __DELETEDCFG_DIR = __DELETEDCFG_DIR[:-1]
    
    for user in config.items('users'):
        USERS[user[0]] = user[1]
    
    MOTD = config.get('daemon', 'motd')
        
    CONFIG_FILE    = __CONFIG_FILE
    HOST           = __HOST
    PORT           = __PORT
    BIN_FILES      = __BIN_FILES
    AUTO_START     = __AUTO_START
    LOG_DIR        = __LOG_DIR
    CFG_DIR        = __CFG_DIR
    RES_DIR        = __RES_DIR
    OLDLOG_DIR     = __OLDLOG_DIR
    DELETEDCFG_DIR = __DELETEDCFG_DIR

loadConfig()
server_process = {}

# statistics
bytesUp   = long(0)
bytesDown = long(0)
connCount = 0
startTime = time.time()
changelog = collections.deque(["" for x in range(20)])
errorlog  = collections.deque(["" for x in range(20)])

# constants
STATUS_OFFLINE          = 0
STATUS_CONFLICT_OFFLINE = 1
STATUS_ONLINE           = 2
STATUS_CONFLICT_ONLINE  = 3

# blacklist for files
fileReplace = re.compile(r'[^a-zA-Z0-9.\-_]+')

# blacklist for servernames
nameReplace = re.compile(r'[^a-zA-Z0-9.\-_\s]+')


class exposedFunctions:

    def get_statistics(self):
        # Get some folder sizes
        logFolderSize = 0
        logFolderCount = 0
        for f in os.listdir(LOG_DIR):
            file = "%s/%s" % (LOG_DIR, f)
            if os.path.isfile(file):
                logFolderSize += os.path.getsize(file)
                logFolderCount += 1
        for f in os.listdir(OLDLOG_DIR):
            file = "%s/%s" % (OLDLOG_DIR, f)
            if os.path.isfile(file):
                logFolderSize += os.path.getsize(file)
                logFolderCount += 1
                
        cfgFolderSize = 0
        cfgFolderCount = 0;
        for f in os.listdir(CFG_DIR):
            file = "%s/%s" % (CFG_DIR, f)
            if os.path.isfile(file):
                cfgFolderSize += os.path.getsize(file)
                cfgFolderCount += 1
        for f in os.listdir(DELETEDCFG_DIR):
            file = "%s/%s" % (DELETEDCFG_DIR, f)
            if os.path.isfile(file):
                cfgFolderSize += os.path.getsize(file)
                cfgFolderCount += 1
    
        return {
            'bytesDown': bytesDown,
            'bytesUp': bytesUp,
            'connCount': connCount,
            'startTime': startTime,
            'currTime': time.time(),
            'logDirSize': logFolderSize,
            'logDirCount': logFolderCount,
            'cfgDirSize': cfgFolderSize,
            'cfgDirCount': cfgFolderCount
        }
        
    def echo(self, arg):
        return {'content': arg}
    
    def get_daemon_message(self):
        global MOTD
        return {'content': MOTD}
    
    def mngmnt_list_servers(self):
        # with open(SERVLIST, 'r') as f:
        # writeData(conn, f.read())
        result = {}
        files = os.listdir(CFG_DIR)
        for file in files:
            if file[-3:]=='cfg':
                name = file[:-4]
                if name in server_process and server_process[name]['process']!=None and server_process[name]['process'].poll()==None:
                    if server_process[name]['running']==False:
                        # online but should be offline
                        result[name] = STATUS_CONFLICT_ONLINE
                    else:
                        # online
                        result[name] = STATUS_ONLINE
                elif name in server_process and server_process[name]['running']==True:
                    # offline but should be online
                    result[name] = STATUS_CONFLICT_OFFLINE
                else:
                    # offline
                    result[name] = STATUS_OFFLINE
                
        return result
                
    # Request bin files list
    def list_binfiles(self):
        keys = xrange(len(BIN_FILES))
        keys = [str(i) for i in keys]
        result = dict(zip(keys, BIN_FILES))
        return result        
    
    # Request the changelog
    def get_changelog(self):
        keys = xrange(len(changelog))
        keys = [str(i) for i in keys]
        result = dict(zip(keys, changelog))
        return result
    
    # Request the errorlog
    def get_errorlog(self):
        keys = xrange(len(errorlog))
        keys = [str(i) for i in keys]
        result = dict(zip(keys, errorlog))
        return result                        
        
    # request config of server
    def request_config(self, serverName):
        serverName = nameReplace.sub('_', serverName)
    
        with open("%s/%s.cfg" % (CFG_DIR, serverName), 'r') as f:
            return {'content': f.read()}

    # edit config of server
    def edit_config(self, serverName, config):
        serverName = nameReplace.sub('_', serverName)
    
        with open("%s/%s.cfg" % (CFG_DIR, serverName), 'w') as f:
            f.write(config)
            return {'content': config}
        logChangeEvent(action, serverName)
                        
    # Request logfile list
    def list_oldlog(self, serverName):
        serverName = nameReplace.sub('_', serverName)
    
        result = {}
        for file in os.listdir(OLDLOG_DIR):
            if fnmatch.fnmatch(file, '%s.*.log' % serverName):
                result[file] = os.path.getsize("%s/%s" % (OLDLOG_DIR, file))
        result['count'] = len(result)
        return result
            
    # Request old logfile
    def download_oldlog(self, fileName):
        # Get the filename
        fileName = "%s/%s" % (OLDLOG_DIR, fileReplace.sub('_', fileName))

        # Send file
        with open(fileName, 'r') as f:
            return {'content': f.read(), 'filesize': os.path.getsize(fileName)}
            
    # Request old logfile
    def logfile(self, fileName):
        # Get the filename
        fileName = "%s/%s" % (OLDLOG_DIR, fileReplace.sub('_', fileName))

        # Send file
        with open(fileName, 'r') as f:
            return f.read()
            
    # request full log of server
    def download_log(self, serverName):
        serverName = nameReplace.sub('_', serverName)
            
        # Get the filename
        fileName = "%s/%s.log" % (LOG_DIR, serverName)

        # Send file
        with open(fileName, 'r') as f:
            return {'content': f.read(), 'filesize': os.path.getsize(fileName)}
            
    # request full log of server
    def serverlog(self, serverName):
        serverName = nameReplace.sub('_', serverName)
            
        # Get the filename
        filename = "%s/%s.log" % (LOG_DIR, serverName)

        # Send file
        with open(filename, 'r') as f:
            return f.read()
        
    # request partial log of server
    def request_log(self, serverName, start, len):
        serverName = nameReplace.sub('_', serverName)
    
        # Get the start argument
        start = int(start)
        if start<0:
            start = 0
        
        # Get the filename
        filename = "%s/%s.log" % (LOG_DIR, serverName)
        
        # Does the log file exist?
        if not os.path.exists(filename):
            return {'content': "NO_DATA", 'cursor': 0}
        else:
            # Get the filesize
            filesize = os.path.getsize(filename)
            
            # Get the length of the data that we have to get
            length = filesize-start
            if length>len:
                length = len
            #if length>32768:
            #    length = 32768
            
            # Send result
            if length<0:
                if filesize<32768:
                    return {'content': "\r\n\r\nEND OF FILE\r\nRESTARTING FROM POSITION 0\r\n\r\n\r\n", 'cursor': 0}
                else:
                    return {'content': "\r\n\r\nEND OF FILE\r\nRESTARTING FROM POSITION %d\r\n\r\n\r\n" % (filesize), 'cursor': filesize}
            elif length==0:
                return {'content': "NO_DATA", 'cursor': filesize}
            else:
                with open(filename, 'r') as f:
                    f.seek(start)
                    cursor = start+length
                    if cursor>filesize:
                        cursor = filesize
                    return {'content': f.read(length), 'cursor': cursor}
    
    # request log size
    def request_logsize(self, serverName):
        serverName = nameReplace.sub('_', serverName)
    
        # Get the filename
        filename = "%s/%s.log" % (LOG_DIR, serverName)
        
        if os.path.exists(filename):
            return {'logsize': os.path.getsize(filename)}
        else:
            return {'logsize': 0}
    
    # request motd of server
    def request_motd(self, serverName):
        serverName = nameReplace.sub('_', serverName)
        
        with open("%s/%s.motd" % (CFG_DIR, serverName), 'r') as f:
            return {'content': f.read()}
    
    # edit motd of server
    def edit_motd(self, serverName, motd):
        serverName = nameReplace.sub('_', serverName)
        logChangeEvent("edit_motd", serverName)
        
        with open("%s/%s.motd" % (CFG_DIR, serverName), 'w') as f:
            f.write(motd)
            return {'content': motd}
    
    # request rules of server
    def request_rules(self, serverName):
        serverName = nameReplace.sub('_', serverName)
        
        with open("%s/%s.rules" % (CFG_DIR, serverName), 'r') as f:
            return {'content': f.read()}
    
    # edit rules of server
    def edit_rules(self, serverName, rules):
        serverName = nameReplace.sub('_', serverName)
        logChangeEvent("edit_rules", serverName)
        
        with open("%s/%s.rules" % (CFG_DIR, serverName), 'w') as f:
            f.write(rules)
            return {'content': rules}
    
    # request authorizations file of server
    def request_auth(self, serverName):
        serverName = nameReplace.sub('_', serverName)
        
        with open("%s/%s.auth" % (CFG_DIR, serverName), 'r') as f:
            return {'content': f.read()}
    
    # edit authorizations file of server
    def edit_auth(self, serverName, auth):
        serverName = nameReplace.sub('_', serverName)
        logChangeEvent("edit_auth", serverName)
        
        with open("%s/%s.auth" % (CFG_DIR, serverName), 'w') as f:
            f.write(auth)
            return {'content': auth}
    
    # server say
    def server_say(self, serverName, message):
        serverName = nameReplace.sub('_', serverName)

        # Get the contents of the server configuration file
        f = open("%s/%s.cfg" % (CFG_DIR, serverName), 'r')
        cfg = f.read()
        f.close()
        
        # Get the IP, port and webserver status from the file
        myIP     = ""
        
        pattern = re.compile("ip([\s]*)=([\s]*)([a-zA-Z0-9\.\-_/\\\(\)]+)")
        match = pattern.search(cfg)
        if match:
            myIP = match.group(3).strip()
            
        myPORT   = 0
        pattern = re.compile("webserverport([\s]*)=([\s]*)([0-9]+)")
        match = pattern.search(cfg)
        if match:
            myPORT = int(match.group(3).strip())
        else:
            pattern = re.compile("port([\s]*)=([\s]*)([0-9]+)")
            match = pattern.search(cfg)
            if match:
                myPORT = int(match.group(3).strip())
                myPORT += 100
            
        mySTATUS = ""
        pattern = re.compile("webserver([\s]*)=([\s]*)([a-z])")
        match = pattern.search(cfg)
        if match:
            mySTATUS = match.group(3).strip()
            
        if(myIP=="" or myPORT==0 or mySTATUS != "y"):
            return {
                'result': 0,
                'status': self.getServerStatus(serverName),
                'content': "Failed to parse configuration file or webserver is disabled."
            }
        else:
            req = httplib.HTTPConnection("%s:%d" % (myIP, myPORT))
            req.request("GET", "/action/say/?message=%s" % urllib.quote(message))
            r = req.getresponse()
            return {
                'result': 1,
                'status': self.getServerStatus(serverName),
                'http_status': r.status,
                'http_reason': r.reason,
                'content': r.read()
            }
    
    # Get URL of admin panel
    def get_cpurl(self, serverName):
        serverName = nameReplace.sub('_', serverName)

        # Get the contents of the server configuration file
        f = open("%s/%s.cfg" % (CFG_DIR, serverName), 'r')
        cfg = f.read()
        f.close()
        
        # Get the IP, port and webserver status from the file
        myIP     = ""
        
        pattern = re.compile("ip([\s]*)=([\s]*)([a-zA-Z0-9\.\-_/\\\(\)]+)")
        match = pattern.search(cfg)
        if match:
            myIP = match.group(3).strip()
            
        myPORT   = 0
        pattern = re.compile("webserverport([\s]*)=([\s]*)([0-9]+)")
        match = pattern.search(cfg)
        if match:
            myPORT = int(match.group(3).strip())
        else:
            pattern = re.compile("port([\s]*)=([\s]*)([0-9]+)")
            match = pattern.search(cfg)
            if match:
                myPORT = int(match.group(3).strip())
                myPORT += 100
            
        mySTATUS = ""
        pattern = re.compile("webserver([\s]*)=([\s]*)([a-z])")
        match = pattern.search(cfg)
        if match:
            mySTATUS = match.group(3).strip()
            
        if(myIP=="" or myPORT==0 or mySTATUS != "y"):
            return {
                'result': 0,
                'status': self.getServerStatus(serverName),
                'content': "Failed to parse configuration file or webserver is disabled."
            }
        else:
            return {
                'result': 1,
                'status': self.getServerStatus(serverName),
                'content': "%s:%d" % (myIP, myPORT)
            }
    
    def mngmnt_copy_server(self, serverName, newName):
        serverName = nameReplace.sub('_', serverName)
        newName = nameReplace.sub('_', newName)
        logChangeEvent("mngmnt_copy_server", serverName)
        
        
        shutil.copyfile("%s/%s.cfg" % (CFG_DIR, serverName), "%s/%s.cfg" % (CFG_DIR, newName))
        shutil.copyfile("%s/%s.auth" % (CFG_DIR, serverName), "%s/%s.auth" % (CFG_DIR, newName))
        shutil.copyfile("%s/%s.rules" % (CFG_DIR, serverName), "%s/%s.rules" % (CFG_DIR, newName))
        shutil.copyfile("%s/%s.motd" % (CFG_DIR, serverName), "%s/%s.motd" % (CFG_DIR, newName))
        
        return {'result': 1}
    
    def mngmnt_delete_server(self, serverName):
        serverName = nameReplace.sub('_', serverName)
        logChangeEvent("mngmnt_delete_server", serverName)
        
        thetime = time.time()
        
        if not os.path.exists("%s/deleted" % CFG_DIR):
            os.makedirs("%s/deleted" % CFG_DIR)
        
        shutil.move("%s/%s.cfg" % (CFG_DIR, serverName), "%s/deleted/%s.%d.cfg" % (CFG_DIR, serverName, thetime))
        shutil.move("%s/%s.auth" % (CFG_DIR, serverName), "%s/deleted/%s.%d.auth" % (CFG_DIR, serverName, thetime))
        shutil.move("%s/%s.rules" % (CFG_DIR, serverName), "%s/deleted/%s.%d.rules" % (CFG_DIR, serverName, thetime))
        shutil.move("%s/%s.motd" % (CFG_DIR, serverName), "%s/deleted/%s.%d.motd" % (CFG_DIR, serverName, thetime))
        
        return {'result': 1}
    
    def server_status(self, serverName):
        serverName = nameReplace.sub('_', serverName)
        
        result = {
            'exists': 0,
            'running': 0,
            'conflict': 0
        }
        if serverName in server_process:
            result['exists'] = 1
            
            if server_process[serverName]['process']!=None and server_process[serverName]['process'].poll()==None:
                result['running'] = 1
            
            if result['running']!=server_process[serverName]['running']:
                result['conflict'] = 1
        
        if result['conflict'] and result['running']:
            result['status'] = STATUS_CONFLICT_ONLINE
            result['message'] = "Server is online, but should be offline."
        elif result['conflict']:
            result['status'] = STATUS_CONFLICT_OFFLINE
            result['message'] = "Server is offline, but should be online."
        elif result['running']:
            result['status'] = STATUS_ONLINE
            result['message'] = "Server is online."
        else:
            result['status'] = STATUS_OFFLINE
            result['message'] = "Server is offline."

        return result
    
    def reload_config(self):
        logChangeEvent("reload_config", "ok")
        
        loadConfig()
        
        rpc.deleteAllUsers()
        rpc.addUsersByDict(USERS)
        return {'message': 'ok'}
    
    def authenticate(self):
        logChangeEvent("authenticate", "ok")
        return {'result': 'ok'}
    
    def getServerStatus(self, serverName):
        serverName = nameReplace.sub('_', serverName)
        
        result = {
            'exists': 0,
            'running': 0,
            'conflict': 0
        }
        if serverName in server_process:
            result['exists'] = 1
                    
            if server_process[serverName]['process']!=None and server_process[serverName]['process'].poll()==None:
                result['running'] = 1
            
            if result['running']!=server_process[serverName]['running']:
                result['conflict'] = 1
        
        if result['conflict'] and result['running']:
            return STATUS_CONFLICT_ONLINE
        elif result['conflict']:
            return STATUS_CONFLICT_OFFLINE
        elif result['running']:
            return STATUS_ONLINE
        else:
            return STATUS_OFFLINE
    
    def server_start(self, serverName):
        serverName = nameReplace.sub('_', serverName)
        logChangeEvent("server_start", serverName)
        
        # Check if server is running
        status = self.getServerStatus(serverName)
        if status == STATUS_ONLINE or status == STATUS_CONFLICT_ONLINE:
            return {'status': status, 'message': "ERROR Server already running"}
        else:
            try:
                # Get the contents of the server configuration file
                f = open("%s/%s.cfg" % (CFG_DIR, serverName), 'r')
                cfg = f.read()
                f.close()
                
                # Get the daemon file from the server configuration file
                binfile = ""
                pattern = re.compile("binfile([\s]*)=([\s]*)([a-zA-Z0-9\.\-_/\\\(\)\s]+)")
                match = pattern.search(cfg)
                if match:
                    binfile = match.group(3).strip()
                if binfile not in BIN_FILES:
                    logError("server_start", serverName, "Error in config: binfile %s not found. Using %s instead." % (binfile, BIN_FILES[0]))
                    binfile = BIN_FILES[0]
                    
                
                # Try to find out where binary really is
                if os.path.exists(binfile):
                    pass
                elif binfile[0]=='/':
                    return {'status': STATUS_OFFLINE, 'message': "FAIL Binary file doesn't exist. (absolute path detected due to leading slash)"}
                elif os.path.exists("bin/%s" % binfile):
                    binfile = "bin/%s" % binfile
                else:
                    # just let it crash
                    pass
                            
                # move old log file
                if not os.path.exists("%s/old" % LOG_DIR):
                    os.makedirs("%s/old" % LOG_DIR)
                if os.path.exists("%s/%s.log" % (LOG_DIR, serverName)):
                    shutil.move("%s/%s.log" % (LOG_DIR, serverName), "%s/old/%s.%d.log" % (LOG_DIR, serverName, time.time()))
                
                # Add server
                if not serverName in server_process:
                    server_process[serverName] = {
                        'process': None,
                        'running': True
                    }
                else:
                    server_process[serverName]['running'] = True
                
                # Start server
                server_process[serverName]['process'] = subprocess.Popen(
                    [
                        binfile,
                        "-c", "%s/%s.cfg" % (CFG_DIR, serverName),
                        "-fg",
                        "-verbosity", "6",
                        "-logfilename", os.path.abspath("%s/%s.log" % (LOG_DIR, serverName)),
                        "-resdir", "%s/" % RES_DIR,
                        "-authfile", os.path.abspath("%s/%s.auth" % (CFG_DIR, serverName)),
                        "-motdfile", os.path.abspath("%s/%s.motd" % (CFG_DIR, serverName)),
                        "-rulesfile", os.path.abspath("%s/%s.rules" % (CFG_DIR, serverName))
                    ]
                )
                server_pid = server_process[serverName]['process'].pid
                return {'status': STATUS_ONLINE, 'message': "SUCCESS Started server '%s' (%d)" % (serverName, server_pid)}
            except OSError as (errno, strerror):
                logError("server_start", serverName, "FAIL [%d] %s" % (errno, strerror))
                return {'status': STATUS_CONFLICT_OFFLINE, 'message': "FAIL [%d] %s" % (errno, strerror)}
            
    def server_stop(self, serverName):
        serverName = nameReplace.sub('_', serverName)
        logChangeEvent("server_stop", serverName)
        
        # Start by changing the status to offline
        if serverName in server_process:
            server_process[serverName]['running'] = False

        # Check if server is running
        status = self.getServerStatus(serverName)
        if status == STATUS_OFFLINE or status == STATUS_CONFLICT_OFFLINE:
            return {'status': status, 'message': "ERROR Server is not running"}
        else:
            try:
                # Terminate server
                server_process[serverName]['process'].terminate()
                return {'status': STATUS_OFFLINE, 'message': "SUCCESS Stopped server '%s'" % (serverName)}
            except OSError as (errno, strerror):
                logError("server_stop", serverName, "FAIL [%d] %s" % (errno, strerror))
                return {'status': STATUS_CONFLICT_ONLINE, 'message': "FAIL [%d] %s" % (errno, strerror)}
        
    def server_kill(self, serverName):
        serverName = nameReplace.sub('_', serverName)
        logChangeEvent("server_kill", serverName)
        
        # Check if server is running
        if (not serverName in server_process) or server_process[serverName]['process']==None or (server_process[serverName]['process'].poll()==None):
            return {'status': STATUS_OFFLINE, 'message': "ERROR Server is not running"}
        else:
            try:
                # Kill server
                server_process[serverName]['process'].kill()
                server_process[serverName]['running'] = False
                return {'status': STATUS_OFFLINE, 'message': "SUCCESS Killed server '%s'" % (serverName)}
            except OSError as (errno, strerror):
                logError("server_kill", serverName, "FAIL [%d] %s" % (errno, strerror))
                return {'status': STATUS_CONFLICT_ONLINE, 'message': "FAIL [%d] %s" % (errno, strerror)}
                

# Create instance
funcs = exposedFunctions()
                
                
# Auto start servers
for server in AUTO_START:
    if server!='':
        funcs.server_start(server.strip())

# Run the server's main loop
rpc = RPCServer.AuthSimpleXMLRPCServer((HOST, PORT))
rpc.addUsersByDict(USERS)
rpc.register_instance(funcs)
rpc.register_introspection_functions()
rpc.register_multicall_functions()
rpc.timeout = 1200
lastCheck = time.time()
while True:
    try:
        rpc.handle_request()
    
        if (time.time() - lastCheck) > 600:
            lastCheck = time.time()
            
            # Restart crashed servers
            for name in server_process:
                # if server_process[name]['running']:
                if funcs.getServerStatus(name)==STATUS_CONFLICT_OFFLINE:
                    funcs.server_start(name)

    except KeyboardInterrupt:
            break
