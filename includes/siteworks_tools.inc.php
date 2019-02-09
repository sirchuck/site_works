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
        if (!$fp) { $this->_s->console[] = "You are calling dmsg() in debug mode, but your ./debug_server is not accessable. - $errstr ($errno)"; } else { fwrite($fp,(($showArray)?$btrace."-> ":'').print_r($s,true).(($showline)?"\n__________________________________________\n":'') . "\0"); fclose($fp); }
      } catch (Exception $e) {
         $this->_s->console[] = 'You are in debug mode, but your ./debug_server is not accessable. ' . $e->getMessage();
      }

		}
	}

  public function thread($path='',$seconds=0,$vars=''){
    if($vars != ''){$vars = ' -q=' . base64_encode(json_encode($vars));}
    exec('bash -c "'. SITEWORKS_DOCUMENT_ROOT.'/php_threader -x1='.$this->_s->thread_php_path.' -x2='.$this->_s->thread_php_version.' -s='.$seconds.' -p=' . SITEWORKS_DOCUMENT_ROOT . '/private/thread_scripts/'.$path.'.php'.$vars.'> /dev/null 2>&1 &"');
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
   $dir = addTrailSlash($dir);
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
    $r = new t_site_works_lang(0,$this->_s->odb);
    $r->query('SELECT  `' . $language . '` as l FROM `site_works_lang` WHERE sw_lang_key = ' . $index);
    $row = $r->getRows();
    $this->_s->printSQL = $holdSQL;
    return (isset($row->l))?$row->l:'';
  }

  public function cleanHTML(&$va){foreach($va as &$v){if(is_array($v)||is_object($v)){siteworks_htmlSpecialChars_recur($v);}else{$v=htmlspecialchars($v);}}}
  public function p_r($array = []){ echo '<pre>'; print_r ($array); echo '</pre>'; }

}
?>