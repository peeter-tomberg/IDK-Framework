<?php
namespace Idk\Orm;
class Orm {
	/**
	 * The driver we'll be using
	 * @var RedBean_Driver
	 */
	public $driver; 	
	
	/**
	 * The query writer we'll be using
	 * @var RedBean_QueryWriter
	 */
	public $queryWriter;
	/**
	 * Is the database frozen or do we allow alterations
	 * @var boolean
	 */
	public $frozen = false;
	/**
	 * Construct the ORM class
	 * @param RedBean_Driver $driver
	 */
	public function __construct(\Idk\Orm\Interfaces\RedBean_Driver $driver, \Idk\Orm\Interfaces\RedBean_QueryWriter $queryWriter) {
		$this->driver = $driver;
		$this->queryWriter = $queryWriter;
		$this->frozen = \AppConfig\GeneralConfig::$productionMode;
	}

	
	public function loadEntityData($type, $id) {
		//Sorry, quite draconic filtering
		$type = preg_replace("/\W/","", $type);
		//First get hold of the toolbox

		$table = $this->queryWriter->getFormattedTableName($type);
		
		try {
			$SQL = "SELECT * FROM `$table` WHERE ".$this->queryWriter->getIDField($table)." = ?";
			$row = array_shift($this->driver->getAll($SQL, $id));
		}
		catch(Exception $e) {
			throw $e;
		}
		return $row;
	}
	public function loadEntitiesData($type, $ids = array()) {
		//Sorry, quite draconic filtering
		$type = preg_replace("/\W/","", $type);
		//First get hold of the toolbox

		$table = $this->queryWriter->getFormattedTableName($type);
		try {
			$SQL = "SELECT * FROM `$table` WHERE ".$this->queryWriter->getIDField($table)." IN ( ".implode(',', array_fill(0, count($ids), " ? "))." )";
			$row = $this->driver->getAll($SQL, $ids);
		}
		catch(Exception $e) {
			throw $e;
		}
		return $row;
	}
	public function loadAllEntitiesData($type, $SQL = ' 1 ') {
		//Sorry, quite draconic filtering
		$type = preg_replace("/\W/","", $type);
		//First get hold of the toolbox

		$table = $this->queryWriter->getFormattedTableName($type);
		try {
			$SQL = "SELECT * FROM `$table` WHERE $SQL";
			$row = $this->driver->getAll($SQL, array());
		}
		catch(Exception $e) {
			throw $e;
		}
		return $row;
	}
	/**
	 * Checks whether the specified table already exists in the database.
	 * Not part of the Object Database interface!
	 * @param string $table
	 * @return boolean $exists
	 */
	public function tableExists($table) {
		//does this table exist?
		$tables = $this->queryWriter->getTables();
		return in_array($this->queryWriter->getFormattedTableName($table), $tables);
	}
	/**
	 * Stores a model in the database.
	 */
	public function storeEntity(IDKModel $entity) {
		$table = $entity->tableName;
		
		$idfield = $this->queryWriter->getIDField($table);
		//Does table exist? If not, create
		if (!$this->frozen && !$this->tableExists($table)) {
			$this->queryWriter->createTable( $table );
		}
		if (!$this->frozen) {
			$columns = $this->queryWriter->getColumns($table) ;
		}
		//does the table fit?
		$insertvalues = array();
		$insertcolumns = array();
		$updatevalues = array();
		
		$properties = $entity->storedProperties;
		
		/**
		 * Handle class properties
		 */
		foreach ($properties as $prop) {
            $annotations = Annotations::getReader()->getPropertyAnnotations($prop);
            foreach($annotations as $annotation) {
            	if($annotation == null) {
            		continue;
            	}
            	if($annotation instanceof Persist) {
                	$p = $prop->name;
					$v = $prop->getValue($entity);
            	}
          		else if($annotation instanceof OneToOne) {
					$propertyName = $prop->name;
					$p = $propertyName. '_id';
					/**
					 * If the variable is initilized, load ID from the variable (user may have changed it)
					 */
					if(isset($entity->$propertyName)) {
						$val = $entity->$propertyName;
						$v = $val->id;
					}
					/**
					 * Else load it from the variable cache we have (variable has not been initilized by the user) 
					 */
					else {
						$v = $entity->getVariable($p);
					}
          		}
          		else if($annotation instanceof OneToMany) {
          			continue;
          		}
                if (!$this->frozen) {
					//What kind of property are we dealing with?
					$typeno = $this->queryWriter->scanType($v);
					//Is this property represented in the table?
					if (isset($columns[$p])) {
						//yes it is, does it still fit?
						$sqlt = $this->queryWriter->code($columns[$p]);
						if ($typeno > $sqlt) {
							//no, we have to widen the database column type
							$this->queryWriter->widenColumn( $table, $p, $typeno );
						}
					}
					else {
						//no it is not
						$this->queryWriter->addColumn($table, $p, $typeno);
					}
				}
				//Okay, now we are sure that the property value will fit
				$insertvalues[] = $v;
				$insertcolumns[] = $p;
				$updatevalues[] = array( "property"=>$p, "value"=>$v );

			}
		}
		/**
		 * Handle class foreign keys
		 */
		foreach($entity->getForeignKeys() as $key => $value) {
			$p = $key . '_id';
			$v = $value;
			echo $p . ' - ' . $v;
			if (!$this->frozen) {
				//What kind of property are we dealing with?
				$typeno = $this->queryWriter->scanType($v);
				//Is this property represented in the table?
				if (isset($columns[$p])) {
					//yes it is, does it still fit?
					$sqlt = $this->queryWriter->code($columns[$p]);
					if ($typeno > $sqlt) {
						//no, we have to widen the database column type
						$this->queryWriter->widenColumn( $table, $p, $typeno );
					}
				}
				else {
					//no it is not
					$this->queryWriter->addColumn($table, $p, $typeno);
				}
			}
			//Okay, now we are sure that the property value will fit
			$insertvalues[] = $v;
			$insertcolumns[] = $p;
			$updatevalues[] = array( "property"=>$p, "value"=>$v );	
		}
		if ($entity->$idfield) {
			if (count($updatevalues)>0) {
				$this->queryWriter->updateRecord( $table, $updatevalues, $entity->$idfield );
			}
			return (int) $entity->$idfield;
		}
		else {
			$id = $this->queryWriter->insertRecord( $table, $insertcolumns, array($insertvalues) );
			return (int) $id;
		}
	}
}