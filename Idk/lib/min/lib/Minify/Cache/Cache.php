<?php 
class Minify_Cache_Cache {
    
    /**
     * Write data to cache.
     *
     * @param string $id cache id (e.g. a filename)
     * 
     * @param string $data
     * 
     * @return bool success
     */
    public function store($id, $data)
    {
    	return Config::getInstance()->getCache()->save($id, $data);
    }
    
    /**
     * Get the size of a cache entry
     *
     * @param string $id cache id (e.g. a filename)
     * 
     * @return int size in bytes
     */
    public function getSize($id)
    {
    	return ((function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2))
                ? mb_strlen($this->fetch($id), '8bit')
                : strlen($this->fetch($id))
            );
    }
    
    /**
     * Does a valid cache entry exist?
     *
     * @param string $id cache id (e.g. a filename)
     * 
     * @param int $srcMtime mtime of the original source file(s)
     * 
     * @return bool exists
     */
    public function isValid($id, $srcMtime)
    {
		return Config::getInstance()->getCache()->contains($id);
    }
    
    /**
     * Send the cached content to output
     *
     * @param string $id cache id (e.g. a filename)
     */
    public function display($id)
    {
    	$data =  $this->fetch($id);
		echo $data ? $data : '';
    	
    }
    
	/**
     * Fetch the cached content
     *
     * @param string $id cache id (e.g. a filename)
     * 
     * @return string
     */
    public function fetch($id)
    {
		return Config::getInstance()->getCache()->fetch($id);
    }

    /**
     * Send message to the Minify logger
     * @param string $msg
     * @return null
     */
    protected function _log($msg)
    {

    }
    
}
