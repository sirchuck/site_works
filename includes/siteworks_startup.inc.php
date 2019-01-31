<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');
class siteworks_startup
{
	public $softwareTimer  = 0;
	public $uri            = null;
	public $odb            = null;
	public $dbo            = [];
	public $tool           = null;
	public $p              = [];

	// Site Output
	public $out = [
		 'header'=>array()	// Stuff after <html> and before title
		,'title'=>array()	// Stuff around the title area, like <title>
		,'meta'=>array()	// Meta tags
		,'css'=>array()		// CSS links and even inpage css if you want.
		,'js'=>array()		// JS links and inpage js
		,'body'=>array()    // Between body tags
		,'footer'=>array()	// This goes just before last body tag. 
	];

	// console will print to the webpages console.
	public $console = [];

	// APCu Array
	public $admin = [];
	public $mem   = [];

	public $site_works_db_classes = [];

    public function __construct(){
    	$this->softwareTimer = microtime(true);
		spl_autoload_register(array($this, 'handle_autoload'));
		$this->tool = new siteworks_tools($this);
		register_shutdown_function(array($this, 'handle_shutdown'));
		if( array_key_exists('default', $this->dbc) ){
			$this->odb = new siteworks_dbc((object)$this->dbc['default'],$this);
			$this->dbo['default'] = $this->odb;
		}else{die('You must have a default database connection for administration.');}
		foreach($this->dbc as $k => $v){ if($k != 'default'){ $this->dbo[$k] = new siteworks_dbc((object)$v,$this); } }

		// Protect Database Connection Passwords from being revield later.
		unset($this->dbc);

		// Start-up output for debug
		$debug_out = '';
		if($this->debugMode){
			$debug_out = "\n\n\n\n\n\n\n\n\n******************************************\n*********** S I T E W O R K S ************\n******************************************
Start Time: " . date('Y-m-d H:i:s') . "
";
		}

		// Not using sessions this way.
		//$this->sess = new siteworks_session($this->odb);

		$hold_printSQL = $this->printSQL;
		$this->printSQL = false;

		// Handle Admin Table and APCu
		$db_load = true;
		$apcu_hold_time = time();
		if( extension_loaded('apcu') ){
			$db_load = false;
			$this->admin = apcu_fetch('_site_works_admin');
			if(!isset($this->admin['apcu_start_time']) || $this->admin['apcu_start_time'] < time() - ($this->APCuTimeoutMinutes * 60 * 1000) ){ $db_load = true; }
		}
		if($db_load){
			$radmin = new t_site_works_admin(1,$this->odb);
			if( (int)$radmin->f['sw_admin_key']['value'] !== 1 ){
					$this->odb->q("CREATE TABLE IF NOT EXISTS `site_works`.`site_works_admin` ( `sw_admin_key` TINYINT(1) UNSIGNED NOT NULL, `sw_version` CHAR(40) NOT NULL , `sw_language` CHAR(40) NOT NULL , PRIMARY KEY (`sw_admin_key`)) ENGINE = InnoDB;");
					$this->odb->q("INSERT INTO `site_works`.`site_works_admin` (`sw_admin_key`,`sw_version`,`sw_language`) VALUES (1,'0','english');");
					die('site_works_admin table was created with default values in the default database. Change the values as needed, sw_admin_key must be set to 1.');
			}
			$this->admin = null;
			$this->admin = ['apcu_start_time' => $apcu_hold_time];
			foreach($radmin->f as $k => $v){ $this->admin[$k] = $v['value']; }
			if( extension_loaded('apcu') ){ apcu_store('_site_works_admin', $this->admin); } //apcu_delete('_site_works_admin'); apcu_clear_cache();
		}
		// Handle Memory Table and APCu
		$db_load = true;
		if( extension_loaded('apcu') ){
			$db_load = false;
			$this->mem = apcu_fetch('_site_works_mem');
			if(!isset($this->mem['apcu_start_time']) || $this->mem['apcu_start_time'] < time() - ($this->APCuTimeoutMinutes * 60 * 1000) ){ $db_load = true; }
		}
		if($db_load){
			$r = new t_site_works_mem(1,$this->odb);
			if( (int)$r->f['sw_mem_key']['value'] !== 1 ){
				$this->odb->q("CREATE TABLE IF NOT EXISTS `site_works`.`site_works_mem` ( `sw_mem_key` TINYINT(1) UNSIGNED NOT NULL , `sw_site_visits` BIGINT(11) UNSIGNED NOT NULL , PRIMARY KEY (`sw_mem_key`)) ENGINE = MEMORY;");
				$this->odb->q("INSERT INTO `site_works`.`site_works_mem` (`sw_mem_key`,`sw_site_visits`) VALUES (1,0);");
			}
			$this->mem = null;
			$this->mem = ['apcu_start_time' => $apcu_hold_time];
			foreach($r->f as $k => $v){ $this->mem[$k] = $v['value']; }
			if( extension_loaded('apcu') ){ apcu_store('_site_works_mem', $this->mem); } //apcu_delete('_site_works_admin'); apcu_clear_cache();
		}
		// Handle language table creation if needed.
		if($db_load){
			$r = new t_site_works_lang(0,$this->odb);
			$r->query("SHOW TABLES LIKE 'site_works_lang';");
			if($r->c->numRows()<1){
				$this->odb->q("CREATE TABLE IF NOT EXISTS `site_works`.`site_works_lang` ( `sw_lang_key` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT , `sw_lang_keep` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0', `sw_lang_category` CHAR(40) NULL , `sw_origional` TEXT NULL , `english` TEXT NULL, PRIMARY KEY (`sw_lang_key`)) ENGINE = InnoDB;");
			}
		}

		// Handle Database setup and management
		if($this->debugMode){
			if(strrpos($_SERVER['DOCUMENT_URI'], 'ajax_') !== false || strrpos($_SERVER['DOCUMENT_URI'], 'iframe_') !== false || strrpos($_SERVER['DOCUMENT_URI'], 'script_') !== false){$this->css_js_minify = false;}

			$radmin = new t_site_works_admin(1,$this->odb);
			$radmin->f['sw_version']['value'] = time();
			$this->admin['sw_version'] = $radmin->f['sw_version']['value'];
			$radmin->updateData();

			// Get a list of db tables, make sure you can start objects and match fields 
			// Verify Database Structure and Tabeles

			$this->site_works_db_classes = scandir( SITEWORKS_DOCUMENT_ROOT . '/includes/dbtables' );
			$tmp = scandir( SITEWORKS_DOCUMENT_ROOT . '/dev/dbtables' );
			foreach($tmp as $v){ if( substr($v, 0, 1) != '.' ){ $this->site_works_db_classes[] = $v; } }
			foreach($this->site_works_db_classes as $k => $v){
				if( substr($v, 0, 1) == '.' ){ unset($this->site_works_db_classes[$k]); } else {
					unset($farray);
					$farray = array();
					$this->site_works_db_classes[$k] = array('value'=>explode('.',$v,2)[0],'f'=>$farray,'primary'=>'');
					$vx = 'SiteWorks\\'. $this->site_works_db_classes[$k]['value'];
					$tmp = new $vx(0,$this->odb);
					$this->site_works_db_classes[$k]['primary'] = $tmp->keyField;
					foreach($tmp->f as $k2 => $v2){
						$farray[] = array('value'=>$k2,'type'=>$v2['value']);
					}
					$this->site_works_db_classes[$k]['f'] = $farray;
				}
			}

			// Pull all tables from all databases then match them up.
			$check_site_works_db_classes=[];
			foreach($this->dbo as $k => $c){
				$result = $c->q('SHOW TABLES;');
				while ($row = $result->fetch_row()) {
					unset($farray);
					$result2 = $c->q('SHOW FIELDS FROM ' . $row[0] . ';');
					while ($row2 = $result2->fetch_row()) {
						// Field   Type    Null    Key     Default     Extra
						$farray[] = array('value'=>$row2[0],'type'=>$row2[1],'primary'=>$row2[3],'dbtype'=>$c->dbt);
					}
					$check_site_works_db_classes[] = Array('value'=>'t_'.$row[0],'f'=>$farray);
					$c->freeResult($result2);
				}
				$c->freeResult($result);
			}

			// Now We match the databases responce with our php db classes
			$db_info_out = ''; $sh = []; $cprimary = '';
			if(count($this->site_works_db_classes)>count($check_site_works_db_classes)){
				$sh[0] =& $this->site_works_db_classes;
				$sh[1] =& $check_site_works_db_classes;
				$sh[2] = 'Database';
			}else{
				$sh[0] =& $check_site_works_db_classes;
				$sh[1] =& $this->site_works_db_classes;
				$sh[2] = 'Class';
			}
			foreach( $sh[0] as $v ){
				$cprimary = (isset($v['primary']))?$v['primary']:$cprimary;
				$foundTable = false;
				foreach( $sh[1] as $v2 ){
					$cprimary = (isset($v2['primary']))?$v2['primary']:$cprimary;
					if($v['value'] == $v2['value']){
						// Check Matching Fields
						$foundTable = true;
						unset($sf); $sf = [];
						if(count($v['f'])>count($v2['f'])){
							$sf[0] =& $v['f'];
							$sf[1] =& $v2['f'];
							$sf[2] = ($sh[2]=='Database')?'Database':'Class';
						}else{
							$sf[0] =& $v2['f'];
							$sf[1] =& $v['f'];
							$sf[2] = ($sh[2]=='Database')?'Class':'Database';
						}
						foreach($sf[0] as $sv){
							$foundField = false;
							foreach($sf[1] as $sv2){
								if($sv['value'] == $sv2['value']){
									$foundField = true;
									$stype='';$stype2='';$primary = '';$dbtype='';
									if($sf[2] == 'Database'){
										$stype = $sv2['type']; $stype2 = $sv['type']; $primary = ($sv2['primary'] != '')?$sv2['value']:'';$dbtype = $sv2['dbtype'];
									}else{
										$stype = $sv['type']; $stype2 = $sv2['type']; $primary = ($sv['primary'] != '')?$sv['value']:'';$dbtype = $sv['dbtype'];
									}

									//*************** CHECKING MySQLi Database Types Only.
									if($dbtype=='mysqli'){
										$stype = explode('(',$stype)[0];
										if(strrpos($stype,"int")===false && $stype!='decimal' && $stype!='numeric' && $stype!='float' && $stype!='double' && $stype!='real' && $stype!='bit'){
											$stype = null;
										}else{$stype=0;}
										if($stype!==$stype2){$db_info_out .= 'Table: [' . preg_replace('/t_/','',$v['value'],1) . '] Field: [' . $sv['value'] . '] (VALUE TYPE) does not match.'."\n";}
									}
									// ************** END VALUE CHECK

									if($primary != ''){
										if($primary!==$cprimary){$db_info_out .= 'Table: [' . preg_replace('/t_/','',$v['value'],1) . '] Field: [' . $sv['value'] . '] (PRIMARY KEYS) do not match.'."\n";}
									}
								}
							}
							if(!$foundField){$db_info_out .= 'Table: [' . preg_replace('/t_/','',$v['value'],1) . '] Field: [' . $sv['value'] . '] (MISSING) in ' . $sf[2] . "\n";}
						}
					}
				}
				if(!$foundTable){ $db_info_out .= 'Table: [' . preg_replace('/t_/','',$v['value'],1) . '] (MISSING) in ' . $sh[2] . "\n";}
			}
			if($db_info_out != ''){ $this->tool->dmsg("\n     [c_light_red]-+-+- Database Errors -+-+-[c_clear]\n".$db_info_out);die('You have a database missmatch, please check your ./debug_server output.'); }

			// Build Language Files
			$r = new t_site_works_lang(0,$this->odb);
			$r->p['list'] = array();
			$r->query('SELECT sw_lang_key, sw_lang_keep, sw_origional FROM `site_works`.`site_works_lang` WHERE sw_lang_category = \'\'');
			while($row = $r->getRows()){$r->p['list'][]=$row;}

			$f = '';
			// Developer Code
       		$this->tool->delTree(SITEWORKS_DOCUMENT_ROOT . '/private/',false);
			foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/dev/',1) as $v){
				if( substr( $v['path'], 0, strlen(SITEWORKS_DOCUMENT_ROOT . '/dev/_') ) !== SITEWORKS_DOCUMENT_ROOT . '/dev/_' ){
					$new_path = preg_replace('@' . SITEWORKS_DOCUMENT_ROOT . '/dev@', SITEWORKS_DOCUMENT_ROOT . '/private', $v['path']);
					if (!is_dir($new_path)){mkdir($new_path,0775,true);}
	        		unset($m);
	        		$f = file_get_contents($v['path'].$v['name']);
	        		$f = preg_replace_callback('/(.)\s*\[__(.*?)__\]/', function($m)use(&$r){return $this->tool->buildText($m,$r);},$f);
					file_put_contents($new_path.$v['name'],$f,775);
				}
			}

			// CSS
			$tmp = '';
       		$this->tool->delTree(SITEWORKS_DOCUMENT_ROOT . '/public/assets/css/siteworks/');
			foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/dev/_css/',1,false) as $v){ if( stripos(strrev($v['name']), 'ssc.') === 0 ){ $tmp .= file_get_contents($v['path'].$v['name']); } }
			foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/dev/_css/themes/',2,false) as $v){
				$new_path = SITEWORKS_DOCUMENT_ROOT . '/public/assets/css/siteworks/themes/'.$v['name'].'/';
				$tmp2 = '';
				foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/dev/_css/themes/'.$v['name'].'/',1,false) as $v2){ if( stripos(strrev($v2['name']), 'ssc.') === 0 ){ $tmp2 .= file_get_contents($v2['path'].$v2['name']); } }
				$tmp2 = $tmp2.$tmp;
				if($tmp2 == ''){$tmp2 = '/* S I T E    W O R K S */';}
				if (!is_dir($new_path)){mkdir($new_path,0775,true);}
				file_put_contents($new_path.'siteworks.cs',$tmp2,775);
				if($this->css_js_minify){
					try{exec('uglifycss ' . $new_path.'siteworks.cs > ' . $new_path.'siteworks_' . $this->admin['sw_version'] . '.css');}catch(Exception $e){unset($e);}
			    }else{
			    	rename ($new_path.'siteworks.cs',$new_path.'siteworks_' . $this->admin['sw_version'] . '.css');
			    }
				$this->tool->removeFile($new_path.'siteworks.cs');
			}
			foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/dev/_css/vendor/',1,true) as $v){
				$new_path = SITEWORKS_DOCUMENT_ROOT . '/public/assets/css/siteworks/vendor' . substr($v['path'],strlen(SITEWORKS_DOCUMENT_ROOT . '/dev/_css/vendor'));
				$tmp = file_get_contents($v['path'].$v['name']);
				if (!is_dir($new_path)){mkdir($new_path,0775,true);}
				file_put_contents($new_path.$v['name'],$tmp2,775);
			}



			// Javascript
			$jscode = 'function getOptions(_sop){return _slang[_sop];}
			function getText(_sid,_sop){_sop=(_sop)?_sop:\'_s\';return _slang[_sop][_sid];}
			function fixText(_s){return _s.replace(/{__(.+?)__}/g,function(m0,m1){return getText(m1);});}
			document.addEventListener(\'DOMContentLoaded\', function () {	 document.documentElement.innerHTML = fixText(document.documentElement.innerHTML);   });
			';

			$tmp = '';
       		$this->tool->delTree(SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/');
			foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/dev/_js/',1,false) as $v){ if( stripos(strrev($v['name']), 'sj.') === 0 ){ $tmp .= file_get_contents($v['path'].$v['name']); }	}
			foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/dev/_js/themes/',2,false) as $v2){
				unset($m);
				$tmp2 = '';
				foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/dev/_js/themes/'.$v2['name'].'/',1,false) as $v){ if( stripos(strrev($v['name']), 'sj.') === 0 ){ $tmp2 .= file_get_contents($v['path'].$v['name']); }	}
	       		$tmp2 = preg_replace_callback('/(.)\s*\[__(.*?)__\]/', function($m)use(&$r){return $this->tool->buildText($m,$r,true);},$tmp2.$tmp);
				$new_path = SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/themes/'.$v2['name'].'/';
				if (!is_dir($new_path)){mkdir($new_path,0775,true);}
				foreach($r->f as $k => $v){
					unset($jsStringArray);
					$jsStringArray = array();
					if( substr( $k, 0, 3 ) !== "sw_" ){
						$result = $r->selectAll('sw_lang_keep < 2','sw_lang_key, sw_lang_category, IF( '.$k.' IS NULL, `english`, `' . $k . '`) as lang');
						while($row=$r->getRows( $result )){
							$use_cat = ($row->sw_lang_category != '')?$row->sw_lang_category:'_s';
							$jsStringArray[$use_cat][$row->sw_lang_key] = $row->lang;
						}
						file_put_contents($new_path.'siteworks.j','var _slang = '.json_encode($jsStringArray).';'.$jscode.$tmp2,775);
						if($this->css_js_minify){
							try{exec('uglifyjs ' . $new_path.'siteworks.j > ' . $new_path.'siteworks_' . $k . '_' . $this->admin['sw_version'] . '.js');}catch(Exception $e){unset($e);} 
						} else {
					    	rename ($new_path.'siteworks.j',$new_path.'siteworks_' . $k . '_' . $this->admin['sw_version'] . '.js');
						}
					}
					$this->tool->removeFile($new_path.'siteworks.j');
				}
			}
			foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/dev/_js/vendor/',1,true) as $v){
				$new_path = SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/vendor' . substr($v['path'],strlen(SITEWORKS_DOCUMENT_ROOT . '/dev/_js/vendor'));
				$tmp = file_get_contents($v['path'].$v['name']);
				if (!is_dir($new_path)){mkdir($new_path,0775,true);}
				file_put_contents($new_path.$v['name'],$tmp2,775);
			}
			unset($m);
			unset($jscode);
			unset($jsStringArray);
			unset($tmp); unset($tmp2);
			// _s_lang_set_delete - if you uncomment the lines with this variable, i'll reload the page to make sure your js files are clean when you mark text for deletion.
			//$_SESSION['_s_lang_set_delete'] = (isset($_SESSION['_s_lang_set_delete']))?$_SESSION['_s_lang_set_delete']:0;
       		// Mark language for deletion
			foreach($r->p['list'] as $v){ if($v->sw_lang_keep<1){ $r->updateData($v->sw_lang_key,'`sw_lang_keep`=3'); /*$_SESSION['_s_lang_set_delete']=time();*/} }
			//if( $_SESSION['_s_lang_set_delete'] > time()-200 ){ header("Refresh:0"); }
			//unset($_SESSION['_s_lang_set_delete']);

			// Return to printing SQL if allowed
			$this->printSQL = $hold_printSQL;

			// Build public index page
			$f = file_get_contents(SITEWORKS_DOCUMENT_ROOT . '/includes/templates/site_index.txt');
			if ($this->showPHPErrors){$f = str_replace("{{SHOWERRORS}}","ini_set('display_errors', 1);ini_set('display_startup_errors', 1);error_reporting(E_ALL);",$f);} else {$f = str_replace("{{SHOWERRORS}}","",$f);}
			file_put_contents(SITEWORKS_DOCUMENT_ROOT . '/public/index.php',$f,775);



		} // End if debug mode
		$this->uri = new siteworks_uri($this);


		$tmp = SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/themes/default/siteworks_' . $this->admin['sw_language'] . '_' . $this->admin['sw_version'] . '.js';
		if(!file_exists( $tmp )){
			$hold_printSQL = $this->printSQL;
			$this->printSQL = false;
			$radmin = new t_site_works_admin(1,$this->odb);
			$this->admin['sw_version'] = $radmin->f['sw_version']['value'];
			$this->admin['sw_language'] = $radmin->f['sw_language']['value'];
			$tmp = SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/themes/default/siteworks_' . $this->admin['sw_language'] . '_' . $this->admin['sw_version'] . '.js';
			if(!file_exists( $tmp )){
				foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/themes/default',1,true) as $v){
					$tmp2 = explode("_",$v['name']);
					if($tmp2[0] == 'siteworks'){
						$tmp3 = explode('.',$tmp2[2]);
						$this->admin['sw_version'] = $tmp3[0];
						$radmin->f['sw_version']['value'] = $tmp3[0];
						$radmin->updateData();
					}
				}
				die('The sw_version we have in the admin database table does not match the siteworks javascript asset file.<br>
					' . SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/themes/default/' . $this->admin['sw_language'] . '/siteworks_' . $this->admin['sw_language'] . '_' . $this->admin['sw_version'] . '.js<br>
					We attempted to update the database with the new sw_verison, reloading this page may fix the problem.<br>
					If not, you may need to manually change the default database admin table to reflect your current sw_version. 
				');
			}
			$this->printSQL = $hold_printSQL;
		}


		if($this->debugMode){
			$this->tool->dmsg($debug_out . '[' . $this->uri->calltypes . '] ' . $this->uri->module . '/' . $this->uri->controller . '/' . $this->uri->method . '/' . (($this->uri->pass_var)?$this->uri->pass_var . '/':'') . implode('/',$this->uri->pass_vars),false,true);
		}
	}

	private function handle_autoload($className){
		$cp = explode('\\',$className);
		$className = end($cp);
		try{
			
			// Load Database Tables $dba['tableName'] = new t_tableName;
			if( preg_match('/^t_/', $className) ){
				require_once(SITEWORKS_DOCUMENT_ROOT.'/includes/dbtables/'.$className.'.inc.php');
			}
			
			// Load standard classes
			else{
				require_once(SITEWORKS_DOCUMENT_ROOT.'/includes/'.$className.'.inc.php');
			}
		}
		catch (Exception $e){
			die('Could not load required include files.');
		}  
		
	}
	public function handle_shutdown(){
		if($this->debugMode && $this->showPHPErrors_debug){
			$e = error_get_last();
			if($e == ''){
				$this->tool->dmsg("\n".'[c_light_green]    No PHP Errors  - America, Hell Yeah!'."\n",false,false);
			}else{
				$this->tool->dmsg("\n\n".'[c_light_red]************   E R R O R S   ************', false, false);
				$this->tool->dmsg('[c_white][File] '.$e['file'], false, false);
				$this->tool->dmsg('[c_white][Line] '.$e['line'], false, false);
				$e = "\n".'Errors: '.implode("\n",$e)."\n";
				$this->tool->dmsg('[c_light_red]'.$e, false, false);
			}
			foreach($this->tail_array as $v){ $this->tool->dmsg('[c_white][Tail] ' . $v[0] . "\n[c_light_purple]" . exec('tail -n '.$v[1].' '.$v[0]) . "\n", false, false); }
		}
		if($this->debugMode){
			$this->tool->dmsg('************   F I N I S H   ************', false, false);
		}
	}

	// APCu functions
	public function set_apcu($apcu_var_name, $variable)   { if( extension_loaded('apcu') ){ apcu_store($apcu_var_name, $variable); } }
	public function get_apcu($apcu_var_name)              { if( extension_loaded('apcu') ){ return apcu_fetch($apcu_var_name); } return ''; }
	public function delete_admin($apcu_var_name)          { if( extension_loaded('apcu') ){ apcu_delete($apcu_var_name); } }
	public function clear_apcu()                          { if( extension_loaded('apcu') ){ apcu_clear_cache(); } }

	public function site_works_finish(){
		$this->out['body'][] = ob_get_contents();
		ob_end_clean();

		// Only output what you put in the body or echo if script or ajax.
		if($this->uri->calltypes == 'scripts'){$x='';foreach($this->out as $v){$x .= implode('',$v);}echo $x;return false;}
		if((isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") || $this->uri->calltypes == 'ajaxs'){$x='';foreach($this->out as $v){$x .= implode('',$v);}echo $x;return false;}


		// Handle Console Output
		$this->console[] = str_replace(["\n","\r"],'','
		 ___ _ _     __      __       _       \n
		/ __(_) |_ __\\\ \\\    / /__ _ _| |__ ___\n
		\\\__ \\\ |  _/ -_) \\\/\\\/ / _ \\\ \'_| / /(_-<\n
		|___/_|\\\__\\\___|\\\_/\\\_/\\\___/_| |_\\\_\\\/__/\n');
		$this->console[] = 'Page Execution took: '.number_format(microtime(true) - $this->softwareTimer, 5, '.','').' seconds ';
		if($this->debugMode){
			$this->console[] = ($this->debugMode)?'Debug Mode: ON':'Debug Mode: OFF';
		}
		$this->out['header']['favicon'] = (isset($this->out['header']['favicon']) && $this->out['header']['favicon'] != '')? $this->out['header']['favicon'] : '<link rel="shortcut icon" type="image/png" href="' . $this->uri->asset_url . '/favicon.ico"/>';

		$_SESSION['theme'] = (isset($_SESSION['theme']) && $_SESSION['theme'] != '') ? $_SESSION['theme'] : $this->theme;
		$_SESSION['language'] = (isset($_SESSION['language']) && $_SESSION['language'] != '') ? $_SESSION['language'] : $this->language;

		$this->out['css'][] = '<link rel="stylesheet" type="text/css" href="' . $this->uri->asset->css . '/siteworks/themes/' . $_SESSION['theme'] . '/siteworks_' . $this->admin['sw_version'] .'.css"/>';
		$this->out['js'][]  = '<script src="' . $this->uri->asset->js . '/siteworks/themes/' . $_SESSION['theme'] . '/siteworks_' .$_SESSION['language'] . '_' . $this->admin['sw_version'] .'.js"></script>';

		// Might not want to auto-include css and js above when using an iframe.
		if($this->uri->calltypes != 'iframes'){
			$this->out['js'][] = '<script language="javascript">console.log("'.implode('\n',$this->console).'")</script>'; 
		}
		echo '<html>
<head>' .
implode( ' ', $this->out['header'] ) .
implode( ' ', $this->out['title'] ) .
implode( ' ', $this->out['meta'] ) .
implode( ' ', $this->out['css'] ) .
implode( ' ', $this->out['js'] ) .
'</head>
<body>' . 
implode( ' ', $this->out['body'] ) . 
implode( ' ', $this->out['footer'] ) . 
'</body>
</hmtl>';
	}

}
?>