<?php
	// This should normally be your first line, the websocket server sends -q=base64
	$q = json_decode( base64_decode( getopt("q:")['q'] ) );

    // Do you want to use the framework?
    // For multi-server setup create a symbolic link in your config folder pointing to that servers real config file.
    // Here I'll call it joint_confing.pconf.php
    // Once you set the config file to use, require the site_works_essentials.php file.
    // You can now access most framework items with
    // $_s->_tool->dmsg('hello'); instead of the usual $this->_tool->dmsg('hello');
    // $_s also includes most of the configuration variables if you need them.
    $use_config = 'joint_config.pconf.php';
    require_once(dirname(__DIR__, 2) . '/site_works_essentials.php');


    // Some terms:
    // ws://    - websocket protocol
    // wss://   - secure websocket protocol
    // YOUR_SERVER - This could be MySite.com or an IP number
    // PORT     - The port your websocket server will listen to. You need to port forwarad / open your firewall for the port.
    // UID      - (Optional) User ID You can send a user id in your call to the websocket server.
    // TAG      - (Optional) This lets you group differnet connections. So you could send a responce to CHATGROUP1 or only CHATGROUP2
    // UNIQUE_ID - (Optional) Use this for security mostly, if that's important to your project.

    /*
        The websocket server sends the following varaibles you can process.

        $q->sw_var
            This is the input you sent from javascript or wherever. You can send a string with json in it
            but you'll hvae to json_decode($q->sw_var)

        $q->sw_action
            * Possible actions from here to server:
            sw_0 - $q->sw_action to sw_0 will tell the websocket server to close the connection to the matching
            array entries you provide in $q->sw_user_list, $q->sw_tag_list, or $q->sw_uniqueid_list

            * Possible actions from clinet (likely your javascript) to server:
            sw_1 - Get all user id's and put them in the $q->sw_user_list array
            sw_2 - Get all tags and put them in the $q->sw_tag_list array
            sw_3 - Get all unique id's and put them in the $q->sw_uniqueid_list array
            sw_10 - Get all uid/tag/uniqueids and put them in: $q->sw_user_list, $q->sw_tag_list, or $q->sw_uniqueid_list respectivly
            Once the websocket server gets one of these requests, you can acceess the proper array, process it, then
            use part or all of that array to send back to the server to disconnect or respond to only those people.

        $q->sw_user_list - An array of connected user id's
        $q->sw_tag_list - An array of connected tag's
        $q->sw_uniqueid_list - An array of unique id's
            Note the arrays can be empty, so you may need to make sure it's an array with a mask (array)
            Ex: implode(',',(array)$q->sw_user_list)
        $q->sw_caller - An array of the calling users parts. [uid, tag, unique_id], the parts of this array are determined by the clinet(javascript)
            in the url. var socket = new WebSocket("wss://b.w2r.com:8089/1/tag/unique_id");
            The first segment is the users id.
            The second segment is the tag you set
            The third segment is a unique id you set that you store in your db to check against for extra security so only valid users can send info.


        * SECURITY MEASURES - may or may not be applicable to what you are doing.

        You can use the unique ids for extra secuirty. When you build your client(javascript) you send a uid/tag/unique_id.
        The unique id can be stored before hand in your database, then you check the list of unique id's list and compare.
        If one of the unique id's does not match, you add it to the $sw_websocket->sw_uniqueid_list array, and set
        $sw_websocket->sw_action to 'sw_0'.

        That will disconnect all unknown unique_id's from the websocket server. 

        Note: When you send sw_0, you are still allowed to set your $sw_websocket->sw_var, and before disconnecting the websocket server
        will pass that to the client before disconnecting them.

        Anyone can connect to your server if they know the url:port, so typically you'll want to send yourself the id and unique id of the user
        sending something so you can match it here with yoru database and then respond accordingly. Responding with $sw_websocket->sw_var = '' will
        tell the websocket server to give no responce to the clients.

        Anyone can listen once connected, so matching the unique id makes it harder for them to listen if that's a problem for you because you
        can match and disconnect their unique id.

        Anyone can send to your server when connected, thats when you use $q->sw_caller[2] to check if they are real or not by matching unique_id
        with your database. If it doesn't match kick the sucker. $sw_websocket->sw_action = 'sw_0' and $sw_websocket->sw_uniqueid_list = [$q->sw_caller[2]]
        For good measure: $sw_websocket->sw_var = '{"message":"Hey, get off my lawn!"}';

    */

    if( isset($q->sw_var) ){
    	// This is an array of values you sent through the websocket script.
    	// You should handled your own security here too, basically have javascript send a code you verify here through a db call.
    	// Access it like:  $q->sw_vars->key1
    }
    if( isset($q->sw_action) ){
    	// If you send sw_0, sw_1, sw_2, or sw_3 the websocket will take aciton, but whatever you send to the websocket server you'll get to see it here
    	// As long as you don't use one of my sw_# variables you can safely use this for your own purposes.
    	// Access it like: $q->sw_action;
    }
    if( isset($q->sw_user_list) ){
    	// If you sent sw_1 for your sw_action this array will be populated with a list of user ID's that are connected to the server.
    	// Typically you would use this to write code to send to just one or a portion of the list.
    	// If you return an empty sw_user_list array, the server will broadcast to all users.
    	// Access it like:  $q->sw_user_list;
    }
    if( isset($q->sw_tag_list) ){
        // If you sent sw_2 for your sw_action this array will be populated with a list of tags being used
        // You would use this array to figure out what specific tags you might want to send a broadcast to
        // If you return an empty sw_tag_list array, the server will broadcast to all tags.
        // Access it like:  $q->sw_tag_list;
    }
    if( isset($q->sw_uniqueid_list) ){
        // If you sent sw_3 for your sw_action this array will be populated with a list of unique ids being used
        // You might use this to check against unique id's in your database so you can close connection with
        // someone who should not be monioring a broadcast. To do this you compare with your database
        // then fill this array with non matching unique_id's in combination with sending sw_action sw_0
        // to kill those connections.
        // If you return an empty sw_uniqueid_list array, the server will broadcast to all uniqueids.
        // Access it like:  $q->sw_uniqueid_list;
    }
    if( isset($q->sw_caller) ){
        // [uid,tag,unique_id]
        // $q->sw_caller[0] = calling users id
        // $q->sw_caller[1] = calling users tag
        // $q->sw_caller[2] = calling users unique id
    }

    // Your websocket server will call a socket script you provide with the -script flag
    // ./php_websockets -port 8090 -script /var/www/html/MY_PROJECT/private/socket_scripts/YOUR_FILE.php
    // You can start the script with UpStart or SystmeD for example as well.
    // What ever you echo from this script, will be sent back to the clients attached to the websockets server.
    $sw_websocket = new stdClass();
    $sw_websocket->sw_var             = ''; // The string you send back, you can use json just send it to websocket server as a string.
    $sw_websocket->sw_action          = ''; // (string) Send sw_0 to disconnect selected users
    $sw_websocket->sw_user_list       = []; // Array of user id's, ['1','3']
    $sw_websocket->sw_tag_list        = []; // Array of tags, ['dog_tag','cat_tag']
    $sw_websocket->sw_uniqueid_list   = []; // Array of unique connection id's, ['unique_id1','unique_id3']

    $sw_websocket->sw_var      = '{"p1":"' . $q->sw_var . '","p2":"MyResponse","uids":"' . implode(',',(array)$q->sw_user_list) . '","tags":"' . implode(',',(array)$q->sw_tag_list) . '","uniqueids":"' . implode(',',(array)$q->sw_uniqueid_list) . '","caller":"'.implode(',',(array)$q->sw_caller).'"}';

    // To disconnect users matching your sw_user_list / sw_tag_list / sw_uniqueid_list
    // $sw_websocket->sw_action   = 'sw_0';




    // Output a json encoded sw_websocket object
    echo json_encode($sw_websocket);
    // The websocket server will not broadcast anything if you send it an empty sw_vars array [].
    // That's useful if you determine with your script that someone is sending garbage you don't want to have sent out.

    // Example way to start your websocet server
	// ./pathtoyour/php_websockets -script=/var/www/html/roverquest/private/socket_scripts/sockets.php -port=8080



/*
<!-- Example html script for a browser client. Replace YOUR_SERVER:PORT UID and TAG -->
<input id="input" type="text" />
<button onclick="send()">Send</button>
<pre id="output"></pre>
<script>
    var input = document.getElementById("input");
    var output = document.getElementById("output");
    var socket = new WebSocket("ws://YOUR_SERVER:8080/UID/TAG");

    socket.onopen = function () {
        output.innerHTML += "Status: Connected\n";
    };

    socket.onmessage = function (e) {
        output.innerHTML += "Server: " + e.data + "\n";
    };

    function send() {
    	// sw_action sw_1 to send list of uid's to your php script
    	// sw_action sw_2 to send list of tag's to your php script
		var obj = {sw_var_array:[input.value],sw_action:""};

        socket.send( JSON.stringify(obj) );
        input.value = "";
    }
</script>
*/


<?php
echo '
<input id="input" type="text" />
<button onclick="send()">Send</button>
<pre id="output"></pre>
<script>
    var input = document.getElementById("input");
    var output = document.getElementById("output");
    var socket = new WebSocket("wss://YOUR_SERVER:PORT/UID/TAG/UNIQUE_ID");

    socket.onopen = function () {
        output.innerHTML += "Status: Connected\n";
    };

    socket.onmessage = function (e) {
        output.innerHTML += "Server: " + e.data + "\n";

        var obj = JSON.parse(e.data);
        output.innerHTML += "Var uids: " + obj.uids + "\n";
        output.innerHTML += "Var tags: " + obj.tags + "\n";
        output.innerHTML += "Var uniqueids: " + obj.uniqueids + "\n";
        output.innerHTML += "Var you sent: " + obj.p1 + "\n";
        output.innerHTML += "Var script said: " + obj.p2 + "\n";
        output.innerHTML += "Var Caller: " + obj.caller + "\n";

    };

    function send() {
        var obj = {sw_var:input.value,sw_action:"sw_10"};

        socket.send( JSON.stringify(obj) );
        input.value = "";
    }
</script>
';

?>

?>