<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

/*
    query($sqlFn=NULL) -> $result | False
    getFieldNames($doInsert=0) -> No return, sets values, unused by end users
    getRow|getRows($_result=false,$returnArray=false) -> $result
    clean($s) -> string
    cleanAll() -> No return, cleans current $this->f values
    clearFields() -> Sets $this->f values to 0 or NULL
    fillData($id) -> true/false
    selectAll($where = NULL, $what = '*') -> $result
    insertData() -> $this->c->getInsertID();
    insertUpdateData() - $this->c->getInsertID();
    updateData($where=NULL,$values=NULL) -> true/false
    deleteData($where=NULL) -> true/false
*/

abstract class siteworks_db_tools
{
    public $c;

    abstract protected function buildQueryArray(); // Command Name => SQL (Use: $this->site->d['user']->query('CheckEmail') )
    
    public     $f = array();                       // Database field names
    public     $p = array();                       // Pass values here to run conditional buildQueryArrays

    protected   $autoInc = false;                  // If true it will not use first field in insert or update commands
    protected   $tableName = '';                   // Name of your database table

    private $insertFieldNames = '';
    private $insertValueList  = '';
    private $updateFieldValue = '';
   
    public function __construct()
    {
    }

    public function fset($f=null,$v=null){ if($f==null){return;} $this->f[$f]['value'] = $v; }
    public function fget($f=null){ if($f==null){return;} return $this->f[$f]['value']; }

    public function query($sqlFn=NULL){
        if( substr_count(trim($sqlFn), ' ') < 1){
            $sqlFn = $this->buildQueryArray( $sqlFn );
        }
        
        if( is_array( $sqlFn ) ){
            $resultArray = array();
            foreach( $sqlFn as $sKey => $sVal ){
                if (($result = $this->c->q( $sVal ) )  && $this->c->numRows() > 0 ) {
                    $resultArray[$sKey] = $result;
                }
                else{
                    $resultArray[$sKey] = false;
                }
            }
            return $resultArray;
        }
        else{
            if( is_null($sqlFn) ){
                return false;
            }else{
                if (($result = $this->c->q( $sqlFn ) )  && $this->c->numRows() > 0 ) {
                    return $result;
                }
            }
        }

       return false;
    }
    
    protected function getFieldNames($doInsert=0){
        $iCount = 1;
        $this->insertFieldNames = '';
        $this->insertValueList  = '';
        $this->updateFieldValue = '';
        $fCount = count($this->f);
        foreach($this->f as $fKey => $fVal){
            if($iCount != 1 || $this->autoInc === false){
                if($doInsert==1 || $doInsert==3){
                    $this->insertFieldNames .= '`'.$fKey.'`';
                    if(is_null($fVal['value'])){
                        $this->insertValueList  .= ' NULL ';
                    }
                    else{
                        $this->insertValueList  .= '"'.$this->c->c($fVal['value']).'"';
                    }

                    if($fCount > $iCount ){
                        $this->insertFieldNames .= ',';
                        $this->insertValueList  .= ',';
                    }
                }
                if($doInsert==2 || $doInsert==3){
                    if(is_null($fVal['value'])){
                        $this->updateFieldValue .= '`'.$fKey.'` = NULL ';
                    }
                    else{
                        $this->updateFieldValue .= '`'.$fKey.'` = "'.$this->c->c($fVal['value']).'"';
                    }
                    if($fCount > $iCount ){ $this->updateFieldValue .= ','; }
                }
            }
            $iCount++;
        }
    }

    public function getRow($_result=false,$returnArray=false){return $this->getRows($_result,$returnArray);}
    public function getRows($_result=false,$returnArray=false){
        if(!$returnArray){return $this->c->fetch_object($_result);}else{return $this->c->fetch_assoc($_result);}
    }

    public function c($s){ return $this->c->c($s); }
    public function clean($s){ return $this->c->c($s); }
    public function cleanAll(){ foreach($this->f as $k => $v){ $this->f[$k]['value'] = $this->clean($v['value']);} foreach($this->p as $k => $v){ $this->p[$k] = $this->clean($v);} }
    public function clearFields(){ foreach($this->f as $k => $v){ $this->f[$k]['value'] = ( gettype($this->f[$k]['value']) == 'integer' || gettype($this->f[$k]['value']) == 'double' ) ? 0 : null;} unset($this->p); }
    
    public function fillData($id=NULL){

        if($id === true ){
            // Typically a database with one table and no key
            $sql = 'SELECT * FROM `'.$this->tableName.'`';
        }
        elseif( gettype($id) == 'integer' ){
            $sql = 'SELECT * FROM `'.$this->tableName.'` WHERE `'.$this->keyField.'` = '.$id;
        }
        elseif( gettype($id) == 'string' ){
            if(strrpos($id,'=')===false && strrpos($id,'>')===false && strrpos($id,'<')===false){
                $sql = 'SELECT * FROM `'.$this->tableName.'` WHERE `'.$this->keyField.'` = \''.$id.'\'';
            } else {
                if( substr( $id, 0, 1 ) == '<' ){
                    $sql = 'SELECT * FROM `'.$this->tableName.'` '.ltrim($id,'<');
                }else{
                    $sql = 'SELECT * FROM `'.$this->tableName.'` WHERE '.ltrim($id,'=');
                }
            }
        }
        else{
            return false;
        }
    
        if (($result = $this->c->q($sql))  && $this->c->numRows() > 0) {
            $row = $this->c->fetch_assoc();
            foreach($this->f as $fKey => $fVal){
                if($fKey != 'iTemp'){ // Ignore iTemp to pass values to table
                    $this->f[$fKey]['value'] = $row[$fKey];
                }
            }
            return true;
        }
        else{
            return false;
        }
    
    }

    public function selectOne($where = NULL, $what = '*',$useArray=false){
        if( is_null($where) ){
            $where = '';
        }else{
            if( substr( $where, 0, 1 ) == '<' ){
                $where = ' '.ltrim($where,'<');
            }else{
                $where = ' WHERE '.ltrim($where,'=');
            }
        }

        $sql = 'SELECT ' . $what . ' FROM `'.$this->tableName.'` '.$where . ' LIMIT 1';
        if (($result = $this->c->q($sql))  && $this->c->numRows() > 0) {
            return $this->getRow($result,$useArray);
        }
        else{
            return false;
        }
    }
    
    public function selectAll($where = NULL, $what = '*'){
        if( is_null($where) ){
            $where = ''; 
        }else{
            if( substr( $where, 0, 1 ) == '<' ){
                $where = ' '.ltrim($where,'<');
            }else{
                $where = ' WHERE '.ltrim($where,'=');
            }
        }

        $sql = 'SELECT ' . $what . ' FROM `'.$this->tableName.'` '.$where;
        if (($result = $this->c->q($sql))  && $this->c->numRows() > 0) {
            return $result;
        }
        else{
            return false;
        }
    }
    
    public function insertData($insertFieldNames=null, $insertValueList=null){
        $this->getFieldNames(1);
        $insertFieldNames=($insertFieldNames==null)? $this->insertFieldNames : $insertFieldNames;
        $insertValueList=($insertValueList==null)? $this->insertValueList : $insertValueList;
        $sql = 'INSERT INTO `'.$this->tableName.'` ('.$insertFieldNames.') VALUES ('.$insertValueList.')';
        $result = $this->c->q($sql);
        if($result === false)
            return false;
        return $this->c->getInsertID();
    }

    public function insertUpdateData($insertFieldNames=null, $insertValueList=null, $updateFieldValues=null){
        $this->getFieldNames(3);
        $insertFieldNames=($insertFieldNames==null)? $this->insertFieldNames : $insertFieldNames;
        $insertValueList=($insertValueList==null)? $this->insertValueList : $insertValueList;
        $updateFieldValues=($updateFieldValues==null)? $this->updateFieldValue : $updateFieldValues;
        $sql = 'INSERT INTO `'.$this->tableName.'` ('.$insertFieldNames.') VALUES ('.$insertValueList.') ON DUPLICATE KEY UPDATE '.$updateFieldValues;
        $result = $this->c->q($sql);
        if($result === false)
            return false;
        return $this->c->getInsertID(); // Not sure this will always return a good id.
    }
    
    public function updateData($where=NULL,$values=NULL){ // $where can be id or where clause, $values 'x=y AND z=r' OR no where to use current t_class id and empty values to update all fields
        if( is_null($where) ){$where = $this->f[$this->keyField]['value'];}
        if( gettype($where) == 'integer' ){
            $where = ' WHERE `'.$this->keyField.'` = '.$where; 
        } elseif( is_null($where) || $where===false ) {
            $where = '';
        } else {
            if(strrpos($where,'=')===false && strrpos($where,'>')===false && strrpos($where,'<')===false){
                $where = ' WHERE `' . $this->keyField .'` = \'' . $where . '\'';
            } else {
                if( substr( $where, 0, 1 ) == '<' ){
                    $where = ' '.ltrim($where,'<');
                }else{
                    $where = ' WHERE '.ltrim($where,'=');
                }
            }
        }
        if( is_null($values) ){$this->getFieldNames(2); $values = $this->updateFieldValue;}
        $sql = 'UPDATE `'.$this->tableName.'` SET '.$values.' '.$where;
        $result = $this->c->q($sql);
        if($result === false)
            return false;
        return true;
    }
    
    public function deleteData($where=NULL){ // Where could be an id, if you dont use a comparitor we assume id.
        if( is_null($where) ){
            if( isset($this->f[$this->keyField]['value']) && $this->f[$this->keyField]['value'] != null ){
                $where = $this->f[$this->keyField]['value'];
            } else { return false; }
        }
        if( gettype($where) == 'integer' ){
            $where = 'WHERE `'.$this->keyField.'` = '.$where;
        } else if($where === false){
            $where = '';
        } else if(strrpos($where,'=')===false && strrpos($where,'>')===false && strrpos($where,'<')===false){
            $where = 'WHERE `'.$this->keyField.'` = \''.$where.'\'';
        } else {
            if( substr( $where, 0, 1 ) == '<' ){
                $where = ' '.ltrim($where,'<');
            }else{
                $where = ' WHERE '.ltrim($where,'=');
            }
        }
        $sql = 'DELETE FROM `'.$this->tableName.'` '.$where.';';
        $result = $this->c->q($sql);
        return true;
    }
   
}
?>