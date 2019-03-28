<?php
	// This should normally be your first line if you are passing a variable array.
	$q = json_decode( base64_decode( getopt("q:")['q'] ) );

	// You access your variables passed like this:
	// echo $q->key1;

	// Do you want to use the framework?
	// For multi-server setup create a symbolic link in your config folder pointing to that servers real config file.
	// Here I'll call it joint_confing.pconf.php
	// Once you set the config file to use, require the site_works_essentials.php file.
	// You can no access most framework items with
	// $_s->_tool->dmsg('hello'); instead of the usual $this->_tool->dmsg('hello');
	// $_s also includes most of the configuration variables if you need them.
	$use_config = 'joint_config.pconf.php';
    require_once(dirname(__DIR__, 2) . '/site_works_essentials.php');

    // Your websocket server will call a socket script you provide with the -script flag
    // ./php_websockets -port 8090 -script /var/www/html/MY_PROJECT/private/socket_scripts/YOUR_FILE.php
    // You can start the script with UpStart or SystmeD for example as well.
    // What ever you echo from this script, will be sent back to the clients attached to the websockets server.

    /*
    	// Example html script for browser client.
		<input id="input" type="text" />
		<button onclick="send()">Send</button>
		<pre id="output"></pre>
		<script>
			var input = document.getElementById("input");
			var output = document.getElementById("output");
			var socket = new WebSocket("ws://localhost:8090/socket_server");

			socket.onopen = function () {
				output.innerHTML += "Status: Connected\n";
			};

			socket.onmessage = function (e) {
				output.innerHTML += "Server: " + JSON.parse(atob(e.data)) + "\n";
			};

			function send() {
				socket.send( btoa(JSON.stringify(input.value)) );
				input.value = "";
			}
		</script>
    */



?>