<?php

namespace TreasureChest\Cache;


class Filesystem implements \TreasureChest\CacheInterface
{

	protected $path;
	
	/**
	 * __constructor
	 * 
	 * @access public
	 * @param string $dir Path to a writeable folder in which store the cache (default: '/tmp')
	 * @return void
	 */
	public function __construct($dir = '/tmp')
	{
		// Doesnt matter if the path has a trailing slash or not, we remove it.
		$this->path = rtrim($dir, '/').'/';
		
		if(!is_writable($this->path)) {
			throw new \TreasureChest\Exception\Cache('Cache directory is not writable');
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
		return $this->path.sha1($key);
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
		    	$value = 0;
		    }
		    
		    $value = filter_var($value, FILTER_VALIDATE_INT);
		    
		    if($value !== false) {
		    	$value += $step;
		    	 
		    	ftruncate($fp, 0);
		    	fwrite($fp, $value);
		    }
		    
		    flock($fp, LOCK_UN); // release the lock
		    fclose($fp);
		    
		    return $value;
		}
	}
	
	/**
	 * Stores a variable in the cache, if it doesnt already exist.
	 *
	 * @author James Moss
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
	 * @author James Moss
	 * @param string $key Store the variable using this name. 
	 * @param string $var The variable to store
	 * @param int $ttl Number of seconds to store this variable. 0 will mean that it never expires.
	 * @return bool TRUE on success, FALSE on failure
	 */
	public function store($key, $var = null, $ttl = 0)
	{
		$file = $this->getPath($key);
		$write = file_put_contents($file, $var);
		
		if(!$write) {
			return false;
		}
		
		return touch($file, time() + $ttl);
	}
	
	/**
	 * Replaces a variable in the cache, only if it already exists.
	 *
	 * @author James Moss
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
	 * Checks if APC key exists
	 *
	 * @author James Moss
	 * @param string $key Store the variable using this name. 
	 * @return bool Returns TRUE if the key exists, otherwise FALSE
	 */
	public function exists($key)
	{
		return file_exists($this->getPath($key));
	}
	
	
	/**
	 * Fetchs a stored variable from the cache. 
	 *
	 * @author James Moss
	 * @param string $key Retreieve variable assigned to this name.
	 * @param bool $success Set to TRUE in success and FALSE in failure.  
	 * @return mixed The stored variable or array of variables on success; FALSE on failure
	 */
	public function fetch($key, &$success = false)
	{
		$file = $this->getPath($key);
		
		if(!file_exists($file) || filemtime($file) < time()) {
			$success = false;
			return false;
		}
		
		return file_get_contents($file);
	}


	/**
	 * Atomically increments a stored number. 
	 *
	 * @author James Moss
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
	 * @author James Moss
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
	 * @author James Moss
	 * @param string $namespace The namespace in which this variable is associated.
	 * @param string $key They key to delete
	 * @return bool Returns TRUE if the key exists, otherwise FALSE
	 */
	public function delete($key)
	{
		return unlink($this->getPath($key));
	}

	
}