<?PHP
namespace SiteWorks;
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class t_site_works_queue extends siteworks_db_tools
{
    
    public function __construct($id = 0, $odb = false){
    	parent::__construct();
    	if($odb){$this->c =& $odb;} else {$this->c = $GLOBALS['_odb'];}


	    $this->tableName    = 'site_works_queue';
	    $this->keyField     = 'sw_ts';
	    $this->autoInc      = false;
	
		$this->f = Array(
			 'sw_ts'                         => array( 'value' => 0       , 'error' => null) // Queue Key time + F + random 6
		    ,'sw_tag'                        => array( 'value' => null    , 'error' => null) // Queue Tag to identify which queue it goes in.
		    ,'sw_script'                     => array( 'value' => null    , 'error' => null) // Queue Script full path
		    ,'sw_vars'                       => array( 'value' => null    , 'error' => null) // Queue Script Variables base64
		    ,'sw_waitstart'                  => array( 'value' => 0       , 'error' => null) // Time to wait before running this specific script
		    ,'sw_timeout'                    => array( 'value' => 0       , 'error' => null) // Default Timeout 30 seconds for script to run
	    );
	
		if($id != '' && $id !== 0)
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
		    $sqlFn = false;
    	}
	    return $sqlFn;
    }
}
?>