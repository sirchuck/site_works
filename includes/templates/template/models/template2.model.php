<?PHP
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class template2_model extends _s
{

	public function addToSharedB($a = 0){
		return  $this->_p['b'] + $a;
	}

}

?>