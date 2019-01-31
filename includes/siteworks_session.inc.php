<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class siteworks_session
{

    private $alive = true;
    private $sessTable;
    private $odb = null;
 
    function __construct($_s,$odb)
    {
      $this->odb = $odb;
      $this->sessTable = $_s->session_dbTable;
      session_set_save_handler(
      array(&$this, 'open'),
      array(&$this, 'close'),
      array(&$this, 'read'),
      array(&$this, 'write'),
      array(&$this, 'destroy'),
      array(&$this, 'clean'));

      ini_set('session.use_trans_sid', false);
      ini_set('session.save_path', $_s->server_session_path);
      ini_set('session.name', $_s->session_name);
      ini_set('session.gc_probability', 1); // prob / divisor to see chance to run
      ini_set('session.gc_divisor', 100);
      ini_set('session.gc_maxlifetime', 60 * $_s->sessionExpireMinutes); // Number of seconds until clean up
      session_start();
    }
 
      function __destruct()
      {
        if($this->alive)
        {
          session_write_close();
          $this->alive = false;
        }
        return true;
      }

      function delete()
      {
        if(ini_get('session.use_cookies'))
        {
          $params = session_get_cookie_params();
          setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
          );
        }

        session_destroy();

        $this->alive = false;
        return true;
      }

      private function open()
      {    
        // $this->odb = new MYSQLi(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME)
        // OR die('Could not connect to database.');
        // return true;
        return true;
      }

      private function close()
      {
        // return $this->odb->close();
        return true;
      }

      private function read($sid)
      {
        $q = 'SELECT `data` FROM `'.$this->sessTable.'` WHERE `id` = "'.md5($this->odb->real_escape_string($sid)).'" LIMIT 1';
        $r = $this->odb->query($q);

        if($r->num_rows == 1)
        {
          $fields = $r->fetch_assoc();

          return $fields['data'];
        }
        else
        {
          return '';
        }
      }

      private function write($sid, $data)
      {
        $q = 'REPLACE INTO `'.$this->sessTable.'` (`id`, `data`) VALUES ("'.md5($this->odb->real_escape_string($sid)).'", "'.$this->odb->real_escape_string($data).'")';
        $this->odb->query($q);

        return $this->odb->affected_rows;
      }

      private function destroy($sid)
      {
        $q = 'DELETE FROM `'.$this->sessTable.'` WHERE `id` = "'.md5($this->odb->real_escape_string($sid)).'"'; 
        $this->odb->query($q);

        $_SESSION = array();

        return $this->odb->affected_rows;
      }

      private function clean($expire)
      {

        $q = 'DELETE FROM `'.$this->sessTable.'` WHERE DATE_ADD(`last_accessed`, INTERVAL '.(int) $expire.' SECOND) < NOW()'; 
        $this->odb->query($q);

        return $this->odb->affected_rows;
      }    
    
}

?>