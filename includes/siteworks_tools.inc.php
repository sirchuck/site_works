<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

// Tools are loaded for each page on your site, they should be things that are used a lot.
// Examples might be login or authentication tools, time tools, and simple calculations

class siteworks_tools
{
	protected $_s;

	function __construct($_s)
	{
		$this->_s =& $_s;
	}

  public function trace($af=0,$fullreport=false){
    if($fullreport){
        $backtraceList = debug_backtrace();
        $returnList = Array();
        for ($i=0; $i<count($backtraceList); $i++) {
          $step = $backtraceList[$i];
          $stepFormatted = '';
          $omit = false;
          foreach ($step as $type => $value) {
            if ($type === 'file' && __FILE__ == $value) {
              $omit = true;
              break;
            }
            if (in_array($type, Array('file', 'line'))) {
              $stepFormatted .= '['.$value.']';
            }
          }
          if ($omit === false) {
            $returnList[] = $stepFormatted;
          }
        }
      return implode("\n", str_replace(SITEWORKS_DOCUMENT_ROOT.'/','',$returnList));
    }else{
      $dout = '';
      $data = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,5);
      foreach($data as $k => $v){ if( $k == $af || $af == 0 ){foreach($v as $k2 => $v2){ if($k2=="file"||$k2=="line"){$dout .= "\n" . '['.$k2.']'.' ' . str_replace(SITEWORKS_DOCUMENT_ROOT.'/','',$v2) . ' ';} } } }
      return $dout;
    }
  }
  public function sw_tracesql(){
      $dout = '';
      $data = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,5);
      foreach($data as $k => $v){
        $d_file = '';
        $d_line = '';
        foreach($v as $k2 => $v2){
          if($k2=="file"){
            $d_file = str_replace(SITEWORKS_DOCUMENT_ROOT.'/','',$v2);
          }
          if($k2=="line"){
            $d_line = $v2;
          }
        }
        if( strpos($d_file, 'private/')!==FALSE ){
          $dout .= "\n[ ". str_pad($d_line,5,' ',STR_PAD_LEFT) . ' ] '. str_replace(['private/','modules/'],'',$d_file);
        }
      }
      return $dout;
  }


	public function dmsg($s,$showArray=true,$showline=true){
		if($this->_s->debugMode){
      try{
        $fp=@fsockopen($this->_s->debug_server, $this->_s->debug_server_port, $errno, $errstr, 30);
        if(!$this->_s->allowDebugColors){
          $s_pattern = ['[c_clear]','[c_black]','[c_red]','[c_green]','[c_orange]','[c_blue]','[c_purple]','[c_cyan]','[c_light_gray]','[c_gray]','[c_light_red]','[c_light_green]','[c_yellow]','[c_light_blue]','[c_light_purple]','[c_light_cyan]','[c_white]'];
          $s = str_replace($s_pattern, '', $s);
        }
        $btrace = '';
        if($showArray===1){ $btrace = $this->trace(0,true); }else{ $btrace = ltrim($this->trace(1,false),"\n"); }
        if (!$fp) { $this->_s->console['_sw_dmsg'] = "You are calling dmsg() in debug mode, but your ./debug_server is not accessable. - $errstr ($errno)"; } else { fwrite($fp,(($showArray)?$btrace."-> ":'').print_r($s,true).(($showline)?"\n__________________________________________\n":'') . "\0"); fclose($fp); }
      } catch (Exception $e) {
         $this->_s->console[] = 'You are in debug mode, but your ./debug_server is not accessable. ' . $e->getMessage();
      }

		}
	}

  public function thread($path='',$Milliseconds=0,$vars=''){
    // This function works with php_threader
    if($vars != ''){$vars = ' -q=' . base64_encode(json_encode($vars));}
    exec('bash -c "'. SITEWORKS_DOCUMENT_ROOT.'/php_threader -x1='.$this->_s->thread_php_path.' -x2='.$this->_s->thread_php_version.' -s='.$Milliseconds.' -p=' . SITEWORKS_DOCUMENT_ROOT . '/private/thread_scripts/'.$path.'.php'.$vars.'> /dev/null 2>&1 &"');
  }

  public function queue($path='',$vars='',$tag='',$waitstart=0,$timeout=0){
    // This function works with php_q_it server
    if($vars != ''){$vars = base64_encode(json_encode($vars));}
    $r = new t_site_works_queue(null,$this->_s->odb);
    $r->f['sw_tag']['value'] = $tag;
    $r->f['sw_script']['value'] = SITEWORKS_DOCUMENT_ROOT . '/private/queue_scripts/'.$path.'.php';
    $r->f['sw_vars']['value'] = $vars;
    $r->f['sw_waitstart']['value'] = $waitstart;
    $r->f['sw_timeout']['value'] = $timeout;
    $r->insertData();
  }

  public function broadcast($sw_var='',$sw_action='',$uid='',$tag='',$uniqueid='',$host='',$port='',$sendhost='',$sendport=''){
    // php_websockets must be running and php_websockets_client must be in your project root folder.
    $host = ($host == '') ? $this->_s->websocket_server : $host;
    $port = ($port == '') ? $this->_s->websocket_port : $port;
    $shost = ($sendhost == '') ? '' : ' -sh="' . $sendhost . '"';
    $sport = ($sendport == '') ? '' : ' -sp="' . $sendport . '"';
    return exec('bash -c \''. SITEWORKS_DOCUMENT_ROOT.'/php_websockets_client -m="'.$sw_var.'" -a="'.$sw_action.'" -h="'.$host.'" -p="'.$port.'" -u="'.$uid.'" -t="'.$tag.'" -uq="'.$uniqueid.'"'.$shost.$sport.'\'');
  }


  public function exitRamvar(){ return $this->ramvar('','','','','sw_exit'); }
  public function clearRamvar(){ return $this->ramvar('','','','','sw_clearData'); }
  public function clearAllRamvar(){ return $this->ramvar('','','','','sw_clearAllData'); }
  public function syncRamvar(){ return $this->ramvar('','','','','sw_sync'); }
  public function fullsyncRamvar(){ return $this->ramvar('','','','','sw_fullsync'); }
  public function setRamvar($k='', $v='', $t=''){ return $this->ramvar('1',$k,$v,$t); }
  public function getRamvar($k='', $v='', $t=''){ return $this->ramvar('2',$k,$v,$t); }
  public function getOrRamvar($k='', $v='', $t=''){ return $this->ramvar('2.1',$k,$v,$t); }
  public function deleteRamvar($k='', $v='', $t=''){ return $this->ramvar('3',$k,$v,$t); }
  public function deleteOrRamvar($k='', $v='', $t=''){ return $this->ramvar('3.1',$k,$v,$t); }
  public function ramvar($a='', $k='', $v='', $t='', $m=false){
      $ret = '';
      try{
        if($this->_s->ramvar_cert_crt != '' && $this->_s->ramvar_cert_key != ''){
          $fp=@fsockopen("tls://".$this->_s->ramvar_local_server, $this->_s->ramvar_local_port, $errno, $errstr, 30);
        }else{
          $fp=@fsockopen($this->_s->ramvar_local_server, $this->_s->ramvar_local_port, $errno, $errstr, 30);
        }
        if (!$fp) {
          if($this->_s->debugMode){ $this->dmsg( 'Failed to connect to the local ramvar server.' ); }
        } else {
          $sc = new \stdClass;
          $sc->a = $a;
          $sc->k = $k;
          $sc->v = $v;
          $sc->t = $t;
          $tmp = fgets($fp);
          fwrite($fp, $this->_s->ramvar_app_key . "\n");
          if($m===false){
            fwrite($fp, json_encode($sc)."\n");
            $ret = fgets($fp);
            fclose($fp);
          }else{
            fwrite($fp, $m."\n");
            fclose($fp);
            $ret = '1';
          }
       }
      } catch (Exception $e) {
          if($this->_s->debugMode){ $this->dmsg( 'Failed to connect to the local ramvar server.' ); }
      }
      return $ret;
  }


  public function listFiles($dir,$ftype=0,$recursive=true,&$results=array()){
    // ftype( 0 all, 1 files only, 2 folders only )
    if(!is_dir($dir)){return false;}
    $files = scandir($dir);
    foreach($files as $key => $value){
        $path = realpath($dir);
        if(!is_dir($path.DIRECTORY_SEPARATOR.$value)) {
            if($ftype==0 || $ftype == 1){ $results[] = array('path'=>$path.DIRECTORY_SEPARATOR,'name'=>$value); }
        } else if( substr($value, 0, 1) != '.' ) {
          if($recursive){ $this->listFiles($path.DIRECTORY_SEPARATOR.$value,$ftype,$recursive,$results); }
          if($ftype==0 || $ftype == 2){$results[] = array('path'=>$path.DIRECTORY_SEPARATOR,'name'=>$value); }
        }
    }
    return $results;
  }

  public function addTrailSlash($s){ return rtrim($s,'/').'/';}
  public function delTree($dir,$include_dir_folder=true) {
   if(!is_dir($dir)){return false;}
   $dir = $this->addTrailSlash($dir);
   $files = array_diff(scandir($dir), array('.','..','.githoldfolderprivate'));
    foreach ($files as $file) {
      (is_dir($dir.$file)) ? $this->delTree($dir.$file,true) : unlink($dir.$file);
    }
    if($include_dir_folder){ return rmdir($dir); }
    return true;
  }

  public function removeFile($s){if(file_exists($s)){unlink($s);}}

  public function buildText($m,$r,$is_js=false){
    $pid = -1;
    foreach($r->p['list'] as $v){
      if($v->sw_origional == $m[2]){
        if($v->sw_lang_keep == 3){
          $pid = $r->updateData($v->sw_lang_key,'`sw_lang_keep`=0');
        }
        $v->sw_lang_keep = '2';
        $pid = $v->sw_lang_key;
      }
    }
    if($pid==-1){
      $r->f['sw_lang_key']['value'] = 0;
      $r->f['sw_lang_keep']['value'] = 0;
      $r->f['sw_lang_category']['value'] = '';
      $r->f['sw_origional']['value'] = $r->clean($m[2]);
      $r->f['english']['value'] = $r->f['sw_origional']['value'];
      $pid = $r->insertData();
      $r->p['list'][] = (object) array('sw_lang_key' => $pid, 'sw_lang_keep' => 2, 'sw_origional' => $m[2]);
    }
    switch($m[1]){
      case '.':
        return $m[1] . ' $this->_s->tool->getText('.$pid.') ';
      break;
      case '+':
        return $m[1] . ' getText('.$pid.') ';
      break;
      case '=':
      case '(':
        if($is_js){ return $m[1] . ' getText('.$pid.') '; } else { return $m[1] . ' $this->_tool->getText('.$pid.') '; }
      break;
      case '"':
      case "'":
        return $m[1] . '{__' . $pid . '__}';
      break;
      default:
        return $m[1] . ' {__' . $pid . '__}';
    }
  }

  public function getText($index, $language=false){
    $_SESSION['language'] = (isset($_SESSION['language']) && $_SESSION['language'] != '') ? $_SESSION['language'] :$this->_s->language;
    $language = ($language==false)? $_SESSION['language'] : $language;
    $holdSQL = $this->_s->printSQL;
    $this->_s->printSQL = false;
    $r = new t_site_works_lang(null,$this->_s->odb);
    $r->query('SELECT  `' . $language . '` as l FROM `site_works_lang` WHERE sw_lang_key = ' . $index);
    $row = $r->getRows();
    $this->_s->printSQL = $holdSQL;
    return (isset($row->l))?$row->l:'';
  }

  public function cleanHTML(&$va){ if(is_array($va)||is_object($va)){ foreach($va as &$v){ if(is_array($v)||is_object($v)){ $this->cleanHTML($v); }else{ $v=htmlspecialchars($v); } } }else{$va=htmlspecialchars($va);} }
  public function noHTML(&$va, $o = ENT_QUOTES | ENT_HTML5, $e = 'UTF-8'){ if(is_array($va)||is_object($va)){ foreach($va as &$v){ if(is_array($v)||is_object($v)){ $this->noHTML($v,$o,$e); }else{ $v=htmlentities($v,$o,$e); } } }else{$va=htmlentities($va,$o,$e);} }
  public function cleanH($va){ $this->cleanHTML($va); return $va; }
  public function noH($va, $o = ENT_QUOTES | ENT_HTML5, $e = 'UTF-8'){ $this->noHTML($va,$o,$e); return $va; }
  public function p_r($array = []){ echo '<pre>'; print_r ($array); echo '</pre>'; }

  public function iRnd($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
      $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
  }

  // Always changing security
  public function iEncrypt($s, $secret_key = 'EuhriejirjijLL', $secret_iv = 'djkRKoejORjohgh', $encrypt_method = 'AES-256-CBC'){ return $this->encrypt_decrypt($s, 'encrypt', $secret_key, $secret_iv, $encrypt_method); }
  public function iDecrypt($s, $secret_key = 'EuhriejirjijLL', $secret_iv = 'djkRKoejORjohgh', $encrypt_method = 'AES-256-CBC'){ return $this->encrypt_decrypt($s, 'decrypt', $secret_key, $secret_iv, $encrypt_method); }
  public function encrypt_decrypt($s,$a, $secret_key = 'EuhriejirjijLL', $secret_iv = 'djkRKoejORjohgh', $encrypt_method = 'AES-256-CBC'){
    $output = false;
    $key = hash('sha256', $secret_key);
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $a == 'encrypt' ) {
        $output = openssl_encrypt($s, $encrypt_method, $key, 0, $iv);
    } else if( $a == 'decrypt' ) {
        $output = openssl_decrypt($s, $encrypt_method, $key, 0, $iv);
    }
    return $output;
  }


}
?>