<?php

namespace TreasureChest\Cache;

use TreasureChest\Exception;
use TreasureChest\CacheInterface;


class Filesystem implements CacheInterface
{

	protected $path;
	protected $prefix;
	
	/**
	 * __constructor
	 * 
	 * @access public
	 * @param string $dir Path to a writeable folder in which store the cache (default: '/tmp')
	 * @return void
	 */
	public function __construct($dir = '/tmp', $prefix = 'cache_')
	{
		// Doesnt matter if the path has a trailing slash or not, we remove it.
		$this->path   = rtrim($dir, '/').'/';
		$this->prefix = $prefix;
		
		if(!is_writable($this->path)) {
			throw new Exception('Cache directory is not writable');
		}
	}
	
	/**
	 * Returns the full path to the file for the provided key 
	 * 
	 * @access protected
	 * @param string $key The key identifier
	 * @return string The full path to the cache file
	 */
	protected function getPath($key)
	{
		$safeName = preg_replace('/[^a-zA-Z0-9\:\.\-\|\!\?\,]/us', '_', $key);

		return $this->path.$this->prefix.$safeName.'_'.substr(sha1($key), -8);
	}
	
	/**
	 * Atomically adjusts (increment or decrements) an integer stored in a file.
	 * 
	 * @access protected
	 * @param string $file
	 * @param integer $step
	 * @param boolean &$success
	 * @return mixed The new adjusted value on success, FALSE on failure.
	 */
	protected function atomicAdjust($file, $step, &$success)
	{
		$success = false;
		
		$fp = fopen($file, "r+");

		if (!flock($fp, LOCK_EX)) {
			return false;
		} else {
		    $value = trim(fread($fp, 1000));
		    if($value === '') {
		    	$value = '0|0';
		    }

		    list($header, $value) = explode('|', $value, 2);
		    
		    $value = filter_var($value, FILTER_VALIDATE_INT);
		    
		    if($value !== false) {
		    	$value += $step;
		    	 
		    	ftruncate($fp, 0);
		    	fwrite($fp, $header.'|'.$value);
		    }
		    
		    flock($fp, LOCK_UN); // release the lock
		    fclose($fp);
		    
		    return $value;
		}
	}
	
	/**
	 * Stores a variable in the cache, if it doesnt already exist.
	 *
	 * @param string $namespace The namespace in which this variable is associated.
	 * @param string $var The variable to store
	 * @param int $ttl Number of seconds to store this variable. 0 will mean that it never expires.
	 * @return bool TRUE on success, FALSE on failure
	 */
	public function add($key, $var = null, $ttl = 0)
	{
		if(file_exists($this->getPath($key))) {
			return false;
		}
		
		return $this->store($key, $var, $ttl);
	}
	
	/**
	 * Stores a variable in the cache, overwriting any existing variable.
	 *
	 * @param string $key Store the variable using this name. 
	 * @param string $var The variable to store
	 * @param int $ttl Number of seconds to store this variable. 0 will mean that it never expires.
	 * @return bool TRUE on success, FALSE on failure
	 */
	public function store($key, $var = null, $ttl = 0)
	{
		$file = $this->getPath($key);

		$wasSerialized = false;
		if(is_object($var) || is_array($var)) {
			$var = serialize($var);
			$wasSerialized = true;
		}

		// Put some headers into the file and write it
		$data = ((int)$wasSerialized).'|'.$var;
		$write = file_put_contents($file, $data);
		
		if(!$write) {
			return false;
		}

		$ttl = ($ttl === 0) ? PHP_INT_MAX : time() + $ttl;
		
		return touch($file, $ttl);
	}
	
	/**
	 * Replaces a variable in the cache, only if it already exists.
	 *
	 * @param string $key Store the variable using this name. 
	 * @param string $var The variable to store
	 * @param int $ttl Number of seconds to store this variable. 0 will mean that it never expires.
	 * @return bool TRUE on success, FALSE on failure
	 */
	public function replace($key, $var = null, $ttl = 0)
	{
		if(!$this->exists($key)) {
			return false;
		}
		
		return $this->store($key, $var, $ttl);
	}
	
	/**
	 * Checks if key exists
	 *
	 * @param string $key Store the variable using this name. 
	 * @return bool Returns TRUE if the key exists, otherwise FALSE
	 */
	public function exists($key)
	{
		$file = $this->getPath($key);

		return (file_exists($file) && filemtime($file) > time());
	}
	
	
	/**
	 * Fetchs a stored variable from the cache. 
	 *
	 * @param string $key Retreieve variable assigned to this name.
	 * @param bool $success Set to TRUE in success and FALSE in failure.  
	 * @return mixed The stored variable or array of variables on success; FALSE on failure
	 */
	public function fetch($key, &$success = false)
	{
		if(!$this->exists($key)) {
			$success = false;
			return false;
		}

		// Get data and extract headers
		$data = file_get_contents($this->getPath($key));
		list($header, $var) = explode('|', $data, 2);

		// Detect if we need to unserialize this
		if($header == 1) {
			$var = unserialize($var);
		}

		return $var;
	}


	/**
	 * Atomically increments a stored number. 
	 *
	 * @param string $key The key of the value being increased.
	 * @param int $step The step, or value to increase.
	 * @param bool $success Set to TRUE in success and FALSE in failure. 
	 * @return int
	 */
	public function inc($key, $step = 1, &$success = null)
	{
		return $this->atomicAdjust($this->getPath($key), $step, $success);
	}
	
	/**
	 * Atomically decrements a stored number. 
	 *
	 * @param string $key The key of the value being decreased.
	 * @param int $step The step, or value to increase.
	 * @param bool $success Set to TRUE in success and FALSE in failure. 
	 * @return int
	 */
	public function dec($key, $step = 1, &$success = null)
	{
		return $this->atomicAdjust($this->getPath($key), -$step, $success);
	}
	
	/**
	 * Deletes an individual key from the cache
	 *
	 * @param string $namespace The namespace in which this variable is associated.
	 * @param string $key They key to delete
	 * @return bool Returns TRUE if the key exists, otherwise FALSE
	 */
	public function delete($key)
	{
		return unlink($this->getPath($key));
	}

	/**
	 * Clears the entire cache
	 *
	 * @return bool Returns TRUE if the cache was cleared, otherwise FALSE
	 */
	public function clear()
	{
		array_map('unlink', glob($this->path.$this->prefix.'*'));

		return true;
	}
	
}