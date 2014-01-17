<?php

namespace TreasureChest\Cache;

class Memcached implements \TreasureChest\CacheInterface
{
	protected $memcached;
	
	public function __construct($host, $port = 11211, $weight = 0)
	{
		$this->memcached = new \Memcached;
		
		if(!is_array($host)) {
			$host = array($host, $port, $weight);
		}
		
		foreach($host as $details) {
			$this->memcached->addServer($details[0], $details[1], $details[2]);
		}
	}
	
	public function getMemcached() {
		return $this->memcached;
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
		return $this->memcached->add($key, $var, $ttl);
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
		return $this->memcached->set($key, $var, $ttl);
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
		return $this->memcached->replace($key, $var, $ttl);
	}
	
	/**
	 * Checks if key exists
	 *
	 * @author James Moss
	 * @param string $key Store the variable using this name. 
	 * @return bool Returns TRUE if the key exists, otherwise FALSE
	 */
	public function exists($key)
	{
		$success = false;
		$this->fetch($key, $success);
		
		return $success;
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
		$result = $this->memcached->get($key);
		$success = ($result !== false);
		
		return $result;
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
		$result = $this->memcached->increment($key, $step);
		$success = ($result !== false);
		
		return $result;
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
		$result = $this->memcached->decrement($key, $step);
		$success = ($result !== false);
		
		return $result;
	}
	
	/**
	 * Deletes an individual key from the cache
	 *
	 * @author James Moss
	 * @param string $namespace The namespace in which this variable is associated.
	 * @param string $key They key to delete
	 * @return bool Returns TRUE if the key exists, otherwise FALSE
	 */
	public function delete($key, $wait = 0)
	{
		return $this->memcached->delete($key, $wait);
	}

	/**
	 * Clears the entire cache
	 *
	 * @return bool Returns TRUE if the cache was cleared, otherwise FALSE
	 */
	public function clear()
	{
		return $this->memcached->flush();
	}
}