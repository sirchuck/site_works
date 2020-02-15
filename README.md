# site_works
PHP, MySQL, Javascript, and CSS framework

# Git Suggested Clone for your repo:
    cd path/to/your_master_repo_folder
    sudo git clone --bare https://github.com/sirchuck/site_works.git your_project_folder_name.git
    cd your_project_folder_name.git
    sudo git remote set-url --push origin no_push
    sudo git config --add remote.origin.fetch +refs/*:refs/*
    sudo git remote show origin
    cd path/to/work_folder
    sudo git clone path/to/your_master_repo/your_project_folder_name.git your_project_folder_name

    Use .gitignore for image upload folders and other large items,
    you shouldn't use git for that sort of thing back them up another way.

    When you want to update your software with the latest changes to the framework,
    go to your master_repository.git folder and type:
    sudo git fetch

    Then move to your working non-bare copies of the repository and type:
    sudo git pull

# Working Setup:
    Ubuntu     18.04
    Nginx      1.14.0
    PHP        7.2
    uglifyjs AND uglifycss     ( optional )
        sudo apt update
        sudo apt install nodejs npm
        sudo npm install uglify-js -g
        sudo npm install uglifycss -g
    PHP APCu                   ( optional )
        sudo apt-get update
        sudo apt-get install php7.2-apcu -y
        sudo service php7.2-fpm restart
        sudo systemctl restart nginx

# Nginx Setup Examples:
    NOTES: 
        In general your root for a dedicated server would be:
            root /var/www/html/site_works/public
        In a non-dedicated server your root would look something like
            root /var/www/html
        Then you would check with a location block for your folder
            location ^~ /site_works/
        Then your try files looks something like this:
            try_files $uri $uri/ /site_works/public/index.php?$args;


    Your server is dedicated to your project:
        server {
	        listen 80;
	        listen [::]:80;
            root /var/www/html/site_works/public;
            index index.php;
            server_name MYDOMAIN.com www.MYDOMAIN.com;
            location ~* \.(?:css|js|jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp4|ogg|ogv|webm|htc)$ { expires max; access_log off; log_not_found off; add_header Cache-Control "public"; }
            location / {
                try_files $uri $uri/ /index.php?$args;
            }
            location ~ \.php$ {
                include fastcgi_params;
                fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
                fastcgi_param DOCUMENT_URI $request_uri;
                fastcgi_param SCRIPT_FILENAME $request_filename;
                fastcgi_param SCRIPT_NAME $fastcgi_script_name;
            }
        } # End Nginx Server Example

    Your server serves multipul projects:
        server {
            listen 80;
            listen [::]:80;
            root /var/www/html;
            index index.php;
            server_name  MYDOMAIN.com www.MYDOMAIN.com;
            # Handle Your Other Normal Servers
            location ~* \.(?:css|js|jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp4|ogg|ogv|webm|htc)$ { expires max; access_log off; log_not_found off; add_header Cache-Control "public"; }
            location / {
                try_files $uri $uri/ =404;
            }
            # Handle Your Other Normal PHP Requests
            location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
            }
            # ---- This is the important part, you can try adding this to your current nginx setup.
            location ^~ /site_works/ {
                # try_files will rewrite the uri so we hold it
                try_files $uri $uri/ /site_works/public/index.php?$args;
                location ~ \.php$ {
                   fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
                   include fastcgi_params;
                   fastcgi_param DOCUMENT_URI $request_uri;
                   fastcgi_param SCRIPT_FILENAME $request_filename;
                   fastcgi_param SCRIPT_NAME $fastcgi_script_name;
                }
            }
        } # End Nginx Server Example

    (Optional) Websocket running through nginx example
        map $http_upgrade $connection_upgrade {
            default upgrade;
            '' close;
        }
        upstream websocket {
            server 127.0.0.1:8090;
        }
        server {
            listen 8089;
            location / {
                proxy_pass http://websocket;
                proxy_http_version 1.1;
                proxy_set_header Upgrade $http_upgrade;
                proxy_set_header Connection $connection_upgrade;
                proxy_read_timeout 86400;
            }
        }

# Folder Permissions Example:
    The framework needs to be able to write to certain folders

    sudo chmod -R 775 conf
    sudo chgrp -R $USER:www-data conf

    sudo chmod -R 775 private
    sudo chgrp -R $USER:www-data private

    sudo chmod -R 775 public
    sudo chgrp -R $USER:www-data public

    sudo chmod -R 775 dev
    sudo chgrp -R $USER:www-data dev

    // One Line
    sudo chmod -R 775 conf && sudo chmod -R 775 private && sudo chmod -R 775 public && sudo chmod -R 775 dev && sudo chown -R $USER:www-data conf && sudo chown -R $USER:www-data private && sudo chown -R $USER:www-data public && sudo chown -R $USER:www-data dev

# Initial Site Load:
    Once you completed the setup above, open your new site in a browser.
    1) You will get a message telling you that the configuration file was created
    and where to find it. Your next step is to open that file and modify it to
    your needs.
    2) Once your config file is loaded properly, the framework will build your
    site_works database tables. You will need to give yourself write access.
    3) Refresh your page, and you should be presented with the template moduals home page.
    You should now be ready to write your own code.

# File Structure:
    ajaxs, iframes, and scripts are exactly like controllers except they only output what you specify.
    controllers will output the overhead like html, head, and body tags.
    To tell the framework you want to use ajax, iframe, or script just prefix the word before the name of that Modual 
        Ex Ajax:    http://www.MySite.com/ajax_modualname/controller/method/pass_var/pass_vars
        Ex iFrame:  http://www.MySite.com/iframe_modualname/controller/method/pass_var/pass_vars
        Ex Script:  http://www.MySite.com/script_modualname/controller/method/pass_var/pass_vars
    SITEWORKS_DOCUMENT_ROOT:
        This variable holds your root directory, ex: /var/www/html/site_works
        You can put a Vendor folder for SDK's like Facebook for example, then include them:
        require_once SITEWORKS_DOCUMENT_ROOT . '/vendor/Facebook/autoload.php';
    PUBLIC:
        The framework overwrites the index page, the css and js siteworks folders, but other than that you should be ok
        to put your own stuff here if you want. Like your root folder, you can drop your third party Vendor folder here.

        * NOTE: You might want to put SDK's in the root or public folder instead of the DEV folder because
        the dev folder gets copied every page load, and you'll just waste time loading your pages to perform the copy.

    DEV:
        All of your php/js/css work should happen in the site_works/dev folder.
        js/css files are all squished together into one js and one css file
        I then take the squished file and add it to a squished version of each theme.
        If you enabled uglify in your config, I also minify these css and js files.
        Files in the vendor folder will just be copied to the public folder as is.

    dev/_css
        - globalcssfiles.css
        themes
            default
                - themedcssfiles.css
        vendor
          - anycssfile.css
    dev/_js
        - globaljsfiles.js
        themes
            default
                - themedjsfiles.js
        vendor
          - anycssfile.js
    dev/dbtables
        - t_myfile.inc.php
    dev/helpers
        - myfile.helper.php
    dev/includes
        - myfile.inc.php
    dev/moduals
        - modualfoldername
            - ajaxs
              - template.ajax.php
            - controllers
              - template.controller.php
            - iframes
              - template.iframe.php
            - models
              - template.model.php
            - scripts
              - template.script.php
            - views
              - template.view.php
    dev/preloads
        - all_preload.php
        - ajax_preload.php
        - controller_preload.php
        - iframe_preload.php
        - script_preload.php
    dev/thread_scripts
        - your_script.php
    dev/queue_scripts
        - your_script.php
    dev/socket_scripts
        - your_script.php
    /
        - sw_pre_boot.php

    If you really want to, you can drop files in the public folder - but I overwrite the index page, assets/js/siteworks, assets/css/siteworks folders,
    everything else shoudl be safe.

    The preloads will be automatically included in your ajax/controller/iframe or script call. For example, if you wanted all of your controllers
    to load jquery from a google CDN you could write in the controller_preload.php file: 
        $this->_out['js'][] = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';

    Use the all_preload file for universal preloads for any type (ajax/controller/iframe/script)

    Files you put in the thread_scripts will be called if you use the threading tool shown below. Files in this directory should have the .php extention.

    Note: sw_pre_boot.php is in your siteworks root folder. Whatever php code you put here happens before anything else with the framework. This could be 
    important if you want to use things like ini_set('session.gc_maxlifetime', 3600); and session_set_cookie_params(3600); for example.

# File Extentions:
    For the framework to find your files, and for readability on your end, give your files the following extentions
    dev/includes - your_file_name.inc.php
    dev/helpers - your_file_name.helper.php
    dev/modules/ajaxs - your_file_name.ajax.php
    dev/modules/controller - your_file_name.controller.php
    dev/modules/iframes - your_file_name.iframe.php
    dev/modules/models - your_file_name.model.php
    dev/modules/views - your_file_name.view.php
    dev/thread_scripts - your_file_name.php
    dev/queue_scripts - your_file_name.php
    dev/socket_scripts - your_file_name.php

# Configuration File:
    $this->dbc - Use this array to set up the connection information to your database(s).
        Important: The arrays 'default' key needs to be the one you want the site_works framework to use.
    $this->theme - If you want to use multipul css and js themes, you can select a default.
    $this->language - This is the default language, but you can manipulate $_SESSION['language'] to handle users choices.
    $this->debugMode - Enable Debugger, This allows us to send info to your debug_server app. Usage: $this->_tool->dmsg("debug_server output");
    $this->debugBuild - If you want to get debug data, but you do not want to spend the time rebuilding files (on production for example) set this to false.
        When true, this will allow the copying and processing of your Dev files.
    $this->allowDebugColors - linux debug_server app can use colors on some systems, set to true if you want to try it.
    $this->showPHPErrors - sends php errors to your web browser, like normal php error enabled scripts.
    $this->showPHPErrors_debug - sends php error messages to the debug_server.
    $this->printSQL - Do you like seeing what your MySQL commands are doing? Enable this.
    $this->showConsoleExecutionTime - Prints SITE_WORKS & Page Execution time in console window.
    $this->useUnitTests - true or false, enable unit testing, if on execution will be slower.
    $this->UnitTestsFile - Default '', if set, the unit test file is saved there. Useful if you need to find a parse error. Ex: /tmp/MyUnitTest.txt
    $this->UnitTestErrorsOnly - true or false, if true successful unit tests are reported to the debug_server, more to read but you may want it from time to time.
    $this->css_js_minify - minifys css and js. Typically, you would turn this on just before pushing to your live server so you can serve minified files.
    $this->css_js_one_file - this puts your css and js into one file to load instead of two. Faster browser loading typically.
    $this->APCuTimeoutMinutes - number of minutes for the apcu cache to refresh $this->mem and $this->admin db records.

    $this->SessionUseDatabase - If you make a multi-server app, you should store your php $_SESSION array on a database instead of only the current active server.
    *NOTE: The next four use ini_set to set values.
    $this->save_path - Some of you insane people may have a separate drive for faster small reads, the rest of us will leave this at the default
    $this->gc_probability - This sets the gc_probability with ini_set (php session garbage cleanup) Set to 0 if you do your own cleanup thru cron or something
    $this->gc_divisor - This creates a percentage for how often gc_probability will trigger. 1/100 = 1% chance to trigger cleanup when page loads.
    $this->gc_maxlifetime - default 0 will use the php default number of seconds to recognize a sesson as old.
    *NOTE: Changing the secure sesson password will break old sessions, so you'll want to delete sessions. I do this automatically, but only after they
         initially load the page once. Also, this secure password is only used when you send data to the db. By secure I only mean as secure
         as the default php openssl_encrypt. 
    $this->sess_secure_password - if you provide a password, we encrypt the session data for the database.
    $this->cookie_samesite - Default '', Lax/Strict php7.3+ samesite variable for phpsess cookie.

    $this->admin_level_options - Enumerated array of user permission levels. $_SESSION['admin_level'] to control user levels.
    $this->allow_auto_delete_language - If db language entry is not found in the code, it is automatically removed instead of marking for deletion.
    $this->log_files - An array of log files you set. ['NickName','log/file/path']
    $this->log_auto_clean_size_kb - delete all but the last 10 lines if max is reached, 0 to never delete from file automatically.
    $this->tail_array - Want to see tail of a file in the debugger? Add the file path and number of lines to show.
    $this->default_module - This determins what dev/modual will be used if none are found in the URL.
    $this->modualLocks - Disable access for unpriviledged users to visit an entire modual.
    $this->controllerLocks - Disable access for unpriviledged users to visit a speific moduals controller.
    $this->email - Array of emails so you do not have to remember addresses, and can change easily throughout your code.
    $this->routes - Yes, you can let someone type something odd in your url, then redirect it to a good path.
        You should use lower case when setting up routes.
        Ex: 'dogs/like/friends' => 'template/template/about_dogs'
        Note: anything following the swapped portion will fall into the proper segment - pass_var pass_vars if you provide modual/contorller/method 

    // Threader
    $this->thread_php_path - The path to your version of php. Ex: /usr/bin/ (Only needed if you want to use the php_threader or php_q_it queue manager)
    $this->thread_php_version - The version of your php. Ex: 7.2 (Only needed if you want to use the php_threader or php_q_it queue manager)

    // Websocket Server
    $this->websocket_server               = '127.0.0.1'; // Your websocket server address
    $this->websocket_port                 = '8090';      // Default ws:// port 8090
    $this->websocket_secure_port          = '8091';      // Default wss:// port 8091
    $this->websocket_allow_duplicates     = 'false';     // Allow same uid/tag/uniqueid to connect more than once: true or false, default false.
    $this->websocket_cert_path            = '';          // If empty you will only be able to connect on the insecure port
    $this->websocket_certkey_path         = '';          // If empty you will only be able to connect on the insecure port
    $this->websocket_keepalive = '0'; // Default 0 , 0 = off, # of Milliseconds to wait before broadcasking keepalive (pong) string. 1(s) = 1000(ms)
    $this->websocket_ping      = '1'; // Default 1 , The string you will send to the websocket server from the client to initiate a pong response
    $this->websocket_pong      = '1'; // Default 1, The string the server responds with from a ping, or the string the server broadcasts during a keepalive.
    $this->websocket_no_pong   = 'false'; // Default: false, If true no pong will be returned when sending a ping to the server. (Client Keepalive Method)

    // Ramvar
    $this->ramvar_local_server        = '127.0.0.1';   // Default 127.0.0.1, normally you wouldnt change this unless thats not your localhost.
    $this->ramvar_local_port          = '8092';   // Default 8092, the starting port for your ramvar server on this machine.
    $this->ramvar_quarantine_seconds  = '600';    // Default 600, Number of seconds to quarentine a servers IP that did not supply the app_key.
    $this->ramvar_app_key             = 'admin';  // Default admin, Set this for some additional security. Your key must be set the same on all servers
    $this->ramvar_servers             = '127.0.0.1:8092';  // Comma separated list of ramvar server ip:ports. 192.168.10.10:8092,192.168.10.20:9090

    $this->debug_server - The IP of the server running your debug_server app.
    $this->debug_server_port - the default port I use is 9200, whatever you set make sure you port forward.
    $this->cPaths - tell the system some basics about your server and asset server paths.
        - Be sure and change /site_works/ to your project name when reading URL's in this ReadMe file.
        The framework looks for framework css and js under:
        http(s)://asset_subdomain.asset_domain.asset_tld/asset_project_name/

    * NOTE: If you are using a development server and a production server for example you may want to add an if statement to your config file
      to build $this->cPaths properly before you git commit and pull on your production server. Something like this:
      if(1==1){
        $this->cPaths = array(
            // Your development server paths
        );
      }else{
        $this->cPaths = array(
            // Your production server paths
        );
      }
      Then you just change it to 1==2 and reload your page to build with the proper URL's for your produciton server. git add / git commit / git pull
      then switch it back to 1==1 so you can develop again.

# The output array:
    You can type echo like usual to output data, but you need more. You need some control over your output right?
    $this->_out['header'][]
    $this->_out['title'][]
    $this->_out['meta'][]
    $this->_out['link'][]
    $this->_out['css'][]
    $this->_out['js'][]
    $this->_out['body'][]
    $this->_out['footer'][]

    *Note: favicon, HTML, HEAD and BODY tags are also set here but have defaults, change them as you wish.
        These defaults are only applied to normal controllers, ajax and scripts will not be autofilled.
    $this->_out['favicon'][0]  = '<link rel="shortcut icon" type="image/png" href="' . $this->_uri->base_url . '/siteworks_favicon.ico"/>';
    $this->_out['html_tag'][0] = '<!DOCTYPE html><html>';
    $this->_out['html_tag'][1] = '</html>';
    $this->_out['head_tag'][0] = '<head>';
    $this->_out['head_tag'][1] = '</head>';
    $this->_out['body_tag'][0] = '<body>';
    $this->_out['body_tag'][1] = '</body>';

    The html & favicon are a special part of the header output, you can set it like this:
    $this->_out['header']['html'] = '<!DOCTYPE html>'; // Default is <!DOCTYPE html> which is the HTML5 Doctype - anything you put in here goes above <head>
    $this->_out['header']['favicon'] = '<link rel="shortcut icon" type="image/png" href="https://path_to/favicon.ico"/>';

    I listed the above in the order they will print out. Each is an array of it's own so you can append to any of them
    during the setup of your code like this:
    $this->_out['body'][] = 'Some new text to be appended to the array';

    You may want to provide some keys of your own if you want to manipulate the order later.
    $this->_out['body'][20] = '20';
    $this->_out['body'][30] = '30';
    $this->_out['body'][10] = '10';
    ksort($this->_out['body']);

    Not enough? You want to write to the browser console too? Ok.
    $this->_console[] = 'This will print to the browsers console window.';

    You can set a log file in the config and assign it a friendly name to write a log file.
    $this->_log['PRETTY_NAME'][] = 'You can do this to write to a log file of your choosing in the config.'

    // If you want to use gd or something you may want to fully control output.
    $this->_s->clean_output = false; If you set this to true, your output will only include what you put in the output array, like a script.

# Important $_SESSION variables
    The framework uses a few session variables to handle routing. If you use a multipul server setup, you should set
    the frameworks session database useage to true in the config. That way each app server can see the value of a
    users session. If you choose not to use session, handle your own verification for letting users access moduals
    and controllers.
    $_SESSION['is_loggedin'] - (booleen) this is used to decide if I should even bother looking at your admin_level. 
        Set it to true if your user is logged in, and false if not
    $_SESSION['admin_level'] - (int) This is the level we test against to see if your user has access to moduals and controllers.
        You can set a number directly, or you can use the config files enumerated array: admin_level_options
        Ex: $_SESSION['admin_level'] = $this->_admin_level_options['admin'];
    $_SESSION['theme'] - (string) You can use this if you want to switch between multipul themes, site_works needs at least one theme you set as the default.
    $_SESSION['language'] - (string) When your user selects a language, you set this and pow, we start using the correct language for that user.

# dbtables
    dbtables are the class represention of your database tables
    To see an example of what a dbtable file should look like, check this path:
        siteworks/dev/dbtables
    Your database tables should follow the this class template, and be placed in the /dev/dbtables folder
    If you want to access one of my frameworks dbtables be sure and use the namespace
        Ex: $r = new SiteWorks\t_site_works_lang(null,$this->_odb);
    To access yours:
        Pass the ID if you want to pull a specific one, or null, and the database connection you want to use.
        Ex: $r = new t_mytable();
            That will load the mytable object using the $this->_odb connection. ( the default )
        Ex: $r = new t_mytable(5,$this->_odb);
            That will autofill your object with mytables infromation where the id = 5
        Ex: $r = new t_mytable(null,$this->_dbo['DB_SERVER_2']);
            That will load the empty object table using your specified database connection
    Note: The autoloader checks if your file starts with t_site_works_. If it does we load it from /includes,
    so for your personal table files in /dev/dbtables do not start filenames with t_site_works_

# The Database Table Object Class
    $this->tableName        = 'site_works_admin';   // Give us the name of your database table
    $this->keyField         = 'sw_admin_key';       // Give us the key field or '' if you do not have one.
    $this->autoInc          = false;                // Are you using autoincremnt for your keyfield?
    $this->f = Array(                               // Your field array. Each table field should be represented here.
        'sw_admin_key'      => array( 'value' => 0    , 'error' => null) // Set value to either 0 or null based on field type
        ,'sw_version'       => array( 'value' => null , 'error' => null) // Set value to either 0 or null based on field type
    );
    If your field type is a number, 0, if not use null.
    I have not done anything with the 'error' element of the array, but it may be useful to you.
    At the bottom of your table object class you will notice the bulidQueryArray
    case 'pullByVersion':                           // This is the name you will use to refer to the query
                                                    // set the $sqlFn to the query you want to run.
        $sqlFn = 'SELECT * FROM `'. $this->tableName . '` WHERE `sw_version` = "' . $this->odb->clean( $this->f['sw_version']['value'] ) . '"';
        // NOTE: $sqlFn can be used as an array if you want to send multipul queries. $sqlFn[] = 'SELECE...'
    break;
    You can use this area to write SQL scripts, so they are easy to find and change later as you'll see below

    Database Objects provide the following Methods:
    # Using insertData insertUpdateData and updateData method will automatically database clean your data if stored in the object. ( $r->c() ) It does not auto clean values you pass directly.

    $r = new t_mytable(5,$this->_odb);
        This instantiation has passed an id of 5, and will automatically call the following fillData method
        id can be true, number, string, or where. If where it must have a =,>, or < symbol.
    $r->fillData(overloaded)
        $r->fillData(true)
            This will pull the last record data, you would only use this typically if you had no key field and just one record.
        $r->fillData(1)
            Fill the object where the key filed id = 1
        $r->fillData('dog')
            Fill the object where the id field = 'dog'
        $r->fillData("myfield='dog'")
            Fill the object where the myfield field = 'dog'
    $r->query($sqlFn=false)
        $r->query('pullByVersion')
            This will load the SQL you set above in the buildQueryArray, and run that array of queries.
        $r->query('SELECT * FROM site_works_admin')
            This will just run the query you send it.
    $r->getFieldNames($is_insert) - This is garbage to you, I use it to build INSERT and UPDATE queries from your database table object.
    $r->getRow($result,$returnArray=false) - This is an alias for getRows, but it may make it easier when debugging and reading your code when you are selecting 1 row only.
    $r->getRows($result,$returnArray=false) - This gets your rows from your result, you get an object for false (default), associative array for true.
    $r->c(string) - this cleans your string to make it ready for insertion to the database.
    $r->clean(string) - this cleans your string to make it ready for insertion to the database.
    $r->cleanAll() - This will traverse your table object fields and clean each value.
    $r->clearFields() - This will set your field values in the object back to your defaults.
    $r->selectOne($where = false, $values_to_return = '*', array(true)object(false) ) - Return an object or array of values for one row. Automatically adds LIMIT 1 to end of statement.
    $r->selectAll($where = false, $values_to_return = '*') - Send your WHERE clause and Value list, this returns a result set.
        Ex: $r->selectAll('x=y AND z=q ORDER BY x desc', '`x`,`y`')
        If you do not specify return values, you'll get the entire record for each row.
        Note: When you use selectAll, the result gets stored in the object as well
            This means you can use $r->getRows() or $r->getRows($result) in the following example.
        Ex: Loop through results
            $r = new t_my_table(null,$this->_odb);
            $result = $r->selectAll('`u_id`='.$r->c($_POST['key']));
            while($row = $r->getRows()){ $output .= $row->MyField . ' - ' . $row->MyField2; }
    $r->insertData(insertFielNames, insertFieldValues) - This will take the field values in your object and attempt to insert it as a new record in your database.
        insertFielNames - null (pulls from object) or your field name list. Ex: `field_1`,`field_2`
        insertFieldValues - null (pulls from object) or your filed value list. Ex: 'one',1
    $r->insertUpdateData(insertFielNames, insertFieldValues, updateFieldValues) - This will insert the data, unless a keyfield is matched in which case it should update it.
        If any paramaters are null, they are built fromm the database class. Example:
        insertFielNames - null or `field_name1`,`field_name2`,`field_name3`
        insertFieldValues - null or 'val1',2,'val3'
        updateFieldValues - null or `field_name1`='val1',`field_name2`=2,`field_name3`='val3'
    $r->updateData($where = false,$values=false) - To update your data, multipul ways to use it.
        $r->updateData(); - This will simply update whatever data you have in the field array of your table object.
        $r->updateData(false); - Putting false in the where clause will update your data without a where clause.
        $r->updateData('x=y'); This will update using your field list, and apply the where clause x=y
        $r->updateData('x=y','x'); This will only update the x field where x=y
        $r->updateData(4,'`x`') This will only update the x field where the id = 4
        $r->updateData('bob','`x`') This will only update the x field where the id = 'bob'
        $r->updateData('=IN()') Adding an = sign as the first character will keep the WHERE clause but allow you to use things like user_id IN(1,2) statements if you don't have need of = > or < comparaison in your WHERE.
        $r->updateData('<ORDER BY xfield') Adding an < sign as the first character will remove the WHERE clause and you can put what you want in place of it, like ORDER BY
        # These examples should work anywhere there is a $where option, like selectAll, updateData, deleteData and fillData
    $r->deleteData($where = '') - To delete data from the database
        $r->deleteData(); - Delete your currently loaded table object.
        $r->deleteData(false); - Delete your data with no where clause.
        $r->deleteData('x=y'); - Delete all rows where x = y.
        $r->deleteData(4); - Delete where id = 4
        $r->deleteData('bob') - Delete where id = 'bob'
    Need to access a field value?
        $r->f['sw_admin_key']['value']

        - Less writing shortcut - get and set - no return
        // Set a field value - sets field value
            $r->fset('sw_admin_key',7);
        // Get a field value - returns field value
            $r->fget('sw_admin_key');
    Need to access a field error?
        $r->f['sw_admin_key']['error']


# Scope: What can I access and How?
    $this->_s      This is the entire site_works object. You can access everything in it from here.
        Well, everything except your database credentials, I remove them after connections are made for added security.
        Ex: $this->_s->tool->dmsg("This will print something to the debug_server");

    Aliases of _s
    $this->_tool   This is an alias for $this->_s->tool for faster typing
        $this->_tool->trace(int)
            Look Away, you should not need this, the framework uses it to help handle error reporting in the debug_server app
        $this->_tool->dmsg(string, showArray(bool)(int), showline(bool))
            Ok, this part is fun :)
            This will print the string you send to the debug_server app you have running.
            showArray - (default true) This adds a trace to your output so you know where in the code it was enacted.
            showArray - (1) If you put a 1 instead of true, it will print the entire debug trace.
            showline - (default true) Adds a __________________________________ after the output for readablity
            If you enabled colors in your config, and your terminal supports it:
               Example:
               $this->_tool->dmsg('Hello, [c_red] this will be in red, and [c_clear] this will be back to normal',false,false);
               Use the [c_COLOR] text in your string when you want to change colors. We automatically clear the color choice at the end of yoru line for you.
               Color Options:
               [c_clear]          [c_black]          [c_red]
               [c_green]          [c_orange]         [c_blue]
               [c_purple]         [c_cyan]           [c_light_gray]
               [c_gray]           [c_light_red]      [c_light_green]
               [c_yellow]         [c_light_blue]     [c_light_purple]
               [c_light_cyan]     [c_white]
        $this->_tool->listFiles($dir,$ftype=0,$recursive=true)
            Dir is your path to the folder you want to start listing files from.
            ftype 0 returns an array of files and folders
            ftype 1 returns an array of files only
            ftype 2 returns an array of folders only
            recursive means it will dig down into subfolders if true
            - ignore the last array if you look at the code, that's to handle the recursion bit.
        $this->_tool->delTree($dir,$include_dir_folder=true)
            Delete an entire folder ( that we have permission to )
            You can choose to leave the root folder of your path by setting the second parameter to false
        $this->_tool->removeFile(string)
           Send it a path to a file, and it will remove it if possible.
        $this->_tool->buildText($match, $replace, $is_javascript)
           Look Away, nothign to see here. Yes, you have access to it, but I can't see you would ever use it.
        $this->_tool->getText(index, $language)
            Returns the text for the provided language from the siteworks_lang database.
        $this->_tool->cleanHTML(string|array|object)
            This is supposed to remove XSS injecton from a html string. You probably won't use it in your code.
            *NOTE: No return, this just modifies the values you send.
        $this->_tool->noHTML( string|array|object, $ops = ENT_QUOTES | ENT_HTML5, $encoding = 'UTF-8')
            Use this, or your own version when printing user data out to your document to help prevent XSS.
            *NOTE: No return, this just modifies the values you send.
        $this->_tool->cleanH(string|array|object)
            Wrapper for cleanHTML, and provides a return variable so you can apply directly in code.
        $this->_tool->noH( string|array|object, $ops = ENT_QUOTES | ENT_HTML5, $encoding = 'UTF-8')
            Wrapper for noHTML, and provides a return variable so you can apply directly in code.
        $this->_tool->p_r($array)
            This lets you pretty print your arrays to the browser by encapsulating your array in 'pre' tags.
        $this->_tool->iRnd( $lenght, $keyspace )
            This will return a string of length $length using the characters you define in $keyspace. Default keyspace is US letters and numbers
        // PHP Basic Encryption 
        $this->_tool->iEncrypt($string, $secret_key, $secret_iv, $encryption_method )
            Send your plain text string along with a secret key (salt), and a secret_iv (salt2), method default 'AES-256-CBC'
            Your secret key is probably reused and set in your config. the secret_iv is generally unique per thing being encrypted. You can store it in plain text to use for decryption.
        $this->_tool->iDecrypt($string, $secret_key, $secret_iv, $encryption_method)
            This decrypts your encryption. You use the same key and iv and method you did when you encrpted. 
        $this->_tool->get_c($element_name, $value_compare, $array) - wrapper for vc() to test $_GET variables as not null or blank.
        $this->_tool->post_c($element_name, $value_compare, $array) - wrapper for vc() to test $_POST variables as not null or blank.
        $this->_tool->request_c($element_name, $value_compare, $array) - wrapper for vc() to test $_REQUEST variables as not null or blank.
        $this->_tool->vc($element_name, $value_compare, $array)
            This will return true if $element_name is not null, not blank and matches $value_compare.
            If you do not send $value_compare it will return true if $element_name is not null and not blank.
            If you do not send $array it will return true if the $element_name variable is not null and not blank.

    $this->_uri
        Note: sw_error_permission is returned from the framework to the default controller as a pass_var if a permission lock for a modual or a controller was tripped.
        $this->_uri->calltype & $this->_uri->calltypes - The URI call type - ajax/ajaxs iframe/iframes script/scripts controller/controllers respectivly
        Your URI will have this structure:
            www.MySite.com/modual/controller/method/pass_var/pass_vars
            If modual is not found, we supply the default modual you gave us in your config file.
            If the controller is not found, we supply the name of the modual provided.
            If the method is not provided, we supply the name of the controller.
            pass_var gives you the first URI segment that didn't match one of the above
            pass_vars is an array of every segment past the pass_var segment
            This means URL's like the following will all get you to http://www.MySite.com/template/template/template/my_pass_var/pvar_array_sub1/pvar_array_sub2
            1) http://www.MySite.com/template/template/template/my_pass_var/pvar_array_sub1/pvar_array_sub2
            2) http://www.MySite.com/my_pass_var/pvar_array_sub1/pvar_array_sub2
            3) http://www.MySite.com/template/my_pass_var/pvar_array_sub1/pvar_array_sub2
            4) http://www.MySite.com/template/template/my_pass_var/pvar_array_sub1/pvar_array_sub2
        $this->_uri->module - This is the modual segment of the URI
        $this->_uri->controller - This is the controller segment of the URI
        $this->_uri->method - This is the method segment of the URI
        $this->_uri->pass_var - This is the pass_var segment of the URI
        $this->_uri->pass_vars - This is the pass_vars segment of the URI
        $this->_uri->root_url - http(s)://subdomain.your_site.tld
        $this->_uri->base_url - http(s)://subdomain.your_site.tld/site_works
        For root and base you can add a _n or a _s to the property to pull the secure or non_secure versions
            Example: $this->_uri->base_url_s will force the secure https version of the domain.
        $this->_uri->asset_url is built from the asset parts you gave us in the config. You can use the _s and _n to force secureness
        $this->_uri->asset
            This just gives you a quick way to plug in your asset path to images, documents and so on:
            $this->_uri->asset->images - http(s)://asset_subdomain.MyAssetSite.asset_tld/project_name_a/assets/images
            ** Special Notice: Your assets will be served from your asset server, 
            ** but you still need to keep anything generated by siteworks on the site servers for framework operations.
            $this->_uri->asset->images     - asset_url + /assets/images
            $this->_uri->asset->documents  - asset_url + /assets/documents
            $this->_uri->asset->js         - asset_url + /assets/js
            $this->_uri->asset->css        - asset_url + /assets/css
            $this->_uri->asset->js_vendor  - asset_url + /assets/js/vendor
            $this->_uri->asset->css_vendor - asset_url + /assets/css/vendor
            The vendor folder is just a fancy way to say third party stuff. Helping you stay organized.
            js/siteworks and css/siteworks are the files we build with the framework
            js and css you put in _js/vendor _css/vendor wont be touched by the framework
            js and css files you directly put in the public folder under js/ or css/ won't be touched
            We only overwrite the siteworks folder in the js and css directories.
        $this->_uri->fixeduri - this removes the project_name from the actual url so we can pull just the usable segments. Garbage to you.
        $this->_uri->sw_module_path - the path to your modual /var/www/html/site_works/private/moduals/tempalte;
        $this->_uri->load_the_model - This tells the framework if it needs to autoload a model. Garbage to you

    ** Special Notice - You won't usually use direct calls to _odb, you'll usually use - #The Database Table Object Class - found below
    $this->_odb - This is the 'default' key in the config files database array. It's the connection we use for the siteworks databases.
        Generally you won't directly use the _odb or _dbo, you'll create a new object table shown below.
        $this->_odb->freeResult($result) - Basic Free result to save memory.
        $this->_odb->close() - Close the database connection.
        $this->_odb->c(string) - Clean your mysql string, General escaping
        $this->_odb->p($sql, $values) - prepaired statments - (SQL,Value Array) like this: ('INSERT INTO table (first_name) VALUES (?)', ['Frost'])
        $this->_odb->q($sql=false) - Run a query ('DELETE FROM table WHERE id=5')
        $this->_odb->getInsertID($result) - Gets the last insert id from a result.
        $this->_odb->getAffectedRows($result) - Return the number of effected rows for the result
        $this->_odb->numRows($result) - Number of rows in the result
        $this->_odb->fetch_assoc($result) - Return an associative array of rows for the result
        $this->_odb->fetch_object($result) - Return a row object for the result

    $this->_dbo - This is an array of _odb's, so it does exactly the same thing but you call it differntly.
        Ex: $this->_dbo['default'] is the same thing as $this->_odb
        This exists because some of us are dumb enough to use multipul databases on different servers :(
        Your database server array is set in your configuration file.
        	$this->_dbo['server_1']->q('DELETE FROM table WHERE x = y');
        	$this->_dbo['server_2']->q('DELETE FROM table WHERE x = y');

    $this->_admin - This is an array bulit from the siteworks admin database site_works_admin
        If you installed APCu, then we cache this every $this->APCuTimeoutMinutes minutes. Otherwize we load it from the database every time.
        Before you touch it, you have access to the following fields:
        $this->_admin['apcu_start_time'] - The time of the last cacheing of the database table.
        $this->_admin['sw_admin_key'] - Just the database key: should always be 1.
        $this->_admin['sw_version'] - Version of the current siteworks build.
            If the sw_version does not match the number set on the built siteworks javascripts file, it will try to update itself.
            That means when you git push to your live server, and the build number has changed, you'll need to re-load the site page to have it renew.

    $this->_mem - Same as _admin but it's a volitile memory table, site_works_mem. Garbage to most.
        $this->_mem['apcu_start_time'] - The time of the last cacheing of the database table.
        $this->_mem['sw_mem_key'] - Database key - should always be 1.
        $this->_mem['sw_site_visits'] - Volitile as a memory table, but you can use it if you want.
    ** Special Notice - Updating the $this->_admin or $this->_mem arrays does NOT change your database and will be refreshed every $this->APCuTimeoutMinutes minutes.
        If you want to really change a value you need to write the information to the database, not the array.
        Genearlly you'll treat the array as read only, but you can write if you want, like for +1 per visit for sw_site_visits since last refresh.

    $this->_m - When you are in your controller and want to quickly access the model with the same name as yoru controller you use $this->_m->YourModelsMethod();
        $this->_m is the auto-loaded model, selected only if it has the same name as the contorller and is under the same Modual as the controller.

    $this->_m_modelname - when you load a model load_model('modelname'); You access it by using $this->_m_ followed by the name of the loaded model.
        $this->_m_modelname must be within the controllers Modual

    $this->_p - This is an array of parameters you want to pass around between your controller, modual, and view pages

    $this->_csrf - Every time you load a page a new csrf token is generated and stored here.

# Loading: Helpers, View Pages, Modles, Includes
    The load functions use the PHP require_once language construct. The following loaded files by default come from your current loaded controller.
    If you want to load a view or model from another modual, just provide the modual name in the modual variable.
    filename should be just the start of the file name, so myfile.view.php should be sent as load_view('myfile'); only.
    $this->load_view(filename,modual) - Loads modual/views/name.view.php - if left empty, it loads the view with the same name as the current controller.
    $this->load_model(filename,modual) - Loads modual/views/name.model.php - if empty, loads model with the same name as modual; However, it should already be loaded.
    To load something that you want available to multipul controller, try using a helper, or include.
    $this->load_helper(filename) - This will load a file from the helper folder, accessable from all your controllers.
    $this->load_path(full_path) - You should never need this, but you could use it to require_once on a path.

    /dev/includes
    Do not start your includes in this folder with siteworks_ as that tells the autoloader to pull from /includes instead of /dev/includes.
    To use the include folder, just drop a file in it and it will be automatically called php autoloader.
    Include files should end in .inc.php Example: mytools.inc.php, 
    You load them like this:
    $mytools = new mytools();

    Your database table classes are special, they go in the dev/dbtables directory and have the following format:
    t_myfilename.inc.php
    the t_ tells the php Autoloader to look in the dbtables directory for the table ojbect, I also use this directory for framework purposes.
    Do not start database tables in /dev/dbtables with t_site_works_


# Template Modual
    I provided this template modual for you to copy and paste to quickly set up a new modual.
    It gives some basic usage examples, go ahead and explore the files.

# Admin Modual
    You can remove this modual if you want to handle language yourself, or fix it up a bit.
    Make sure you revoke access to this folder so your users can not manipulate your langauge files.
    You can also pick a database table and get some quick sample code to help you set up your
    HTML, CSS, JS, and AJAX quicker.
    Notice: When you choose to get sample code, at the bottom I provided a bit of a framework cheat sheet for you.
    You would not generally use the MVC this way, but I wanted to keep it all contained within the Modual in case
    you want to remove it completely without hanving to hunt down javascript files and css files.

# CSS & JS
    If you write the word root_asset_url, base_asset_url, asset_url, base_url, or root_url in your parsable CSS code in the dev folder,
    the system will swap those with the url with the proper http or https.
    root_url  = https://subdomain.domain.tld/
    base_url  = https://subdomain.domain.tld/project_folder
    // assets use the asset subdomain / domain / tld set in your config
    root_asset_url = https://subdomain.domain.tld
    base_asset_url = https://subdomain.domain.tld/project_folder
    asset_url = https://subdomain_a.domain_a.tld_a/project_folder_a/assetfolder
    If you plan to bounce between an Nginx setup with and without a project_folder, its best to just use base_url.

    You can also pass your own variables with the sw_array in your config.
    To access:
        var x = sw_array['mykey'];
        body{background: url('sw_array[mykey]');}
            - Note, do not use the apostrophies with your sw_pass element key. (nicer for software editors)

# Main Feature: Multi-Language Handling
    The selling point of this framework for some developers will be the easy way to handle sites that need multipul langauges.
    When you are busy writing code, you do not always have access to the boss to tell you what specific words to use for differnt things.
    Now you can write your code and use [__My Words__]
    Encaplulating your text in bracket underscore undercore will on your next page load trigger site_works to:
        1) Check the site_works_lang database to see if it already exists
            a) If it does, we leave the text but set it to useable. sw_lang_keep = 0
            b) If it does not, we insert it into the sw_origional, and english fields.
        2) Check all other encapulated text, and mark whatever is not found as ready for deletion. sw_lang_keep = 3
        3) If the system things you are in php it will rewrite your dev code to the private code as $this->_s->tool->getText(6) which would be the key of the inserted text.
        4) If it thinks javascript, getText(6), or {__6__} if it needs to run through the javascript page parcer because you put the text in $this->_out['body'][] for example.
    System generated language always has a sw_lang_category of nothing, if you want to add options for select dropdowns for example, just give it a category
    then you will have access to it in your javascript file as an array.
    You can force specific text to never change its keep value by force keeping it by setting sw_lang_keep to 1
    Once the developers text is in the database, your boss, or your interpreter can change the value for the language they want
    and it will be pulled for the user based on $_SESSION['language'].
    The string just to the left of your [__WORDS__] determins what to use:
    Possible triggers for php are: . ( and =
    Possible triggers for js are: + ( and = NOTE: if you write js code outside of your js file you might want to use the + to force the javascript call.
    The default is to use {__words__}.
      Ex: $w = $w . [__This is Where the Developers Words go__];  That turns into $w = $w . $this->_s->tool->getText(6); in the rebuilt private code.
      js Ex: $w = '' + [__My Javascript text__]; Turns into $w = '' + getText(6);
      As long as your in a js file you could do $w = [__My Words Here__] and the system will get the right getText() version
      Ex: $w = '[__My Words__]'; Will become $w = '{__6__}'; Then that will be auto-parsed by the js on page load.

# PHP Unit Testing - Reimagined
    Typically unit testing means creating a file that you run with lines and lines of code. The trade off with my version is you will not
    be able to just access all your tests at once in one file. Instead, each test is associated with the function it is meant to test.
    That means to change a test you will have to locate the file and function related to your test. ( Ex: terminal:path/to/work/folder> ifind 'MyFunc' )
    Advantages:
        When you have your debug_mode active, the framework pre-processes all of your code files under the /dev directory. This allows me to examine each
        function for your test code and create a test class and file to run. If you have a fatal error in one of your tests, obviously the code will break.
        That's ok, set your unit test path variable $this->UnitTestsFile to a writable file path, you can open and read it to track the offending line. 
        If you do not set a UnitTestsFile, the test file is automatically removed after it is used. 
        Another advantage is the speed you can write your test. Just one line sets it up while your working on your function and it's fresh on your mind.
        Standard success and fail messages show you the file and function in question in your debug_server output.
    How it works:
    ...your code...
        #_sw> "a","b",5,>7
        private function MyFunc($a,$b,$c=0){
            // Do some things
            $x = $r->updateData(); #_sw< $x = true;
            // More things to do
            #_sw[
                if( $r->updateData() ){
                    $myInt = 8;
                } else {
                    $myInt = 7;
                }
            #_sw]
            // Do more things
            return $myInt;
        }
    ...your code...

    TEST Starter:
        #_sw> "a","b",5,>7
        If I find #_sw> I use the rest of the line up to the ,> as your fuction input values and use everything after ,> as your expected return value.
        The test translates this line into a call to MyFunc, MyFunc( "a", "b", 5 );
    TEST Line Replacement:
        $x = $r->updateData(); #_sw< $x = true;
        When you end a php line with #_sw< we replace the php code of that line with what you put in the comment after the $_sw<
        This line will be translated to: $x = true;
        $this and Database calls for example, do not work while unit testing, so you can change those lines on the fly
        so you can get the expected return value by adding the tester line replacement.
    TEST Block Clear:
        #_sw[
            Whatever is between the tags will be removed for the test.
            You may have a large query that spans multipul lines for example
            Or a block of code you do not need to run during a unit test.
        #_sw]
    TEST More than one set of variables:
        When you preceed a function call with the test starter #_sw>, it runs the test with the provided values. If you want to run
        another test on the same function with different values, just add another $_sw> line before the function to be tested.
    *NOTE:
        Only .php files under your dev folder will be scanned when you do Unit Testing. Running unit tests of course take more processing power
        so I would only turn tests on just before building for the production server. If all runs good, disable testing again.

# Included Apps to help you develop faster:
    ifind
        This is a linux only app, you can install it /usr/bin/ifind if you want to run it without typing the ./
        - Usage
            ./ifind 'My Word\'s go here'
        - What does it do?
            This will search and report on any match of words you give it starting in the directory you type it.
    debug_server
        This is a linux only app, you can install it /usr/bin/debug_server if you want to run it without typing the ./
        - Usage
            ./debug_server      - To listen on default port 9200
            ./debug_server 5555 - To listen on port 5555
        - What does it do?
            This just sits and waits for data strings, then it prints them to the terminal.
            You can tell it to print colors using the [c_COLOR] format
               Color Options:
               [c_clear]          [c_black]          [c_red]
               [c_green]          [c_orange]         [c_blue]
               [c_purple]         [c_cyan]           [c_light_gray]
               [c_gray]           [c_light_red]      [c_light_green]
               [c_yellow]         [c_light_blue]     [c_light_purple]
               [c_light_cyan]     [c_white]
            Ex: $this->_tool->dmsg('Hello, [c_red] this will be in red, and [c_clear] this will be back to normal',false,false);
    debug_server.exe
        This is the windows version of the linux debug_server app, colors probably will not work.
    php_threader
        This is a linux only app, you need to leave it in your site_works root folder so the framework can find it.
        - Parameters
            File = This is the filename of the file in your thread_scripts folder you want to run. We add the path and .php for you.
            Wait =  before starting the script: Integer number of Milliseconds to wait before starting your thread. 1(s) = 1000(ms)
            Vars = Send an array of key => value pairs to retrieve in your thread script.
        - Why
            Say you want to let a user visit your page, but you want to do some behind the scenes work without making them wait.
            Now you can run a script like the following to run a background script, starting 20 seconds from now, with an array of variables.
            You'll find an example of this in the template controller.
        - Usage
            $this->_tool->thread( 'MyFile', 20000, ['key1'=>'var1','key2'=>'var2'] );
            // File, WaitTimer(ms), Vars 
            Will run private/thread_scripts/MyFile.php in 20 seconds
        - Your threaded script
            Your thread scripts should be located in /dev/thread_scripts
            * NOTE: When your page is processed by the framework you reference it in the private/thread_scripts folder. 
            They should have the extention .php
            They must contain vanilla php code as they are not run thought the framework
            If you need to run through the framework try something like this in your thread script:
                $x = file_get_contents('http://www.MySite.com/Modual/Controller/Method/pass_var/pass_vars');
                - OR Read below to add site_works_essentials
        - Passing Variables to the thread file
            Your thread files should always start with this line:
            $q = json_decode( base64_decode( getopt("q:")['q'] ) );
            If you sent an array of variables, as shown in the call above, then you will access them here as an object.
            $q->key1 and $q->key2 as per the calling example above.
    php_q_it
        This is the queue manager. When you put something in the site_works_queue database, and you have the php_q_it queue manager running
        it will run the script in the order it was put in.
        - Parameters
            File = This is the filename of the file in your queue_scripts folder you want to run. We add the path and .php for you.
            Vars = Send an array of key => value pairs to retrieve in your thread script.
            Tag = You can run multipul queues at once by giving each queue item a tag it belongs to. Queue tag A and B can run at the same time
                The second queue item with the tag A will come after the first queue item with the tag A.
            WaitStart = How many Milliseconds should we wait before starting this individual queue item? Deafult 0, do not wait.
            Timeout = How many Seconds we wait for your script to run? 0 for default 30 seconds. If your script needs more time tell us here
                or the next in line may start before your first one is complete. 
        - Why
            You need to process something that takes a while and you don't want users to see they are hung up on a page.
        - Usage
            $this->_tool->queue( 'MyFile', ['key1'=>'var1','key2'=>'var2'], 'MyQueueTag', 0, 0 );
            // File, Vars, Tag, WaitStart(ms), Timeout(s)
        - Your queue script
            Your queue scripts should be located in /dev/queue_scripts
            * NOTE: When your page is processed by the framework you reference it in the private/queue_scripts folder. 
            They should have the extention .php
            They must contain vanilla php code as they are not run thought the framework
            If you need to run through the framework try something like this in your queue script:
                $x = file_get_contents('http://www.MySite.com/Modual/Controller/Method/pass_var/pass_vars');
                - OR Read below to add site_works_essentials
        - Passing Variables to the queue file
            Your queue files should always start with this line:
            $q = json_decode( base64_decode( getopt("q:")['q'] ) );
            If you sent an array of variables, as shown in the call above, then you will access them here as an object.
            $q->key1 and $q->key2 as per the calling example above.
        - Unlike the threader, you need to start php_q_it yourself if you want it to opperate. Typically you might use something like UPSTART or SYSTEMD for this.
        - php_p_it takes the following command line arguments, if you provide a config file and another item like -prt, then -prt will take precedence. 
            Collected by your Config if you provided it:
            -x1 : This goes on the left side of the php command. /usr/bin/ for example. Set by: $this->thread_php_path in config
            -x2 : This goes on the right side of the php command. 7.2 for example. Set by: $this->thread_php_version in config
                 If you leave it blank we would call: php /path/to/queue_script.php or per the example: /usr/bin/php7.2 /path/to/queue_script.php
            -h   : hostname for your database
            -u   : username for your database
            -p   : password for your database
            -d   : database name
            -prt : Database Port / Socket
            -t   : Database type ( mysqli / postgres ) - not currently used.
            Not Collected by your Config, you provide them in UPSTART or SYSTEMD or Command line
            -c   : Full path to your configuration file, Ex: /var/www/YOURSITE/conf/siteworks.YOURSITECOM.pconf.php
            -s   : Milliseconds to wait between calls to read your database for new queue items to manage. Default is to check every 1 second, 1000(ms).
        - SYSTEMD Example for php_q_it
            sudo chmod +x /path/to/php_q_it
            sudo nano /lib/systemd/system/myservice.service
                [Unit]
                Description=Example Systemd Service.

                [Service]
                type=simple
                ExecStart=/path/to/php_q_it -c /path/to/siteworks.YOURSITE.pconf.php -s 10000
                Restart=always
                RestartSec=3

                [Install]
                WantedBy=multi-user.target
            # Start The service for testing
            sudo systemctl start myservice
            # Check the Status
            sudo systemctl status myservice
            # To stop it
            sudo systemctl stop myservice
            # To restart it
            sudo systemctl restart myservice
            # If its all good, enable it
            sudo systemctl enable myservice
            # Reboot and Check if its running
            sudo systemctl status myservice
    php_websockets
        This is a linux only app to allow you handle websockets between a client and the websocket server.
        - Parameters
            -x1 : This goes on the left side of the php command. /usr/bin/ for example. Set by: $this->thread_php_path in config
            -x2 : This goes on the right side of the php command. 7.2 for example. Set by: $this->thread_php_version in config
                 If you leave it blank we would call: php /path/to/socket_script.php or per the example: /usr/bin/php7.2 /path/to/socket_script.php
            -port    = Websocket Server Port, default 8090
            -sport   = Websocket Server Secure Port, default 8091
            -script  = /var/www/html/YOURSITE/private/socket_scripts/YOUR_FILE.php The file the socket server should call for processing.
            -to      = Script Timeout, default 30 seconds.
            -debug   = true / false, default false
            -dupes   = Default: false, Allow duplicate connections for identical UID/TAG/UNIQUEID
            -c       = path to your siteworks config. /var/www/html/YOURSITE/conf/siteworks.YOURSERVER.pconf.php
            -cert    = (optional) path to your ssl cert. Only needed for secure connection.
            -certkey = (optional) path to your ssl cert key. Only needed for secure connection

            // Websockets have a problem. The client and server can get disconnected for many reasons unexpectedly.
            // In some situaitons settig a keepalive timer can work, this will tell the php_websocket server to send out a 
            // pong to each connected client every (keepalive) Milliseconds. I suggest setting it to less than 30(s) 30000(ms) if you use 
            // the keepalive method. Note, if you have 10000 clients connected, you'll be sending the pong string to 10000 people every
            // (keepalive) Milliseconds. Not a real issue at a reasonable setting (20000), but something to think about.
            // Your other option is to set keepalive to 0 and not use it, but have the connected client servers call the socket server
            // with a ping. The server will respond with the string you set in the pong. Once you recieve your pong on the client,
            // you restart the clients timer to send another ping. If for some reason you do not recieve a pong from the server
            // you can attempt to reestablish the websocket connection. I provide a javascript example with some robustness below.
            -keepalive   = Number of Milliseconds to loop a brodcast of the pong string to all clients. 0 to turn off Default 0
            -ping   = This is the string you plan to send to the websocket server to request a pong. Anything else gets passed to your php socket script.
            -pong   = This is the string the server will resopnd with when pinged, and the string sent with keepalive.
            -nopong = Default: false, If true the server will trash incomming pings, meaning you can run a client side keepalive.

        - Why
            You want a chat component for your site, or you want to brodcast an event. (This is not a streamer) Techincally you could
            probalby use it to stream but it would be really ineffeicnet for that. Use it more to send and recieve messages from
            many clients. You can select individuals to send text too, or send to everyone at once. 
        - Usage
            You start this with UpSart or SystemD, the command line would be something like:
            sudo ./php_websockets -script=/var/www/SITE/private/socket_scripts/sockets.php -c=conf/siteworks.SITE.pconf.php
        - Your WebSocket script
            Your socket scripts should be located in /dev/socket_scripts
            * NOTE: When your page is processed by the framework you reference it in the private/socket_scripts folder. 
            They should have the extention .php
            They must contain vanilla php code as they are not run thought the framework
            If you need to run through the framework try something like this in your socket script:
                $x = file_get_contents('http://www.MySite.com/Modual/Controller/Method/pass_var/pass_vars');
                - OR Read below to add site_works_essentials
        - Passing Variables to the socket script file
            Your socket files should always start with this line:
            $q = json_decode( base64_decode( getopt("q:")['q'] ) );
            The websocket server sends the following json encoded, base64 encoded variables to your socket script.
            - $q->sw_var
                This is the string you passed from your client ( likley javacript )
                Ex:
                    var obj = {sw_var:"{\"input\":\""+input.value+"\"}",sw_action:"sw_10"};
                    socket.send( JSON.stringify(obj) );
                Notice I created my object with an embeded escaped json object string? You can pass json like this to your script if you want.
            - $q->sw_action
                Other than sw_* variables, you can use this passed string as you like. If you pass my sw_* commands:
                - sw_0 = Pass this from your socket script to tell the socket server you want to disconnect the people you selected below
                - sw_1 = Pass this from your client(javascript) to have the socket server fill the sw_user_list with active user id's.
                - sw_2 = Pass this from your clinet, socket server sends your script filled active tags - sw_tag_list 
                - sw_3 = Pass this from your client, socket server sends your script filled active uniqueids - sw_uniqueid_list
                - sw_10 = Pass this from your clinet, socket server sends filled active: sw_user_list, sw_tag_list, and sw_uniqueid_list

                - sw_allow = This aciton is sent anytime someone attempts a connection to the server. You respond with a "1" or "0". If you send a "1"
                the user will be allowed, anything else will disconnect them. We also send $q->sw_caller when someone tries to connect.
                You can use uid / tag / unique id from the $q->sw_caller array to match your database for added security to chooes who you allow in.

            - $q->sw_user_list
                When your clients sends sw_1 action, this array will be filled with a list of active user id's.
            - $q->sw_tag_list
                Your client sends sw_2, this array will be filled with all active id's. 
            - $q->sw_uniqueid_list
                Your client sneds sw_3, this array will be filled with all active unique_id's.
            - $q->sw_caller
                This array is automatically sent to your php socket script ['uid','tag','uniqueid'], with the
                information of the clinet making the call. ( Assuming you set it of course )
            # Note I don't know your specific use case, so you may not use user ids, tags, or unique ids. That's fine, your arrays for those will just be
            empty when you call the sw_ actoins. Calling sw_0 when all users id's are '' will disconnect everyone.
        - Security
            - sw_0, when you pass this action from your php socket script it removes everyone you specify in the arrays sw_user_list, sw_tag_list, and sw_uniqueid_list.
            This means if you send sw_tag_list['chat_server_1'] with the sw_0 sw_action, everyone with the tag chat_server_1 gets disconnected.
            If you send some users and some tags, then everyone who matches a user or a tag gets diconnected. 
            - This is how you control who you send to as well. If you dont specify sw_0, then the people or tags you list will be the only ones that recive the broadcast.
            You can use this to hold a conversation with a single user in a chat room for example.
            - Anyone can connect if they know your server and port, so if security is an issue I suggest you make use of passing uid/tag/unique_id, this 
            means when someone puts in fake info you can check it in your php script, then sw_0 boot them and send them a message sw_var = '{"message":"Get off my lawn!"}'
            - Anyone can listen. If they connect to the webserver they may be able to listen in. Again, use the unique_id's and match it with the one you have in your
            database for a particular user id. You automate a script sending sw_3, match all unique_id's, and put any that do not match in the sw_uniqueid_list array and
            send the sw_0 to kick them. Or whitelist by filling the sw_uniqueid_list array with the people you want to send the data to and do not send the sw_0 action 
            command.

            # Important - If your server isn't allowing connections, make sure you are handling the sw_allow sw_action in your socket script
            - Remember, on any connection to the server, we send sw_action of sw_allow. In your script, when you see this special sw_action, you can check
            their user id / tag / and unique id against your database. If you echo a "1" the user will be allowed to finish the connection. If you echo anything
            other than "1", the user will be disconnected.

        - NOTES
            * The socet server only sends you unique uids/tags/uniqueid's. So if Frost has uid 1 and has 100 clients, and you send sw_1, you'll get 1, one time in the uid array.
            * Swapping around between php/javascript JSON_encode and decoding, be carful of special characters like /n, you'll probably want to nl2br at some point for example.
        - SYSTEMD Example for php_websockets
            // Often your ExecStart starts with /bin/bash with your options in quotes, but the programs are just binary executables so you dont call somehting else to start it.
            sudo chmod +x /path/to/php_websockets
            sudo nano /lib/systemd/system/myservice2.service
                [Unit]
                Description=Example Systemd Service.

                [Service]
                type=simple
                ExecStart=/path/to/php_websockets -c /path/to/siteworks.YOURSITE.pconf.php -script /path/to/private/socket/scripts/YOURSCRIPT.php
                Restart=always
                RestartSec=3

                [Install]
                WantedBy=multi-user.target
            # Start The service for testing
            sudo systemctl start myservice2
            # Check the Status
            sudo systemctl status myservice2
            # To stop it
            sudo systemctl stop myservice2
            # To restart it
            sudo systemctl restart myservice2
            # If its all good, enable it
            sudo systemctl enable myservice2
            # Reboot and Check if its running
            sudo systemctl status myservice2
        - NGINX I provided an example nginx script for this above, but it's not really neccessary. You can directly connect to the ports opened by the websocket server.
        If for some reason you want to run though Nginx, that's fine too, but it may get more confusing with load balancing.
        
        - CLIENT
            - javascript
                The connection:
                    var socket = new WebSocket("ws://YOUR_SERVER:PORT/UID/TAG/UNIQUEID");

                    If you wanted to let someone make multipul connections with the same uid/tag/uniqueid set
                    $this->websocket_allow_duplicates to true in config then you could write something like this
                    in your javascript client:
                    var n = new Date().getTime();
                    var socket = new WebSocket("ws://YOUR_SERVER:PORT/UID/TAG/UNIQUEID/" + n);

                    That will create a new socket connection for each new browser they open.
                    NOTE: Allowing duplicates just means your user can open multipul connections with the same credentials: UID/TAG/UNIQUEID
                         Don't confuse that with UNIQUEID which is only passed for you to run security checks on. Each connection made is
                         of course a unique connection, that's why we need the unique + n segment above to open multipule connections.
                         Setting allow_duplicates to false means when user Frost opens two browsr windows with the same UID/TAG/UNIQUEID
                         but a differnet +n segment, only the newest connection will be open, the rest will be closed for him if they
                         have the same UID/TAG/UNIQUEID.

                Send your variable string - could be JSON:
                    var obj = {sw_var:"{\"input\":\""+msg.value+"\"}",sw_action:"sw_10"};
                    socket.send( JSON.stringify(obj) );

                JAVASCRIPT CLIENT EXAMPLE:
                <!-- Example Javascript for a browser client. Replace YOUR_SERVER:[PORT/SPORT] UID TAG UNIQUEID-->
                <input id="input" type="text" />
                <button onclick="send()">Send</button>
                <pre id="output"></pre>
                <script>
                    var input = document.getElementById("input");
                    var output = document.getElementById("output");

                    var socket = null;

                    // If you set the config to allow dupes(duplicates), then you can add additional unique segments to the websocket url
                    // For example, Frost would like to open two browser windows to monitor the same chat server. Your code is set up to
                    // check Frosts UID and UNIQUEID for secuirty, but Frost only has one uniqueid associated with his account.
                    // If you allow dupes, you just add another unique segment to the calling websocket url as show below
                    // If you do not want to allow dulicates, the /socket_unique is not neccessary because the server will kick
                    // any old connection that matches UID/TAG/UNIQUEID.

                    // I use socket_unique if I plan to allow duplicates
                    var socket_unique = new Date().getTime();

                    function socket_connect(){
                        socket = new WebSocket("ws://YOUR_SERVER:PORT/UID/TAG/UNIQUEID" + "/" + socket_unique );

                        // To use a secure websocket
                        // socket = new WebSocket("wss://YOUR_SERVER:SPORT/UID/TAG/UNIQUEID" + "/" + socket_unique );
                    }

                    // If you want to use a ping / pong to keep connections alive, set up an interval timer to send your ping.
                    var pong_recieved = true;
                    var NUM_SECONDS_BEFORE_KEEPALIVE_CHECK = 20;
                    var iTimer = {};
                    iTimer.keepalive = 0;

                    function iTimerF(){
                        if(iTimer.keepalive >= NUM_SECONDS_BEFORE_KEEPALIVE_CHECK){

                            // If using ping/pong or ping/nopong 
                            if(pong_recieved){
                                console.log("Sending the Ping");
                                socket.send( "1" );

                                // If you exect to recieve a pong set pong_recieved to false
                                // However if you set nopong to true in config, comment this next line out.
                                pong_recieved = false;

                            }else{
                                // Hmm we sent a ping, but did not recieve an expected pong
                                // That likely means we have been disconnected so lets try to reconnect

                                // Make sure the socket is closed
                                socket.close();

                                // Reset our pong_recieved starter status
                                pong_recieved = true;

                                // Reconnect
                                socket_connect();
                            }
                            iTimer.keepalive = 0;
                        }
                        iTimer.keepalive++;
                    }

                    // Start our initial websocket connection
                    socket_connect();

                    // Use a javascript timer for ping/pong and ping/nopong client side keepalive stratagies
                    // Comment the next line out if you just plan on using server side keepalive
                    // To Stop the timer use: clearInterval(SecondTimer);
                    var SecondTimer = setInterval(iTimerF, 1000);

                    socket.onopen = function () {
                        output.innerHTML += "Status: Connected\n";
                    };

                    socket.onclose = function () {
                        // The client thinks it was disconnected
                        // You could do a socket_connect() here to reconnect
                        // Or if you are using the interval timer above it should reconnect automatically
                        console.log("closed");
                    };

                    socket.onmessage = function (e) {
                        // Handle Pong return from our Ping
                        if(e.data == "1"){
                            // If you are using server side keepalive, or ping pong you should handle the incoming pong.
                            pong_recieved = true;
                            console.log("pong");
                        }else{
                           // Non-pong responce, This is your php socket script file responce
                            output.innerHTML += "Server: " + e.data + "\n";

                            // If you sent back JSON, parse it
                            var obj = JSON.parse(e.data);
                            output.innerHTML += "p: " + obj.p + "\n";
                            output.innerHTML += "i: " + obj.i + "\n";
                            output.innerHTML += "w: " + obj.w + "\n";

                            // You can have JSON within JSON and parse that too
                            var obj2 = JSON.parse(obj.w);
                            output.innerHTML += "w2: " + obj2[2] + "\n";
                        }
                    };

                    function send() {
                        // Lets send the socket server some json for the sw_var and an empty sw_action
                        var obj = {sw_var:"{\"input\":\""+input.value+"\"}",sw_action:""};

                        // Now we JSON encode the above object and send it off to the server.
                        socket.send( JSON.stringify(obj) );

                        // Clear the input textbox
                        input.value = "";
                    }
                </script>



            - php
                // Turns out it's really ugly to have php connect to a websocket, so I created an app you call from php to handle it for you.
                // To use this you must have php_websockets_client in your project root folder, and it must be executable.
                // It will take a message, connect to the websocket server, send your message, close the connection and return the string resopnce to you.
                // I wrote this because, say you want your queue manager to handle something, then you want to report to all watchers that the
                // funciton has completed. You could also use curl to call a javascript/python script but this seems like the easiest solution for most situations.
                Send a message like this:
                    $reply = $this->_tool->broadcast($sw_var='',$sw_action='',$uid='',$tag='',$uniqueid='',$host='',$port='',$sendhost='',$sendport='');
                    # Note, if you return something like {"key":"val"}, you may want to addslashes in some contexts.
                    Ex: $this->_console[] = addslashes( $this->_tool->broadcast('hello','','1','tag','38483') );
                Parameters:
                    $sw_var = (string) Your message, you can send JSON but as a string, then json_decode it in your socket script.
                    $sw_action = (string) You can use one of the sw_ commands like above, or use it for your own purposes.
                    $uid = (string) Set the Callers user id for this connection.
                    $tag = (string) Set the Callers tag for this connection.
                    $uniqueid = (string) Set the Callers unique id for this connection.
                    $host = (string) If you do not provide a host, we'll use the one you set up in config: $this->_s->websocket_server  Default:127.0.0.1
                    $port = (string) If you do not provide a port, we'll use the one you set up in config: $this->_s->websocket_port    Default:8090
                    // You may not need to use the following to in most situations. Websockets accept the sender host and port, the default
                    // is localhost and blank, which will just use a random open port. If for some reason the defaults will not work for you
                    // you can set these to a specific host and open port.
                    $sendhost = (string)(optional)
                    $sendport = (string)(optional)
                Response:
                    The response is a string that you sent from your socket script file.
    php_ramvar
        This is the multiserver ram key value pair server. Somewhat similar to Memcache but nothing to install, just run the app, set the config
        and use it. Along with sending a key and a value, you can also send a tag. That may help for some projects where you want to pull or 
        delete similar types of variables all at once. When you call to access a variable you do it locally, then the software shares the
        information to the other servers, that way all your php code accesses a local server making it faster than connecting to another server
        holding your database for example.
        - Security
            Keep in mind you are currently passing plain text data over TCP sockets with this. I'd score it a B for security, but if someone has access
            to your internal system, it could be comprimised. I would not use this for critical or sensitive data.
            Secondly, anyone with access to the ramvar server and port will be able to attempt a connection.
            To make you a little safer, when someone attempts to connect, but does not provide the proper
            app_key you set in your configuration file, their IP will be qurantined for 600 seconds by default. That means they get one guess at your password
            before having to wait the deault 600 seconds to try again from the same IP. Set your app_key to something people won't guess, the default is admin.

            Example Localhost certificate and Key creation:
                openssl req -x509 -out localhost.crt -keyout localhost.key -newkey rsa:2048 -nodes -sha256 -subj '/CN=localhost' -extensions EXT -config <(printf "[dn]\nCN=localhost\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:localhost\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth")

            If you pass a certificate crt and key, then the server will listen on secure tls; however, all of your ramvar servers will need to listen securly and it will be slower, but not too bad.

        - Config
            $this->ramvar_local_server        '127.0.0.1', likely you'll leave this alone, unless you really want to access a report ramserver from the framework.
            $this->ramvar_local_port          Set the port you want your localhost ramserver to run on.
            $this->ramvar_quarantine_seconds  The default is likely fine, but change it as you see fit, number of seconds an IP remains in quarantine
            $this->ramvar_app_key             Use a key people won't easily guessed.
            $this->ramvar_servers             Comma separated list of ramvar servers: 192.168.10.10:8092,192.168.10.11:8093
            $this->ramvar_cert_crt            Full path to your certificate crt /my/path/localhost.crt, leave empty to use non secure
            $this->ramvar_cert_key            Full path to your certificate key /my/path/localhost.key, leave empty to use non secure

        - Parameters
            $action  = (Insert/Update) 1, (GET) 2/2.0 (AND) 2.1 (OR), (DELETE) 3/3.0 (AND) 3.1 (OR)
            $key     = This is your app key, default admin
            $value   = This is the number of seconds to quarantine an ip, default 600 seconds
            $tag     = true or false, default false
            $message = false, you would ues a ramvar command here like sw_exit, sw_clearData, sw_clearAllData, sw_sync, sw_fullsync (Or just use one of the wrappers)
            Note: $action is not needed when you use one of the wrapper functions ( setRamvar, getRamvar, getOrRamvar, deleteRamvar, deleteOrRamvar, exitRamvar, clearRamvar, clearAllRamvar, syncRamvar, fullsyncRamvar )
        - Wrappers, used like $this->_tool->setRamvar($key, $value, $tag)
            setRamvar($key, $value, $tag)      See Usage below
            getRamvar($key, $value, $tag)      See Usage below
            getOrRamvar($key, $value, $tag)    See Usage below
            deleteRamvar($key, $value, $tag)   See Usage below
            deleteOrRamvar($key, $value, $tag) See Usage below
            exitRamvar()    = This will force the ramvar server to exit, but if you have systemD or upstart be aware it may restart on you. I don't think you'll ever use this
            clearRamvar()   = This will clear all variables held by the local ramvar, you would likely call syncRamvar() after clearing. You might do this if you thought your ramvar was corrupted
            clearAllRamvar() = This will clear all variables held by all ramvar servers. 
            syncRamvar()    = This will call the first available ramvar server and update the local ramvar variables. It does not clear any of its old variables.
            fullsyncRamvar() = This will call every available ramvar server and update its current variables. It will not clear any old variables on its own.
        - Command Line Action
            -c       = path to your siteworks config. /var/www/html/YOURSITE/conf/siteworks.YOURSERVER.pconf.php
            -k       = This is your app key, default admin
            -qs      = This is the number of seconds to quarantine an ip, default 600 seconds
            -debug   = true or false, default false, just putting -debug sets this argument as true, set it last to avoid argument conflict. 
            -p       = This is the port your local server will listen on
            -s       = This is a comma separated list of servers that will sync with each other. Ex: 192.168.10.10:8092,192.168.10.11:9090 Default 127.0.0.1:8092
            -cc      = This is the full path to your certificate crt
            -ck      = This is the full path to your certificate key
        - Why
            Your service wants a fast way to store key value pairs without using a database, perhaps Rasberry Pi project and you need to access
            the data on many machines locally.
        - Usage
            $this->_tool->ramvar( $action, $key, $value, $tag, $message )
                $action = The action to perform 1, 2/2.0, 2.1, 3/3.0, 3.1
                $key = Your variable key of the key value pair
                $value = Your key value pair value
                $tag = Your key value pair tag to group values if you desire
                $message = You should just use the wrappers, but you could send a ramvar command. 
                Actions:
                    1: Insert or Update if already inserted
                    2: Get where key = key AND value = value AND tag = tag, leaving a value empty excludes it from the evaluation
                    2.1: Same as 2, but OR instead of AND
                    3: Delete where key = key AND value = value AND tag = tag, leaving a value empty excludes it from the evaluation
                    3.1: Same as 3, but OR instead of AND
                    Ex: $this->_tool->ramvar( 2.1, 'pluto', 'dog', '');
                    Evaluates to SELECT ALL WHERE key = pluto OR value = dog
                    Ex: $this->_tool->ramvar( 2, 'pluto', 'dog', '');
                    Evaluates to SELECT ALL WHERE key = pluto AND value = dog
                    Ex: $this->_tool->ramvar( 2, '', 'dog', 'dogtag');
                    Evaluates to SELECT ALL WHERE value = dog AND value = dogtag
            $this->_tool->setRamvar($key, $value, $tag) Returns 1 if value was set
            $this->_tool->getRamvar($key, $value, $tag) Returns object array [{"a":"action","k":"key","v":"value","t":"tag","d1":"TimeStamp","d2":"Initial Ramvar ServerID"}] (AND)
            $this->_tool->getOrRamvar($key, $value, $tag) Returns object array [{"a":"action","k":"key","v":"value","t":"tag","d1":"TimeStamp","d2":"Initial Ramvar ServerID"}] (OR)
            $this->_tool->deleteRamvar($key, $value, $tag) Returns 1 if delete was successful (AND)
            $this->_tool->deleteOrRamvar($key, $value, $tag) Returns 1 if delete was successful (OR)
        - SYSTEMD Example for php_ramvar
            sudo chmod +x /path/to/php_ramvar
            sudo nano /lib/systemd/system/myservice.service
                [Unit]
                Description=Example Systemd Service.

                [Service]
                type=simple
                ExecStart=/path/to/php_ramvar -c /path/to/siteworks.YOURSITE.pconf.php
                Restart=always
                RestartSec=3

                [Install]
                WantedBy=multi-user.target
            # Start The service for testing
            sudo systemctl start myservice
            # Check the Status
            sudo systemctl status myservice
            # To stop it
            sudo systemctl stop myservice
            # To restart it
            sudo systemctl restart myservice
            # If its all good, enable it
            sudo systemctl enable myservice
            # Reboot and Check if its running
            sudo systemctl status myservice

# SITE_WORKS_ESSENTIALS
    You may find yourself wishing you didn't have to rewrite vanilla php code to access
    your databases, configs, and site_works tools.
    - Add these lines to get the essence of the framework added to your vanilla code
        $use_config = 'joint_config.pconf.php';
        require_once '/var/www/html/YOUR_PROJECT/site_works_essentials.php';

    # Note, your path may differ between servers, you could do something like this php7+
        This will get the queue or threaders root_dir path and move up by two, which 
        should be your project folder root:
        require_once dirname(__DIR__, 2) . '/site_works_essentials.php';

    Requiring the essnentials file requires you to specify a configuration file. 
    Some of you will have a development server and a live server, so you'll have to create
    a shared config file for this. An easy way to do it is create a symbolic link to your individual servers
    personlized config file with a common name.
        - Ex: ln -s /var/www/html/YOUR_PROJECT/conf/siteworks.mysitecom.pconf.php /var/ww/html/YOUR_PROJECT/conf/joint_config.pconf.php

    By creating a symbolic link on each of your servers pionting to that individual servers
    real config, you can call joint_config.pconf.php in your code and the framework will find the right file.

    How do you access the framework essentials?
        - $_s->
        You can var_dump($_s) to see what you have access too. 
        - $_s->_tool or $_s->tool will work
        You may be used to $this->_tool or $this->_s-> but you are no longer using the framework as an object here.



- site_works Author: Frost Cinderstorm (FrostCandy)