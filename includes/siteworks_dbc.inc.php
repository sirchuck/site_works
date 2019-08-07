<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class siteworks_dbc{
    public $dbt = '';       // Database type mysqli | postgres
    private $c = null;      // Database Connection
    private $last_result;   // Last Result run.
    protected $_s;

    private $connected = false;
    private $hostname  = null;
    private $username  = null;
    private $password  = null;
    private $database  = null;
    private $port      = null;

    public function __construct($dbc,$s) {
        $this->_s =& $s;
        $this->c = null;
        $this->dbt = $dbc->dbtype;

        $this->hostname = $dbc->hostname;
        $this->username = $dbc->username;
        $this->password = $dbc->password;
        $this->database = $dbc->database;
        $this->port     = $dbc->port;
    }

    private function cleanConnectionVars($connected=false){
        $this->connected = $connected;
        if($connected===true){
            $this->hostname  = null;
            $this->username  = null;
            $this->password  = null;
            $this->database  = null;
            $this->port      = null;
        }
    }

    // Connect to the database
    public function connect(){
        if($this->connected !== false){return $this->connected;}

        switch ($this->dbt) {
            case "mysqli":
                $dbCreated = 0;
                while($dbCreated < 2){
                    if ((integer)$this->port !== 3306 && (integer)$this->port > 0) {
                        $this->c = @new \mysqli($this->hostname, $this->username, $this->password, $this->database, $this->port);
                    }
                    else if ($this->port != '' && (string)$this->port != '/tmp/mysql.sock'&& file_exists($this->port)) {
                        $this->c = @new \mysqli($this->hostname, $this->username, $this->password, $this->database, false, $this->port);
                    }
                    else {
                        $this->c = @new \mysqli($this->hostname, $this->username, $this->password, $this->database);
                    }
                    if ($this->c->connect_errno) {
                        if($dbCreated == 0){
                            $dbCreated = 1;
                            $tc = null;
                            if ((integer)$this->port !== 3306 && (integer)$this->port > 0) {
                                $tc = new \mysqli($this->hostname, $this->username, $this->password, false, $this->port);
                            }
                            else if ($this->port != '' && (string)$this->port != '/tmp/mysql.sock'&& file_exists($this->port)) {
                                $tc = new \mysqli($this->hostname, $this->username, $this->password, false, false, $this->port);
                            }
                            else {
                                $tc = new \mysqli($this->hostname, $this->username, $this->password);
                            }
                            if ($tc->connect_error) {
                                $dbCreated = 10;
                                throw new \ErrorException('Verify Database Exists: ( CREATE DATABASE IF NOT EXISTS `' . $this->database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; ) MySQLi Connection Failure: ['.$this->database.' '.(string)$tc->connect_errno.'] '.$tc->connect_error);
                                $this->cleanConnectionVars(false);
                                return false;
                            } else {
                                $tc->query("CREATE DATABASE IF NOT EXISTS `" . $this->database . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                            }
                            $tc->close();
                        } else {
                            $dbCreated = 10;
                            throw new \ErrorException('Verify Database Exists: -( CREATE DATABASE IF NOT EXISTS `' . $this->database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; ) MySQLi Connection Failure: ['.$this->database.' '.(string)$tc->connect_errno.'] '.$tc->connect_error);
                            $this->cleanConnectionVars(false);
                            return false;
                        }
                    } else {
                        $dbCreated = 10;
                    }
                }
                $this->cleanConnectionVars(true);
                return true;
            break;
            case "postgres":
                $conn = implode(' ', array(
                     'host='.    $this->hostname
                    ,'dbname='.  $this->database
                    ,'user='.    $this->username
                    ,'password='.$this->password
                    ,'port='.    $this->port
                ));
                // pg_close($this->C);
                try {
                    $this->c = pg_connect($conn);
                    if(pg_connection_status($this->c) !== PGSQL_CONNECTION_OK){
                        throw new \Exception( __CLASS__.'::'.__FUNCTION__.' Postgres Connection Failure [' . $this->database . ']');
                        $this->cleanConnectionVars(false);
                        return false;
                    }
                }
                catch(\Exception $e) {
                    throw new \ErrorException('postgres failure SQL: ( CREATE DATABASE IF NOT EXISTS `' . $this->database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; )  [' . $this->database . ']:  '.$e->getMessage() );
                    $this->cleanConnectionVars(false);
                    return false;
                }
                $this->cleanConnectionVars(true);
                return true;
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
                $this->cleanConnectionVars(false);
                return false;
        }
        throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        $this->cleanConnectionVars(false);
        return false;
    }

    // Close db
    public function freeResult($r)
    {
        switch ($this->dbt) {
            case "mysqli":
                mysqli_free_result($r);
            break;
            case "postgres":
                pg_free_result($r);
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }
    }

    // Close db
    public function close()
    {
        switch ($this->dbt) {
            case "mysqli":
                $this->c->close();
            break;
            case "postgres":
                $this->c->close();
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }
    }


    // Clean String for db
    public function clean($s){ return $this->c($s); } // Alias of c($s)
    public function c($s)
    {
        switch ($this->dbt) {
            case "mysqli":
                return $this->c->real_escape_string($s);
            break;
            case "postgres":
                return pg_escape_string($s);
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }
    }

    // Prepaired Statment Query, needs work.
    public function p($sql,$params)
    {
        switch ($this->dbt) {
            case "mysqli":
                $sq = $this->c->prepare($sql);
                $sq->bind_pram(implode(',',$params));
                $sq->execute();
                $sq->close();
            break;
            case "postgres":
                throw new \ErrorException('Function not supported');
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }
    }

    
    // DB Query
    public function q($sql=false){
        if($this->_s->debugMode && $this->_s->printSQL){
            $this->_s->tool->dmsg("[c_yellow]" . $this->_s->tool->trace(3).'-> '.$sql,false);
        }
        switch ($this->dbt) {
            case "mysqli":
                $this->last_result = $this->c->query($sql);
                if ($this->_s->debugMode && $this->c->error) {
                    $this->_s->tool->dmsg('MySQL Error: ['.$this->c->errno.'] '.$this->c->error."\n".'SQL: '.$sql);
                }
                return $this->last_result;
            break;
            case "postgres":
                if(false === pg_send_query($this->c, $sql)) {
                    if ($this->debugMode && $this->c->error) {
                        $this->_s->tool->dmsg(__CLASS__.'::'.__FUNCTION__ . ' MySQL Error: ['.$this->c->errno.'] '.$this->c->error."\n".'SQL: '.$sql);
                    }
                }
                $this->last_result = pg_get_result($this->c);
                $state = pg_result_error_field($this->last_result, PGSQL_DIAG_SQLSTATE);
                if ($state != 0) {
                    if ($debugMode && $this->c->error) {
                        $errorDetails = array(
                             'SEVERITY'           => pg_result_error_field($this->result, PGSQL_DIAG_SEVERITY)
                            ,'SQLSTATE'           => $state
                            ,'MESSAGE_PRIMARY'    => pg_result_error_field($this->result, PGSQL_DIAG_MESSAGE_PRIMARY)
                            ,'MESSAGE_DETAIL'     => pg_result_error_field($this->result, PGSQL_DIAG_MESSAGE_DETAIL)
                            // ,'MESSAGE_HINT'       => pg_result_error_field($this->result, PGSQL_DIAG_MESSAGE_HINT)
                            // ,'STATEMENT_POSITION' => pg_result_error_field($this->result, PGSQL_DIAG_STATEMENT_POSITION)
                            // ,'INTERNAL_POSITION'  => pg_result_error_field($this->result, PGSQL_DIAG_INTERNAL_POSITION)
                            // ,'INTERNAL_QUERY'     => pg_result_error_field($this->result, PGSQL_DIAG_INTERNAL_QUERY)
                            // ,'CONTEXT'            => pg_result_error_field($this->result, PGSQL_DIAG_CONTEXT)
                            // ,'SOURCE_FILE'        => pg_result_error_field($this->result, PGSQL_DIAG_SOURCE_FILE)
                            // ,'SOURCE_LINE'        => pg_result_error_field($this->result, PGSQL_DIAG_SOURCE_LINE)
                            // ,'SOURCE_FUNCTION'    => pg_result_error_field($this->result, PGSQL_DIAG_SOURCE_FUNCTION)
                            ,'SQL'                => $sql
                        );
                        $this->_s->tool->dmsg($errorDetails);
                    }
                }
                return $this->last_result;
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }
    }

    public function getInsertID($_result=false)
    {
        switch ($this->dbt) {
            case "mysqli":
                if (isset($this->c->insert_id)) {
                    return $this->c->insert_id;
                }
                return 0;
            break;
            case "postgres":
                if(!$_result){$_result=$this->last_result;}
                if(is_bool($_result)){return 0;}
                return pg_last_oid($_result);
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }

    }

    public function getAffectedRows($_result=false)
    {
        if(!$_result){$_result=$this->last_result;}
        if(is_bool($_result)){return 0;}
        switch ($this->dbt) {
            case "mysqli":
                return ( isset($this->c->affected_rows) )?$this->c->affected_rows:0;
            break;
            case "postgres":
                return ( null !== pg_affected_rows($_result ) )?pg_affected_rows($_result):0;
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }
    }

    public function numRows($_result=false)
    {
        if(!$_result){$_result=$this->last_result;}
        if(is_bool($_result)){return 0;}
        switch ($this->dbt) {
            case "mysqli":
                return ( null !== mysqli_num_rows($_result) )?mysqli_num_rows($_result):0;
            break;
            case "postgres":
                return ( null !== pg_num_rows($_result) )?pg_num_rows($_result):0;
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }
    }


    public function fetch_assoc($_result=false)
    {
        if(!$_result){$_result=$this->last_result;}
        if(is_bool($_result)){return null;}
        switch ($this->dbt) {
            case "mysqli":
                return $_result->fetch_assoc();
            break;
            case "postgres":
                return pg_fetch_assoc($_result);
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }
    }

    public function fetch_object($_result=false)
    {
        if(!$_result){$_result=$this->last_result;}
        if(is_bool($_result)){return null;}
        switch ($this->dbt) {
            case "mysqli":
                return $_result->fetch_object();
            break;
            case "postgres":
                return pg_fetch_object($_result);
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$this->database.']');
        }
    }

}
?>