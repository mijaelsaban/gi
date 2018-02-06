<?

//////////////////////////////////////////////////////////
// Clase 
/////////////////////////////////////////////////////////

class ControlUsers extends Control {

	protected function setup() {
		//
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	//Inicia sesión para el usuario y contraseña dados
	public function login() {
		try {
			$email = $this->web->getVarchar('email', '');
			$password = $this->web->getVarchar('password', '');
			//Tratar de logear con ese usuario y esa pass
			if (($email !== NULL) && ($email != '')) {
				$this->session->remove('USER_ID');
				$this->session->remove('USER_EMAIL');
				$this->session->remove('USER_NAME');
				$this->session->remove('USER_LASTNAME');
				$this->session->remove('USER_ROLES');
				$query = new Query($this->db);
				$query->param('email', $email, 'varchar');
				if (!$query->run('SELECT * FROM users  
									WHERE (email = #email#) AND (active = 1)')) {
					throw new VKException('Wrong email address or password. '.$this->dbError(), 1);
				}
				if (!$query->fetchRow()) {
					throw new VKException('Wrong email address or password. '.$this->dbError(), 2);
				}
				if (md5($password) != $query->row()->password) {
					throw new VKException('Wrong email address or password. '.$this->dbError(), 3);
				}
				$idusuario = $query->row()->id;
				$emailusuario = $query->row()->email;
				$nameusuario = $query->row()->name;
				$lastnameusuario = $query->row()->lastname;
				$sql = 'SELECT * FROM user_roles WHERE id = '.$idusuario;
				if (!$aux = $this->queryList($sql)) 
					throw new VKException('Error en login: '.$this->dbError(), 1);
				if (!count($aux))
					throw new VKException('Unidentified user.', 1);
				$roles = array();
				foreach($aux as $value) 
					$roles[] = $value->role;
				$this->session->set('USER_ID', $idusuario);
				$this->session->set('USER_EMAIL', $emailusuario);
				$this->session->set('USER_NAME', $nameusuario);
				$this->session->set('USER_LASTNAME', $lastnameusuario);
				$this->session->set('USER_ROLES', $roles);
				return TRUE;	
			}
			throw new VKException('Wrong email address or password', 1);
		} catch (VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	public function validateUser() {
		return ($this->session->exists('USER_ID') && $this->session->exists('USER_EMAIL'));
	}
	
	protected function userData() {
		$userData = (object) array();
		$userData->logged = FALSE;	
		return $userData;
	}
	
	public function userExists($email) {
		return $this->exists(new Tuple('users', 'email', $email));
	}
	
	public function getRoles($idusuario) {
		$sql = 'SELECT * FROM user_roles WHERE id = '.$idusuario;
		if ($roles = $this->queryList($sql)) {
			$aux = array();
			foreach($roles as $value)
				$aux[] = $value->role;
			return $aux;
		}
		return FALSE;
	}
		
	private function checkEmail($email) {
		if(!preg_match("/^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$/", $email)) return FALSE;
		return TRUE;
	}
	
	public function getFromEmail($email) {
		return $this->get(new Tuple('users', 'email', $email));
	}
	
	public function startPasswordRecovery() {
		try {	
			if (!$user = $this->getFromEmail($this->web->getVarchar('email', ''))) throw $this->exception;
			$codpwd = '';
			if ($user->active) {
				$query = new Query($this->db);
				$query->param('email', $this->web->getVarchar('email', ''), 'varchar');
				$codpwd = $this->genCodReg().'9';
				$query->param('codpwd', $codpwd, 'varchar');
				if (!$query->run('UPDATE users SET codpwd = #codpwd# WHERE email = #email#'))
					throw new VKException('Cant Init Proccess', 1);
			}
			$user->codpwd = $codpwd;
			return $user;
		} catch (VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	public function getFromSafeCode($safecode) {
		try {	
			$query = new Query($this->db);
			$query->param('safecode', $safecode, 'varchar');
			if (!$query->run('SELECT * FROM users WHERE codpwd = #safecode#') || 
				!$query->fetchRow()) {
				throw new VKException('User not found', 1);
			}
			return $query->row();
		} catch (VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	public function changePassword($safecode) {
		try {
			if (!$user = $this->getFromSafeCode($safecode)) {
				throw new VKException('The security code is incorrect.', 1);
			}
			$aux = new Tuple('users');
			$aux->readInput('password', '');
			$rpassword = $this->web->getVarchar('rpassword', '');
			if ($aux->get('password') != $rpassword) {
				throw new VKException('The entered passwords do not match', 2);
			}
			$aux->set('password', md5($rpassword));
			$aux->set('codpwd', '');
			$aux->set('id', $user->id, TRUE);
			if (!$this->modify($aux)) {
				throw new VKException('Error at change password. ', 3);
			}
			return TRUE;
		} catch (VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	public function signup($aRole) {
		try {
			$user = new Tuple('users');
			$user->readInput('email', '');
			$user->readInput('name', '');
			$user->readInput('lastname', '');
			$user->readInput('password', '');
			$user->set('registerdate', date('Y-m-d'));
			$rpassword = $this->web->getVarchar('rpassword', '');
			$user->set('active', 1);
			$user->set('email', trim($user->get('email')));
			if ($user->get('email') == '') 
				throw new VKException('Invalid email address', 1);
			if ($this->userExists($user->get('email')))
				throw new VKException('Email address already exists.', 2);	
			if ($user->get('password') != $rpassword)
				throw new VKException('Passwords do not match.', 3);
			$user->set('password', md5($rpassword));
			if (!$this->insert($user))
				throw new VKException('Error at signup: '.$this->dbError(), 3);
			$user->set('id', $this->insertId());
			$this->setRole($user->get('id'), $aRole);
			return (object) $user->fields();
		} catch (VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	private function setRole($rowId, $idRole) {
		$role = new Tuple('user_roles', 'id', $rowId);
		$role->set('role', $idRole);
		if (!$this->insert($role)) 
			throw new VKException('No se puedo actualizar el rol del usuario: '.$this->dbError());
	}
	
	public function confirm() {
		try {
			$query = new Query($this->db);
			$query->param('codreg', $this->web->getVarchar('codreg', ''));
			if (!$query->run('select * from users where codreg like #codreg# and codreg <> ""')) 
				throw new VKException('Can\'t confirm', 1);
			if (!$query->fetchRow())
				throw new VKException('Code not found', 2);
			$row = $query->row();
			if (!$query->run('update users set active = 1 where codreg = #codreg#'))
				throw new VKException('Can\'t confirm', 3);
			$this->session->set('USER_ID', $row->id);
			return $row; 
		} catch (VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	//Cierra sesión eliminando el id y nombre del usuario en linea
	public function logout() {
		$this->session->clean();
	}
	
	//Genera un código de registro aleatorio para completar el proceso de registro
	public function genCodReg() {
		$max = 1;
		$query = new Query($this->db);
		if ($query->run('select max(id) as count from users')) {
			if ($query->fetchRow()) {
				$max = $query->row()->count + 1;
			}
		}
		$code = rand(100, 999) . $max . rand(100, 999);
		return $code;
	}
	
	/////////////////////////////////////////////////////////////////////
	public function getForNew() {
		$respuesta = array();
		$respuesta['id'] = 0;
		$respuesta['email'] = '';
		$respuesta['password'] = '';
		$respuesta['name'] = '';
		$respuesta['lastname'] = '';
		$respuesta['address1'] = '';
		$respuesta['address2'] = '';
		$respuesta['telephone'] = '';
		$respuesta['active'] = 0;
		return (object) $respuesta; 
	}
	
	/////////////////////////////////////////////////////////////////////
	public function getForEdit() {
		return $this->get(new Tuple('users', 'id', 0, TRUE));
	}
	
	/////////////////////////////////////////////////////////////////////
	public function save() {
		try {
			$id = $this->web->getInt('id', 0);
			$user = new Tuple('users');
			$user->readInput('email', '');			
			$user->readInput('name', NULL);
			$user->readInput('lastname', NULL);			
			$user->readInput('active', 0);
			if ($id) {
				$user->set('id', $id, TRUE);
				if (!$this->modify($user)) 
					throw new VKException('Can\'t modify. '.$this->dbError());
			}else{
				if (trim($user->get('email')) == '')
					throw new VKException('Enter email address. Code 1');
				if ($this->exists(new Tuple('users', 'email', $user->get('email')))) 
					throw new VKException('Email address already exists');
				$user->set('password', md5('xxx'));
				$user->set('registerdate', date('Y-m-d'));
				if (!$this->insert($user)) 
					throw new VKException('Can\'t insert user');
				$id = $this->insertId();
			} 
			return $id;
		} catch (VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	/////////////////////////////////////////////////////////////////////
	protected function savepass() {
		try {
			$id = (int) $this->web->getVarchar('id', 0);
			$user = new Tuple('users', 'id', $id);
			$password = $this->web->getVarchar('password', '');
			if (!$password || ($password == '')) throw new VKException('Entre the new password');
			$password = md5($password);
			$rpassword = md5($this->web->getVarchar('rpassword', ''));
			if ($password != $rpassword) throw new VKException('Entered passwords do not match');
			$user->set('password', $password);
			if (!$this->modify($user)) throw new VKException('Can\'t modify user. '.$this->dbError());
			return $id;
		} catch (VKException $e) {
			return $this->exception($e)->result();
		}
	}
	
	/////////////////////////////////////////////////////////////////////
	public function deleteRow() {
		return $this->delete(new Tuple('users', 'id', 0, TRUE));
	}
	
	/////////////////////////////////////////////////////////////////////
	public function simpleList() {
		return $this->queryList('SELECT * FROM users ORDER BY email');
	}
	
	
} // Fin Clase

?>