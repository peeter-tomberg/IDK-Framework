<?php

namespace Idk;

class Config {
	
	private $cacheInstance = null;
	/**
	 * @return Cache
	 */
	public function getCache() {
		
		$cache = \AppConfig\GeneralConfig::$cache;
		
		if($this->cacheInstance == null) {
			if($cache == 'xcache') {
				 $this->cacheInstance = new \Doctrine\Common\Cache\XcacheCache();
			}
			else if($cache == 'filecache') {
				 $this->cacheInstance = new \Doctrine\Common\Cache\FileCache(\AppConfig\GeneralConfig::$fileCacheLocation);
			
			}
			else {
				$this->cacheInstance = new \Doctrine\Common\Cache\FileCache(\AppConfig\GeneralConfig::$fileCacheLocation);
			}
		}
		return $this->cacheInstance;
	}
	
	/**
	 * Singleton instance
	 */
	private static $instance;
	/**
	 * Get instance
	 * @return Config
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
            self::$instance = new Config();
        }
        return self::$instance;
	}
	/**
	 * Private constructor for singleton pattern
	 */
	private function __construct() {}
}

?>