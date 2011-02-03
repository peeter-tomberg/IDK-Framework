<?php
/**
 * Interface for database drivers
 * @file 		RedBean/Driver.php
 * @description		Describes the API for database classes
 *					The Driver API conforms to the ADODB pseudo standard
 *					for database drivers.
 * @author			Gabor de Mooij
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
namespace Idk\Orm\Interfaces;
interface RedBean_Driver {

	/**
	 * Implements Singleton
	 * Requests an instance of the database
	 * @param $host
	 * @param $user
	 * @param $pass
	 * @param $dbname
	 * @return RedBean_Driver $driver
	 */
	public static function getInstance();

	/**
	 * Runs a query and fetches results as a multi dimensional array
	 * @param $sql
	 * @return array $results
	 */
	public function getAll($sql, $values = array());

	/**
	 * Runs a query and fetches results as a column
	 * @param $sql
	 * @return array $results
	 */
	public function getCol( $sql, $aValues=array() );

	/**
	 * Runs a query an returns results as a single cell
	 * @param $sql
	 * @return mixed $cellvalue
	 */
	public function getCell( $sql, $aValues=array() );

	/**
	 * Runs a query and returns a flat array containing the values of
	 * one row
	 * @param $sql
	 * @return array $row
	 */
	public function getRow( $sql, $aValues=array() );

	/**
	 * Returns the error constant of the most
	 * recent error
	 * @return mixed $error
	 */
	public function errorNo();

	/**
	 * Returns the error message of the most recent
	 * error
	 * @return string $message
	 */
	public function errorMsg();

	/**
	 * Runs an SQL query
	 * @param $sql
	 * @return void
	 */
	public function execute( $sql, $aValues=array() );

	/**
	 * Escapes a value according to the
	 * escape policies of the current database instance
	 * @param $str
	 * @return string $escaped_str
	 */
	public function escape( $str );

	/**
	 * Returns the latest insert_id value
	 * @return integer $id
	 */
	public function getInsertID();

	/**
	 * Returns the number of rows affected
	 * by the latest query
	 * @return integer $id
	 */
	public function getAffectedRows();


	/**
	 * Commits a transaction
	 */
	public function commitTrans();

	/**
	 * Starts a transaction
	 */
	public function startTrans();

	/**
	 * Rolls back a transaction
	 */
	public function failTrans();


}
