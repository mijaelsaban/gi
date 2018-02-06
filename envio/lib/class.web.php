<?

class Web {	
	
	const DEFAULT_LANG = 'es';
	
	private $title = '';
	private $response = NULL;
	private $urlBase = NULL;
	private $langsFolder = '';
	private $lang = NULL;
	private $activelang = NULL;
	
	private $meses;
	
	public function Web($title = NULL, $urlBase = NULL, $langsFolder = NULL) {
		try {
			if (LOG_ERRORS) set_error_handler('myErrorHandler', E_ALL|E_STRICT);
			if ($title === NULL) 
				throw new VKException('Web Title not found');
			$this->title = $title;
			if ($langsFolder !== NULL) {
				$this->langsFolder = $langsFolder;
			}
			if ($urlBase !== NULL) {
				$this->urlBase = $urlBase;
			}
			$fileLang = $this->langsFolder.'/'.$this->getVarchar('lang', self::DEFAULT_LANG).'.xml';
			$this->activelang = $this->getVarchar('lang', self::DEFAULT_LANG);
			if (file_exists($fileLang)) {
				$this->lang = new SimpleXMLElement($fileLang, NULL, TRUE);
			}
			$this->meses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 
									'septiembre', 'octubre', 'noviembre', 'diciembre');
		}catch(VKException $e) {
			$this->response($e);
		}
	}
	
	public function title($title = NULL) {
		if ($title !== NULL) {
			$this->title = $title;
		}
		return $this->title;
	}
	
	public function urlBase() {
		if ($this->urlBase) return $this->urlBase;
		return '';	
	}
	
	public function lang() {
		if ($this->lang === NULL) {
			throw new VKException('NO HAY ARCHIVO DE IDIOMAS DEFINIDO');
		}
		return $this->lang;
	}
	
	public function activelang() {
		return $this->activelang;
	}
	
	public function response($value = NULL) {
		if ($value !== NULL)
			$this->response = $value;
		return $this->response;
	}
	
	public function getParamArray($nombreparametro, $valorpordefecto = NULL) {
		try {
			if (!isset($_POST[$nombreparametro]) || !is_array($_POST[$nombreparametro])) 
				throw new VKWarning('Array param name not found');
		} catch(VKWarning $e) { return array(); }
		$arr = $_POST[$nombreparametro];
		$count = count($arr);
		for($i=0; $i<$count; $i++) {
			$arr[$i] = stripslashes($arr[$i]);
		}
		return $arr;		
	}
	
	public function getParam($nombreparametro, $valorpordefecto = NULL, $checkType = TRUE) {	
		try {
			if ($checkType) throw new VKWarning('Using WEB::getParam instead type based function'); 
		} catch(VKWarning $w) { }
		if (isset($_GET[$nombreparametro]) && !((string) $_GET[$nombreparametro] == '')) {
			return stripslashes($_GET[$nombreparametro]);
		}else{
			if (isset($_POST[$nombreparametro]) && !((string) $_POST[$nombreparametro] == '')) {
				return stripslashes($_POST[$nombreparametro]);
			}
		}	
		try {
			if ($checkType) throw new VKWarning('Param not found'); 
		} catch(VKWarning $w) { }
		return $valorpordefecto;			
	}
	
	public function getInt($nombreparametro, $valorpordefecto = NULL) {
		$val = $this->getParam($nombreparametro, $valorpordefecto, FALSE);
		if (($val == NULL) || ($val === FALSE)) return $val;
		return (int) $val;
	}
	
	public function getVarchar($nombreparametro, $valorpordefecto = NULL) {
		$val = $this->getParam($nombreparametro, $valorpordefecto, FALSE);
		if (($val == NULL) || ($val === FALSE)) return $val;
		return (string) $val;
	}
	
	////////////////////////////////////////////////////////////////
	// Elimina los archivos en la carpeta indicada cuando tienen mas de $segundos de antiguedad
	function clearTemp($path, $segundos) {
		
		if (!$dir = opendir($path))
			throw new VKWarning('Can\'t read directory');
		$archivos = array();
		while ($elemento = readdir($dir)) {
			if (($elemento != '.') && ($elemento != '..')) {
				if (!$creado = filectime($path.'/'.$elemento))
					throw new VKWarning('Can\'t get file information'.$path.'/'.$elemento);
				$segundosdevida = time() - $creado;
				if ($segundosdevida >= $segundos) {
					if (is_dir($path.'/'.$elemento)) {
						$this->eliminarTemporales($path.'/'.$elemento, $segundos);
						if (!rmdir($path.'/'.$elemento)) 
							throw new VKWarning('Can\'t delete directory '.$path.'/'.$elemento);
					}else{
						if (!unlink($path.'/'.$elemento)) 
							throw new VKWarning('Can\'t delete temporary file '.$path.'/'.$elemento);
					}
				}		
			}
		}
		closedir($dir);
	}
	
	public function makeFUrl($string) {
		$aux = str_replace(array('á','é','í','ó','ú','ü','ñ'), array('a','e','i','o','u','u','n'), strtolower($string));
		$aux = preg_replace('([^A-Za-z0-9])', '_', strtolower($aux));
		return trim(preg_replace('(_+)', '-', $aux), '-');
	}
	
	public function getClientIP() {
		return $_SERVER['REMOTE_ADDR'];
	}
	
	public function makeUrl($path) {
		return str_replace(DOCUMENT_ROOT, '', $path);
	}		
	
	//Convierte una fecha en formato dd/mm/yyyy o en formato inglés mm/dd/yyy
//a formato yyyy-mm-dd (ISO)
//OJO CON EL FORMATO INGLES: si la fecha que viene como parámetro está en el formato mm/dd/YY se debe
//indicar $englishFormat en TRUE o de lo contrario se traducirá a una fecha incorrecta.
	public function toISODate($fecha, $englishFormat = FALSE) {
		if (!empty($fecha)) {
			if (!$englishFormat) {
				$fecha = explode('/', $fecha);
				$fecha = $fecha[2].'/'.$fecha[1].'/'.$fecha[0];
			}
			return date('Y-m-d', strtotime($fecha));
		}else{
			return date('Y-m-d');
		}
	}
	
	//Convierte una fecha en formato yyyy-mm-dd (ISO) o en formato mm/dd/yyy (inglés)
	//a formato español dd/mm/yyyy
	public function toESDate($fecha) {
		return date('d/m/Y', strtotime($fecha));
	}
	
	public function toEnglishDate($fecha) {
		return date('m/d/Y', strtotime($fecha));
	}
	
	public function zerofill($entero, $largo) {
   		$relleno = ''; 
		$diferencia = $largo - strlen($entero);    
    	if ($diferencia > 0) {
        	$relleno = str_repeat('0', $diferencia);
    	}
    	return $relleno.$entero;
	}
	
	public function nombreMes($mes) {
		return $this->meses[$mes-1];
	}
	
	public function httpReferer() {
		if (isset($_SESSION['FORCED_HTTP_REFERER'])) {
			$referer = $_SESSION['FORCED_HTTP_REFERER'];
			unset($_SESSION['FORCED_HTTP_REFERER']);
		}else {
			if (isset($_SERVER['HTTP_REFERER'])) 
				$referer = $_SERVER['HTTP_REFERER'];
			else $referer = $this->urlBase();
		}
		$_SESSION['HTTP_REFERER'] = $referer;
		return $referer;
	}
	
	public function forceHttpReferer($url) {
		$_SESSION['FORCED_HTTP_REFERER'] = $url;
	}
	
}// Fin clase

?>