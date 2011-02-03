<?php
/**
 * RedBean MySQLWriter
 * @file 		RedBean/QueryWriter/MySQL.php
 * @description		Represents a MySQL Database to RedBean
 *					To write a driver for a different database for RedBean
 *					you should only have to change this file.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */

namespace Idk\Orm\QueryWriters;    
  
class RedBean_QueryWriterMysql extends RedBean_QueryWriterBase implements \Idk\Orm\Interfaces\RedBean_QueryWriter {

	/**
	 * Here we describe the datatypes that RedBean
	 * Uses internally. If you write a QueryWriter for
	 * RedBean you should provide a list of types like this.
	 */

	/**
	 * DATA TYPE
	 * Boolean Data type
	 * @var integer
	 */
	const C_DATATYPE_BOOL = 0;

	/**
	 * DATA TYPE
	 * Unsigned 8BIT Integer
	 * @var integer
	 */
	const C_DATATYPE_UINT8 = 1;

	/**
	 * DATA TYPE
	 * Unsigned 32BIT Integer
	 * @var integer
	 */
	const C_DATATYPE_UINT32 = 2;

	/**
	 * DATA TYPE
	 * Double precision floating point number and
	 * negative numbers.
	 * @var integer
	 */
	const C_DATATYPE_DOUBLE = 3;

	/**
	 * DATA TYPE
	 * Standard Text column (like varchar255)
	 * At least 8BIT character support.
	 * @var integer
	 */
	const C_DATATYPE_TEXT8 = 4;

	/**
	 * DATA TYPE
	 * Long text column (16BIT)
	 * @var integer
	 */
	const C_DATATYPE_TEXT16 = 5;

	/**
	 * DATA TYPE
	 * 32BIT long textfield (number of characters can be as high as 32BIT) Data type
	 * This is the biggest column that RedBean supports. If possible you may write
	 * an implementation that stores even bigger values.
	 * @var integer
	 */
	const C_DATATYPE_TEXT32 = 6;

	/**
	 * DATA TYPE
	 * Specified. This means the developer or DBA
	 * has altered the column to a different type not
	 * recognized by RedBean. This high number makes sure
	 * it will not be converted back to another type by accident.
	 * @var integer
	 */
	const C_DATATYPE_SPECIFIED = 99;




	/**
	 * @var array
	 * Supported Column Types
	 */
	public $typeno_sqltype = array(
			  
			  RedBean_QueryWriterMysql::C_DATATYPE_UINT8=>" TINYINT(3) UNSIGNED ",
			  RedBean_QueryWriterMysql::C_DATATYPE_UINT32=>" INT(11) UNSIGNED ",
			  RedBean_QueryWriterMysql::C_DATATYPE_DOUBLE=>" DOUBLE ",
			  RedBean_QueryWriterMysql::C_DATATYPE_TEXT8=>" VARCHAR(255) ",
			  RedBean_QueryWriterMysql::C_DATATYPE_TEXT16=>" TEXT ",
			  RedBean_QueryWriterMysql::C_DATATYPE_TEXT32=>" LONGTEXT "
	);

	/**
	 *
	 * @var array
	 * Supported Column Types and their
	 * constants (magic numbers)
	 */
	public $sqltype_typeno = array(
			  "set('1')"=>RedBean_QueryWriterMysql::C_DATATYPE_BOOL,
			  "tinyint(3) unsigned"=>RedBean_QueryWriterMysql::C_DATATYPE_UINT8,
			  "int(11) unsigned"=>RedBean_QueryWriterMysql::C_DATATYPE_UINT32,
			  "double" => RedBean_QueryWriterMysql::C_DATATYPE_DOUBLE,
			  "varchar(255)"=>RedBean_QueryWriterMysql::C_DATATYPE_TEXT8,
			  "text"=>RedBean_QueryWriterMysql::C_DATATYPE_TEXT16,
			  "longtext"=>RedBean_QueryWriterMysql::C_DATATYPE_TEXT32
	);

	

	/**
	 *
	 * @var RedBean_Adapter_DBAdapter
	 */
	protected $driver;

	/**
	 * Indicates the field name to be used for primary keys;
	 * default is 'id'
	 * @var string
	 */
	protected $idfield = "id";



	/**
	 * Checks table name or column name.
	 * @param string $table
	 * @return string $table
	 */
	public function check($table) {
		if (strpos($table,"`")!==false) throw new RedBean_Exception_Security("Illegal chars in table name");
		return $this->driver->escape($table);
	}

	/**
	 * Constructor.
	 * The Query Writer Constructor also sets up the database.
	 * @param RedBean_Adapter_DBAdapter $adapter
	 */
	public function __construct(\Idk\Orm\Interfaces\RedBean_Driver $driver) {
		$this->driver = $driver;
	}


	/**
	 * Returns all tables in the database.
	 * @return array $tables
	 */
	public function getTables() {
		return $this->driver->getCol( "show tables" );
	}

	/**
	 * Creates an empty, column-less table for a bean.
	 * @param string $table
	 */
	public function createTable( $table ) {
		$idfield = $this->getIDfield($table);
		$table = $this->getFormattedTableName($table);
		$table = $this->check($table);
		$sql = "
                     CREATE TABLE `$table` (
                    `$idfield` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
                     PRIMARY KEY ( `$idfield` )
                     ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
				  ";
		$this->driver->execute( $sql );
	}

	/**
	 * Returns an array containing the column names of the specified table.
	 * @param string $table
	 * @return array $columns
	 */
	public function getColumns( $table ) {
		$table = $this->getFormattedTableName($table);
		$table = $this->check($table);
		$columns = array();
		$columnsRaw = $this->driver->getAll("DESCRIBE `$table`");
		foreach($columnsRaw as $r) {
			$columns[$r["Field"]] = $r["Type"];
		}
		return $columns;
	}

	/**
	 * Returns the MySQL Column Type Code (integer) that corresponds
	 * to the given value type.
	 * @param string $value
	 * @return integer $type
	 */
	public function scanType( $value ) {

		if (is_null($value)) {
			return RedBean_QueryWriterMysql::C_DATATYPE_UINT8;
		}
		$orig = $value;
		$value = strval($value);
		if ($value=="1" || $value=="" || $value=="0") {
			return RedBean_QueryWriterMysql::C_DATATYPE_UINT8; //RedBean_QueryWriterMysql::C_DATATYPE_BOOL;
		}
		if (is_numeric($value) && (floor($value)==$value) && $value >= 0 && $value <= 255 ) {
			return RedBean_QueryWriterMysql::C_DATATYPE_UINT8;
		}
		if (is_numeric($value) && (floor($value)==$value) && $value >= 0  && $value <= 4294967295 ) {
			return RedBean_QueryWriterMysql::C_DATATYPE_UINT32;
		}
		if (is_numeric($value)) {
			return RedBean_QueryWriterMysql::C_DATATYPE_DOUBLE;
		}
		if (strlen($value) <= 255) {
			return RedBean_QueryWriterMysql::C_DATATYPE_TEXT8;
		}
		return RedBean_QueryWriterMysql::C_DATATYPE_TEXT16;
	}

	/**
	 * Adds a column of a given type to a table.
	 * @param string $table
	 * @param string $column
	 * @param integer $type
	 */
	public function addColumn( $table, $column, $type ) {
		$table = $this->getFormattedTableName($table);
		$column = $this->check($column);
		$table = $this->check($table);
		$type=$this->typeno_sqltype[$type];
		$sql = "ALTER TABLE `$table` ADD `$column` $type ";
		$this->driver->execute( $sql );
	}

	/**
	 * Returns the Type Code for a Column Description
	 * @param string $typedescription
	 * @return integer $typecode
	 */
	public function code( $typedescription ) {
		return ((isset($this->sqltype_typeno[$typedescription])) ? $this->sqltype_typeno[$typedescription] : 99);
	}

	/**
	 * Change (Widen) the column to the give type.
	 * @param string $table
	 * @param string $column
	 * @param integer $type
	 */
	public function widenColumn( $table, $column, $type ) {
		$table = $this->getFormattedTableName($table);
		$column = $this->check($column);
		$table = $this->check($table);
		$newtype = $this->typeno_sqltype[$type];
		$changecolumnSQL = "ALTER TABLE `$table` CHANGE `$column` `$column` $newtype ";
		$this->driver->execute( $changecolumnSQL );
	}

	/**
	 * Update a record using a series of update values.
	 * @param string $table
	 * @param array $updatevalues
	 * @param integer $id
	 */
	public function updateRecord( $table, $updatevalues, $id) {
		$idfield = $this->getIDField($table);
		$table = $this->getFormattedTableName($table);
		$sql = "UPDATE `".$this->check($table)."` SET ";
		$p = $v = array();
		foreach($updatevalues as $uv) {
			$p[] = " `".$uv["property"]."` = ? ";
			$v[]=( $uv["value"] );
		}
		$sql .= implode(",", $p ) ." WHERE $idfield = ".intval($id);
		$this->driver->execute( $sql, $v );
	}

	/**
	 * Inserts a record into the database using a series of insert columns
	 * and corresponding insertvalues. Returns the insert id.
	 * @param string $table
	 * @param array $insertcolumns
	 * @param array $insertvalues
	 * @return integer $insertid
	 */
	public function insertRecord( $table, $insertcolumns, $insertvalues ) {
		$idfield = $this->getIDField($table);
		$table = $this->getFormattedTableName($table);
		//if ($table == "__log") $idfield="id"; else
		$table = $this->check($table);
		if (count($insertvalues)>0 && is_array($insertvalues[0]) && count($insertvalues[0])>0) {
			foreach($insertcolumns as $k=>$v) {
				$insertcolumns[$k] = "`".$this->check($v)."`";
			}
			$insertSQL = "INSERT INTO `$table` ( $idfield, ".implode(",",$insertcolumns)." ) VALUES ";
			$pat = "( NULL, ". implode(",",array_fill(0,count($insertcolumns)," ? "))." )";
			$insertSQL .= implode(",",array_fill(0,count($insertvalues),$pat));
			foreach($insertvalues as $insertvalue) {
				foreach($insertvalue as $v) {
					$vs[] = ( $v );
				}
			}
			$this->driver->execute( $insertSQL, $vs );
			return ($this->driver->errorMsg()=="" ?  $this->driver->getInsertID() : 0);
		}
		else {
			$this->driver->execute( "INSERT INTO `$table` ($idfield) VALUES(NULL) " );
			return ($this->driver->errorMsg()=="" ?  $this->driver->getInsertID() : 0);
		}
	}

	/**
	 * Selects a record based on type and id.
	 * @param string $type
	 * @param integer $id
	 * @return array $row
	 */
	public function selectRecord($type, $ids) {
		$idfield = $this->getIDField($type);
		$type = $this->getFormattedTableName($type);
		$type=$this->check($type);
		$sql = "SELECT * FROM `$type` WHERE $idfield IN ( ".implode(',', array_fill(0, count($ids), " ? "))." )";
		$rows = $this->driver->getAll($sql,$ids);
		return ($rows) ? $rows : NULL;

	}
	/**
	 * Selects a record based on type and id.
	 * @param string $type
	 * @param integer $id
	 * @return array $row
	 */
	public function selectSingleRecord($type, $id) {
		$idfield = $this->getIDField($type);
		$type = $this->getFormattedTableName($type);
		$type=$this->check($type);
		$sql = "SELECT * FROM `$type` WHERE $idfield = ? ";
		$rows = $this->driver->getAll($sql,$id);
		return ($rows) ? $rows : NULL;
	}
	/**
	 * Deletes a record based on a table, column, value and operator
	 * @param string $table
	 * @param string $column
	 * @param mixed $value
	 * @param string $oper
	 * @todo validate arguments for security
	 */
	public function deleteRecord( $table, $id) {
		$table = $this->getFormattedTableName($table);
		$table = $this->check($table);
		$this->driver->execute("DELETE FROM `$table` WHERE `".$this->getIDField($table)."` = ? ",array(strval($id)));
	}
	/**
	 * Deletes a record based on a table, column, value and operator
	 * @param string $table
	 * @param string $column
	 * @param mixed $value
	 * @param string $oper
	 * @todo validate arguments for security
	 */
	public function deleteRecordByForeignKey( $table, $fk, $id) {
		$table = $this->getFormattedTableName($table);
		$table = $this->check($table);
		$this->driver->execute("DELETE FROM `$table` WHERE `".$fk."` = ? ",array(strval($id)));
	}
	
	/**
	 * Adds a Unique index constrain to the table.
	 * @param string $table
	 * @param string $col1
	 * @param string $col2
	 * @return void
	 */
	public function addUniqueIndex( $table,$columns ) {
		$table = $this->getFormattedTableName($table);
		sort($columns); //else we get multiple indexes due to order-effects
		foreach($columns as $k=>$v) {
			$columns[$k]="`".$this->driver->escape($v)."`";
		}
		$table = $this->check($table);
		$r = $this->driver->getAll("SHOW INDEX FROM `$table`");
		$name = "UQ_".sha1(implode(',',$columns));
		if ($r) {
			foreach($r as $i) {
				if ($i["Key_name"]==$name) {
					return;
				}
			}
		}
		$sql = "ALTER IGNORE TABLE `$table`
                ADD UNIQUE INDEX `$name` (".implode(",",$columns).")";
		$this->driver->execute($sql);
	}

	/**
	 * Selects a record using a criterium.
	 * Specify the select-column, the target table, the criterium column
	 * and the criterium value. This method scans the specified table for
	 * records having a criterium column with a value that matches the
	 * specified value. For each record the select-column value will be
	 * returned, most likely this will be a primary key column like ID.
	 * If $withUnion equals true the method will also return the $column
	 * values for each entry that has a matching select-column. This is
	 * handy for cross-link tables like page_page.
	 * @param string $select, the column to be selected
	 * @param string $table, the table to select from
	 * @param string $column, the column to compare the criteria value against
	 * @param string $value, the criterium value to match against
	 * @param boolean $withUnion (default is false)
	 * @return array $mixedColumns
	 */
	public function selectByCrit( $select, $table, $column, $value, $withUnion=false ) {
		$table = $this->getFormattedTableName($table);
		if($select != '*')
			$select = $this->noKW($this->driver->escape($select));
		$table = $this->noKW($this->driver->escape($table));
		$column = $this->noKW($this->driver->escape($column));
		$value = $this->driver->escape($value);
		$sql = "SELECT $select FROM $table WHERE $column = ? ";
		$values = array($value);
		if ($withUnion) {
			$sql .= " UNION SELECT $column FROM $table WHERE $select = ? ";
			$values[] = $value;
		}
		return $this->driver->getAll($sql,$values);
	}
	/**
	 * Selects a set of columns using criteria + SQL
	 * @param unknown_type $table
	 * @param unknown_type $column
	 * @param unknown_type $value
	 * @param unknown_type $SQL
	 */
	public function selectAllByCritWithSQL($table, $column, $value, $SQL = ""){
		$table = $this->getFormattedTableName($table);
		$table = $this->noKW($this->driver->escape($table));
		$column = $this->noKW($this->driver->escape($column));
		$value = $this->driver->escape($value);
		$query = "SELECT * FROM $table WHERE $column = ? " . $SQL;
		return $this->driver->getAll($query, array($value));
	}
	/**
	 * Selects a set of columns using criteria + SQL
	 * @param unknown_type $table
	 * @param unknown_type $column
	 * @param unknown_type $value
	 * @param unknown_type $SQL
	 */
	public function selectNumRowsByCritWithSQL($table, $column, $value, $SQL = ""){
		$table = $this->getFormattedTableName($table);
		$table = $this->noKW($this->driver->escape($table));
		$column = $this->noKW($this->driver->escape($column));
		$value = $this->driver->escape($value);
		$query = "SELECT count(id) FROM $table WHERE $column = ? " . $SQL;
		return $this->driver->getAll($query, array($value));
	}
	/**
	 * This method takes an array with key=>value pairs.
	 * Each record that has a complete match with the array is
	 * deleted from the table.
	 * @param string $table
	 * @param array $crits
	 * @return integer $affectedRows
	 */
	public function deleteByCrit( $table, $crits ) {
		$table = $this->getFormattedTableName($table);
		$table = $this->noKW($this->driver->escape($table));
		$values = array();
		foreach($crits as $key=>$val) {
			$key = $this->noKW($this->driver->escape($key));
			$values[] = $this->driver->escape($val);
			$conditions[] = $key ."= ? ";
		}
		$sql = "DELETE FROM $table WHERE ".implode(" AND ", $conditions);
		return (int) $this->driver->execute($sql, $values);
	}

	/**
	 * Puts keyword escaping symbols around string.
	 * @param string $str
	 * @return string $keywordSafeString
	 */
	public function noKW($str) {
		return "`".$str."`";
	}



	public function sqlStateIn($state, $list) {

		$sqlState = "0";
		if ($state == "42S02") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE;
		if ($state == "42S22") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN;
		if ($state == "23000") $sqlState = RedBean_QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION;
		return in_array($sqlState, $list);
	}

}