<?php

namespace TreasureChest\Cache;

/**
 * Implements a simple key/value store in memory. Used for testing
 */
class Faux implements \TreasureChest\CacheInterface
{
	protected $data = array();

	/**
	 * Stores a variable in the cache, if it doesnt already exist.
	 */
	public function add($key, $var = null, $ttl = 0)
	{
		if(isset($this->data[$key])) {
			return false;
		}

		return $this->store($key, $var, $ttl);
	}

	/**
	 * Stores a variable in the cache, overwriting any existing variable.
	 */
	public function store($key, $var = null, $ttl = 0)
	{
		$ttl = ($ttl === 0) ? PHP_INT_MAX : time() + $ttl;

		$this->data[$key] = array($var, $ttl);

		return true;
	}

	/**
	 * Replaces a variable in the cache, only if it already exists.
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
	 */
	public function exists($key)
	{
		return isset($this->data[$key]) && ($this->data[$key][1] > time());
	}


	/**
	 * Fetchs a stored variable from the cache.
	 */
	public function fetch($key, &$success = false)
	{
		if(!$this->exists($key)) {
			$success = false;
			return false;
		}

		return $this->data[$key][0];
	}


	/**
	 * Atomically increments a stored number.
	 */
	public function inc($key, $step = 1, &$success = null)
	{
		$oldVal = (int)$this->fetch($key);
		$this->data[$key][0] = $oldVal + $step;
		$success = true;

		return $this->data[$key][0];
	}

	/**
	 * Atomically decrements a stored number.
	 */
	public function dec($key, $step = 1, &$success = null)
	{
		$oldVal = (int)$this->fetch($key);
		$this->data[$key][0] = $oldVal - $step;
		$success = true;

		return $this->data[$key][0];
	}

	/**
	 * Deletes an individual key from the cache
	 */
	public function delete($key)
	{
		if(!$this->exists($key)) {
			return false;
		}

		unset($this->data[$key]);

		return true;
	}

	public function clear()
	{
		$this->data = array();

		return true;
	}
}