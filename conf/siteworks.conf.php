<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

//    This is your main configuration file, if you want to make something cross server put it in here.
//    If you want to override anything, put it in your individual server file.

class siteworks_config extends siteworks_startup
{
    /*********************************************************************************************************
    **                            D A T A  B A S E     C O M P O N E N T S                                  **
    *********************************************************************************************************/
    // dbtype:  Select a driver mysqli or postgres
    // port:    You can use port number or socket information here
    // default: SiteWorks will always use default for it's database connection
    // Add additional arrays to the dbc to connect to multipul databases if needed. (Normally not)
    public $dbc = Array(
        'default' => ['hostname'=>'','username'=>'','password'=>'','database'=>'','port'=>'','dbtype'=>'']
    );


    /*********************************************************************************************************
    **                            T O G G L E S    A N D    D E F A L T S                                   **
    *********************************************************************************************************/
    public $theme                = 'default';  // Use $_SESSION['theme'] to handle theme choice for users
    public $language             = 'english';  // Use $_SESSION['language'] to handle lang choice for users
    public $debugMode            = true;       // Enable Debugger $this->_tool->dmsg("debug_server output");
    public $allowDebugColors     = false;      // Enable colorful debugging in linux terminals. (May not work for you)
    public $showPHPErrors        = true;       // Turn on php Errors on browser;
    public $showPHPErrors_debug  = true;       // Print php Errors to debug_server;
    public $printSQL             = true;       // Prints SQL to debug_server if debugMode is on;

    // Turning this on will make yoru page loads slower, you might run it once before pushing to production servers
    public $css_js_minify        = false;      // Minify css&js, only in debug mode and when loading a controller 
    public $css_js_one_file      = true;       // To minimize number of called assets you can merge your js and css

    // Setting this will only reload $this->admin and $this->mem tables every x minutes otherwise uses cache.
    public $APCuTimeoutMinutes   = 60;         // PHP APCu 7.2+, 0 to disable cacheing. Default 60


    /*********************************************************************************************************
    **                                      A D M I N I S T A T I O N                                       **
    *********************************************************************************************************/
    // This array lets use enumerate uesr login levels with readable names
    // Your user database should hold that users admin_level and store it in $_SESSION['admin_level']
    // You could set an admin_level like this: $_SESSION['admin_level'] = $this->admin_level_options['default'];
    // You also need to set $_SESSION['is_loggedin'] = 1; after a user successfully logs in.
    public $admin_level_options = array(
         'default' => '0'
        ,'banned'  => '2'
        ,'active'  => '10'
        ,'admin'   => '1000'
        ,'manager' => '6000'
        ,'dev'     => '10000'
    );

    // Would you like to write to a log file? You can do that just make sure php has write permissions.
    // To use in code: $this->_log['pretty_name1'][] = 'Message to write to selected log file.';
    public $log_files = array(
    //     array('pretty_name1','/var/www/mydir/my_log_file_name1')
    //    ,array('pretty_name2','/var/www/mydir/my_log_file_name2')
    );

    // Set to 0 if you never want us to delete data from your file
    // If file size in KB is met, we delete all but the last 10 lines.
    public $log_auto_clean_size_kb = 400; // 1000 = 1MB (Default: 400KB)

    // Would you like to monitor log files? We run tail -n # on the file you pass in the array.
    // Make sure webserver can read files: chmod 644 or set owner to www-data normally
    // array('YOUR_LOG_FILE_NAME',INT_NUMBER_OF_LINES)
    public $tail_array = array(
    //     array('/var/log/nginx/error.log',2)
    //    ,array('/var/log/php7.2-fpm.log',2)
    );


    /*********************************************************************************************************
    **                                        R O U T I N G                                                 **
    *********************************************************************************************************/
    // Modual / Controller / Method - when modual missing we use the default
    // when controller is missing we use the modual name
    // when method is missing we use the controller name. 
    // This means MySite.com routes to MySite.com/template/template/template if default_modual is template
    public $default_module       = 'template'; // Default modual when ommitted or not found in URI path 
    public $secure               = false;      // This is set automatically in the constructor.

    // To block users from an entire Modual add an array( MODUAL_NAME , ADMIN_MINIMUM_LEVEL_NAME (default) ) 
    public $modualLocks = array(
        // array('sw_admin','admin')
    );

    // To block users from specific controllers add array( MODUAL_NAME , CONTROLLER_NAME , ADMIN_MINIMUM_LEVEL_NAME (default) )
    public $controllerLocks = array(
        // array('template','template','default')
    );

    // If you want to use the router, match the modual block with where to send it. Use Lower Case
    // www.MySite.com/dogs/like/to/party example: 'dogs/like/to/party'=>'template/template/about_dogs'
    public $routes = Array(
    //    'dogs/like/to/party'=>'template/template/about_dogs'
    );

    // If you want to use theadding tell us where PHP is and what version it is. 
    // The defaults will run /usr/bin/php7.2 as the php executable.
    public $thread_php_path = '/usr/bin/';    //  default: /usr/bin/
    public $thread_php_version = '7.2';       //  default: 7.2


    /*********************************************************************************************************
    **                               U R I    C O M P O N E N T S                                           **
    *********************************************************************************************************/
    // Tell the server how to contact your debug_server.
    public $debug_server                       = 'IP_NUMBER';           // Your debug server IP
    public $debug_server_port                  = '9200';                // Default port 9200

    // The siteworks URI needs some path information.
    public $cPaths = array(
        // Web Server Public Address: http(s)://www.MySite.com/site_works/public (Normally your Nginx Root Dir)
         'subdomain'       => 'www'                  // Website Subdomain
        ,'domain'          => 'MySite'               // Site Domain Name
        ,'tld'             => 'com'                  // Top-Level Domain
        ,'project_name'    => 'site_works'           // Your project folder name

        // Asset Web Server Address: http(s)://assets.MySite.com/public_path
        ,'subdomain_a'     => 'assets'               // Asset subdomain (Usually to avoid sending cookies to assests)
        ,'domain_a'        => 'MySite'               // Asset Site Domain Name
        ,'tld_a'           => 'com'                  // Asset Top-Level Domain
    );



    public function __construct(){

        // Here we set the secure setting for URI to choose correct paths.
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) { $this->secure = true; }

        // You could set things like meta tags or load jquery here or in yoru personal server
        // Example: $this->out['meta'][] = '<meta property="og:title" content="OG EXAMPLE META YOUR TITLE" />';
        // Example: $this->out['js'][] = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';

        // This is the parent constructor for your servers individual configuration, should be last line.
        parent::__construct();
    }
}
?>