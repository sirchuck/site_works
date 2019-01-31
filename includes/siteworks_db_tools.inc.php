<?php
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

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

    public function query($sqlFn=false){
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
            if($sqlFn !== false){
                if (($result = $this->c->q( $sqlFn ) )  && $this->c->numRows() > 0 ) {
                    return $result;
                }
                else{
                    return false;
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
        $fCount = ( array_key_exists('iTemp', $this->f) ) ? $fCount-1 : $fCount ;
        foreach($this->f as $fKey => $fVal){
            if($fKey != 'iTemp'){ // Ignore iTemp to pass values to table
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
    }

    public function getRows($_result=false,$returnArray=false){
        if(!$returnArray){return $this->c->fetch_object($_result);}else{return $this->c->fetch_assoc($_result);}
    }

    public function clean($s){ return $this->c->c($s); }
    public function cleanAll(){ foreach($this->f as $k => $v){ $this->f[$k]['value'] = $this->clean($v['value']);} foreach($this->p as $k => $v){ $this->p[$k] = $this->clean($v);} }
    public function clearFields(){ foreach($this->f as $k => $v){ $this->f[$k]['value'] = (is_numeric($this->f[$k]['value'])) ? 0 : null;} unset($this->p); }
    
    public function fillData($id=0){

        if($id === true ){
            // Typically a database with one table and no key
            $sql = 'SELECT * FROM `'.$this->tableName.'`';
        }
        elseif( is_numeric($id) && $id > 0 ){
            $sql = 'SELECT * FROM `'.$this->tableName.'` WHERE `'.$this->keyField.'` = '.$id;
        }
        elseif( !is_numeric($id) && $id != '' ){
            $sql = 'SELECT * FROM `'.$this->tableName.'` WHERE `'.$this->keyField.'` = \''.$id.'\'';
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
    
    public function selectAll($where = false, $what = '*'){
        if( $where !== false && $where != '' ){$where = ' WHERE '.$where;}else{$where = '';}

        $sql = 'SELECT ' . $what . ' FROM `'.$this->tableName.'` '.$where;
        if (($result = $this->c->q($sql))  && $this->c->numRows() > 0) {
            return $result;
        }
        else{
            return false;
        }
    }
    
    public function insertData(){
        $this->getFieldNames(1);
        $sql = 'INSERT INTO `'.$this->tableName.'` ('.$this->insertFieldNames.') VALUES ('.$this->insertValueList.')';
        $result = $this->c->q($sql);
        if($result === false)
            return false;
        return $this->c->getInsertID();
    }

    public function insertUpdateData(){
        $this->getFieldNames(3);
        $sql = 'INSERT INTO `'.$this->tableName.'` ('.$this->insertFieldNames.') VALUES ('.$this->insertValueList.') ON DUPLICATE KEY UPDATE '.$this->updateFieldValue;
        $result = $this->c->q($sql);
        if($result === false)
            return false;
        return $this->c->getInsertID(); // Not sure this will ever return a good id.
    }
    
    public function updateData($where = false,$values=false){ // $where can be id or where clause, $values 'x=y AND z=r' OR no where to use current t_class id and empty values to update all fields
        if($where === false){$where = $this->f[$this->keyField]['value'];}
        if( is_numeric($where) && $where > 0){
            $where = ' WHERE `'.$this->keyField.'` = '.$where; 
        } elseif( $where == 0 || is_null($where) ) {
            $where = '';
        } else {
            if(strrpos($where,'=')===false && strrpos($where,'>')===false && strrpos($where,'<')===false){
                $where = ' WHERE `' . $this->keyField .'` = \'' . $where . '\'';
            } else {
                $where = ' WHERE '.$where; 
            }
        }
        if($values === false){$this->getFieldNames(2); $values = $this->updateFieldValue;}
        $sql = 'UPDATE `'.$this->tableName.'` SET '.$values.' '.$where;
        $result = $this->c->q($sql);
        if($result === false)
            return false;
        return true;
    }
    
    public function deleteData($where = ''){ // Where could be an id, if you dont use a comparitor we assume id.
        if($where == ''){
            if(isset($this->f[$this->keyField]['value']) && ($this->f[$this->keyField]['value'] > 0 || $this->f[$this->keyField]['value'] != null) ){
                $where = $this->f[$this->keyField]['value'];
            } else { return false; }
        }
        if(strrpos($where,'=')===false && strrpos($where,'>')===false && strrpos($where,'<')===false){
            if(is_numeric($where)){$where = '`'.$this->keyField.'` = '.$where;}else{$where = '`'.$this->keyField.'` = \''.$where.'\'';}
        }
        $sql = 'DELETE FROM `'.$this->tableName.'` WHERE '.$where.';';
        $result = $this->c->q($sql);
        return true;
    }
   
}
?>