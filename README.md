# site_works
PHP, MySQL, Javascript, and CSS framework

# Working Setup:
    Ubuntu     18.04
    Nginx      1.14.0
    PHP        7.2
    uglifyjs AND uglifycss     ( optional )
        sudo apt update
        sudo apt install nodejs npm
        npm install uglify-js -g
        npm install uglifycss -g
    PHP APCu                   ( optional )
        sudo apt-get update
        sudo apt-get install php7.2-apcu -y
        sudo service php7.2-fpm restart
        sudo systemctl restart nginx

# Nginx Setup Examples:
    NOTES: you can change site_works with your project name.
        you must keep the /public in your route, or your try files

    Your server is dedicated to your project:
        server {
	        listen 80;
	        listen [::]:80;
            root /var/www/html/site_works/public;
            index index.php;
            server_name MYDOMAIN.com www.MYDOMAIN.com;
            # Note: try_files will change our url, but we want to know the origional.
            location / {
                # Note: You handle everything through index.php if not found, so 404 errors dont really exist
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
            index index.php index.html;
            server_name  MYDOMAIN.com www.MYDOMAIN.com;
            # Handle Your Other Normal Servers
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
                # Note: try_files will change our url, but we want to know the origional.
                # set $holduri $uri; or set $holduri $request_uri; if you plan on corrupting the $request_uri
                try_files $uri $uri/ /site_works/public/index.php?$args;
                location ~ \.php$ {
                    include fastcgi_params;
                    fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
                    fastcgi_param DOCUMENT_URI $request_uri;
                    fastcgi_param SCRIPT_FILENAME $request_filename;
                    fastcgi_param SCRIPT_NAME $fastcgi_script_name;
                }
            }
        } # End Nginx Server Example

# Folder Permissions Example:
    The framework needs to be able to write to certain folders

    sudo chmod -R 775 conf
    sudo chgrp -R www-data conf

    sudo chmod -R 775 private
    sudo chgrp -R www-data private

    sudo chmod -R 775 public
    sudo chgrp -R www-data public

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
        - ajax_preload.php
        - controller_preload.php
        - iframe_preload.php
        - script_preload.php
    dev/thread_scripts
        - your_script.php

    If you really want to, you can drop files in the public folder - but I overwrite the index page, assets/js/siteworks, assets/css/siteworks folders,
    everything else shoudl be safe.

    The preloads will be automatically included in your ajax/controller/iframe or script call. For example, if you wanted all of your controllers
    to load jquery from a google CDN you could write in the controller_preload.php file: 
        $this->_out['js'][] = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';

    Files you put in the thread_scripts will be called if you use the threading tool shown below. Files in this directory should have the .php extention.

# File Extentions:
    For the framework to find your files, and for readability on your end, give your files the following extentions
    dev/includes - your_file_name.inc.php
    dev/helpers - your_file_name.helper.php
    dev/ajaxs - your_file_name.ajax.php
    dev/controller - your_file_name.controller.php
    dev/iframes - your_file_name.iframe.php
    dev/models - your_file_name.model.php
    dev/views - your_file_name.view.php

# Configuration File:
    $this->dbc - Use this array to set up the connection information to your database(s).
        Important: The arrays 'default' key needs to be the one you want the site_works framework to use.
    $this->theme - If you want to use multipul css and js themes, you can select a default.
    $this->language - This is the default language, but you can manipulate $_SESSION['language'] to handle users choices.
    $this->debugMode - Enable Debugger, This allows us to send info to your debug_server app. Usage: $this->_tool->dmsg("debug_server output");
    $this->allowDebugColors - linux debug_server app can use colors on some systems, set to true if you want to try it.
    $this->showPHPErrors - sends php errors to your web browser, like normal php error enabled scripts.
    $this->showPHPErrors_debug - sends php error messages to the debug_server.
    $this->printSQL - Do you like seeing what your MySQL commands are doing? Enable this.
    $this->css_js_minify - minifys css and js. Typically, you would turn this on just before pushing to your live server so you can serve minified files.
    $this->css_js_one_file - this puts your css and js into one file to load instead of two. Faster browser loading typically.
    $this->APCuTimeoutMinutes - number of minutes for the apcu cache to refresh $this->mem and $this->admin db records.
    $this->admin_level_options - Enumerated array of user permission levels. $_SESSION['admin_level'] to control user levels.
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
    $this->thread_php_path - The path to your version of php. Ex: /usr/bin/ (Only needed if you want to use the php_threader)
    $this->thread_php_version - The version of your php. Ex: 7.2 (Only needed if you want to use the php_threader)
    $this->debug_server - The IP of the server running your debug_server app.
    $this->debug_server_port - the default port I use is 9200, whatever you set make sure you port forward.
    $this->cPaths - tell the system some basics about your server and asset server paths.
        - Be sure and change /site_works/ to your project name when reading URL's in this ReadMe file.
        The framework looks framework css and js under:
        http(s)://asset_subdomain.asset_domain.asset_tld/project_name/public/

# The output array:
    You can type echo like usual to output data, but you need more. You need some control over your output right?
    $this->_out['header'][]
    $this->_out['title'][]
    $this->_out['meta'][]
    $this->_out['css'][]
    $this->_out['js'][]
    $this->_out['body'][]
    $this->_out['footer'][]
    The favicon is a special part of the header output, you can set it like this:
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


# Important $_SESSION variables
    Sometimes the framework needs to get some information from your user.
    To do that we use session variables that you can control, usually when you create a login scheme for your user.
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
        Ex: $r = new SiteWorks\t_site_works_lang(0,$this->_odb);
    To access yours:
        Pass the ID if you want to pull a specific one, or 0, and the database connection you want to use.
        Ex: $r = new t_mytable();
            That will load the mytable object using the $this->_odb connection. ( the default )
        Ex: $r = new t_mytable(5,$this->_odb);
            That will autofill your object with mytables infromation where the id = 5
        Ex: $r = new t_mytable(0,$this->_dbo['DB_SERVER_2']);
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
        $sqlFn = 'SELECT * FROM `'. $this->tableName . '` WHERE `sw_version` = "' . $this->odb->dbClean( $this->f['sw_version']['value'] ) . '"';
        // NOTE: $sqlFn can be used as an array if you want to send multipul queries. $sqlFn[] = 'SELECE...'
    break;
    You can use this area to write SQL scripts, so they are easy to find and change later as you'll see below

    Database Objects provide the following Methods:
    $r = new t_mytable(5,$this->_odb);
        This instantiation has passed an id of 5, and will automatically call the following fillData method
    $r->fillData(overloaded)
        $r->fillData(true)
            This will pull the last record data, you would only use this typically if you had no key field and just one record.
        $r->fillData(1)
            Fill the object where the key filed id = 1
        $r->fillData('dog')
            Fill the object where the id field = 'dog'
    $r->query($sqlFn=false)
        $r->query('pullByVersion')
            This will load the SQL you set above in the buildQueryArray, and run that array of queries.
        $r->query('SELECT * FROM site_works_admin')
            This will just run the query you send it.
    $r->getFieldNames($is_insert) - This is garbage to you, I use it to build INSERT and UPDATE queries from your database table object.
    $r->getRows($result,$returnArray=false) - This gets your rows from your result, you get an object for false (default), associative array for true.
    $r->clean(string) - this cleans your string to make it ready for insertion to the database.
    $r->cleanAll() - This will traverse your table object fields and clean each value.
    $r->clearFields() - This will set your field values in the object back to your defaults.
    $r->selectAll($where = false, $values_to_return = '*') - Send your WHERE clause and Value list
        Ex: $r->selectAll('x=y AND z=q ORDER BY x desc', '`x`,`y`')
        If you do not specify return values, you'll get the entire record for each row.
    $r->insertData() - This will take the field values in your object and attempt to insert it as a new record in your database.
    $r->insertUpdateData() - This will insert the data, unless a keyfield is matched in which case it should update it.
    $r->updateData($where = false,$values=false) - To update your data, multipul ways to use it.
        $r->updateData(); - This will simply update whatever data you have in the field array of your table object.
        $r->updateData('x=y'); This will update using your field list, and apply the where clause x=y
        $r->updateData('x=y','x'); This will only update the x field where x=y
        $r->updateData(4,'`x`') This will only update the x field where the id = 4
        $r->updateData('bob','`x`') This will only update the x field where the id = 'bob'
    $r->deleteData($where = '') - To delete data from the database
        $r->deleteData(); - Delete your currently loaded table object.
        $r->deleteData('x=y'); - Delete all rows where x = y.
        $r->deleteData(4); - Delete where id = 4
        $r->deleteData('bob') - Delete where id = 'bob'
    Need to access a field value?
        $r->f['sw_admin_key']['value']
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
        $this->_tool->cleanHTML(string)
            This is supposed to remove XSS injecton from a html string. You probably won't use it in your code.
        $this->_tool->p_r($array)
            This lets you pretty print your arrays to the browser by encapsulating your array in 'pre' tags.

    $this->_uri
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
        $this->_uri->base_url - http(s)://subdomain.your_site.tld/site_works (Note our Nginx just pushes this to our public folder)
        For root, base and public you can add a _n or a _s to the property to pull the secure or non_secure versions
            Example: $this->_uri->base_url_s will force the secure https version of the domain.
        $this->_uri->asset_url is built from the asset parts you gave us in the config. You can use the _s and _n to force secureness
        $this->_uri->asset
            This just gives you a quick way to plug in your asset path to images, documents and so on:
            $this->_uri->asset->images - http(s)://asset_subdomain.MyAssetSite.asset_tld/site_works/public/assets/images
            Notice the siteworks/public has been added. so if you use a real second server for assets make sure you build the path properly.
            ** Special Notice: Your assets will be served from your asset server, 
            ** but you still need to keep anything generated by siteworks on the site servers for framework operations.
            $this->_uri->asset->images     - asset_site + /site_works/public/assets/images
            $this->_uri->asset->documents  - asset_site + /site_works/public/assets/documents
            $this->_uri->asset->js         - asset_site + /site_works/public/assets/js
            $this->_uri->asset->css        - asset_site + /site_works/public/assets/css
            $this->_uri->asset->js_vendor  - asset_site + /site_works/public/assets/js/vendor
            $this->_uri->asset->css_vendor - asset_site + /site_works/public/assets/css/vendor
            The vendor folder is just a fancy way to say third party stuff. Helping you stay organized.
            js/siteworks and css/siteworks are the files we build with the framework
            js and css you put in _js/vendor _css/vendor wont be touched by the framework
            js and css files you directly put in the public folder under js/ or css/ won't be touched
            We only overwrite the siteworks folder in the js and css directories.
        $this->_uri->fixeduri - this removes the site_works/public from the actual url so we can pull just the usable segments. Garbage to you.
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

# Loading: Helpers, View Pages, Modles, Includes
    The load functions use the PHP require_once() function. The following loaded files must be found under the Modual your current controller is in.
    filename should be just the start of the file name, so myfile.view.php should be sent as load_view('myfile'); only.
    $this->load_view(filename) - Loads modual/views/name.view.php - if left empty, it loads the view with the same name as the current controller.
    $this->load_model(filename) - Loads modual/views/name.model.php - if empty, loads model with the same name as controller; However, it should already be loaded.
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

# CSS
    If you write the word asset_url in your parsable CSS code in the dev folder, the system will swap that out with
    The current http or https://subdomain.domain.tld/assetfolder url. in css add a slash and the rest of your path.
    Or, if you are using the systems public folder you can get to your files just starting with a /assets/path.

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
        - Why
            Say you want to let a user visit your page, but you want to do some behind the scenes work without making them wait.
            Now you can run a script like the following to run a background script, starting 20 seconds from now, with an array of variables.
            You'll find an example of this in the template controller.
        - Usage
            $this->_tool->thread( 'MyFile', 20, ['key1'=>'var1','key2'=>'var2'] );
        - Your threaded script
            Your thread scripts should be located in /dev/thread_scripts
            They should have the extention .php
            They must contain vanilla php code as they are not run thought the framework
            If you need to run through the framework try something like this in your thread script:
                $x = file_get_contents('http://www.MySite.com/Modual/Controller/Method/pass_var/pass_vars');
        - Passing Variables to the thread file
            Your thread files should always start with this line:
            $q = json_decode( base64_decode( getopt("q:")['q'] ) );
            If you sent an array of variables, as shown in the call above, then you will access them here as an object.
            $q->key1 and $q->key2 as per the calling example above.





- site_works Author: Frost Cinderstorm (FrostCandy)