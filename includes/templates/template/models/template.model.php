<?PHP
class template_model extends _s
{
	public function addToA($a = 0, $b = 0){
		return  $a + $b;
	}

	public function addToSharedA($b = 0){
		return  $this->_p['a'] + $b;
	}

}

?>