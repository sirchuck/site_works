<?PHP
/***************************************************************************************************
**                                E X A M P L E     I F R A M E                                   **
***************************************************************************************************/

class template_iframe extends _s
{
    public function template(){
    	$this->_out['body'][] = 'This is just like a basic controller, but it only prints exactly what I tell it to, check page sorce. :)';
        $this->_out['body'][] = '<div><a href="' . $this->_uri->base_url . '">Return Home</a></div>';
    }


}

?>
