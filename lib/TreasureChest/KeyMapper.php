<?php

namespace TreasureChest;

/**
* KeyMapper Class
*/
class KeyMapper
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

	public $delimiter;


	public function __construct(CacheInterface $cache, $delimiter = ':')
	{
		$this->delimiter = $delimiter;
	}

	public function parse($key)
	{
		# code...
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
}