<?PHP
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

/***************************************************************************************************
**                                 E X A M P L E     S C R I P T                                  **
***************************************************************************************************/

class template_script extends _s
{
    public function template(){
    	$this->_out['body'][] = 'This is just like a basic controller, but it only prints exactly what I tell it to, check page sorce. :)';
    }


}

?>