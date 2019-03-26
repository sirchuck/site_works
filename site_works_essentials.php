<?php
namespace SiteWorks{
	define('USING_ESSENTIALS',true);
	define('SITEWORKS_DOCUMENT_ROOT', dirname(__FILE__));
	class siteworks_startup
	{
		public $odb            = null;
		public $dbo            = [];
		public $tool           = null;

		// Logs are written at page end to files you specify in config.
		public $log = [];

		public $admin = [];
		public $mem   = [];

		public $site_works_db_classes = []; // Check this

	    public function __construct(){
			spl_autoload_register(array($this, 'handle_autoload'));
			$this->tool = new siteworks_tools($this);
			if( array_key_exists('default', $this->dbc) ){
				$this->odb = new siteworks_dbc((object)$this->dbc['default'],$this);
				$this->dbo['default'] = $this->odb;
				$GLOBALS['_odb'] =& $this->odb;
			}else{die('You must have a default database connection for administration.');}
			foreach($this->dbc as $k => $v){ if($k != 'default'){ $this->dbo[$k] = new siteworks_dbc((object)$v,$this); } }
			$dbc_database_name = $this->dbc['default']['database'];
			// Protect Database Connection Passwords from being revield later.
			unset($this->dbc);
		}
		private function handle_autoload($className){
			$cp = explode('\\',$className);
			// You can add your own autoload under other namespaces.
			if(count($cp)==1 || $cp[0] == 'SiteWorks' ){
				$className = end($cp);
				try{
					
					// Load Database Tables $dba['tableName'] = new t_tableName;
					if( preg_match('/^t_/', $className) ){
		                if( preg_match('/^t_site_works_/', $className) ){
		    				require_once(SITEWORKS_DOCUMENT_ROOT.'/includes/dbtables/'.$className.'.inc.php');
		                } else {
		    				require_once(SITEWORKS_DOCUMENT_ROOT.'/private/dbtables/'.$className.'.inc.php');
		                }
					}
					
					// Load standard classes
					else{
		                if( preg_match('/^siteworks_/', $className) ){
							require_once(SITEWORKS_DOCUMENT_ROOT.'/includes/'.$className.'.inc.php');
		                } else {
							require_once(SITEWORKS_DOCUMENT_ROOT.'/private/includes/'.$className.'.inc.php');
						}
					}
				}
				catch (Exception $e){
					die('Could not load required include files.');
				}  
			}
		}
		// APCu functions
		public function set_apcu($apcu_var_name, $variable)   { if( extension_loaded('apcu') ){ apcu_store($this->uri->fixedapcu.$apcu_var_name, $variable); } }
		public function get_apcu($apcu_var_name)              { if( extension_loaded('apcu') ){ return apcu_fetch($this->uri->fixedapcu.$apcu_var_name); } return ''; }
		public function delete_admin($apcu_var_name)          { if( extension_loaded('apcu') ){ apcu_delete($this->uri->fixedapcu.$apcu_var_name); } }
		public function clear_apcu()                          { if( extension_loaded('apcu') ){ apcu_clear_cache(); } }
	} // End SiteWorks Startup Class

	require_once(SITEWORKS_DOCUMENT_ROOT.'/conf/siteworks.conf.php');
	$tmp = SITEWORKS_DOCUMENT_ROOT.'/conf/' . $use_config;
	if(file_exists($tmp)){
		require_once($tmp);
	} else {
		die('Site configuration file is missing. Please set the $use_config variable. Ex: $use_config = joint_config.pconf.php  <br /><br />');
	}


} // End SiteWorks NameSpace
namespace{
	$_s           = new SiteWorks\siteworks_override();
	$_s->_tool    = $_s->tool;
	$_s->_odb     = $_s->odb;
	$_s->_dbo     = $_s->dbo;
	$_s->_log     = $_s->log;
	$_s->_admin   = $_s->admin;
	$_s->_mem     = $_s->mem;
}
?>