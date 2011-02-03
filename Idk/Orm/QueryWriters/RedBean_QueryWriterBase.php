<?php
namespace Idk\Orm\QueryWriters;

class RedBean_QueryWriterBase {
	
	public function getFormattedTableName($type) {
		return strtolower($type);
	}
	/**
	 * Returns the column name that should be used
	 * to store and retrieve the primary key ID.
	 * @param string $type
	 * @return string $idfieldtobeused
	 */
	public function getIDField( $type ) {
		return  "id";
	}
}

?>