<?



//////////////////////////////////////////////////////////
// Clase 
/////////////////////////////////////////////////////////

class Tuple extends AppObject {

	protected $table = '';
	protected $fields;
	private $tableFields;
	protected $keys;
	
	public function Tuple($table, $setParam = NULL, $setParamValue = NULL, $readInput = FALSE) {
		parent::AppObject();
		$this->table = $table;
		$this->keys = '';
		$this->fields = array();
		$this->tableFields = array();
		if ($setParam !== NULL) {
			if ($readInput)
				$this->readInput($setParam, $setParamValue, TRUE); //En este caso setParamValue se convierte en default Value
			else $this->set($setParam, $setParamValue, TRUE);
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function clean() {
		$this->keys = '';
		$this->fields = array();
		$this->tableFields = array();
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function readInput($fieldName, $defaultValue, $iskey = FALSE, $tableFieldName = NULL) {
		$fieldValue = $this->web->getParam($fieldName, $defaultValue, FALSE);
		$this->fields[$fieldName] = $fieldValue;
		if ($tableFieldName !== NULL) {
			$this->tableFields[$fieldName] = $tableFieldName;
		}
		if ($iskey) {
			$this->keys = ($this->keys == '')? $fieldName : $this->keys.', '.$fieldName;
		}
		return $fieldValue;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function table($table = NULL) {
		if ($table !== NULL) {
			$this->table = $table;
		}
		return $this->table;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function setKeys($keys) {
		$this->keys = $keys;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function removeKeys() {
		$this->keys = '';
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function get($fieldName) {
		return $this->fields[$fieldName];
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function set($fieldName, $value, $iskey = FALSE) {
		if ($value === NULL) $value = 'NULL';
		$this->fields[$fieldName] = $value;
		if ($iskey) {
			$this->keys = ($this->keys == '')? $fieldName : $this->keys.', '.$fieldName;
		}
		return $value;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function remove($fieldName) {
		unset($this->fields[$fieldName]);
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function keys() {
		return $this->keys;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function fields() {
		$aux = array();
		foreach($this->fields as $key => $value) {
			if (array_key_exists($key, $this->tableFields)) {
				$key = $this->tableFields[$key];
			}
			$aux[$key] = $value;
		}
		return $aux;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function fieldsObject() {
		return (object) $this->fields();
	}
	
		
}// Fin Clase

?>
