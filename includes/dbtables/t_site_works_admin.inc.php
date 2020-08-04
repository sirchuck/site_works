<?PHP
namespace SiteWorks;
class t_site_works_admin extends siteworks_db_tools
{
    public function __construct($id = NULL, &$odb = NULL){
    	parent::__construct($odb);

	    $this->tableName    = 'site_works_admin';
	    $this->keyField     = 'sw_admin_key';
	    $this->autoInc      = false;
	
		$this->f = Array(
			 'sw_admin_key'               => array( 'value' => 0    , 'error' => null) // Key Field
		    ,'sw_version'                 => array( 'value' => null , 'error' => null) // SiteWorks Version Number
	    );

    	$this->fillData($id);
		return true;
    }

    public function buildQueryArray($sqlName=false){
        switch ($sqlName) {

        	// Set up your queries here that can be called by name.
	        case 'pullByVersion':
	            $sqlFn = 'SELECT * FROM `'. $this->tableName . '` WHERE `sw_version` = "' . $this->odb->clean( $this->f['sw_version']['value'] ) . '"';
			break;

	    default:
		    $sqlFn = NULL;
    	}
	    return $sqlFn;
    }
}
?>