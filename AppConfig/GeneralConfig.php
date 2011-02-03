<?php
namespace AppConfig;

class GeneralConfig {
	/**
	 * Turn this to true to stop database schema changes and enable caching for longer periods of time.
	 * 		
	 * 		Autoloader will try to store all found classes in the cache for an unlimited time.
	 */
	public static $productionMode = false;
	/**
	 * The base URL for this application
	 */
	public static $baseURL = 'http://localhost/idk/';
	
	/**
	 * Based on your settings the PHP's $_SERVER['DOCUMENT_ROOT'] might lead to the wrong directory. This will fix issues in PHP MIN library.
	 * Leave it an empty string ('') to use PHP's $_SERVER['DOCUMENT_ROOT']
	 * @var string
	 */
	public static $baseDocumentRoot = "C:/Users/tombergp/workspace-php/idk";
	/**
	 * The default controller, used when no data is present in the URL 
	 * @var string
	 */
	public static $defaultController = 'raadiod';	
	/**
	 * The default action, used when no data is present in the URL 
	 * @var string
	 */
	public static $defaultAction = 'index';	
	/**
	 * Set the cache 
	 * @param $cache - xcache will use the xcache addon 
	 */
	public static $cache = 'filecache';
	/**
	 * The time in seconds to keep data in the cache
	 */
	public static $cacheTTL = '600';
	/**
	 * The location of the file based cache. This should be a writeable directory. 
	 * @var string
	 */
	public static $fileCacheLocation = "Cache/";
	/**
	 * Should IDK automatically compress CSS files found in the HEAD tag into a single minfied version?
	 * 
	 * Reduces load times.
	 * @var unknown_type
	 */
	public static $minify_CSS = true;
	/**
	 * Should IDK automatically compress JS files found in the HEAD tag into a single minfied version?
	 * 
	 * Reduces load times.
	 * @var unknown_type
	 */
	public static $minify_JS = true;
	/**
	 * Should IDK automatically compress view file HTMLs? (Removes whitespaces and comments)
	 * 
	 * Reduces load times.
	 * @var unknown_type
	 */
	public static $minify_HTML = true;
	
	
	
	
	
	
	
}

?>