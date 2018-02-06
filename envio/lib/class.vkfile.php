<?

define('TMP_PREFIX', 'genTmpPrefix'); 

class VKFile {

	protected $path = NULL;		
	
	public function VKFile($path = NULL) {
		if ($path !== NULL) $this->path($path);
	}
	
	private function cleanSpecialChars($string) {
		$aux = str_replace(array('á','é','í','ó','ú','ü','ñ'), array('a','e','i','o','u','u','n'), strtolower($string));
		$aux = preg_replace('([^A-Za-z0-9])', '_', strtolower($aux));
		return trim(preg_replace('(_+)', '_', $aux), '_');
	}
	
	public function path($value = NULL) {
		try {
			if ($value !== NULL) {
				if (is_dir($value)) throw new VKWarning('Given path '.$value.' is a directory');
				if (!file_exists($value)) throw new VKWarning('File '.$value.' not found');
				$this->path = $value;
			}
			return $this->path;
		}catch(VKWarning $e) {
			return FALSE;
		}
	}	
	
	public function url() {
		try {
			if ($this->path === NULL) throw new VKWarning('Path is null');
			return str_replace(DOCUMENT_ROOT, '', $this->path());
		}catch(VKWarning $e) {
			return FALSE;
		}
	}		
	
	public function exists($path = NULL) {
		try {
			if ($path !== NULL) {
				if (is_dir($path)) throw new VKWarning('Path '.$path.' is a directory');
				return file_exists($path);
			}
			if ($this->path === NULL) throw new VKWarning('Path is null');
			return file_exists($this->path);
		}catch(VKWarning $e){
			return FALSE;
		}
	}
	
	private function getExtension($path) {
		$partes = pathinfo($path);
		return strtolower($partes['extension']);
	}
	
	//Obtiene la extension de un nombre de archivo
	public function extension() {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			return $this->getExtension($this->path);
		}catch(VKWarning $e) {
			return FALSE;
		}
	}
	
	private function getDirName($path) {
		$partes = pathinfo($path);
		return $partes['dirname'];
	}
	
	//Obtiene la extension de un nombre de archivo
	public function dirName() {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			return $this->getDirName($this->path);
		}catch(VKWarning $e) {
			return FALSE;
		}
	}
	
	private function getBaseName($path) {
		$partes = pathinfo($path);
		return $partes['basename'];
	}
	
	//Obtiene la extension de un nombre de archivo
	public function baseName() {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			return $this->getBaseName($this->path);
		}catch(VKWarning $e) {
			return FALSE;
		}
	}
	
	private function getFileName($path) {
		$partes = pathinfo($path);
		return $partes['filename'];
	}
	
	//Obtiene la extension de un nombre de archivo
	public function fileName() {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			return $this->getFileName($this->path);
		}catch(VKWarning $e) {
			return FALSE;
		}
	}
	
	//Copia el archivo a otro directorio y reemplaza todos los caracteres
	//especiales por _, quita los acentos en las vocales y agrega el prefijo $prefix si se indica uno 
	public function copyTo($newDir, $prefix = '', $replace = FALSE) {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			if ($prefix == TMP_PREFIX) $prefix = $this->genTmpPrefix();
			$newPath = $newDir.'/'.$prefix.$this->cleanSpecialChars($this->fileName()).'.'.$this->extension();
			if (file_exists($newPath)) 
				if (!$replace) throw new VKWarning('Target file path '.$newPath.' already exists');
				else unlink($newPath);
			if (!copy($this->path, $newPath)) throw new VKWarning('Cant copy file to path '.$newPath);
			return $newPath;
		}catch(VKWarning $e) {
			return FALSE;
		}
	}
	
	public function moveTo($newDir, $prefix = '', $replace = FALSE) {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			if ($prefix == TMP_PREFIX) $prefix = $this->genTmpPrefix();
			$newPath = $newDir.'/'.$prefix.$this->cleanSpecialChars($this->fileName()).'.'.$this->extension();
			if (file_exists($newPath)) 
				if (!$replace) throw new VKWarning('Target file path '.$newPath.' already exists');
				else unlink($newPath);
			if (!rename($this->path, $newPath)) throw new VKWarning('Cant move file to path '.$newPath);
			$this->path = $newPath;
			return $newPath;
		}catch(VKWarning $e) {
			return FALSE;
		}
	}

	////////////////////////////////////////////////////////////////
	// Lee y devuelve el contenido del archivo
	public function read($lines = FALSE) {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			if ($lines) return file($this->path);
			if (!$fp = fopen($this->path, 'r')) throw new VKWarning('Cant open file '.$this->path.' for read');
			$content = fread($fp, $this->size());
			fclose($fp);
			return $content;
		}catch(VKWarning $e) {
			return FALSE;
		}
	}
	
	////////////////////////////////////////////////////////////////
	// Lee y devuelve el contenido del archivo
	public function write($content) {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			if (!$fp = fopen($this->path, 'w')) throw new VKWarning('Cant open file '.$this->path.' for write');
			if (!fwrite($fp, $content)) {
				fclose($fp);
				throw new VKWarning('Cant write into file '.$this->path);
			}
			fclose($fp);
			return TRUE;
		}catch(VKWarning $e) {
			return FALSE;
		}
	}	
	
	public function size() {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			return filesize($this->path);
		}catch(VKWarning $e) {
			return FALSE;
		}
	}
	
	////////////////////////////////////////////////////////////////
	// Elimina el archivo 
	public function delete() {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			if (!unlink($this->path)) throw new VKWarning('Cant delete file '.$this->path);
			return TRUE;
		}catch(VKWarning $e) {
			return FALSE;
		}		
	}	

	////////////////////////////////////////////////////////////////
	// Genera un nombre aleatorio
	protected function genTmpPrefix() {
		return time().rand(0, 99).rand(99, 199).rand(199, 299);
	}
	
}

?>