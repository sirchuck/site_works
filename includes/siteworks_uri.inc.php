<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class siteworks_uri
{
	// Break down the request components - domain(_n / _s) / module / controller / method / pass_var / pass_vars
	public $calltype;
	public $calltypes;
	public $module;
	public $controller;
	public $method;
	public $pass_var;
	public $pass_vars;
	
	// Root URL of the domain default auto, _n force not secure, _s force secure
	public $root_url;
	public $root_url_n;
	public $root_url_s;

	// Base URL when you want to stay in a pubic folder for example.
	public $base_url;
	public $base_url_n;
	public $base_url_s;

	// With assets, public is redundant for most setups, but i'll leave it in case I want to add plugins that access my cookies.
	public $public_url;
	public $public_url_n;
	public $public_url_s;
	
	public $asset;
	public $asset_url;
	public $asset_url_n;
	public $asset_url_s;

	public $fixeduri = '';

	public $sw_module_path;

	public $load_the_model = 0;

	public function __construct(&$_s)
	{
		// Root is basically the domain http://www.example.com
		$root_url = ( ( $_s->cPaths['subdomain'] != '' ) ? $_s->cPaths['subdomain'] . '.' : '' ) . $_s->cPaths['domain'] . '.'. $_s->cPaths['tld'];
		$this->root_url_n   = 'http://'  . $root_url;
		$this->root_url_s   = 'https://' . $root_url;
		$this->root_url     = ( $_s->secure ) ? $this->root_url_s : $this->root_url_n ;
		
		// Base gets you to your site folder http://www.example.com/siteworks
		$base_url = $_s->cPaths['project_name'];
		$this->base_url_n   = 'http://'  . $root_url . '/' . $base_url ;
		$this->base_url_s   = 'https://' . $root_url . '/' . $base_url ;
		$this->base_url     = ( $_s->secure ) ? $this->base_url_s : $this->base_url_n ;
		
		// Public gets you to your sites public folder http://www.example.com/siteworks/public
		$public_url = 'public' ;
		$this->public_url_n   = 'http://'  . $root_url . '/' . $base_url . '/' . $public_url ;
		$this->public_url_s   = 'https://' . $root_url . '/' . $base_url . '/' . $public_url ;
		$this->public_url     = ( $_s->secure ) ? $this->public_url_s : $this->public_url_n ;
		
		// So many ways to handle assets, you'll have to figure out the best way for your own project of course. This will work for most sites.
		$asset_url = ( ( $_s->cPaths['subdomain_a'] != '' ) ? $_s->cPaths['subdomain_a'] . '.' : '' ).$_s->cPaths['domain_a'].'.'.$_s->cPaths['tld_a'].'/'.$_s->cPaths['project_name'].'/public';
		$this->asset_url_n   = 'http://'  . $asset_url ;
		$this->asset_url_s   = 'https://' . $asset_url ;
		$this->asset_url     = ( $_s->secure ) ? $this->asset_url_s : $this->asset_url_n ;

		$this->asset = new \stdClass;
		$this->asset->images      = $this->asset_url . '/assets/images';
		$this->asset->documents   = $this->asset_url . '/assets/documents';
		$this->asset->js          = $this->asset_url . '/assets/js';
		$this->asset->css         = $this->asset_url . '/assets/css';
		$this->asset->js_vendor   = $this->asset_url . '/assets/js/vendor';
		$this->asset->css_vendor  = $this->asset_url . '/assets/css/vendor';

		$uri = $_SERVER['DOCUMENT_URI'];
		$this->fixeduri = trim(str_replace([$_s->cPaths['project_name'].'/public',$_s->cPaths['project_name']],'',trim($uri, '/')), '/');
		$params = explode('/', $this->fixeduri);
		if(!isset($params[0])){$params[0]=$_s->_s->default_module;}
		if( isset( $_s->routes[ $params[0] ] ) ){ $params[0] = $_s->routes[ $params[0] ]; }
		$p = explode('_', $params[0], 2);
		$this->calltypes = 'controllers';
		if(isset($p[1]) && $p[1]){
			switch($p[0]){
				case 'ajax':
					$this->calltypes = 'ajaxs';
					$params[0] = $p[1];
					break;
				case 'iframe':
					$this->calltypes = 'iframes';
					$params[0] = $p[1];
					break;
				case 'script':
					$this->calltypes = 'scripts';
					$params[0] = $p[1];
					break;
				default:
			}
		}
		$this->calltype = substr($this->calltypes,0,-1);
		$this->module     = (isset($params[0]) && $params[0]) ? (string)$params[0]      : $_s->default_module;
		$this->sw_module_path = SITEWORKS_DOCUMENT_ROOT . '/private/modules/' . $this->module;
		if(!is_dir( $this->sw_module_path )){
			$this->module     = $_s->default_module;
			$this->sw_module_path = SITEWORKS_DOCUMENT_ROOT . '/private/modules/' . $this->module;
			array_unshift($params,$_s->default_module);
		}
		$this->controller = (isset($params[1]) && $params[1]) ? $params[1] : $this->module;

		if(!$this->load_path( $this->sw_module_path . '/' . $this->calltypes . '/' . $this->controller . '.' . $this->calltype . '.php' ) ){
			$this->controller = $this->module;
			array_unshift($params,$this->module);
			$this->load_path( $this->sw_module_path . '/' . $this->calltypes . '/' . $this->controller . '.' . $this->calltype . '.php' );
		}
		$this->method     = (isset($params[2]) && $params[2]) ? $params[2] : $this->controller;

		if( !method_exists($this->controller . '_' . $this->calltype, $this->method) ){ // 'SiteWorks\\' . 
			$this->method     = $this->controller;
			array_unshift($params,$this->module);
			$params[2] = $this->controller;
		}
		$this->pass_var   = (isset($params[3]) && $params[3]) ? $params[3]      : '';
		$this->pass_vars  = (count($params) > 4)              ? array_slice($params, 4) : array();

		// If you have a model with the same name as your controller/ajax/iframe/script we'll autoload it for you here.
		$this->load_the_model = $this->load_path( $this->sw_module_path . '/models/' . $this->controller . '.model.php' );

		// Do not allow users to enter restricted areas
		$sw_err = '';
		$_SESSION['admin_level'] = (isset($_SESSION['admin_level']))? $_SESSION['admin_level']:0;
		$_SESSION['is_loggedin'] = (isset($_SESSION['is_loggedin']))? $_SESSION['is_loggedin']:false;
		foreach($_s->modualLocks as $v){
			if( ( $v[0] == $this->module ) && ( ( $_SESSION['admin_level'] < $_s->admin_level_options[$v[1]] ) || ( !$_SESSION['is_loggedin'] ) ) ){ $sw_err = 'error_permission'; }
		}
		foreach($_s->controllerLocks as $v){
			if( ( $v[0] == $this->module && $v[1] == $_s->uri->controller ) && ( ( $_SESSION['admin_level'] < $_s->admin_level_options[$v[2]] ) || ( !$_SESSION['is_loggedin'] ) ) ){ $sw_err = 'error_permission'; }
		}
		if($sw_err != ''){ header('Location: ' . $this->public_url . '/' . $sw_err); exit(0);}


	}
	public function load_path($p=false){ if($p && file_exists($p)){ require_once($p); return true;} return false;}

}

?>