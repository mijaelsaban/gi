<?

//////////////////////////////////////////////////////////
// Clase 
/////////////////////////////////////////////////////////

class TupleColl extends AppObject {

	protected $table = '';
	protected $tuples;
	protected $parentField;
	protected $parentFieldValue;
	protected $cbObject = NULL;
	protected $cbFunction = NULL;
	
	public function TupleColl($table, $parentField, $parentFieldValue, $cbObject = NULL, $cbFunction = NULL) {
		parent::AppObject();
		$this->table = $table;
		$this->tuples = array();
		$this->parentField = $parentField;
		$this->parentFieldValue = $parentFieldValue;
		$this->cbObject = $cbObject;
		$this->cbFunction = $cbFunction;
	}
		
	/////////////////////////////////////////////////////////////////////////////
	public function readInput() { 
		$args = func_get_args();
		$params = array();
		$aux = $this->web->getParamArray($args[0], NULL);
		$countFirst = 0;
		if ($aux) {
			$params[$args[0]] = $aux;
			$countFirst = count($params[$args[0]]);
		}
		$count = count($args);
		for($i=1; $i<$count; $i++) {
			$aux = $this->web->getParamArray($args[$i], NULL);
			if ($aux) {
				$params[$args[$i]] = $aux;
				if (count($params[$args[$i]]) != $countFirst) throw new VKException('Error in param count');
			}
		}
		for($i=0; $i<$countFirst; $i++) {
			$tuple = new Tuple($this->table);
			$tuple->set($this->parentField, $this->parentFieldValue, TRUE);
			$tupleValid = TRUE;
			foreach($args as $key => $value) {
				if ($params[$value][$i] === NULL) {
					$tupleValid = FALSE;
				}else $tuple->set($value, $params[$value][$i]);
			}
			if ($this->cbObject && $this->cbFunction) {
				if (!call_user_func_array(array($this->cbObject, $this->cbFunction), array($this->parentFieldValue, $tuple))) 
					$tupleValid = FALSE;
			}
			if ($tupleValid)
				$this->tuples[] = $tuple;	
		}
	}
		
	/////////////////////////////////////////////////////////////////////////////
	public function getColl() {
		return $this->tuples;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function getAt($index) {
		return $this->tuples[$index];
	}
	
	/////////////////////////////////////////////////////////////////////////////
	public function removeAt($index) {
		unset($this->tuples[$index]);
	}
	
	public function count() {
		return count($this->tuples);
	}
	
	public function find($field, $value) {
		foreach($this->tuples as $tuple)
			if ($tuple->get($field) == $value) return $tuple;
		return FALSE;
	}
	
}// Fin Clase

?>
