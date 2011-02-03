<?php
namespace Idk;

class IDK {
	
	/**
	 * The Savant Singleton
	 * @var Savant3
	 */
	private static $savant;
	/**
	 * The Savant variable, stored here for easy access to templates.
	 * @var Savant3
	 * @return Savant3
	 */
	public static function getSavant() {
		if(self::$savant == null) {
			self::$savant = new \Savant\Savant3();
		}
		return self::$savant;
	}
	
	function __construct() {
		/**
		 * Initiate the users session
		 */
		session_start();
		/**
		 * Require the Doctrine class loader
		 */
		require 'Idk/lib/Doctrine/Common/ClassLoader.php';
		/**
		 * Define the doctrine common class loader
		 */
		$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', 'Idk' . DIRECTORY_SEPARATOR .'lib');
		$classLoader->register();
		
		$classLoader = new \Doctrine\Common\ClassLoader('Savant', 'Idk' . DIRECTORY_SEPARATOR .'lib');
		$classLoader->register();
		
		$classLoader = new \Doctrine\Common\ClassLoader('Idk');
		$classLoader->register();
		
		$classLoader = new \Doctrine\Common\ClassLoader('AppConfig');
		$classLoader->register();
		
		$classLoader = new \Doctrine\Common\ClassLoader('controllers');
		$classLoader->register();
		
		$classLoader = new \Doctrine\Common\ClassLoader('models');
		$classLoader->register();
		
		/**
		 * Initiate the logger
		 * @var KLogger
		 */
		$log = KLogger::getInstance();
		/**
		 * Start the database connection
		 */
		$database = Database::getInstance();
		$log->logDebug("Database connection achieved");
		/**
		 * Initilize the routes
		 */
		RouteMap::getInstance()->generateRoutes();
		$log->logDebug("Routes initilized");
	}
	
}
?>