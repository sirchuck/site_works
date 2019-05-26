<?PHP
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class t_site_works_session extends siteworks_db_tools
{
    
    public function __construct($id = NULL, $odb = false){
    	parent::__construct();
    	if($odb){$this->c =& $odb;} else {$this->c = $GLOBALS['_odb'];}

	    $this->tableName    = 'site_works_session';
	    $this->keyField     = 'sw_sess_key';
	    $this->autoInc      = false;
	
		$this->f = Array(
			 'sw_sess_key'                    => array( 'value' => null    , 'error' => null) // Session Key
		    ,'sw_sess_data'                   => array( 'value' => null    , 'error' => null) // Session Data
		    ,'sw_sess_ts'                     => array( 'value' => 0       , 'error' => null) // Session Time Last used
	    );
	
        if($id != NULL)
	    	$this->fillData($id);
		return true;
    }

    public function buildQueryArray($sqlName=false){
        switch ($sqlName) {

        	// Set up your queries here that can be called by name.
	        case 'pullByVisits':
	            $sqlFn = 'SELECT * FROM `'. $this->tableName . '` WHERE `sw_site_visits` > 0';
			break;

	    default:
		    $sqlFn = NULL;
    	}
	    return $sqlFn;
    }
}
?>