<?PHP
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

/***************************************************************************************************
**                                E X A M P L E     I F R A M E                                   **
***************************************************************************************************/

class template_iframe extends _s
{
    public function template(){
    	$this->_out['body'][] = 'This is just like a basic controller, but it only prints exactly what I tell it to, check page sorce. :)';
        $this->_out['body'][] = '<div><a href="' . $this->_uri->public_url . '">Return Home</a></div>';
    }


}

?>
