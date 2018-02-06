<?

//////////////////////////////////////////////////////////
// Custom exception
/////////////////////////////////////////////////////////

class VKSuccess  {
	
	private $data;
	private $html;
	
	public function VKSuccess($data = NULL) {
		if ($data !== NULL) $this->data = $data; else $this->data = (object) array();
		$this->html = '';
	}
	
	public function result() { return TRUE; }
	
	public function addValue($key, $value) {
		$this->data->$key = $value;
	}
	
	public function data($data) {
		$this->data = $data;
	}
	
	public function html($html) {
		$this->html = $html;
	}
	
	public function jsonResponse () {
		$response = array();
		$response['success'] = 1;
		$response['data'] = $this->data;
		$response['html'] = $this->html;
		die(json_encode($response));
	}
	
	public function type() {
		return 'success';	
	}
	
}// Fin Clase

?>
