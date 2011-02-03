<?php
class User extends IDKModel {
	
	/**
	 * @Persist
	 */
	public $email;
	/**
	 * @Persist
	 */
	public $password;
	/**
	 * @Persist
	 */
	public $ckey;
	/**
	 * @Persist
	 */
	public $ctime;
	/**
    * @OneToOne(class="group")
    */
	public $group;
	
	
	public function hasPermission($permission) {
		foreach($this->group->permissions as $perm) {
			if($perm->title == $permission) {
				return true;
			}
		}
		return false;
	}
	
	private static function clearData() {
		/************ Delete the sessions****************/
		unset($_SESSION['user_id']);
		unset($_SESSION['user_name']);
		unset($_SESSION['user_level']);
		unset($_SESSION['HTTP_USER_AGENT']);
		session_unset();
		session_destroy(); 
		
		/* Delete the cookies*******************/
		setcookie("user_id", '', time()-60*60*24*10, "/");
		setcookie("user_name", '', time()-60*60*24*10, "/");
		setcookie("user_key", '', time()-60*60*24*10, "/");
		
	}
	
	public function logout() {
		 self::clearData();

		$this->ckey = '';
		$this->ctime = '';
	}
	
	private static $authedUser = null;
	
	public static function getAuthenticatedUser() {
		
		if(self::$authedUser != null) {
			return self::$authedUser;
		}
		
		if (isset($_SESSION['HTTP_USER_AGENT'])) {
			if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'])) {
				self::clearData();
		        exit;
		    }
		}
		// before we allow sessions, we need to check authentication key - ckey and ctime stored in database
	
		/* If session not set, check for cookies set by Remember me */
		if (!isset($_SESSION['user_id'])) {
			
			if(isset($_COOKIE['user_id']) && isset($_COOKIE['user_key'])){
				/* we double check cookie expiry time against stored in database */

				$user = new User($_COOKIE['user_id']);
				
				if( (time() - $user->ctime) > 60*60*24*10) {
					self::clearData();
					return null;
				}
				/* Security check with untrusted cookies - dont trust value stored in cookie. 		
				/* We also do authentication check of the `ckey` stored in cookie matches that stored in database during login*/
		
				if( !empty($user->ckey) && is_numeric($_COOKIE['user_id']) && $_COOKIE['user_key'] == sha1($user->ckey)  ) {
					session_regenerate_id(); //against session fixation attacks.
				
					$_SESSION['user_id'] = $_COOKIE['user_id'];
					$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
					self::$authedUser = $user;
					return $user;
				} 
				else {
				   self::clearData();
				   return null;
				}
			}
		}
		else {
			self::$authedUser = new User($_SESSION['user_id']);
			return self::$authedUser;
		}
		return null;
		
	}
	public static function login($email, $password, $rememberMe) {

		$data = self::find("email = :email AND password = :pass limit 1", array('email' => strtolower($email), 'pass' => $password));
		if (count($data) == 1) { 
			
			$user = $data[0];
			
			session_regenerate_id (true); //prevent against session fixation attacks.
	
		   // this sets variables in the session 
			$_SESSION['user_id']= $user->getId();  
			$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
			
			//update the timestamp and key for cookie
			$stamp = time();
			$ckey = self::genKey();
			$user->ctime = $stamp;
			$user->ckey = $ckey;
			$user->store();
			//set a cookie 
			
		   if($rememberMe) {
				setcookie("user_id", $_SESSION['user_id'], time()+60*60*24*10, "/");
				setcookie("user_key", sha1($ckey), time()+60*60*24*10, "/");
			}
			return $user;
		}
		return null;
	}

	public static function genKey($length = 7) {
		$password = "";
		$possible = "0123456789abcdefghijkmnopqrstuvwxyz"; 
		$i = 0; 
	    
		while ($i < $length) { 
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
			if (!strstr($password, $char)) { 
				$password .= $char;
				$i++;
	    	}
		}
		return $password;
	}
}