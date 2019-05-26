<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class siteworks_session
{
    private $alive = true;
    private $_s = null;
    private $tmpid = null;

    function __construct($_s){
        $this->_s = $_s;
        session_set_save_handler(array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'gc'));
        if( @session_start()===false ){ $this->md5destroy($this->tmpid); session_start(); }
        $this->tmpid = null;
    }
    function __destruct(){ if($this->alive){ session_write_close(); $this->alive = false; } }
    // Open & Close are not being used right now, i'm only interested in database storage of sessions.
    private function open($savePath, $sessionName) : bool { return true; }
    private function close() : bool { return true; }
    private function read($sid){
        $sid = md5($sid);
        $this->tmpid = $sid;
        $r = new t_site_works_session($sid, $this->_s->odb);
        if($r->fget('sw_sess_data')===null){return '';}
        if($this->_s->sess_secure_password != ''){
          return $this->_s->tool->iDecrypt($r->fget('sw_sess_data'), $this->_s->sess_secure_password, $sid, 'AES-256-CBC');
        }
        return $r->fget('sw_sess_data'); 
    }
    private function write($sid, $data){
        $r = new t_site_works_session(null,$this->_s->odb);
        $sid = md5($sid);
        $r->fset('sw_sess_key',$sid);
        $data = ($this->_s->sess_secure_password != '') ? $this->_s->tool->iEncrypt($data, $this->_s->sess_secure_password, $sid, 'AES-256-CBC') : $data;
        $r->fset('sw_sess_data',$data);
        $r->fset('sw_sess_ts',time());
        $r->insertUpdateData();
        return true;
    }
    private function destroy($sid) : bool { $r = new t_site_works_session(md5($sid),$this->_s->odb); $r->deleteData(); return true; }
    private function md5destroy($sid) : bool { $r = new t_site_works_session($sid,$this->_s->odb); $r->deleteData(); return true; }
    private function gc($expire) : bool {
        if( $this->_s->gc_probability == 0 ){ return true; }
        $r = new t_site_works_session(null,$this->_s->odb);
        $r->deleteData('sw_sess_ts < ' . time() - (int) $expire);
        return true;
    }
    
}

?>