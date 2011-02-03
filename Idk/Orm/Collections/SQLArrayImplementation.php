<?php
/**
 * Array for SQL data.
 * @author User
 *
 */

namespace Idk\Orm\Collections;

use \ReflectionClass, \ArrayIterator;

class SQLArrayImplementation extends SQLArrayCollection {

	/**
	 * @var String
	 */	
	private $table;
	
	private $sql;
	
	private $modelName;
	public function __construct($model, $table, $sql = ' 1 ') {
		$this->modelName = $model;
		$this->table = $table;
		$this->sql = $sql;
	}
	/**
	 * This function will fetch data from the database and convert it to models. 
	 */
	public function fetch() {
		$type = $this->modelName;
		$database = \Idk\Database::getInstance();
		$returnValues = array();
		$class = new ReflectionClass($type);
		$data = $database->orm->loadAllEntitiesData($this->table, $this->sql . $this->orderBy . $this->order . $this->limit);
		foreach($data as $item) {

			$object = new $type();
			$object->id = $item['id'];
			$properties = $class->getProperties();
			
			\Idk\IDKModel::initiateValues($object, $properties, $item);
			$returnValues[] = $object;
			
		}
		$this->_elements = $returnValues;
    	$this->state = 1;
    	
    	return $this;
	}
    /**
     * Gets an iterator for iterating over the elements in the collection.
     * Automatically fetches data from the database and converts it to models if it has not been done yet.
     * @return ArrayIterator
     */
    public function getIterator() {
    	if($this->state == 0) {
	    	$this->fetch();
    	}
	    return new ArrayIterator($this->_elements);
    }

}

?>