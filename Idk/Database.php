<?php
namespace Idk;

class Database {
	/**
	 * Instnace of the Database Class
	 * @var unknown_type
	 */
	private static $instance = null;
	/**
	 * The ORM class
	 */
	public $orm;
	/**
	 * Returns the Database class instance
	 */
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new Database();
		}
		return self::$instance;
	}
	
	private function __construct() {
		
		$driver = new \Idk\Orm\Drivers\RedBean_Driver_Mysql();
		$queryWriter = new \Idk\Orm\QueryWriters\RedBean_QueryWriterMysql($driver);
		
		$this->orm = new \Idk\Orm\Orm($driver, $queryWriter);
	}
	
}

