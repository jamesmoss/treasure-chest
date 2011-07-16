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
 * @version 0.1
 * @package namespace-cache
 */
 
 
namespace TreasureChest;
 
class Instance
{
	/**
	 * Holds the current version number for each namespace
	 *
	 * @var array 
	 */
	protected $index = array();
	
	/**
	 * This string is prefixed to all namespace keys to prevent clashes with normal APC keys
	 *
	 * @var string 
	 */
	protected $prefix = 'ns';
	
	/**
	 * This string is prefixed to all namespace keys to prevent clashes with normal APC keys
	 *
	 * @var string 
	 */
	protected $delimiter = ':';
	
	public function __construct($cache)
	{
		$this->cache = $cache;
	}
	
	
	public function __call($method, $args)
	{
		$allowedMethods = array(
			'add', 'store', 'exists', 'fetch', 'inc', 'dec', 'delete'
		);
		
		if(!in_array($method, $allowedMethods)) {
			throw new TreasureChestException('Unknown method '.$method);
		}
		
		list($namespace, $key) = explode($this->delimiter, $args[0], 2);
		
		// if no namespace is used
		if(!$key) {
			$key = $namespace;
			$namespace = '';
		}
		
		$args[0] = $this->getNamespaceKey($namespace, $key);
		
		return call_user_func_array(array($this->cache, $method), $args);
	}
	
	/**
	 * generates the final cache identifier for the provided namespace and key
	 *
	 * @author James Moss
	 * @param $namespace
	 * @param $key
	 * @return string The final key name which gets passed to the store
	 */
	protected function getNamespaceKey($namespace, $key)
	{
		if(empty($namespace)) { 
			return $key;
		}
		
		// see if the namespace version exists in the index
		if(!isset($this->index[$namespace])) {
		
			// get the latest version number for this namespace
			$version_key = $this->getVersionKey($namespace);
			$version = $this->cache->fetch($version_key);
			
			// if there is no version number then this namespace has never been used before.
			if($version === false) {
				// create a new version number starting at 0
				$this->cache->add($version_key, 0);
				$this->index[$namespace] = 0;
			} else {
				$this->index[$namespace] = $version;
			}
		}
		
		// generate the full key which will be passed to the native APC functions
		$new_key = $this->prefix.$this->delimiter.$namespace.$this->delimiter.'v'.$this->index[$namespace].$this->delimiter.$key;
		
		return $new_key;
	}
	
	
	/**
	 * Gets the current version of the provided namespace
	 *
	 * @author James Moss
	 * @param string $namespace 
	 * @return string 
	 */
	protected function getVersionKey($namespace)
	{
		// an example version key might be ns:version:news
		return $this->prefix.$this->delimiter.'version'.$this->delimiter.$namespace;
	}
	
	protected function parseNamespace($key)
	{
		$position = strpos($key, $this->delimiter);
		
		// no namespace used in this key
		if($position === false) {
			$namespace = '';
		} else {
			$namespace = substr($key, 0, $position - 1);
			$key 	   = substr($key, -$position);
		}
		
		return array(
			'namespace'	=> $namespace,
			'key'		=> $key,
		);
	}
	
	
	
	/**
	 * Gets the current version of the provided namespace
	 *
	 * @author James Moss
	 * @param string $prefix The new prefix to use 
	 * @return void 
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}
	
	/**
	 * Gets the current version of the provided namespace
	 *
	 * @author James Moss
	 * @param string $prefix The new deimiter to use 
	 * @return void 
	 */
	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;
	}

	
	/**
	 * Deletes all the keys in an entire namespace
	 *
	 * @author James Moss
	 * @param string $namespace The namespace in which this variable is associated.
	 * @return return type
	 */
	public function invalidate($namespace)
	{
		$version_key = $this->getVersionKey($namespace);
		
		if($this->cache->exists($version_key)) {
			$this->index[$namespace] = $this->cache->inc($version_key, 1);
		}
	}
}