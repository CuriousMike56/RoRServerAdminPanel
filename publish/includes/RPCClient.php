<?php
/**
 * XML RPC client class
 * Copyright (c) 2013 moreit.eu
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * This is a modified version!
 */

class RemoteClassError extends Exception
{

}

class RemoteClass
{
    protected $url, $procedures, $context;
	
    function __construct($url, $get_methods = false)
	{
        $this->url = $url;
		if($get_methods)
		{
			// Request a list of procedures
			$this->procedures = $this->call('system.listMethods', null);
			
			// Flip the array so we can do lookups more efficiently
			if($this->procedures) $this->procedures = array_flip($this->procedures);
		}
		else
			$this->procedures = null;
		
		
		// Create context
		$options = array
		(
			'http' => array
			(
				'method'  => 'POST',
				'header' => Array
				(
					'Content-Type: text/xml'
					//'Authorization: Basic ' . base64_encode('username:password'),
					//'Connection: Keep-Alive',
					//'Keep-Alive: 5',
					//'Content-Type: text/plain'
				),
				'content' => ''
			)
		);
		$this->context  = stream_context_create($options);
    }
	
    protected function call($procedureName, $params = null)
	{
		$r = stream_context_set_option($this->context, 'http', 'method', 'POST');
		if($r===false) throw new RemoteClassError("Failed to set context option 'method'.");
		
		$r = stream_context_set_option($this->context, 'http', 'content', xmlrpc_encode_request($procedureName, $params));
		if($r===false) throw new RemoteClassError("Failed to set context option 'http'.");

		// Send the result (blocking)
		$r = @file_get_contents($this->url . '/RPC2', false, $this->context);
		if($r===false) throw new RemoteClassError("Couldn't connect to external server.");
		
		// Convert the XML to an array and return it
		// Note: this returns NULL with even the slightest encoding error
        return xmlrpc_decode($r, 'UTF-8');
	}
	
	public function __call($procedureName, $params)
    {
        if(!$this->procedures || array_key_exists($procedureName, $this->procedures))
		{
			$result = $this->call($procedureName, $params);
			
			// Check and log possible error
			if(is_array($result) and array_key_exists('faultCode', $result) and array_key_exists('faultString', $result))
			{
				error_log("RPC| Procedure $procedureName(".serialize($params)."): $result[faultCode]: $result[faultString]");
			}
			
			// Just return the result.
			return $result;
		}
		else
		{
			error_log("RPC| No procedure found with name '$procedureName'.");
			return false;
		}
    }
	
	public function get()
	{
		$r = stream_context_set_option($this->context, 'http', 'method', 'GET');
		if($r===false) throw new RemoteClassError("Failed to set context option 'method'.");
		
		$r = stream_context_set_option($this->context, 'http', 'content', '');
		if($r===false) throw new RemoteClassError("Failed to set context option 'http'.");
		
		// construct uri
		$uri = $this->url . '/';
		$arg_list = func_get_args();
		for ($i=0; $i<func_num_args(); ++$i)
		{
			$uri .= urlencode($arg_list[$i]) . '/';
		}

		// Send the result (blocking)
		$r = @file_get_contents($uri, false, $this->context);
		if($r===false) throw new RemoteClassError("Couldn't connect to external server.");
		
		return $r;
	}
}


?>