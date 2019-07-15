<?PHP
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class t_site_works_template_table extends SiteWorks\siteworks_db_tools
{
    public function __construct($id = NULL, &$odb = NULL){
        parent::__construct($odb);

        $this->tableName    = 'DATABASE_TABLE_NAME';
        $this->keyField     = 'FIELD_NAME1';
        $this->autoInc      = true;

        $this->f = Array(
            'FIELD_NAME1'               => array( 'value' => 0    , 'error' => null)   // Notes on this field
           ,'FIELD_NAME2'               => array( 'value' => null , 'error' => null)   // Notes on this field
        );

        if($id != NULL)
            $this->fillData($id);
        return true;
    }

    public function buildQueryArray($sqlName=false){
        switch ($sqlName) {
            // Set up your queries here that can be called by name.
            case 'FriendlySQLName':
                $sqlFn = 'SELECT * FROM `'. $this->tableName . '` WHERE `FIELD_NAME1` = "' . $this->odb->clean( $this->f['FIELD_NAME1']['value'] ) . '"';
            break;
            default:
                $sqlFn = NULL;
        }
        return $sqlFn;
    }
}
?>