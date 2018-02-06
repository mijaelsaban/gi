<?

//////////////////////////////////////////////////////////
// Parent class
/////////////////////////////////////////////////////////

class AppObject {

	protected $web;
	protected $session;
	protected $exception;
	
	public function AppObject() {
		
		global $web;
		global $session;
		
		if ($web !== NULL) $this->web = $web;
		if ($session !== NULL) $this->session = $session;
		
		$this->exception = NULL;
		$this->msgs = array();
	}
	
	protected function exception($e) {
		if ($e !== NULL) {
			$this->exception = $e;
			$this->web->response($e);
		}
		return $this->exception;
	}
	
}// Fin Clase

?>
