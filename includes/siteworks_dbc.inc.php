<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class siteworks_dbc{
    public $dbt = '';       // Database type mysqli | postgres
    private $c = null;      // Database Connection
    private $last_result;   // Last Result run.
    protected $_s;

    // DB Connect
    public function __construct($dbc,$s) {
        $this->_s =& $s;
        $this->c = null;
        $this->dbt = $dbc->dbtype;
        switch ($this->dbt) {
            case "mysqli":
                if ((integer)$dbc->port !== 3306 && (integer)$dbc->port > 0) {
                    $this->c = new \mysqli($dbc->hostname, $dbc->username, $dbc->password, $dbc->database, $dbc->port);
                }
                else if ($dbc->port != '' && (string)$dbc->port != '/tmp/mysql.sock'&& file_exists($dbc->port)) {
                    $this->c = new \mysqli($dbc->hostname, $dbc->username, $dbc->password, $dbc->database, false, $dbc->port);
                }
                else {
                    $this->c = new \mysqli($dbc->hostname, $dbc->username, $dbc->password, $dbc->database);
                }

                if ($this->c->connect_errno) {
                    throw new \ErrorException('MySQLi Connection Failure: ['.$dbc->database.' '.(string)$c->connect_errno.'] '.$c->connect_error);
                    return false;
                }
                return $this->c;
            break;
            case "postgres":
                $conn = implode(' ', array(
                     'host='.    $dbc->hostname
                    ,'dbname='.  $dbc->database
                    ,'user='.    $dbc->username
                    ,'password='.$dbc->password
                    ,'port='.    $dbc->port
                ));
                // pg_close($this->C);
                try {
                    $this->c = pg_connect($conn);
                    if(pg_connection_status($this->c) !== PGSQL_CONNECTION_OK){
                        throw new \Exception( __CLASS__.'::'.__FUNCTION__.' Postgres Connection Failure [' . $dbc->database . ']');
                        return false;
                    }
                }
                catch(\Exception $e) {
                    throw new \ErrorException('postgres failure [' . $dbc->database . ']:  '.$e->getMessage() );
                    return false;
                }
                return $this->c;
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
        }
        throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
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
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
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
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
        }
    }


    // Clean String for db
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
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
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
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
        }
    }

    
    // DB Query
    public function q($sql=false){
        if($this->_s->debugMode && $this->_s->printSQL){
            $this->_s->tool->dmsg("[c_yellow]" . $this->_s->tool->trace(2).'-> '.$sql,false);
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
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
        }
    }

    public function getInsertID($_result=false)
    {
        if(!$_result){$_result=$this->last_result;}
        if(is_bool($_result)){return 0;}
        switch ($this->dbt) {
            case "mysqli":
                if (isset($this->c->insert_id)) {
                    return $this->c->insert_id;
                }
                return 0;
            break;
            case "postgres":
                return pg_last_oid($_result);
            break;
            default:
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
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
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
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
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
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
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
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
                throw new \ErrorException('Unknown Database Type: ['.$dbc->database.']');
        }
    }

}
?>