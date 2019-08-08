<?PHP
namespace SiteWorks;
class t_site_works_mem extends siteworks_db_tools
{
    
    public function __construct($id = NULL, &$odb = NULL){
    	parent::__construct($odb);

	    $this->tableName    = 'site_works_mem';
	    $this->keyField     = 'sw_mem_key';
	    $this->autoInc      = false;
	
		$this->f = Array(
			 'sw_mem_key'                    => array( 'value' => 0    , 'error' => null) // Key Field
		    ,'sw_site_visits'                => array( 'value' => 0    , 'error' => null) // SiteWorks number of visitors
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