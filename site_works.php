<?php
namespace SiteWorks{
	session_start();
	define('SITEWORKS_TIMER_START',microtime(true));
	define('SITEWORKS_DOCUMENT_ROOT', dirname(__FILE__));
	// Remove XSS and Injection from passed Values (tool->cleanHTMl() also, but we want this done even before we load our _s object tools)
	function siteworks_htmlSpecialChars_recur(&$va){foreach($va as &$v){if(is_array($v)||is_object($v)){siteworks_htmlSpecialChars_recur($v);}else{$v=htmlspecialchars($v);}}}
	siteworks_htmlSpecialChars_recur($_REQUEST);
	require_once(SITEWORKS_DOCUMENT_ROOT.'/includes/siteworks_startup.inc.php');
	try {
	    // Configuartion for everyone
		require_once(SITEWORKS_DOCUMENT_ROOT.'/conf/siteworks.conf.php');
		// Configuration per server
		$tmp = SITEWORKS_DOCUMENT_ROOT.'/conf/siteworks.' . str_replace('.', '', (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != '')?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'] ) . '.pconf.php';
		$tmpu = posix_getpwuid(fileowner(SITEWORKS_DOCUMENT_ROOT . '/index.php'))['name'];
		if(file_exists($tmp)){
			require_once($tmp);
		} else {
			if( !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/site_conf.txt',$tmp) ){
				die('Welcome to site_works!<br><br>'
					. 'To get started you need to set some file permissions so the framework can write to files:<br>'
					. '1) ' . SITEWORKS_DOCUMENT_ROOT . '/conf<br>'
					. '1) ' . SITEWORKS_DOCUMENT_ROOT . '/dev<br>'
					. '2) ' . SITEWORKS_DOCUMENT_ROOT . '/private<br>'
					. '3) ' . SITEWORKS_DOCUMENT_ROOT . '/public<br><br>'
					. 'Example commands:<br><br>'
					. 'sudo chmod -R 775 conf<br>'
					. 'sudo chown -R '.$tmpu.':www-data conf<br><br>'
					. 'sudo chmod -R 775 dev<br>'
					. 'sudo chown -R '.$tmpu.':www-data dev<br><br>'
					. 'sudo chmod -R 775 private<br>'
					. 'sudo chown -R '.$tmpu.':www-data private<br><br>'
					. 'sudo chmod -R 775 public<br>'
					. 'sudo chown -R '.$tmpu.':www-data public<br><br>'
					. ' - or - <br><br>'
					. 'sudo chmod -R 775 conf && sudo chown -R '.$tmpu.':www-data conf && sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev && sudo chmod -R 775 private && sudo chown -R '.$tmpu.':www-data private && sudo chmod -R 775 public && sudo chown -R '.$tmpu.':www-data public<br><br>'
					. 'When you are done setting permissions, refresh this page.<br>');
			}else{
				if( !is_dir(SITEWORKS_DOCUMENT_ROOT.'/dev/modules/template') ){
					exec('cp -r '.SITEWORKS_DOCUMENT_ROOT.'/includes/templates/template '.SITEWORKS_DOCUMENT_ROOT.'/dev/modules/template');
				}
				if( !is_dir(SITEWORKS_DOCUMENT_ROOT.'/dev/modules/sw_admin') ){
					exec('cp -r '.SITEWORKS_DOCUMENT_ROOT.'/includes/templates/sw_admin '.SITEWORKS_DOCUMENT_ROOT.'/dev/modules/sw_admin');
				}
				if( !is_dir(SITEWORKS_DOCUMENT_ROOT.'/dev/modules/template') || !is_dir(SITEWORKS_DOCUMENT_ROOT.'/dev/modules/sw_admin') ){
					unlink($tmp);
					die('We could not write the template or sw_admin folder in your dev/moduals folder. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}

				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/dbtables/t_site_works_template_table.inc.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/t_site_works_template_table.inc.php',SITEWORKS_DOCUMENT_ROOT.'/dev/dbtables/t_site_works_template_table.inc.php') ){
					unlink($tmp);
					die('We could not write the sw_admin folder in your dev/moduals folder. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}

				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/preloads/ajax_preload.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/ajax_preload.php',SITEWORKS_DOCUMENT_ROOT.'/dev/preloads/ajax_preload.php') ){
					unlink($tmp);
					die('We could not write the sw_admin folder in your dev/moduals folder. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}
				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/preloads/controller_preload.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/controller_preload.php',SITEWORKS_DOCUMENT_ROOT.'/dev/preloads/controller_preload.php') ){
					unlink($tmp);
					die('We could not write the sw_admin folder in your dev/moduals folder. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}
				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/preloads/iframe_preload.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/iframe_preload.php',SITEWORKS_DOCUMENT_ROOT.'/dev/preloads/iframe_preload.php') ){
					unlink($tmp);
					die('We could not write the sw_admin folder in your dev/moduals folder. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}
				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/preloads/script_preload.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/script_preload.php',SITEWORKS_DOCUMENT_ROOT.'/dev/preloads/script_preload.php') ){
					unlink($tmp);
					die('We could not write the sw_admin folder in your dev/moduals folder. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}

				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/helpers/template.helper.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/template.helper.php',SITEWORKS_DOCUMENT_ROOT.'/dev/helpers/template.helper.php') ){
					unlink($tmp);
					die('We could not write the /dev/helpers/template.helper.php file. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}
				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/includes/template.inc.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/template.inc.php',SITEWORKS_DOCUMENT_ROOT.'/dev/includes/template.inc.php') ){
					unlink($tmp);
					die('We could not write the /dev/includes/template.inc.php file. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}


				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/thread_scripts/template_threader.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/template_threader.php',SITEWORKS_DOCUMENT_ROOT.'/dev/thread_scripts/template_threader.php') ){
					unlink($tmp);
					die('We could not write the /dev/thread_scripts/template_threader.php file. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}
				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/queue_scripts/template_queue.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/template_queue.php',SITEWORKS_DOCUMENT_ROOT.'/dev/queue_scripts/template_queue.php') ){
					unlink($tmp);
					die('We could not write the /dev/queue_scripts/template_queue.php file. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}

				if( !file_exists(SITEWORKS_DOCUMENT_ROOT.'/dev/socket_scripts/template_socket.php') && !@copy(SITEWORKS_DOCUMENT_ROOT.'/includes/templates/template_socket.php',SITEWORKS_DOCUMENT_ROOT.'/dev/socket_scripts/template_socket.php') ){
					unlink($tmp);
					die('We could not write the /dev/socket_scripts/template_socket.php file. Try: sudo chmod -R 775 dev && sudo chown -R '.$tmpu.':www-data dev');
				}


				die('Almost done, the next step is to set up your configuration file found at:<br>'
					. $tmp . '<br><br>'
					. 'You will need at least one database connection with the \'default\' key.<br>'
					. 'You will need to give yourself access to the config file Example: <br><br>'
					. 'sudo chown ' . $tmpu . ' ' . $tmp . '<br><br>'
					. 'Once you finish setting up your servers config file, just refresh this page.<br>');
			}
		}
	}
	catch (Exception $e) { die('Site configuration file is missing. Please try again later. <br /><br />'); }
}
namespace{
	// This will be our base class for your modules to work from.
	$_s = new SiteWorks\siteworks_override();
	class _s{
		public $_s = null;                      // Then entire SiteWorks framework
		public $_tool = null;                   // Mostly for $this->_tool->dmsg('for the debugger');
		public $_uri = null;                    // URI vars - modual/controller/method/pass_var/pass_vars[]
		public $_odb = null;                    // Database odb is the default database
		public $_dbo = null;                    // Database array dbo['default']
		public $_out = null;                    // The output array [header/title/meta/css/js/body/footer]
		public $_log = null;                    // The log array
		public $_console = null;                // The console output array
		public $_m = null;                      // The default modual that gets loaded with the same name as Controller
		public $_p = array();                   // The Pass array to share variables between Controller Moduals and Views
		public $_admin = null;                  // The site_works admin array
		public $_mem = null;                    // The site_works mem array

	    public function __construct(){
	    }
	    public function site_works_prefetch(&$_s,$model=false){
	    	$this->_s 	       = $_s;
			$this->_tool       =& $this->_s->tool;
			$this->_uri        =& $this->_s->uri;
			$this->_odb        =& $this->_s->odb;
			$this->_dbo        =& $this->_s->dbo;
			$this->_out        =& $this->_s->out;
			$this->_log        =& $this->_s->log;
			$this->_console    =& $this->_s->console;
			$this->_p          =& $this->_s->p;
			$this->_admin      =& $this->_s->admin;
			$this->_mem        =& $this->_s->mem;

			// If your controller has the same name as a module we'll load it automaticaly into $this->_model for you to play with.
			if($model && $_s->uri->load_the_model){
	   			require_once( SITEWORKS_DOCUMENT_ROOT . '/private/preloads/' . $_s->uri->calltype . '_preload.php' );
				try{ $this->_m = new $model; $this->_m->site_works_prefetch($_s,false);}catch(Exception $e){unset($e);}
			}else{}
	    }
	    public function load_view($path=false){
	    	// Views are not classes just html to be dropped directly in your code as output.
	    	return $this->load_path(SITEWORKS_DOCUMENT_ROOT.'/private/modules/' . $this->_uri->module . '/views/' . (($path) ? $path : $this->_s->uri->controller) . '.view.php');
	    }
	    public function load_model($path=false){
	    	// Models are classes with access to the framework. Model cars becomes $this->_m_cars->your_function();
	    	if( $this->load_path(SITEWORKS_DOCUMENT_ROOT.'/private/modules/' . $this->_uri->module . '/models/' . (($path) ? $path : $this->_s->uri->controller) . '.model.php') ){
	    		$tmp = '_m_'.$path;
	    		$tmp2 = $path . '_model';
				try{ $this->$tmp = new $tmp2; $this->$tmp->site_works_prefetch($this->_s,false); }catch(Exception $e){unset($e);}
	    		return true;
	    	} else { return false; }
	    }
	    public function load_helper($path=false){
	    	if(!$path)return false;
	    	// Helpers should be a list of functions, currently I see no reason for them to be in a class. Just load and use the functions.
	    	return $this->load_path(SITEWORKS_DOCUMENT_ROOT.'/private/helpers/' . $path . '.helper.php');
	    }
		public function load_path($p=false){ if($p && file_exists($p)){ require_once($p); return true;}return false;}

	}
	if(!ob_start("ob_gzhandler")) ob_start();
	$use_controller = $_s->uri->controller . '_' . $_s->uri->calltype;
	$use_method = $_s->uri->method;
	$user_object = new $use_controller;
	$user_object->site_works_prefetch($_s,$_s->uri->controller.'_model');
	$user_object->$use_method();
	$user_object->_s->site_works_finish();
}
?>