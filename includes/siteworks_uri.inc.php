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

	// Public URL mysite.com/public
	public $public_url;
	public $public_url_n;
	public $public_url_s;
	
	public $asset;
	public $asset_url;
	public $asset_url_n;
	public $asset_url_s;

	public $root_asset_url;
	public $root_asset_url_n;
	public $root_asset_url_s;

	public $base_asset_url;
	public $base_asset_url_n;
	public $base_asset_url_s;

    public $fixedapcu = '';
	public $fixeduri = '';

	public $sw_module_path;

	public $load_the_model = 0;

	public function __construct(&$_s)
	{
		// php APCu is open to other projects to read if they have the key name, so lets try to make our keys apply to this project only.
		// Note: No real secuirty here, you woulnd't put sensitive information in APCu anyway.
		$this->fixedapcu = 'siteworks_'.( ( $_s->cPaths['subdomain'] != '' ) ? $_s->cPaths['subdomain'] : '' ) . $_s->cPaths['domain'] . $_s->cPaths['tld'].$_s->cPaths['project_name'].'_';

		// Root is basically the domain http://www.example.com
		$root_url = ( ( $_s->cPaths['subdomain'] != '' ) ? $_s->cPaths['subdomain'] . '.' : '' ) . $_s->cPaths['domain'] . '.'. $_s->cPaths['tld'];
		$this->root_url_n   = 'http://'  . $root_url;
		$this->root_url_s   = 'https://' . $root_url;
		$this->root_url     = ( $_s->secure ) ? $this->root_url_s : $this->root_url_n ;

		// Base gets you to your site folder http://www.example.com/siteworks
		$base_url = ( $_s->cPaths['project_name'] != '' ) ? '/'.$_s->cPaths['project_name']:'';
		$this->base_url_n   = 'http://'  . $root_url . $base_url ;
		$this->base_url_s   = 'https://' . $root_url . $base_url ;
		$this->base_url     = ( $_s->secure ) ? $this->base_url_s : $this->base_url_n ;

		$this->public_url = $this->base_url . '/public';
		$this->public_url_n = $this->base_url_n . '/public';
		$this->public_url_s = $this->base_url_s . '/public';

		// So many ways to handle assets, you'll have to figure out the best way for your own project of course. This will work for most sites.
		$asset_url = ( ( $_s->cPaths['subdomain_a'] != '' ) ? $_s->cPaths['subdomain_a'] . '.' : '' ).$_s->cPaths['domain_a'].'.'.$_s->cPaths['tld_a']. ( ($_s->cPaths['project_name'] != '' ) ? '/'.$_s->cPaths['project_name'] : '' ).'/public/assets';
		$this->asset_url_n   = 'http://'  . $asset_url ;
		$this->asset_url_s   = 'https://' . $asset_url ;
		$this->asset_url     = ( $_s->secure ) ? $this->asset_url_s : $this->asset_url_n ;

		$root_asset_url = ( ( $_s->cPaths['subdomain_a'] != '' ) ? $_s->cPaths['subdomain_a'] . '.' : '' ).$_s->cPaths['domain_a'].'.'.$_s->cPaths['tld_a'];
		$this->root_asset_url_n   = 'http://'  . $root_asset_url ;
		$this->root_asset_url_s   = 'https://' . $root_asset_url ;
		$this->root_asset_url     = ( $_s->secure ) ? $this->root_asset_url_s : $this->root_asset_url_n ;

		$root_asset_url .= ( ($_s->cPaths['project_name'] != '' ) ? '/'.$_s->cPaths['project_name'] : '' );
		$this->base_asset_url_n   = 'http://'  . $root_asset_url ;
		$this->base_asset_url_s   = 'https://' . $root_asset_url ;
		$this->base_asset_url     = ( $_s->secure ) ? $this->base_asset_url_s : $this->base_asset_url_n ;



		$this->asset = new \stdClass;
		$this->asset->images      = $this->asset_url . '/images';
		$this->asset->documents   = $this->asset_url . '/documents';
		$this->asset->js          = $this->asset_url . '/js';
		$this->asset->css         = $this->asset_url . '/css';
		$this->asset->js_vendor   = $this->asset_url . '/js/vendor';
		$this->asset->css_vendor  = $this->asset_url . '/css/vendor';
	}

	public function uri_finish(&$_s){
		$uri = $_SERVER['DOCUMENT_URI'];

		if( $_s->cPaths['project_name'] == '' ){
			$this->fixeduri = strtolower(trim(str_replace('public','',trim($uri, '/')), '/'));
		}else{
			$this->fixeduri = strtolower(trim( preg_replace( '/'. preg_quote($_s->cPaths['project_name'].'/public','/') . '|' . preg_quote($_s->cPaths['project_name'],'/') .'/', '' ,trim($uri, '/'), 1 ), '/'));
		}

		// The Router will automatically swap the largest key match with the sent URI segments. 
		krsort($_s->routes);
		foreach( $_s->routes as $k => $v ){
			if( substr($this->fixeduri, 0, strlen($k)) == $k ){
				$this->fixeduri = preg_replace('/'.preg_quote($k,'/').'/', $v, $this->fixeduri, 1);
				break;
			}
		}

		$params = explode( '/', explode('?', $this->fixeduri)[0] );
		if(!isset($params[0])){$params[0]=$_s->_s->default_module;}

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
			if( ( $v[0] == $this->module ) && ( ( $_SESSION['admin_level'] < $_s->admin_level_options[$v[1]] ) || ( !$_SESSION['is_loggedin'] ) ) ){ $sw_err = 'sw_error_permission'; }
		}
		foreach($_s->controllerLocks as $v){
			if( ( $v[0] == $this->module && $v[1] == $_s->uri->controller ) && ( ( $_SESSION['admin_level'] < $_s->admin_level_options[$v[2]] ) || ( !$_SESSION['is_loggedin'] ) ) ){ $sw_err = 'sw_error_permission'; }
		}
		if($sw_err != ''){ header('Location: ' . $this->base_url . '/' . $sw_err); exit(0);}


	}
	public function load_path($p=false){ if($p && file_exists($p)){ require_once($p); return true;} return false;}

}

?>