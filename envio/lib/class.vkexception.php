<?

function myErrorHandler($errno, $errstr, $errfile, $errline) {
	trace('('.$errno.') '.$errstr.' en '.$errfile.' || Linea: '.$errline, 'PHP Error');
}

function trace($value, $title = '') {
	$content = '';
	if (file_exists(LOG_ERROR_FILE)) {
		$content = file_get_contents(LOG_ERROR_FILE);
	}
	$fp = fopen(LOG_ERROR_FILE, 'w');
	$str = nl2br(var_export($value, TRUE));
	$str = '<div style="padding:10px; background: #ddd">'.date('d/m/Y, h:i:s').' --- '.$title.'</div>
			<div style="padding:10px">'.$str.'</div>
			<hr style="margin: 10px 0 10px 0">'.$content;
	fwrite($fp, $str, strlen($str));
	fclose($fp);
}

function errorpage() {
	if (file_exists(LOG_ERROR_FILE)) {
		return file_get_contents(LOG_ERROR_FILE);
	}
	return 'Empty';
}

//////////////////////////////////////////////////////////
// Custom exception
/////////////////////////////////////////////////////////

class VKException extends Exception {
	
	private $data;
	
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
		$this->data = (object) array();
		if (!PRODUCTION && LOG_ERRORS) 
			trace($message.' (Code: '.$code.') in '.$this->getFile().' line '.$this->getLine(), 'EXCEPTION');
    }
	
	public function jsonResponse () {
		$response = array();
		$response['success'] = 0;
		$response['code'] = $this->getCode();
		$response['msg'] = $this->getMessage();
		$response['data'] = $this->data;
		die(json_encode($response));
	}
	
	public function result() { return FALSE; }
	
	public function addValue($key, $value) {
		$this->data->$key = $value;
	}
	
	public function data($data) {
		$this->data = $data;
	}
	
	public function type() {
		return 'exception';	
	}
	
	
}// Fin Clase

?>
