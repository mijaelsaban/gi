<?

class VKMysql extends mysqli {
	
	private $host = NULL;
	private $user = NULL;
	private $port = NULL;
	private $pass = NULL;
	private $name = NULL;

	///////////////////////////////////////////////////////////////
	/////////  CONSTRUCTOR ////////////////////////////////////////
	///////////////////////////////////////////////////////////////
	
	function __construct($host = NULL, $port = NULL, $name = NULL, $user = NULL, $pass = NULL) {
		$this->host = ($host !== NULL)?  $host : DB_HOST;
		$this->port = ($port !== NULL)?  $port : DB_PORT;
		$this->user = ($user !== NULL)?  $user : DB_USER;
		$this->name = ($name !== NULL)?  $name : DB_NAME;
		$this->pass = ($pass !== NULL)? $pass : DB_PASS;
		
		parent::__construct($this->host, $this->user, $this->pass, $this->name, $this->port);
		if ($this->connect_errno) 
			throw new VKException('Fallo al contenctar a la base de datos: '.$this->connect_error);
	}		
		
	///////////////////////////////////////////////////////////////////////////////////////////////
	function begin() {
		if (!$this->autocommit(FALSE)) throw new VKException($this->error);
		return TRUE;
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////
	function rollback() {
		//if (!parent::rollback()) throw new VKException($this->error);
		if (!$this->autocommit(TRUE)) throw new VKException($this->error);
		return TRUE;
	}
	
	function commit() {
		//if (!parent::commit()) throw new VKException($this->error);
		if (!$this->autocommit(TRUE)) throw new VKException($this->error);
		return FALSE;
	}
		
	///////////////////////////////////////////////////////////////////////////////////////////////
	public function affectedRows() {
		return $this->affected_rows;
	}
	
	//Devuelve el juego de caracteres en el cliente
	function charset() {
		return $this->character_set_name();
	}
	
	//Setea el charset 
	function setUTF8() {
		if (!$this->set_charset('utf8')) throw new VKException($this->error);
		return TRUE;
	}
	
	function setISO() {
		if(!$this->set_charset('latin1')) throw new VKException($this->error);
		return TRUE;
	}
	
	public function escape($value, $type) {
		$mq = ini_get('magic_quotes_gpc');
		if ($mq) {
			$value = stripslashes($value);
		}
		/* Desde el punto de vista de PHP hay tres tipos de datos a insertar en la base. 
			Texto, número flotante o número entero. Estos tres tipos son los que se escapan. */ 
		if ($value !== NULL) {
			switch ($type) {
				case 'varchar' :
				case 'date' :	
					return '"'.$this->real_escape_string($value).'"';
				case 'float' :
					return (float) str_replace(',', '.', $value);
				default : 
					return (int) $value;
			}
		} else return 'NULL';
	}
	
	public function error() {
		return $this->error;	
	}
	
}

?>