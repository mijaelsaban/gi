<?

//////////////////////////////////////////////////////////
// Custom warning
/////////////////////////////////////////////////////////

class VKWarning extends Exception {
	
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
		if (!PRODUCTION && LOG_WARNINGS) {
			trace($message.' (Code: '.$code.') in '.$this->getFile().' line '.$this->getLine(), 'WARNING');
		}
    }
	
	public function result() { return FALSE; }
	
	public function type() {
		return 'warning';
	}
	
	public function jsonResponse () {
		$response = array();
		$response['success'] = 0;
		$response['code'] = $this->getCode();
		$response['msg'] = $this->getMessage();
		die(json_encode($response));
	}
	
}// Fin Clase

?>
