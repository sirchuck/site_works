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

	// Logs are written at page end to files you specify in config.
	public $log = [];
	// console will print to the webpages console.
	public $console = [];

	public $admin = [];
	public $mem   = [];

	public $site_works_db_classes = [];

	public $apcu_is_set = false;

    public function __construct(){
    	$this->softwareTimer = microtime(true);
		spl_autoload_register(array($this, 'handle_autoload'));
		$this->tool = new siteworks_tools($this);
		$this->uri = new siteworks_uri($this);
		register_shutdown_function(array($this, 'handle_shutdown'));
		if( array_key_exists('default', $this->dbc) ){
			$tmp = ( !isset($this->dbc['default']['hostname']) || $this->dbc['default']['hostname'] == '') ? 'Default Database Hostname Missing<br>' : '';
			$tmp = ( !isset($this->dbc['default']['username']) || $this->dbc['default']['username'] == '') ? $tmp . 'Default Database Username Missing<br>' : $tmp;
			$tmp = ( !isset($this->dbc['default']['password']) || $this->dbc['default']['password'] == '') ? $tmp . 'Default Database Password Missing<br>' : $tmp;
			$tmp = ( !isset($this->dbc['default']['database']) || $this->dbc['default']['database'] == '') ? $tmp . 'Default Database Database name Missing<br>' : $tmp;
			$tmp = ( !isset($this->dbc['default']['dbtype']) || $this->dbc['default']['dbtype'] == '') ? $tmp . 'Default Database dbtype Missing<br>' : $tmp;
			if($tmp!=''){
				die('You need to set up your database in the server configuration file.<br><br>'.$tmp);
			}
			$this->odb = new siteworks_dbc((object)$this->dbc['default'],$this);
			$this->dbo['default'] = $this->odb;
			$GLOBALS['_odb'] =& $this->odb;
		}else{die('You must have a default database connection for administration.');}
		foreach($this->dbc as $k => $v){ if($k != 'default'){ $this->dbo[$k] = new siteworks_dbc((object)$v,$this); } }
		$dbc_database_name = $this->dbc['default']['database'];

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
		$this->apcu_is_set = ( $this->APCuTimeoutMinutes > 0 && extension_loaded('apcu') ) ? true : false;
		$db_load = true;
		$had_to_build_databases = false;
		$apcu_hold_time = time();
		if( $this->apcu_is_set ){
			$db_load = false;
			$this->admin = apcu_fetch($this->uri->fixedapcu.'admin');
			if(!isset($this->admin['apcu_start_time']) || $this->admin['apcu_start_time'] < time() - ($this->APCuTimeoutMinutes * 60 * 1000) ){ $db_load = true; }
		}
		if($db_load){
			$radmin = new t_site_works_admin(1,$this->odb);
			if( (int)$radmin->f['sw_admin_key']['value'] !== 1 ){
					$this->odb->q("CREATE TABLE IF NOT EXISTS `" . $dbc_database_name . "`.`site_works_admin` ( `sw_admin_key` TINYINT(1) UNSIGNED NOT NULL, `sw_version` CHAR(40) NOT NULL , PRIMARY KEY (`sw_admin_key`)) ENGINE = InnoDB;");
					$this->odb->q("INSERT INTO `" . $dbc_database_name . "`.`site_works_admin` (`sw_admin_key`,`sw_version`) VALUES (1,'0');");
					$had_to_build_databases = true;
			}
			$this->admin = null;
			$this->admin = ['apcu_start_time' => $apcu_hold_time];
			foreach($radmin->f as $k => $v){ $this->admin[$k] = $v['value']; }
			if( $this->apcu_is_set ){ apcu_store($this->uri->fixedapcu.'admin', $this->admin); } //apcu_delete('_site_works_admin'); apcu_clear_cache();
		}
		// Handle Memory Table and APCu
		$db_load2 = true;
		if( $this->apcu_is_set ){
			$db_load2 = false;
			$this->mem = apcu_fetch($this->uri->fixedapcu.'mem');
			if(!isset($this->mem['apcu_start_time']) || $this->mem['apcu_start_time'] < time() - ($this->APCuTimeoutMinutes * 60 * 1000) ){ $db_load2 = true; }
		}
		if($db_load2){
			$r = new t_site_works_mem(1,$this->odb);
			if( (int)$r->f['sw_mem_key']['value'] !== 1 ){
				$this->odb->q("CREATE TABLE IF NOT EXISTS `" . $dbc_database_name . "`.`site_works_mem` ( `sw_mem_key` TINYINT(1) UNSIGNED NOT NULL , `sw_site_visits` BIGINT(11) UNSIGNED NOT NULL , PRIMARY KEY (`sw_mem_key`)) ENGINE = MEMORY;");
				$this->odb->q("INSERT INTO `" . $dbc_database_name . "`.`site_works_mem` (`sw_mem_key`,`sw_site_visits`) VALUES (1,0);");
				// $had_to_build_databases = true;
			}
			$this->mem = null;
			$this->mem = ['apcu_start_time' => $apcu_hold_time];
			foreach($r->f as $k => $v){ $this->mem[$k] = $v['value']; }
			if( $this->apcu_is_set ){ apcu_store($this->uri->fixedapcu.'mem', $this->mem); } //apcu_delete('_site_works_admin'); apcu_clear_cache();
		}
		// Handle language table creation if needed.
		// If $db_load is true, we'll check to see if the following tables exist, if not create them but we dont pull anything.
		if($db_load){
			$r = new t_site_works_lang(null,$this->odb);
			$r->query("SHOW TABLES LIKE 'site_works_lang';");
			if($r->c->numRows()<1){
				$this->odb->q("CREATE TABLE IF NOT EXISTS `" . $dbc_database_name . "`.`site_works_lang` ( `sw_lang_key` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT , `sw_lang_keep` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0', `sw_lang_category` CHAR(40) NULL , `sw_origional` TEXT NULL , `english` TEXT NULL, PRIMARY KEY (`sw_lang_key`)) ENGINE = InnoDB;");
				$had_to_build_databases = true;
			}

			$r = new t_site_works_queue(null,$this->odb);
			$r->query("SHOW TABLES LIKE 'site_works_queue';");
			if($r->c->numRows()<1){
				$this->odb->q("CREATE TABLE IF NOT EXISTS `" . $dbc_database_name . "`.`site_works_queue` ( `sw_ts` BIGINT(13) UNSIGNED NOT NULL AUTO_INCREMENT , `sw_tag` TEXT NOT NULL , `sw_script` TEXT NOT NULL , `sw_vars` LONGTEXT NOT NULL , `sw_waitstart` INT(11) NOT NULL DEFAULT '0' , `sw_timeout` INT(11) NOT NULL DEFAULT '0' , PRIMARY KEY (`sw_ts`)) ENGINE = InnoDB;");
				$had_to_build_databases = true;
			}

		}
		if($had_to_build_databases){die($dbc_database_name . ' database tables were created with default values. Change the values as needed, sw_admin_key and sw_mem_key must be set to 1. <br><br>If the site_work tables were not created in your database, verify the config is set correctly and that the user has proper grant priviledges. Setup Complete: Refresh This Page.');}


		// Handle Database setup and management
		if($this->debugMode){
			if(strrpos($_SERVER['DOCUMENT_URI'], 'ajax_') !== false || strrpos($_SERVER['DOCUMENT_URI'], 'iframe_') !== false || strrpos($_SERVER['DOCUMENT_URI'], 'script_') !== false){$this->css_js_minify = false;}

			$radmin = new t_site_works_admin(1,$this->odb);
			$radmin->f['sw_version']['value'] = time();
			$this->admin['sw_version'] = $radmin->f['sw_version']['value'];
			$radmin->updateData();

			// Build Language Files
			$r = new t_site_works_lang(null,$this->odb);
			$r->p['list'] = array();
			$r->query('SELECT sw_lang_key, sw_lang_keep, sw_origional FROM `' . $dbc_database_name . '`.`site_works_lang` WHERE sw_lang_category = \'\'');
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


			// Get a list of db tables, make sure you can start objects and match fields 
			// Verify Database Structure and Tabeles
			$tmp = scandir( SITEWORKS_DOCUMENT_ROOT . '/includes/dbtables' );
			foreach($tmp as $v){ if( substr($v, 0, 1) != '.' ){ $this->site_works_db_classes[] = array($v,'0'); } }
			$tmp = scandir( SITEWORKS_DOCUMENT_ROOT . '/private/dbtables' );
			foreach($tmp as $v){ if( substr($v, 0, 1) != '.' ){ $this->site_works_db_classes[] = array($v,'1'); } }

			foreach($this->site_works_db_classes as $k => $v){
				if( substr($v[0], 0, 1) == '.' || $v[0] == 't_site_works_template_table.inc.php'){ unset($this->site_works_db_classes[$k]); } else {
					unset($farray);
					$farray = array();
					$swdb = $v[1];
					$this->site_works_db_classes[$k] = array('value'=>explode('.',$v[0],2)[0],'sw'=>$v[1],'f'=>$farray,'primary'=>'');
					if($swdb == 0){
						$vx = 'SiteWorks\\'. $this->site_works_db_classes[$k]['value'];
					}else{
						$vx = $this->site_works_db_classes[$k]['value'];
					}
					$tmp = new $vx(null,$this->odb);
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
						$row2[3] = ( strtoupper(substr( $row2[3], 0, 3 )) === "PRI" ) ? 'PRI':'';
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
				$tmp2 = preg_replace(['/asset_url/','/base_url/','/root_url/'],[$this->uri->asset_url,$this->uri->base_url,$this->uri->root_url],$tmp2);
				foreach($this->sw_pass as $sk => $sv){$tmp2 = preg_replace('/sw_pass\['. $sk .'\]/',$sv,$tmp2); }
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
			if($this->css_js_one_file){
				$jscode .= '(function addcss(css){var s = document.createElement(\'style\');s.setAttribute(\'type\', \'text/css\');if (s.styleSheet) {s.styleSheet.cssText = css;} else {s.appendChild(document.createTextNode(css));}document.getElementsByTagName(\'head\')[0].appendChild(s); })("%D%");';
			}

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
						$tmp3 = '';
						if($this->css_js_one_file){$tmp3 = file_get_contents(SITEWORKS_DOCUMENT_ROOT . '/public/assets/css/siteworks/themes/'.$v2['name'].'/'.'siteworks_' . $this->admin['sw_version'] . '.css'); }
						$tmp3 = preg_replace(["/\r\n|\r|\n/",'/"/'],["",'\"'],$tmp3);
						$tmp2 = preg_replace(['/asset_url/','/base_url/','/root_url/'],[$this->uri->asset_url,$this->uri->base_url,$this->uri->root_url],$tmp2);
						foreach($this->sw_pass as $sk => $sv){$tmp2 = preg_replace('/sw_pass\['. $sk .'\]/',$sv,$tmp2);}

						file_put_contents($new_path.'siteworks.j','var _slang = '.json_encode($jsStringArray).';'.preg_replace('/%D%/',$tmp3,$jscode).$tmp2,775);
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
			unset($tmp); unset($tmp2); unset($tmp3);
			// _s_lang_set_delete - if you uncomment the lines with this variable, i'll reload the page to make sure your js files are clean when you mark text for deletion.
			//$_SESSION['_s_lang_set_delete'] = (isset($_SESSION['_s_lang_set_delete']))?$_SESSION['_s_lang_set_delete']:0;
       		// Mark language for deletion
       		if($this->allow_auto_delete_language){
				foreach($r->p['list'] as $v){ if($v->sw_lang_keep<1){ $r->deleteData($v->sw_lang_key); /*$_SESSION['_s_lang_set_delete']=time();*/} }
       		}else{
				foreach($r->p['list'] as $v){ if($v->sw_lang_keep<1){ $r->updateData($v->sw_lang_key,'`sw_lang_keep`=3'); /*$_SESSION['_s_lang_set_delete']=time();*/} }
       		}
			//if( $_SESSION['_s_lang_set_delete'] > time()-200 ){ header("Refresh:0"); }
			//unset($_SESSION['_s_lang_set_delete']);

			// Return to printing SQL if allowed
			$this->printSQL = $hold_printSQL;

			// Build public index page
			$f = file_get_contents(SITEWORKS_DOCUMENT_ROOT . '/includes/templates/site_index.txt');
			if ($this->showPHPErrors){$f = str_replace("{{SHOWERRORS}}","ini_set('display_errors', 1);ini_set('display_startup_errors', 1);error_reporting(E_ALL);",$f);} else {$f = str_replace("{{SHOWERRORS}}","",$f);}
			file_put_contents(SITEWORKS_DOCUMENT_ROOT . '/public/index.php',$f,775);



		} // End if debug mode
		$this->uri->uri_finish($this);


		$tmp = SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/themes/' . $this->theme . '/siteworks_' . $this->language . '_' . $this->admin['sw_version'] . '.js';
		if(!file_exists( $tmp )){
			$hold_printSQL = $this->printSQL;
			$this->printSQL = false;
			$radmin = new t_site_works_admin(1,$this->odb);
			$this->admin['sw_version'] = $radmin->f['sw_version']['value'];
			$tmp = SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/themes/' . $this->theme . '/siteworks_' . $this->language . '_' . $this->admin['sw_version'] . '.js';
			if(!file_exists( $tmp )){
				foreach($this->tool->listFiles(SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/themes/' . $this->theme,1,true) as $v){
					$tmp2 = explode("_",$v['name']);
					if($tmp2[0] == 'siteworks'){
						$tmp3 = explode('.',$tmp2[2]);
						$this->admin['sw_version'] = $tmp3[0];
						$radmin->f['sw_version']['value'] = $tmp3[0];
						$radmin->updateData();
					}
				}
				die('The sw_version we have in the admin database table does not match the siteworks javascript asset file.<br>
					' . SITEWORKS_DOCUMENT_ROOT . '/public/assets/js/siteworks/themes/' . $this->theme . '/siteworks_' . $this->language . '_' . $this->admin['sw_version'] . '.js<br>
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
	public function handle_shutdown(){
		if( $this->apcu_is_set ){
			apcu_store($this->uri->fixedapcu.'admin', $this->admin);
			apcu_store($this->uri->fixedapcu.'mem', $this->admin);
		}
		if($this->debugMode && $this->showPHPErrors_debug){
			$e = error_get_last();
			if($e == ''){
				$this->tool->dmsg('[c_light_green]    No PHP Errors  - America, Hell Yeah!'."\n",false,false);
			}else{
				$this->tool->dmsg("\n".'[c_light_red]************   E R R O R S   ************', false, false);
				$this->tool->dmsg('[c_white][File] '.$e['file'], false, false);
				$this->tool->dmsg('[c_white][Line] '.$e['line'], false, false);
				$e = "\n".'Errors: '.implode("\n",$e)."\n";
				$this->tool->dmsg('[c_light_red]'.$e, false, false);
			}
		}
		if($this->debugMode){
			foreach($this->tail_array as $v){$this->tool->dmsg('[c_white][Tail] ' . $v[0] . "\n[c_light_purple]" . shell_exec('tail -n '.$v[1].' '.$v[0]) . "\n", false, false); }
			$this->tool->dmsg('************   F I N I S H   ************', false, false);
		}
	}

	// APCu functions
	public function set_apcu($apcu_var_name, $variable)   { if( extension_loaded('apcu') ){ apcu_store($this->uri->fixedapcu.$apcu_var_name, $variable); } }
	public function get_apcu($apcu_var_name)              { if( extension_loaded('apcu') ){ return apcu_fetch($this->uri->fixedapcu.$apcu_var_name); } return ''; }
	public function delete_admin($apcu_var_name)          { if( extension_loaded('apcu') ){ apcu_delete($this->uri->fixedapcu.$apcu_var_name); } }
	public function clear_apcu()                          { if( extension_loaded('apcu') ){ apcu_clear_cache(); } }

	public function site_works_finish(){
		$this->out['body'][] = ob_get_contents();
		ob_end_clean();

        // Handle Log Output
        foreach( $this->log_files as $v ){
	        if( $this->log_auto_clean_size_kb > 0 && filesize( $v[1] ) >= $this->log_auto_clean_size_kb * 1000 ){
                $tmp = '';
                $file = file( $v[1] );
                $c = count($file);
                if( $c > 10 ){
                    for( $i=$c-10; $i<$c; $i++ ){
                    	$tmp .= $file[$i];
                    }
                }
                $myfile = fopen($v[1], "w");
                fwrite($myfile, $tmp);
                fclose($myfile);
	        }
	        if( isset( $this->log[$v[0]] ) ){ file_put_contents( $v[1], "\n".implode("\n",$this->log[$v[0]]), FILE_APPEND | LOCK_EX);}
        }

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
		$this->out['header']['favicon'] = (isset($this->out['header']['favicon']) && $this->out['header']['favicon'] != '')? $this->out['header']['favicon'] : '<link rel="shortcut icon" type="image/png" href="' . $this->uri->base_url . '/siteworks_favicon.ico"/>';

		$_SESSION['theme'] = (isset($_SESSION['theme']) && $_SESSION['theme'] != '') ? $_SESSION['theme'] : $this->theme;
		$_SESSION['language'] = (isset($_SESSION['language']) && $_SESSION['language'] != '') ? $_SESSION['language'] : $this->language;

		if(!$this->css_js_one_file){
			$this->out['css'][] = '<link rel="stylesheet" type="text/css" href="' . $this->uri->asset->css . '/siteworks/themes/' . $_SESSION['theme'] . '/siteworks_' . $this->admin['sw_version'] .'.css"/>';
		}
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
implode( ' ', $this->out['link'] ) .
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