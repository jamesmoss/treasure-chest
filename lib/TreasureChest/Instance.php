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
 * @version 0.2
 * @package namespace-cache
 */
 
 
namespace TreasureChest;
 
class Instance
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
	
	public function __construct($cache)
	{
		$this->cache = $cache;
		$this->mapper = new KeyMapper($this->cache, $this->delimiter);
	}
	
	public function __call($method, $args)
	{
		// Only the methods from the CacheInterface are allowed to be called
		$allowedMethods = get_class_methods('\\'.__NAMESPACE__.'\\CacheInterface');
		
		if(!in_array($method, $allowedMethods)) {
			throw new Exception('Unknown method '.$method);
		}

		// If we are auto prefixing all keys with a namespace, do that here
		if($this->prefix) {
			$args[0] = $this->prefix.$this->delimiter.$args[0];
		}
		
		// convert the user supplied key into one actually used in the cache
		$args[0] = $this->mapper->parse($args[0]);
		
		// Call the method on the cache class, passing in supplied arguments
		return call_user_func_array(array($this->cache, $method), $args);
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
	
	/**
	 * Deletes all the keys in an entire namespace.
	 *
	 * @param string $namespace The namespace in which this variable is associated.
	 */
	public function invalidate($namespace)
	{
		return $this->mapper->invalidate($namespace);
	}
}