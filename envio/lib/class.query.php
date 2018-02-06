<?

class Query {
	
	protected $db = NULL;
	protected $result = NULL;
	protected $row = NULL;
	protected $params = NULL;
	protected $paramTypes = NULL;
	protected $sql = '';
	
	// Pagination
	private $pagination = FALSE;
	private $rpp = 15;
	private $activePage = 1;
	private $records = 0;
	
	///////////////////////////////////////////////////////////////
	/////////  CONSTRUCTOR ////////////////////////////////////////
	///////////////////////////////////////////////////////////////
	
	public function Query($db) {
		$this->db = $db;
		$this->params = array();
		$this->paramTypes = array();
	}	

	///////////////////////////////////////////////////////////////
	/////////  METODOS PUBLICOS  //////////////////////////////////
	///////////////////////////////////////////////////////////////
	
	//Ejecuta una sentencia select
	// $desde representa la pagina a partir de la cual se deben mostrar $registros registros
	// Si $desde es 0, no hay paginacion. Si es mayor que cero se pagina teniendo en cuenta
	// $registros como la cantidad de registros a mostrar. Si $desde es mayor que cero pero 
	// $registros es cero, este ultimo toma el valor de la propiedad protegida registrosPorPagina
	public function run($sql, $desde = 0, $registros = 0) { //desde pagina (por defecto 0, si es > 0 se pagina)
		$this->sql = $sql;
		if (!$this->assignParams()) {
			return FALSE;
		}
		$sqlAdd = '';
		if ($desde > 0) {
			$this->pagination = TRUE;
			$this->activePage = $desde;
			if ($registros > 0) $this->rpp = $registros;
			$sqlAdd = ' limit '.
						(($this->activePage-1)*$this->rpp).
						', '.
						$this->rpp; 
			if (!$result = $this->db->query('select count(*) as total from ('.$this->sql.') as tabla'))
				throw new VKException($this->db->error);
			if (!$row = $result->fetch_object()) 
				$this->records = 0;	
			$this->records = $row->total;
		}
		$this->sql .= $sqlAdd;
		if (!$this->result = $this->db->query($this->sql))		
			throw new VKException($this->db->error);
		return TRUE;
	}
	
	private function pages() {
		if (!$this->pagination) return 1;
		if ($this->records <= $this->rpp) return 1;
		if (!$this->rpp) throw new VKException('Error division by zero');
		return ceil($this->records / $this->rpp);
	}

	public function pagination() {
		$pag = array();
		$pag['pages'] = (int) $this->pages();	
		$pag['rpp'] = (int) $this->numRows();
		$pag['records'] = (int) $this->records;
		$pag['page'] = (int) $this->activePage;
		return (object) $pag;
	}
	
	//Obtiene la siguiente fila del resultado indicado y la guarda en la propiedad row
	public function fetchRow() {
		if ($this->result) {					 
			if ($this->row = $this->result->fetch_row()) {
				$aux = array();
				foreach($this->row as $key => $value) {
					$fieldData = $this->result->fetch_field_direct($key); 
					if (empty($value)) {
						$aux[$fieldData->name] = $value;
					}else{
						switch ($fieldData->type) {
							//Integers
							case 16: //Bit
							case 1: //Tyniint, bool
							case 2: //Smallint
							case 3: //Integer
							case 8: //Bigint, Serial
							case 9: //Mediumint
								$aux[$fieldData->name] = (int) $value; 
							break;
							//Floats
							case 4: //Float
							case 5: //Double
							case 246: //Decimal, Numeric, Fixed
								$aux[$fieldData->name] = (float) $value; 
							break;
							//Texts
							case 252: //Blobs y text
							case 253: //Varchar, Varbinary
							case 254: //Char, Set, Enum
								$aux[$fieldData->name] = stripslashes($value); 
							break;
							//Dates
							default: 
								$aux[$fieldData->name] = $value; 
						}
					}
				}
				$this->row = $aux;
				return TRUE;				
			}else{ 
				$this->result->free(); 
			}
		}
		return FALSE;
	}
	
	//Devuelve un objecto con los valores de la fila actual
	public function row($asObject = TRUE) {
		if ($this->row) {
			if ($asObject) {		
				return (object) $this->row;
			}else{
				return $this->row;
			}
		}
		return NULL;
	}
	
	public function numRows() {
		if ($this->result) {
			return $this->result->num_rows;
		}
		return FALSE;
	}	
	
	public function sql() {
		return $this->sql;	
	}	
	
	public function db($db = NULL) {
		if ($db !== NULL) {
			$this->db = $db;
		}
		return $this->db;
	}
	
	public function param($paramName, $value = NULL, $type = NULL) { 
		if ($value !== NULL) {
			$this->params[$paramName] = $value;
			if ($type === NULL) {
				$type = 'varchar';
			}
		}
		if ($type !== NULL) {
			$this->paramTypes[$paramName] = $type;
		}
		return $this->params[$paramName];
	}
	
	protected function paramType($paramName) {
		return $this->paramTypes[$paramName];
	}
	
	public function clearParams() {
		$this->params = array();	
	}
	
	///////////////////////////////////////////////////////////////
	/////////  METODOS PRIVADOS  //////////////////////////////////
	///////////////////////////////////////////////////////////////		
	
	protected function assignParams() {
		$sqlaux = '';
		$largo = strlen($this->sql);
		$comillas = FALSE;
		$char1 = '';
		$char2 = '';
		for ($i=0; $i<$largo; $i++) {
			$char2 = $char1;
			$char1 = $this->sql[$i];
			if (($char1 == '"') && (ord($char2) != 92)) $comillas = !$comillas; //92 es la barra invertida "\"
			if (($char1 == '#') && !$comillas) {
				$sqlaux .= '##';
			}else{
				$sqlaux .= $char1;
			}
		}
		$aux = explode('##', $sqlaux);
		if (count($aux) > 1) {
			$newSQL = '';
			foreach($aux as $key => $value) {
				if (($key % 2) == 0) {
					$newSQL .= ' '.$value;
				}else{
					if (!isset($this->params[$value])) 
						throw new VKException('Param not found');
					if (!isset($this->paramTypes[$value]))
						throw new VKException('Unknown param type');
					$escValue = $this->escape($value);
					$newSQL .= ' '.$escValue;
				}
			}
			$this->sql = $newSQL;
		}
		return TRUE;
	}							
	
	protected function fieldsAsArray($keys) {
		$aux = explode(',', $keys);
		$keys = array();
		foreach($aux as $key => $value) {
			$keys[] = trim($value);
		}
		return $keys;
	}
	
	protected function escape($paramName) {
		return $this->db->escape($this->params[$paramName], $this->paramTypes[$paramName]);
	}
	
}

?>