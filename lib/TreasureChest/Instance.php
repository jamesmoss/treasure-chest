<?php

/**
 * TreasureChest
 *
 * A wrapper around the apc_* functions which introduces namespaces for keys.
 * This gives the ability to logically group a set of keys and invalidate
 * them all at once making cache management easier.
 * Note: This library requires APC version 3.1.4 or higher.
 *
 * @author James Moss <email@jamesmoss.co.uk>
 * @version 0.1.4
 * @package treasure-chest
 */


namespace TreasureChest;

class Instance implements CacheInterface
{
	/**
	 * The character used to indicate the seperation of cache namespaces
	 *
	 * @var string
	 */
	protected $delimiter = ':';

	/**
	 * This namespace can be appended to the start of all other keys passed in to
	 * the class to faciliate logical partitioning of cache data.
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Constructor.
	 *
	 * @param CacheInterface $cache The cache instance to use
	 */
	public function __construct(CacheInterface $cache)
	{
		$this->cache  = $cache;
		$this->mapper = new KeyMapper($this->cache, $this->delimiter);
	}

	/**
	 * Sets the current version of the provided namespace
	 *
	 * @param string $prefix The new prefix to use
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

	/**
	 * Sets the namespace delimiter
	 *
	 * @param string $prefix The new delimiter to use
	 */
	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;
		$this->mapper->setDelimiter($delimiter);
	}

	/**
	 * Set your own custom mapper.
	 *
	 * @param KeyMapperInterface $mapper [description]
	 */
	public function setMapper(KeyMapperInterface $mapper)
	{
		$this->mapper = $mapper;
		$this->mapper->setDelimiter($this->delimiter);
	}

	public function add($key, $var = null, $ttl = 0)
	{
		return $this->_callCacheMethod('add', func_get_args());
	}

	public function store($key, $var = null, $ttl = 0)
	{
		return $this->_callCacheMethod('store', func_get_args());
	}

	public function replace($key, $var = null, $ttl = 0)
	{
		return $this->_callCacheMethod('replace', func_get_args());
	}

	public function exists($key)
	{
		return $this->_callCacheMethod('exists', func_get_args());
	}

	public function fetch($key, &$success = false)
	{
		return $this->_callCacheMethod('fetch', func_get_args());
	}

	public function inc($key, $step = 1, &$success = null)
	{
		return $this->_callCacheMethod('inc', func_get_args());
	}

	public function dec($key, $step = 1, &$success = null)
	{
		return $this->_callCacheMethod('dec', func_get_args());
	}

	public function delete($key)
	{
		return $this->_callCacheMethod('delete', func_get_args());
	}

	public function clear()
	{
		return $this->_callCacheMethod('clear', func_get_args());
	}

	/**
	 * Deletes all the keys in an entire namespace.
	 *
	 * @param string $namespace The namespace in which this variable is associated.
	 */
	public function invalidate($namespace)
	{
		if($this->prefix) {
			$namespace = $this->prefix.$this->delimiter.$namespace;
		}

		return $this->mapper->invalidate($namespace);
	}

	/**
	 * Call a given method on the stored cache instance.
	 *
	 * This is used with specific methods, rather than using the `__call()`
	 * magic method so that we can use an interface to define the methods. This
	 * makes this class easier to hint in your application.
	 *
	 * @param  string $method Method name to call
	 * @param  array  $args   Arguments to use
	 *
	 * @return mixed          Result of calling the method on the cache instance
	 */
	protected function _callCacheMethod($method, array $args = array())
	{
		if (isset($args[0])) {
			// If we are auto prefixing all keys with a namespace, do that here
			if($this->prefix) {
				$args[0] = $this->prefix.$this->delimiter.$args[0];
			}

			// Convert the user supplied key into one actually used in the cache
			$args[0] = $this->mapper->parse($args[0]);
		}

		// Call the method on the cache class, passing in supplied arguments
		return call_user_func_array(array($this->cache, $method), $args);
	}
}