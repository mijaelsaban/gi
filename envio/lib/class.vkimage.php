<?

define('IMG_NONE', 0);
define('IMG_WIDTH', 1);
define('IMG_HEIGHT', 2);
define('IMG_FORCE', 3);
define('IMG_LIMIT', 4);
define('IMG_CUT', 5);


//***************************************************//
// Clase Imagen

class VKImage extends VKFile {

	private $info = NULL;

	public function VKImage($path) {
		parent::VKFile($path);
		$this->getInfo();
	}
		
	private function getInfo() {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			if (!$this->info = getimagesize($this->path)) throw new VKWarning('Cant get image info');
		}catch(VKWarning $e){
			return FALSE;
		}
	}
	
	public function info() {
		try {
			$info = (object) array();
			if (!$this->exists()) throw new VKWarning('File not exists');
			if ($this->info === NULL) throw new VKWarning('Unknown file info');
			$info->path = $this->path;
			$info->url = $this->url();
			$info->width = $this->width();
			$info->height = $this->height();
			$info->dimensions = $this->dimensions();
			return $info;
		}catch(VKWarning $e) {
			return FALSE;
		}
	}
	
	public function width() {
		return $this->info[0];
	}
	
	public function height() {
		return $this->info[1];
	}
	
	public function imageType() {
		return $this->info[2];
	}
	
	public function dimensions() {
		return $this->info[3];
	}
	
	public function bits() {
		return $this->info['bits'];
	}
	
	public function channels() {
		return $this->info['channels'];
	}
	
	public function mime() {
		return $this->info['mime'];
	}
	
	//Copia la imagen a una nueva ubicacion. En el proceso se puede indicar su nuevo tamaño.
	//Mantiene acitva la imagen original
	public function copyTo($dir, $prefix = '', $replace = FALSE, $criterion = IMG_NONE, $newWidth = 0, $newHeight = 0, $quality = 9) {
		if (!$newPath = parent::copyTo($dir, $prefix, $replace)) return FALSE;
		if ($criterion != IMG_NONE) {
			$imgaux = new VKImage($newPath);
			if (!$imgaux->resize($newWidth, $newHeight, $criterion, $quality)) return FALSE;
		}
		return $newPath;
	}
	
	//Mueve la imagen a la nueva ubicación y cambia el path activo por el nuevo si tiene éxito.
	//En el proceso se puede indicar su nuevo tamaño.
	//Notese que si al moverla se cambia de tamaño no se podrá recuperar el tamaño original.
	public function moveTo($dir, $prefix = '', $replace = FALSE, $criterion = IMG_NONE, $newWidth = 0, $newHeight = 0, $quality = 9) {
		if (!parent::moveTo($dir, $prefix, $replace)) return FALSE;
		if ($criterion != IMG_NONE)	$this->resize($newWidth, $newHeight, $criterion, $quality);
		return $this->path;
	}
	
	//Calcula las nuevas dimensiones para la imagen
	private function calculateDimensions($dim, $newWidth, $newHeight, $criterion) {
		switch ($criterion) {
			case IMG_WIDTH: 
				if ($dim->width > $newWidth) {
					$porcentaje = $newWidth * 100 / $dim->width;
					$dim->height = (int) ($dim->height * $porcentaje / 100);
					$dim->width = $newWidth;
				}
			break;
			case IMG_HEIGHT: 
				if ($dim->height > $newHeight) {
					$porcentaje = $newHeight * 100 / $dim->height;
					$dim->width = (int)($dim->width * $porcentaje / 100);
					$dim->height = $newHeight;
				}
			break;
			case IMG_FORCE: 
				$dim->width = $newWidth;
				$dim->height = $newHeight;			
			break;
			case IMG_LIMIT:
				$scale_width = $newWidth / $dim->width;
				$scale_height = $newHeight / $dim->height;
				$criterion = ($scale_width <= $scale_height)? IMG_WIDTH : IMG_HEIGHT;
				$dim = $this->calculateDimensions($dim, $newWidth, $newHeight, $criterion);
			break;
			case IMG_CUT: 
				$scale_width = $newWidth / $dim->width;
				$scale_height = $newHeight / $dim->height;
				$criterion = ($scale_width > $scale_height)? IMG_WIDTH : IMG_HEIGHT;
				$dim = $this->calculateDimensions($dim, $newWidth, $newHeight, $criterion);			
				if ($criterion == IMG_WIDTH) {
					if ($dim->height > $newHeight) {
						$dY = ($dim->height - $newHeight) / $dim->height * $dim->srcHeight;
						$dim->srcY = (int) ($dY / 2);
						$dim->srcHeight = (int) ($dim->srcHeight - $dY);
						$dim->height = $newHeight;
					}
				}else{
					trace($dim, 'dim');
					trace($dim, $newWidth);
					if ($dim->width > $newWidth) {
						$dX = ($dim->width - $newWidth) / $dim->width * $dim->srcWidth;
						$dim->srcX = (int) ($dX / 2);
						$dim->srcWidth = (int) ($dim->srcWidth - $dX);
						$dim->width = $newWidth;
					}
				}	
			break;
			default : 
			break;		
		}	
		return $dim;
	}
	
	//Cambia el tamaño a la imágen activa.
	//OJO. Reemplaza la imagen por la de nuevas dimensiones.
	public function resize($newWidth, $newHeight, $criterion, $quality) {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			$dim = (object) array();
			$dim->srcX = 0;
			$dim->srcY = 0;
			$dim->srcWidth = $this->width();
			$dim->srcHeight = $this->height();
			$dim->width = $this->width();
			$dim->height = $this->height();
			$dim = $this->calculateDimensions($dim, $newWidth, $newHeight, $criterion);
			if (($dim->srcWidth == $dim->width) && ($dim->srcHeight == $dim->height)) return TRUE;	
			if (!$target = imagecreatetruecolor($dim->width, $dim->height)) throw new VKWarning('Cant create true color image of '.$dim->width.' width x '.$dim->height.' height');
			trace($target);
			switch ($this->extension()) {
				case 'jpg':
				case 'jpeg':
					if (!$source = imagecreatefromjpeg($this->path)) throw new VKWarning('Cant create jpg image '.$this->path);
					$func_write_image = 'imagejpeg';
					$image_quality = $quality*10;
				break;
				case 'gif':
					/*if (!imagecolortransparent($target, imagecolorallocate($target, 0, 0, 0))) throw new VKWarning('Cant set gif params for new image');*/
					if (!$source = imagecreatefromgif($this->path)) throw new VKWarning('Cant create gif image '.$this->path);
					$func_write_image = 'imagegif';
					$image_quality = null;
				break;
				case 'png':
					//if (!imagecolortransparent($target, imagecolorallocate($target, 0, 0, 0))) throw new VKWarning('Cant set png params for new image');
					if (!imagealphablending($target, false)) throw new VKWarning('Cant set png params for new image');
					if (!imagesavealpha($target, true)) throw new VKWarning('Cant set png params for new image');
					if (!$source = imagecreatefrompng($this->path)) throw new VKWarning('Cant create png image '.$this->path);
					$func_write_image = 'imagepng';
					$image_quality = $quality;
				break;
				default:
					$source = null;
			}
			if (!imagecopyresampled($target, $source, 0, 0, $dim->srcX, $dim->srcY,
									$dim->width, $dim->height, $dim->srcWidth, $dim->srcHeight))
				throw new VKWarning('Cant copy image data from source to target');
		 	if (!$func_write_image($target, $this->path, $image_quality)) throw new VKWarning('Cant write target data to file '.$this->path);
			@imagedestroy($source);
			@imagedestroy($target);
			return TRUE;
		}catch(VKWarning $e) {
			return FALSE;
		}
	}
	
	//Copia la imagen a la nueva ubicación, cambia su tamaño según $criterion. 
	//Mantiene activo el path original y devuelve un objeto con path, width y height de la nueva imágen.
	public function thumbnail($dir, $prefix, $criterion, $width, $height, $quality = 9) {
		try {
			if (!$this->exists()) throw new VKWarning('File not exists');
			$createThumb = TRUE;
			$newPath = $dir.'/'.$prefix.$this->baseName();
			//Si el prefijo es temporal creo la copia con el nuevo tamaño.
			if (($prefix == TMP_PREFIX) || !file_exists($newPath) || (filectime($newPath) <= filectime($this->path))) {
				if (!$newPath = $this->copyTo($dir, $prefix, TRUE, $criterion, $width, $height, $quality))
					throw new VKWarning('File copy error');
			}
			
			$imgaux = new VKImage($newPath);
			$auxobj = (object) array();
			$auxobj->path = $imgaux->path();
			$auxobj->url = $imgaux->url();
			$auxobj->width = $imgaux->width();
			$auxobj->height = $imgaux->height();
			$auxobj->dimensions = $imgaux->dimensions();
			return $auxobj;
		}catch(VKWarning $e) {
			return FALSE;
		}
	}

/////////////////////////////////////////////////////////
} //Fin de la clase


?>