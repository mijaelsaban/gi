<?

// La tabla de usuarios debe tener como mínimo los campos id, usuario, password, encriptar, nombre y activo

class Session {
	
	private $error = NULL;
	
	public function Session() {
		session_start();
	}
	
	public function sid() {
		return session_id();
	}
	
	public function exists($paramName) {
		return (isset($_SESSION['SES_DATA'][$paramName]) && ($_SESSION['SES_DATA'][$paramName] !== NULL));
	}
	
	public function set($paramName, $value) {
		$_SESSION['SES_DATA'][$paramName] = $value;
	}
	
	public function remove($paramName) {
		unset($_SESSION['SES_DATA'][$paramName]);
	}
	
	public function get($paramName) {
		return @$_SESSION['SES_DATA'][$paramName];
	}
	
	public function serializar($paramName, $obj) {
		$_SESSION['SES_DATA'][$paramName] = serialize($obj);
	}
	
	public function desSerializar($paramName) {
		return unserialize($_SESSION['SES_DATA'][$paramName]);
	}
	
	//Destruye los datos de sesion	
	public function destroy() {
		session_unset();
		session_destroy();
	}
	
	public function clean() {
		unset($_SESSION['SES_DATA']);	
	}
	
	public function error() {
		return $this->error;
	}

///////////////////////////////////////////////////////////////////////////////////
// Métodos privados
///////////////////////////////////////////////////////////////////////////////////


}

?>