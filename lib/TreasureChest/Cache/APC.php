<?php

namespace TreasureChest\Cache;

class APC implements \TreasureChest\CacheInterface
{

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
		return \apc_add($key, $var, $ttl);
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
		return \apc_store($key, $var, $ttl);
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
	 * Checks if APC key exists
	 *
	 * @param string $key Store the variable using this name. 
	 * @return bool Returns TRUE if the key exists, otherwise FALSE
	 */
	public function exists($key)
	{
		if(!function_exists('apc_exists')) {
			$success = false;
			$this->fetch($key, $success);
			return $success;
		}
		
		return \apc_exists($key);
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
		return \apc_fetch($key, $success);
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
		return \apc_inc($key, $step, $success);
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
		return \apc_dec($key, $step, $success);
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
		return \apc_delete($key);
	}

	/**
	 * Clears the entire cache
	 *
	 * @return bool Returns TRUE if the cache was cleared, otherwise FALSE
	 */
	public function clear()
	{
		apc_clear_cache('user');

		return true;
	}



}