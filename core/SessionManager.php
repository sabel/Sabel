<?php

/**
 * セッションを管理する
 *
 *
 */
interface SessionManageService
{
	public function add($key, $val);
	public function contains($key);
	public function get($key);
	public function remove($key);
	public function save();
}

/**
 * SessionManagerServiceの実装クラス
 *
 */
class SessionManager implements SessionManageService
{
	private $container;
	
	protected function __construct() {
		$this->container = array();
	}

	protected function setContainer($container)
	{
		if (is_array($container)) {
			$this->container = $container;
		}
	}
	
	public function add($key, $val) {
		$this->container[$key] = $val;
		$this->save();
	}
	
	public function contains($key) {
		if (isset($this->container[$key])) {
			return true;
		}else{
			return false;
		}
	}
	
	public function get($key) {
		return $this->container[$key];
	}

	public function countUp()
	{
		$this->container['counter']++;
		$this->save();
	}
	
	public static function makeInstance()
	{
		if (isset($_SESSION['SESM_CONTAINER'])) {
			$sesm = new SessionManager();
			$sesm->setContainer($_SESSION['SESM_CONTAINER']);
			return $sesm;
		}else{
			$sesm = new SessionManager();
			$sesm->add('counter', 0);
			return $sesm;
		}
	}
	
	public function remove($key) {
		unset($this->container[$key]);
		$this->save();
	}
	
	public function save() {
		$_SESSION['SESM_CONTAINER'] = $this->container;
	}

	public function printing()
	{
		print_r($this);
	}
}

?>
