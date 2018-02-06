<?

class Control extends AppObject {

	protected $db;
	protected $params;
	protected $uploadDir;
	protected $storeDir;
	protected $resDir;
	protected $tmpDir;
	protected $data;
	private $sessionIndex;
	
	public function Control() {
		parent::AppObject();
		global $db;
		if ($db !== NULL) $this->db = $db;
		$this->params = array();
		$this->uploadDir = DOCUMENT_ROOT.UPLOAD_DIR;
		$this->storeDir = DOCUMENT_ROOT.STORE_DIR;
		$this->resDir = DOCUMENT_ROOT.RESOURCES_DIR;
		$this->tmpDir = DOCUMENT_ROOT.TMP_DIR;
		$this->data = (object) array();
		$this->sessionIndex = get_called_class();
		if (!$this->session->exists($this->sessionIndex) )
			$this->session->set($this->sessionIndex, array());
		$this->setup();		
	}
	
	/////////////////////////////////////////////////////////////////////////////
	protected function setup() { die('You must redeclare setup method'); }
	
	public function data() {
		return $this->data;	
	}
	
	protected function beginTransaction() {
		return $this->db->begin();	
	}

	protected function commit() {
		return $this->db->commit();	
	}

	protected function rollback() {
		return $this->db->rollback();	
	}

	
	/////////////////////////////////////////////////////////////////////////////
	protected function insertId() {
		return $this->db->insert_id;	
	}
	
	/////////////////////////////////////////////////////////////////////////////
	protected function exists($tuple) {
		try {
			$table = $tuple->table();
			$dataSet = new DataSet($this->db, $table);
			foreach($tuple->fields() as $key => $value) {
				$dataSet->param($key, $value);
			}
			return $dataSet->exists($tuple->keys());
		} catch(VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	protected function insert($tuple) {
		try {
			$table = $tuple->table();
			$dataSet = new DataSet($this->db, $table);
			$strfields = '';
			foreach($tuple->fields() as $key => $value) {
				$dataSet->param($key, $value);
				$strfields .= $key.', ';
			}
			$strfields = rtrim($strfields, ', ');
			if (!$dataSet->insert($strfields))
				throw new VKException('In class Control cant insert: '.$this->dbError());
			return TRUE; 
		}catch(VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	protected function insertColl($tupleColl) {
		try {
			$coll = $tupleColl->getColl();
			foreach($coll as $key => $value)	
				if (!$this->insert($value)) throw new VKException('Cant insert tuple from tuple coll');
			return TRUE;
		}catch(VKException $e) {
			return 	$this->exception($e)->result();
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	protected function updateColl($tupleColl, $updateField) {
		try {
			$coll = $tupleColl->getColl();
			foreach($coll as $key => $value) {
				$updateValue = $value->get($updateField);
				$value->remove($updateField);
				if ($updateValue) {
					$value->set($updateField, $updateValue, TRUE);
					if (!$this->modify($value)) throw new VKException('Cant update tuple from tuple coll');
				} else 
					if (!$this->insert($value)) throw new VKException('Cant insert tuple from tuple coll');
			}
			return TRUE;
		}catch(VKException $e) {
			return 	$this->exception($e)->result();
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	protected function modify($tuple) {
		try {
			$table = $tuple->table();
			$arKeys = explode(',', $tuple->keys());
			for($i=0; $i<count($arKeys); $i++) {
				$arKeys[$i] = trim($arKeys[$i]);
			}
			$dataSet = new DataSet($this->db, $table);
			$strFields = '';
			foreach($tuple->fields() as $key => $value) {
				$dataSet->param($key, $value);
				if (!in_array($key, $arKeys)) {
					$strFields .= $key.', ';
				}
			}
			$strFields = rtrim($strFields, ', ');
			if (!$dataSet->modify($strFields, $tuple->keys()))
				throw new VKException('In Class Control cant modify: '.$this->dbError());
			return TRUE;
		}catch(VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	protected function delete($tuple) {
		try {
			$table = $tuple->table();
			$dataSet = new DataSet($this->db, $table);
			foreach($tuple->fields() as $key => $value) {
				$dataSet->param($key, $value);
			}
			if (!$dataSet->delete($tuple->keys()))
				throw new VKException('In Class Control cant delete: '.$this->dbError());
			return TRUE;
		}catch(VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	protected function queryCount($sql) {
		try {
			$query = new Query($this->db);
			if (isset($this->params) && is_array($this->params))
				foreach($this->params as $key => $value) {
					$query->param($key, $value['value'], $value['type']);
				}
			if (!$query->run($sql))
				throw new VKException('In Class Control cant count: '.$this->dbError());
			$this->params = array();	
			if (!$query->fetchRow()) return 0;
			$row = array_values($query->row(FALSE));
			return $row[0];
		} catch(VKException $e) {
			$this->params = array();
			return $this->exception($e)->result();
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	/// Para evitar que salte por error al no encontrar rowView en las clases hijas
	protected function defaultView($row) {
		return $row;
	}
	
	/////////////////////////////////////////////////////////////////////////////
	protected function get($tuple, $fields = NULL, $view = 'defaultView') {
		try {
			$table = $tuple->table();
			$dataSet = new DataSet($this->db, $table);
			foreach($tuple->fields() as $key => $value) {
				$dataSet->param($key, $value);
			}
			if (!$row = $dataSet->get($tuple->keys(), $fields))
				throw new VKException('In Class Control cant get row: '.$this->dbError());
			return $this->$view($row);
		} catch (VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	protected function param($paramName, $value, $type = 'varchar') {
		if (!isset($this->params)) {
			$this->params = array();
		}
		$newParam = array('value' => $value, 'type' => $type);
		$this->params[$paramName] = $newParam;
	}
	 
	/////////////////////////////////////////////////////////////////////////////
	/// DEVUELVE UN UNICO REGISTRO EXTRAIDO DE QUERY
	protected function queryRow($sql, $view = 'defaultView') {
		try {
			$query = new Query($this->db);
			if (isset($this->params) && is_array($this->params))
				foreach($this->params as $key => $value) {
					$query->param($key, $value['value'], $value['type']);
				}
			if (!$query->run($sql))
				throw new VKException('In Class Control cant get row: '.$this->dbError());
			$this->params = array();	
			if (!$query->fetchRow()) return FALSE;
			return $this->$view($query->row());
		} catch(VKException $e) {
			$this->params = array();
			return $this->exception($e)->result();
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	/// DEVUELVE UN UNICO REGISTRO EXTRAIDO DE QUERY
	protected function queryList($sql, $view = 'defaultView', $page = NULL, $rowspp = 0) {
		try {
			$query = new Query($this->db);
			if (isset($this->params) && is_array($this->params))
				foreach($this->params as $key => $value) {
					$query->param($key, $value['value'], $value['type']);
				}
			if (!$query->run($sql, $page, $rowspp))
				throw new VKException('In Class Control cant list: '.$this->dbError(), 1);
			if ($page !== NULL) $pagination = $query->pagination();
			$this->params = array();	
			$list = array();
			while ($query->fetchRow()) {
				$list[] = $this->$view($query->row());
			}
			if ($page !== NULL) {
				return (object) array('list' => $list, 'pagination' => $pagination);
			}
			return $list;
		} catch(VKException $e) {
			$this->params = array();
			return $this->exception($e)->result();
		}
	}
	
	public function dbError() {
		return $this->db->error;
	}

	public function getInt($paramName, $defaultValue) {
		return $this->web->getInt($paramName, $defaultValue);
	}
	
	public function getVarchar($paramName, $defaultValue) {
		return $this->web->getVarchar($paramName, $defaultValue);
	}
	
	public function sessionSet($name, $value) {
		$aux = $this->session->get($this->sessionIndex);
		$aux[$name] = $value;
		$this->session->set($this->sessionIndex, $aux);
	}
	
	public function sessionGet($name) {
		$aux = $this->session->get($this->sessionIndex);
		return $aux[$name];
	}
	
	public function sessionExists($name) {
		$aux = $this->session->get($this->sessionIndex);
		return array_key_exists($name, $aux);
	}
	
	public function sessionRemove($name) {
		$aux = $this->session->get($this->sessionIndex);
		unset($aux[$name]);
		$this->session->set($this->sessionIndex, $aux);
	}
	
	public function sessionClean() {
		$this->session->remove($this->sessionIndex);
	}
	
}// Fin Clase

?>
