<?

class DataSet extends Query {
	
	
	private $table = NULL;
	
	public function DataSet($db, $table) {
		$this->db = $db;
		$this->table = $table;
		$this->getSchema($this->table);
	}	
	
	private function getSchema($table) {
		$this->paramTypes = array();
		if (!$result = $this->db->query('show columns from '.$table)) 
			throw new VKException('Can\'t get columns information');
		while ($row = $result->fetch_array()) {
			$sem = TRUE;
			if ((strpos($row['Type'], 'text') !== FALSE) && $sem)  {
				$this->paramTypes[$row['Field']] = 'varchar';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'blob') !== FALSE) && $sem)  {
				$this->paramTypes[$row['Field']] = 'varchar';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'varchar') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'varchar';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'char') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'varchar';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'string') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'varchar';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'date') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'date';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'datetime') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'date';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'timestamp') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'date';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'integer') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'integer';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'int64') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'integer';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'int') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'integer';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'float') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'float';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'double') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'float';
				$sem = FALSE;
			}
			if ((strpos($row['Type'], 'number') !== FALSE) && $sem) {
				$this->paramTypes[$row['Field']] = 'float';
				$sem = FALSE;
			}
			if ($sem) throw new VKException('Unknown data type in Class DataSet, field '.$row['Field']);
		}
	}
	
	public function exists($keys) {
		$keys = $this->fieldsAsArray($keys);
		$sqlwhere = ' where 1';
		foreach($keys as $key => $param) {
			$paramValue = $this->escape($param);
			$sqlwhere .= ' and '.$param.' = '.$paramValue;
		}
		$sql = 'select * from '.$this->table.$sqlwhere;
		if (!$result = $this->db->query($sql))
			throw new VKException($this->db->error);
		return ($result->num_rows > 0);
	}	
	
	public function insert($fields) {
		$fields = $this->fieldsAsArray($fields);				
		$sqlfields = '(';
		$sqlvalues = '(';
		foreach($fields as $key => $param) {
			$sqlfields .= ' '. $param . ',';
			$paramValue = $this->escape($param);
			$sqlvalues .= ' '.$paramValue.',';
		}
		$sqlfields = rtrim($sqlfields, ',') . ' )';
		$sqlvalues = rtrim($sqlvalues, ',') . ' )';
		$sql = 'insert into '.$this->table.' '.$sqlfields.' values '.$sqlvalues;
		if (!$this->db->query($sql))
			throw new VKException($this->db->error);
		return TRUE;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	public function modify($fields, $keys) {
		$fields = $this->fieldsAsArray($fields);
		$keys = $this->fieldsAsArray($keys);
		$sqlvalues = ' set';
		foreach($fields as $key => $param) {
			$paramValue = $this->escape($param);
			$sqlvalues .= ' '.$param . ' = '.$paramValue.',';
		}
		$sqlvalues = rtrim($sqlvalues, ',');
		$sqlkeys = ' where 1';
		foreach($keys as $key => $param) {
			$paramValue = $this->escape($param);
			$sqlkeys .= ' and '.$param . ' = '.$paramValue;
		}
		$sql = 'update '.$this->table.$sqlvalues.$sqlkeys;
		if (!$this->db->query($sql)) 
			throw new VKException($this->db->error());				
		return TRUE;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	public function delete($keys) {				
		$keys = $this->fieldsAsArray($keys);		
		$sql = 'delete from '.$this->table.' where 1';
		foreach($keys as $key => $param) {
			$paramValue = $this->escape($param);
			$sql .= ' and '.$param.' = '.$paramValue;
		}
		if (!$this->db->query($sql)) 
			throw new VKException($this->db->error());
		return TRUE;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	public function get($keys, $fields = NULL) {
		if ($fields === NULL) $fields = '*';				
		$keys = $this->fieldsAsArray($keys);		
		$sql = 'select '.$fields.' from '.$this->table.' where 1';
		foreach($keys as $key => $param) {
			$paramValue = $this->escape($param);
			$sql .= ' and '.$param.' = '.$paramValue;
		}
		$sql .= ' limit 1';
		$qData = new Query($this->db);
		if (!$qData->run($sql)) 
			throw new VKException($this->db->error());
		if (!$qData->fetchRow()) 
			throw new VKException($this->db->error());
		return $qData->row();		
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	public function param($paramName = NULL, $value = NULL, $type = NULL) {
		if ($type === NULL) {
			$type = $this->paramTypes[$paramName];
		}
		parent::param($paramName, $value, $type);
	}
	
}

?>