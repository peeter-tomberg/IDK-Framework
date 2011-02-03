<?php
/**
 * PDO Driver
 * @file			RedBean/PDO.php
 * @description		PDO Driver
 *					This Driver implements the RedBean Driver API
 * @author			Desfrenes
 * @license			BSD
 *
 *
 * (c) Desfrenes & Gabor de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 *
 */
namespace Idk\ORM\Drivers;

use \mysqli;

class RedBean_Driver_Mysql implements \Idk\Orm\Interfaces\RedBean_Driver {
	/**
	 *
	 * @var unknown_type
	 */
	private static $instance;
	/**
	 *
	 * @var unknown_type
	 */
	private $mysql;
	/**
	 * Determines if there is a database connection already
	 */	
	private $isConnected = false;
	
	private $affected_rows = 0;
	/**
	 * Returns an instance of the MySql Driver.
	 */
	public static function getInstance() {
		if(is_null(self::$instance)) {
			self::$instance = new RedBean_Driver_Mysql();
		}
		return self::$instance;
	}
	/**
	 * Starts a database connection
	 */
	public function connect() {

		if ($this->isConnected) return;
		
		$this->mysql = new mysqli(
				  \AppConfig\DatabaseConfig::$database_host,
				  \AppConfig\DatabaseConfig::$database_username,
				  \AppConfig\DatabaseConfig::$database_password,
				  \AppConfig\DatabaseConfig::$database_database
		);
		
		/* check connection */
		if (mysqli_connect_errno()) {
		    exit("Database connect failed: ". mysqli_connect_error());
		}
		$this->isConnected = true;
	}

	/**
	 * Returns all of the rows
	 * @param unknown_type $sql - the SQL statement
	 * @param unknown_type $values - array of data to add
	 * @throws Exception - When the prepared statement fails
	 */
	public function getAll( $sql, $values = array()) {
		$this->connect();
		try {
			$stmt = $this->mysql->prepare($sql);
			if($stmt) {
				if(is_array($values) && count($values) > 0) {					
					$bindingString = '';
					$bindingValues = array();
					foreach($values as $value) {
						if(is_numeric($value)) {
							$bindingString .= 'd';
						}
						else {
							$bindingString .= 's';
						}
						$bindingValues[] = $value;
					}
					
					array_unshift($bindingValues, $bindingString);
					call_user_func_array(array($stmt, 'bind_param'), $this->refValues($bindingValues));

				}
				else if (!is_array($values)){
					if(is_numeric($values))
						$stmt->bind_param('d', $values);
					else
						$stmt->bind_param('s', $values);
				}
				
				$stmt->execute();
				$meta = $stmt->result_metadata();
				while ( $field = $meta->fetch_field() ) {
	            	$parameters[] = &$row[$field->name];
				}
					       
	            call_user_func_array(array($stmt, 'bind_result'), $this->refValues($parameters));
	              
	            while ( $stmt->fetch() ) { 
	            	$x = array(); 
					foreach( $row as $key => $val ) { 
						$x[$key] = $val; 
					} 
					$results[] = $x; 
	            }
				if(!isset($results)) {
					$results = null;
				}
				$rows = $results;
				$stmt->close();
			}
		}
		catch(Exception $e) {
			throw $e;
		}
		if(!isset($rows)) {
			$rows = array();
		}
		return $rows;
	}
	
    function refValues($arr){
    	if (strnatcmp(phpversion(),'5.3') >= 0){
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }
	/**
	 * Creates and executes a prepared statement
	 * @return the amount of rows changed
	 * @param unknown_type $sql - the SQL statement
	 * @param unknown_type $values - array of data to add
	 * @throws Exception - When the prepared statement fails
	 */
	public function execute($sql, $values = array()) {
		$this->connect();
		try {
			$stmt = $this->mysql->prepare($sql);
			if($stmt) {
				if(is_array($values) && count($values) > 0) {					
					$bindingString = '';
					$bindingValues = array();
					foreach($values as $value) {
						if(is_numeric($value)) {
							$bindingString .= 'd';
						}
						else {
							$bindingString .= 's';
						}
						$bindingValues[] = $value;
					}
					array_unshift($bindingValues, $bindingString);
					call_user_func_array(array($stmt, 'bind_param'), $this->refValues($bindingValues));
				}
				else if (!is_array($values)){
					if(is_numeric($values))
						$stmt->bind_param('d', $values);
					else
						$stmt->bind_param('s', $values);
				}
				
				$stmt->execute();
				$stmt->close();
			}
			
		}
		catch(Exception $e) {
			throw $e;
		}
		
	}
	public function getCol($sql, $values = array()) {
		$this->connect();

		$rows = $this->getAll($sql,$values);
		$cols = array();

		if ($rows && is_array($rows) && count($rows)>0) {
			foreach ($rows as $row) {
				$cols[] = array_shift($row);
			}
		}
		return $cols;
	}

	public function getCell($sql, $values = array()) {
		$this->connect();
		$arr = $this->getAll($sql, $values);
		$row1 = array_shift($arr);
		$col1 = array_shift($row1);
		return $col1;
	}
	public function getRow($sql, $values = array()) {
		$this->connect();
		$arr = $this->getAll($sql, $values);
		return array_shift($arr);
	}
	public function errorNo() {
		$this->connect();
		return  $this->mysql->errno;
	}
	public function errorMsg() {
		$this->connect();
		return $this->mysql->error;
	}

	

	public function escape( $str ) {
		$this->connect();
		return $this->mysql->real_escape_string ($str);
	}

	public function getInsertID() {
		$this->connect();
		return (int) $this->mysql->insert_id;
	}

	public function getAffectedRows() {
		$this->connect();
		return (int) $this->affected_rows;
	}
	/**
	 * Starts a transaction.
	 */
	public function startTrans() {
		$this->connect();
		$this->mysql->beginTransaction();
	}
	/**
	 * Commits a transaction.
	 */
	public function commitTrans() {
		$this->connect();
		$this->mysql->commit();
	}
	/**
	 * Rolls back a transaction.
	 */
	public function failTrans() {
		$this->connect();
		$this->mysql->rollback();
	}
}

