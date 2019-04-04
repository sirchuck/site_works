<?php
	// This should normally be your first line, the websocket server sends -q=base64
	$q = json_decode( base64_decode( getopt("q:")['q'] ) );

    // Do you want to use the framework?
    // For multi-server (test/dev/production) setup create a symbolic link in your config folder pointing to that servers real config file.
    // Here I'll call it joint_confing.pconf.php
    // Once you set the config file to use, require the site_works_essentials.php file.
    // You can now access most framework items with
    // $_s->_tool->dmsg('hello'); instead of the usual $this->_tool->dmsg('hello');
    // $_s also includes most of the configuration variables if you need them.
    $use_config = 'joint_config.pconf.php';
    require_once(dirname(__DIR__, 2) . '/site_works_essentials.php');


    // The Websocket URL segments
    // ws://    - websocket protocol
    // wss://   - secure websocket protocol
    // YOUR_SERVER - This could be MySite.com or an IP number
    // PORT     - The port your websocket server will listen to. You need to port forwarad / open your firewall for the port.
    // UID      - (Optional) User ID You can send a user id in your call to the websocket server.
    // TAG      - (Optional) This lets you group differnet connections. So you could send a responce to CHATGROUP1 or only CHATGROUP2
    // UNIQUE_ID - (Optional) Use this for security mostly, if that's important to your project.

    /*
        The websocket server accepts variables from your javascript client or php/ruby/whatever. I'll
        provide a javascript example below to get you started. Once the websocket server accepts your
        connection, it then calls a script (Ex: this page) you provide when starting the websocket server.
        The websocet send you the variables laid out below for you to process, and then echo a json object
        that the websocket server will pick up and send the message part back to your client.

        The variables the websocket server sends to your php page:

        $q->sw_var
            This is the input you sent from javascript or wherever. You can send a string with json in it
            but you'll hvae to $MyVar = json_decode($q->sw_var);

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

            sw_allow - This is sent anytime someone attempts a connection to the server. You respond with a "1" or "0". If you send a "1"
                the user will be allowed, anything else will disconnect them. We also send $q->sw_caller when someone tries to connect.
                You can use uid / tag / unique id to match your database for added security to chooes who you allow in.

        $q->sw_user_list - An array of connected user id's
        $q->sw_tag_list - An array of connected tag's
        $q->sw_uniqueid_list - An array of unique id's
            Note the arrays can be empty, so you may need to make sure it's an array 
            Ex: implode(',',(array)$q->sw_user_list)
        $q->sw_caller - An array of the calling users parts. [uid, tag, unique_id], the parts of this array are determined by the clinet(javascript)
            in the url. var socket = new WebSocket("wss://b.w2r.com:8091/1/tag/unique_id");
            The first segment $q->sw_caller[0] is the users id.
            The second segment $q->sw_caller[1] is the tag you set
            The third segment $q->sw_caller[2] is a unique id


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
        // Or if you sent a json string, $MyVar = json_decode($q->sw_vars); 
        // Then: $MyVar->key1
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

    // Your websocket server will call a socket script you provide with the -script flag, and a config file flag -c
    // EX: ./php_websockets -port 8090 -script /var/www/html/MY_PROJECT/private/socket_scripts/YOUR_FILE.php -c=/path/to/sitworks.yourserver.pconf.php
    // You can start the script with UpStart or SystmeD for example as well.
    // It is a full server so you can connect directly to it, or if you prefer you can run it though nginx or whatever.

    // I say go ahead and just use these standard class variables I set up for you here. Just set them to the values you want.
    $sw_websocket = new stdClass();
    $sw_websocket->sw_var             = ''; // (string) The string you send back, you can use json
    $sw_websocket->sw_action          = ''; // (string) Send sw_0 to disconnect selected users
    $sw_websocket->sw_user_list       = []; // Array of user id's, ['1','3']
    $sw_websocket->sw_tag_list        = []; // Array of tags, ['chat_server_1','chat_server_2']
    $sw_websocket->sw_uniqueid_list   = []; // Array of unique connection id's, ['unique_id1','unique_id3']

    $m = json_decode($q->sw_var);
    $p1 = (isset($m->input)) ? $m->input: 'You did not send the variable: input';

    $sw_websocket->sw_var      = '{"p1":"' . $p1 . '","p2":"MyResponse","uids":"' . implode(',',(array)$q->sw_user_list) . '","tags":"' . implode(',',(array)$q->sw_tag_list) . '","uniqueids":"' . implode(',',(array)$q->sw_uniqueid_list) . '","caller":"'.implode(',',(array)$q->sw_caller).'"}';

    // To disconnect users matching your sw_user_list OR sw_tag_list OR sw_uniqueid_list
    // $sw_websocket->sw_action   = 'sw_0';


    // When the action is sw_allow, that means someone is trying to connect to the websocket server.
    // Respond with a "1" if you wan't to allow them, anything else will disconnect them.
    if(  isset($q->sw_action) && $q->sw_action == 'sw_allow'){
        // Handled your security check here then respond with a "1" or "0", "1" to allow the connection.
        echo "1";
    } else {
        // Output a json encoded sw_websocket object
        echo json_encode($sw_websocket);
    }





/*
<!-- Example Javascript for a browser client. Replace YOUR_SERVER:PORT UID TAG UNIQUEID-->
    <input id="msg" type="text" />
    <button onclick="send()">Send</button>
    <div id="out"></div>
    <script>
        var socket = new WebSocket("ws://YOUR_SERVER:8080/UID/TAG/UNIQUEID");
        // Secure port usage:
        //var socket = new WebSocket("wss://YOUR_SERVER:8081/UID/TAG/UNIQUEID");
        var msg = document.getElementById("msg");
        var out = document.getElementById("out");
        // Handle when socket is connected
        socket.onopen = function () {out.innerHTML += "Connection Established\n";};
        // Handle when socket recieves a message
        socket.onmessage = function (e) { out.innerHTML += "Server: " + e.data + "\n"; };
        function send() {
            var obj = {sw_var_array:[msg.value],sw_action:""};
            socket.send( JSON.stringify(obj) );
            msg.value = "";
        }
    </script>
*/

?>