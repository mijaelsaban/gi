<?

class MailSender extends AppObject {

	public $sended = FALSE;
	public $attempt = FALSE;
	//Para manipular los datos del formulario
	private $params = array();
	
	private $pluginDir = '';
	private $host = 'localhost';
	private $user = NULL;
	private $pass = NULL;
	private $ssl = FALSE;
	private $por = 25;
	
	private $from = NULL;
	private $fromname = NULL;
	private $to = NULL;
	private $cco = NULL;
	private $replyto = NULL;
	private $subject = NULL;
	private $body = NULL;
	private $altbody = NULL;
	private $atachments = NULL;
	
	
	public function MailSender($includePath = '', $host = 'localhost', $user = NULL, $pass = NULL) {
		parent::AppObject();
		$this->includePath = DOCUMENT_ROOT.'lib';
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
	}
	
	public function clean() {
		$this->params = array();	
	}
	
	public function subject($subject) {
		$this->subject = $subject;
	}
	
	public function body($filename) {
		if (!file_exists($filename)) throw new Exception('Body file not found');
		$this->body = file_get_contents($filename);
	}
	
	public function from($from) {
		$this->from = $from;
	}
	
	public function fromName($fromName) {
		$this->fromname = $fromName;
	}
	
	public function to($to) {
		$this->to = $to;
	}
	
	public function cco($cco) {
		$this->cco = $cco;
	}
	
	public function replyto($replyto) {
		$this->replyto = $replyto;
	}
	
	public function ssl($value = NULL) {
		if ($value !== NULL) {
			$this->ssl = $value;
		}
		return $this->ssl;
	}
	
	public function port($value = NULL) {
		if ($value !== NULL) {
			$this->port = $value;
		}
		return $this->port;
	}
	
	public function readInput($paramName, $defaultValue) {
		$value = $this->web->getVarchar($paramName, $defaultValue);
		$this->set($paramName, $value);	
	}
	
	public function set($paramName, $value) {
		$this->params[$paramName] = $value;	
	}
	
	public function get($paramName) {
		if (isset($this->params[$paramName]))
			return $this->params[$paramName];
	}
	
	public function send($cleanAfter = TRUE) {
		$this->loadParams();
		if ($this->host == 'localhost') {
			$this->sended = $this->sendViaLocal();
		}else{
			$this->sended = $this->sendViaSMTP();
		}
		if (!$this->sended) throw new Exception('Cant send message');
		return TRUE;
	}
	
	public function errors() {
		return $this->errors;
	}
	
	public function checkEmail($email) {
		if(!preg_match("/^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$/", $email)) return FALSE;
		return TRUE;
	}
	
	/**************************************************************************************/
	/*
	/* METODOS PRIVADOS
	/*
	/**************************************************************************************/
	
	private function loadParams() {
		if (!$this->body) throw new Exception('Body param not found');
        foreach($this->params as $key => $value)
			$this->body = str_replace('(MP:'.$key.')', $value, $this->body);	
	}
	
	//Envia usando la clase phpMailer
	private function sendViaSMTP() {		
		include_once($this->pluginDir.'class.phpmailer.php');
		$mail = new PHPMailer();
		$mail->SMTPDebug = 1;
		$mail->PluginDir = $this->pluginDir;
		$mail->Port = $this->port;
		$mail->IsSMTP(); // telling the class to use SMTP
		if ($this->ssl) {
			$mail->SMTPSecure = "ssl";
		}
		$mail->SMTPAuth = true; // turn on SMTP authentication
		$mail->SMTPKeepAlive = true;
		$mail->ContentType = ("text/html; charset=utf-8\n");
		$mail->CharSet = "utf-8"; 
		$mail->Host = $this->host; // SMTP server
		$mail->Username = $this->user;
		$mail->Password = $this->pass;	
		$mail->From = $this->from;
		$mail->FromName = $this->fromname;
		$mail->Subject = $this->subject;
		$mail->AddAddress($this->to);
		//Convertimos cco a un array
		if ($this->cco != NULL) {
			$this->cco = str_replace(' ', '', $this->cco);
			$this->cco = explode(',', $this->cco);
			if (is_array($this->cco) && (count($this->cco) > 0)) 
				foreach($this->cco as $key => $value) 
					$mail->AddBCC($value);
		}
		$mail->AddReplyTo($this->replyto);
		$mail->IsHTML(true); 	
		$mail->MsgHTML($this->body);
		$mail->AltBody = 'Enable rich text for correct view of this message';					  
		if (is_array($this->atachments) && (count($this->atachments) > 0)) 
			foreach($this->atachments as $key => $value) 
				$mail->AddAttachment($value);
		$intentos = 0;
		$sended = FALSE;
		while (!$sended && ($intentos < 3)) {
			$sended = $mail->Send();
			$intentos++;
		}
		return $sended;
	}
	
	private function sendViaLocal() {
		//Confertimos cco a un array
		if ($this->cco != NULL) {
			$this->cco = str_replace(' ', '', $this->cco);
			$this->cco = explode(',', $this->cco);
		}		
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8\r\n";
		$headers .= "From: ".$this->fromname." <".$this->from.">\r\n";
		$headers .= "Reply-To: ".$this->replyto."\r\n";
		if (is_array($this->cco) && (count($this->cco) > 0)) {
			$headers .= "Bcc: ";
			foreach($this->cco as $key => $value) {
				$headers .= $value . ", ";
			}
			$headers = rtrim($headers, ', ');
			$headers .= "\r\n";
		}
		return mail($this->to, $this->subject, $this->body, $headers);
	}
	
}

?>