<?PHP
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class t_site_works_admin extends siteworks_db_tools
{
    public function __construct($id = 0, $odb = false){
    	parent::__construct();
    	if($odb){$this->c =& $odb;} else {$this->c =& $GLOBALS['_odb'];}
        

	    $this->tableName    = 'site_works_admin';
	    $this->keyField     = 'sw_admin_key';
	    $this->autoInc      = false;
	
		$this->f = Array(
			 'sw_admin_key'               => array( 'value' => 0    , 'error' => null) // Key Field
		    ,'sw_version'                 => array( 'value' => null , 'error' => null) // SiteWorks Version Number
		    ,'sw_language'                => array( 'value' => null , 'error' => null) // SiteWorks Default Language
	    );

		if($id > 0)
	    	$this->fillData($id);
		return true;
    }

    public function buildQueryArray($sqlName=false){
        switch ($sqlName) {

        	// Set up your queries here that can be called by name.
	        case 'pullByLanguage':
	            $sqlFn = 'SELECT * FROM `'. $this->tableName . '` WHERE `sw_language` = "' . $this->odb->dbClean( $this->f['sw_language']['value'] ) . '"';
			break;

	    default:
		    $sqlFn = false;
    	}
	    return $sqlFn;
    }
}
?>