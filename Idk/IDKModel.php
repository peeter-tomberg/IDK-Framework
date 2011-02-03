<?php

namespace Idk;

use \ReflectionClass;

class IDKModel  {
	/**
	 * Name of the table this class represents
	 */
	public $tableName = '';
	/**
	 * Gets the name of the table this class represents
	 */
	public function getName() {
		return $this->tableName;
	}
	
	/**
	 * Reflection class instance of the model
	 */
	public $class;
	
	/**
	 * Stores all of the current properties 
	 */
	public $storedProperties;
	/**
	 * Constructs a IDKModel
	 */
	public function __construct() {
		$this->tableName = substr(strtolower(strtolower(get_class($this))), 1+strpos(strtolower(get_class($this)), '\\', substr_count(strtolower(get_class($this)), '\\')));
		 
		$this->class = new ReflectionClass(get_class($this));
		$this->storedProperties = $this->class->getProperties();

		if(func_num_args() == 1 && is_numeric(func_get_arg(0))) {
			$this->loadById(func_get_arg(0));
		}
				
	}
	/**
	 * The ID of this Entity
	 */
	public $id;
	/**
	 * Store / update the 
	 */
	public function store() {
		$database = Database::getInstance();
		$id = $database->orm->storeEntity($this);
		$this->id = $id;
	}

	public function __toString() {
		return $this->getName() . "(".$this->id.")";
	}
	
	/**
	 * Loads a models data from the database by primary ID
	 */
	private function loadById($id) {
		$database = Database::getInstance();
		$data = $database->orm->loadEntityData($this->tableName, $id);
		if($data == null) {
			throw new \Exception("Trying to load Model (".$this->tableName.") with the ID ".$id." failed (no record in database)");
	    }
	    $this->id = $id;
		$properties = $this->class->getProperties();
		self::initiateValues($this, $properties, $data);
			
	}
	/**
	 * Loads a models data from the database by primary ID
	 */
	public static function loadByIds($ids) {
		$type = strtolower(get_called_class());
		$returnValues = array();
		
		$database = Database::getInstance();
		
		$class = new \ReflectionClass($type);
		$data = $database->orm->loadEntitiesData($type, $ids);
		foreach($data as $item) {
			
			$object = new $type();
			$object->id = $item['id'];
			$properties = $class->getProperties();

			self::initiateValues($object, $properties, $item);
			
			$returnValues[] = $object;
		}
		return $returnValues;
	}
	/**
	 * @return SQLArrayImplementation
	 */
	public static function find($sql = ' 1 ') {
		$data = new \Idk\Orm\Collections\SQLArrayImplementation(get_called_class(), substr(strtolower(get_called_class()), 1+strpos(strtolower(get_called_class()), '\\', substr_count(strtolower(get_called_class()), '\\'))), $sql);
		return $data;
	}
	
	private $variables = array();
	
	public function storeVariable($var, $val) {
		$this->variables[$var] = $val;
	}
	public function getVariable($var) {
		return $this->variables[$var];
	}
	
	
	private $fkeys = array();
	
	public function storeForeignKey($var, $val) {
		$this->fkeys[$var] = $val;
	}
	public function getForeignKey($var) {
		return $this->fkeys[$var];
	}
	public function getForeignKeys() {
		return $this->fkeys;
	}
	public function hasForeignKey($var) {
		return in_array($var, $this->fkeys);
	}
	/**
	 * Magic function for lazy loading 
	 */
	public function __get($name) {
		$prop = new \ReflectionProperty($this->tableName, $name);
		$annotations = Annotations::getReader()->getPropertyAnnotations($prop);
		foreach($annotations as $annotation) {
        	if($annotation instanceof OneToOne) {
            	$propertyName = $prop->name;
                $class = $annotation->class;
                $idField = new $class($this->getVariable($propertyName . '_id'));
                $prop->setValue($this, $idField);
			}
			else if($annotation instanceof OneToMany) {
							
				$propertyName = $prop->name;
				$class = $annotation->class;
				
				$array = new \Idk\Orm\Collections\OneToManyArray($class, $propertyName . '_id', $this);
				$prop->setValue($this, $array);
			}
		}
		return $prop->getValue($this);
    }
	/**
	 * Remove this entry from the database completly
	 */
	public function remove() {
		
	}
	
	/**
	 * Initiate values for a class
	 * @param class $object - the class we're setting values for
	 * @param $properties - the properties of the class we're setting values for
	 * @param $data - the values we'll be setting for the class
	 */
	public static function initiateValues(&$object, $properties, $data) {
		foreach ($properties as $prop) {
	      	$annotations = Annotations::getReader()->getPropertyAnnotations($prop);
			foreach($annotations as $annotation) {
	           	if($annotation instanceof \Idk\Annotations\Persist) {
					$propertyName = $prop->name;
					if(isset($data[$propertyName])) {
						$prop->setValue($object, $data[$propertyName]);
					}
				}
	            else if($annotation instanceof OneToOne) {
	               	$propertyName = $prop->name;
	               	unset($object->$propertyName);
	               	$object->storeVariable($propertyName . '_id', $data[$propertyName . '_id']);
				}
				else if($annotation instanceof OneToMany) {
					$propertyName = $prop->name;
	               	unset($object->$propertyName);
				}
			}
		}
	}
}


?>