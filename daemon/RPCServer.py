## @package AuthSimpleXMLRPCServer
#  This module contains a XML-RPC server with support for HTTP Basic authentication.
#
#  AuthSimpleXMLRPCServer extends SimpleXMLRPCServer and allows adding/removing users.

from SimpleXMLRPCServer import SimpleXMLRPCServer, SimpleXMLRPCRequestHandler
from base64 import b64encode
import re
import os.path, urllib, urlparse, xmlrpclib

## Extension of SimpleXMLRPCRequestHandler to support HTTP Basic Authentication.
#
#  @see SimpleXMLRPCRequestHandler
class AuthSimpleXMLRPCRequestHandler(SimpleXMLRPCRequestHandler):

    def is_rpc_path_valid(self, path = None): # override
        if path==None:
            path = self.path
            
        if self.server.rpc_paths:
            return path in self.server.rpc_paths
        else:
            # If rpc_paths is empty, just assume all paths are legal
            return True
    
    def is_resource_path_valid(self, path = None):
        if path==None:
            path = self.path
    
        if self.server.resource_paths:
            return path in self.server.resource_paths
        else:
            # If resource_paths is empty, just assume all paths are legal
            return True

    def report_401(self):
        # Report a 401 error
        self.send_response(401)
        response = 'Authentication required.'
        self.send_header('WWW-Authenticate', 'Basic realm=\"Authentication required.\"')
        self.send_header("Content-type", "text/plain")
        self.send_header("Content-length", str(len(response)))
        self.end_headers()
        self.wfile.write(response)
    
    # We overrride this method in order to implement authentication
    def do_POST(self): # override
    
        # Check for Basic HTTP authentication
        if self.checkAuthorization():
            SimpleXMLRPCRequestHandler.do_POST(self)
        else:
            self.report_401()
    
    def do_GET(self):
    
        """Handles the HTTP GET request.

        Attempts to interpret all HTTP GET requests as resource requests,
        which are forwarded to the server's _dispatch method for handling.
        Note: this is a modified copy of the do_POST method
        """

        try:
            # Split the path, parameters and query
            request = urlparse.urlparse(self.path)
            pathparts = [urllib.unquote_plus(i) for i in request.path.split('/') if len(i)>0]

            # Check that the path is legal
            if not self.is_resource_path_valid(pathparts[0]):
                self.report_404()
                return
                
            # Do the callback
            response = self.server._dispatch(pathparts[0], tuple(pathparts[1:]))
        except Exception, e: # This should only happen if the module is buggy
            # internal error, report as HTTP server error
            self.send_response(500)

            # Send information about the exception if requested
            if hasattr(self.server, '_send_traceback_header') and \
                    self.server._send_traceback_header:
                self.send_header("X-exception", str(e))
                self.send_header("X-traceback", traceback.format_exc())

            self.send_header("Content-length", "0")
            self.end_headers()
            raise
        else:
            # got a valid XML RPC response
            self.send_response(200)
            # note: We don't bother with content-type here
            #if self.encode_threshold is not None:
            #    if len(response) > self.encode_threshold:
            #        q = self.accept_encodings().get("gzip", 0)
            #        if q:
            #            try:
            #                response = xmlrpclib.gzip_encode(response)
            #                self.send_header("Content-Encoding", "gzip")
            #            except NotImplementedError:
            #                pass
            self.send_header("Content-length", str(len(response)))
            self.end_headers()
            self.wfile.write(response)
        
        
    def checkAuthorization(self):
        header = self.headers.getheader('Authorization')
        if header != None:
            match = self.server.authorizationRegex.match(header)
            if match is not None and match.groups(1)[0] in self.server.authorizations:
                return True
        return False
		
# Attempt to fix delays
# See http://www.answermysearches.com/xmlrpc-server-slow-in-python-how-to-fix/2140/
import BaseHTTPServer
def not_insane_address_string(self):
	host, port = self.client_address[:2]
	return '%s (no getfqdn)' % host #used to call: socket.getfqdn(host)
BaseHTTPServer.BaseHTTPRequestHandler.address_string = \
	not_insane_address_string

## Extension of SimpleXMLRPCServer to support HTTP Basic Authentication.
#
#  @see SimpleXMLRPCServer
class AuthSimpleXMLRPCServer(SimpleXMLRPCServer):

    authorizations = set()
    authorizationRegex = re.compile(r'Basic ([^\s\n]+)')
    rpc_paths = []
    resource_paths = []

    def __init__(self, addr, requestHandler=AuthSimpleXMLRPCRequestHandler,
                 logRequests=True, allow_none=False, encoding=None, bind_and_activate=True):
        SimpleXMLRPCServer.__init__(self, addr, requestHandler, logRequests, allow_none, encoding, bind_and_activate)
        
    ## Adds users to the access list.
    #
    #  @param users A dictionary containing user:password pairs (with user as key and password as value).
    def addUsersByDict(self, users):
        # Copy the users
        for user, password in users.items():
            self.addUser(user, password)
    
    ## Adds a user to the access list.
    #
    #  @param username The username of the user (unencoded).
    #  @param password The password of the user (unencoded).
    def addUser(self, username, password):
        self.authorizations |= set([b64encode('%s:%s' % (username, password))])

    ## Deletes a user from the access list.
    #
    #  @param username The username of the user (unencoded).
    #  @param password The password of the user (unencoded).
    def deleteUser(self, username, password):
        self.authorizations -= set([b64encode('%s:%s' % (username, password))])

    ## Deletes a user from the access list.
    #
    #  @param userB64 The username:password pair (base64 encoded).
    def deleteUserB64(self, userB64):
        self.authorizations -= set([userB64])
    
    def deleteAllUsers(self):
        self.authorizations = set()