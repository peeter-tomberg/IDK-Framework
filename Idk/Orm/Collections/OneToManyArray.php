<?php
/**
 * Array for OneToMany relationships.
 * @author User
 *
 */

namespace Idk\Orm\Collections;

use \ReflectionClass, \ArrayIterator;

class OneToManyArray extends SQLArrayCollection {

	/**
	 * @var String
	 */	
	private $table;
	/**
	 * @var String
	 */
	private $column;
	/**
	 * @var IDKModel
	 */
	private $entity;
	
	public function __construct($table, $column, $entity) {
		$this->table = $table;
		$this->column = $column;
		$this->entity = $entity;
	}
	/**
	 * This function will fetch data from the database and convert it to models. 
	 */
	public function fetch() {
	    $class = $this->table;
	    $data = \Idk\Database::getInstance()->orm->queryWriter->selectAllByCritWithSQL($class, $this->column, $this->entity->id, $this->orderBy . $this->order . $this->limit);
	    $returnValues = array();
		foreach($data as $item) {
			$refClass = new ReflectionClass($class);
			
			$object = new $class();
			$object->id = $item['id'];
			$properties = $refClass->getProperties();
			IDKModel::initiateValues($object, $properties, $item);
			$returnValues[] = $object;
		}
		$this->_elements = $returnValues;
    	$this->state = 1;
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
    
	/**
     * Adds an element to the collection, creates an association.
     *
     * @param mixed $value
     * @return boolean Always TRUE.
     */
    public function add(IDKModel $value) {
        $this->_elements[] = $value;
        AssociationManager::createOneToManyRelationship($this->entity, $value, substr($this->column,0,-3));
        return true;
    }
	/**
     * Returns the number of rows in the database.
     * @return integer The number of rows in the database.
     */
    public function countTotalRows() {
    	$data = \Idk\Database::getInstance()->orm->queryWriter->selectNumRowsByCritWithSQL($this->table, $this->column, $this->entity->id, $this->orderBy . $this->order . $this->limit);
   		return reset(reset($data));
    }
}

?>