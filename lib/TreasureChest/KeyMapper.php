<?php

namespace TreasureChest;

/**
* KeyMapper Class
*/
class KeyMapper implements KeyMapperInterface
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
		$this->cache = $cache;
		$this->delimiter = $delimiter;
	}

	public function parse($key)
	{
		// Keys without namespaces don't need to be mapped
		if(strpos($key, $this->delimiter) === false) {
			return $key;
		}

		// Split the namespaces from the rest of the key
		// The last element is always the key, the rest as namespaces
		$parts = explode($this->delimiter, $key);
		$key = array_pop($parts);
		$namespaces = $parts;

		return $this->getNamespaceKey($namespaces, $key);
	}

	/**
	 * generates the final cache identifier for the provided namespace and key
	 *
	 * @author James Moss
	 * @param $namespace
	 * @param $key
	 * @return string The final key name which gets passed to the store
	 */
	protected function getNamespaceKey(array $namespaces, $key)
	{
		if(empty($namespaces)) { 
			return $key;
		}

		$component = '';
		$parts = array();
		foreach($namespaces as $namespace) {
			$component.= '_'.$namespace;
			$parts[] = $this->getVersionNumber($component);
		}

		$versionKey = implode($this->delimiter, $parts);
		
		// generate the full key which will be passed to the low level cache functions
		$new_key = $component.$this->delimiter.$versionKey.$this->delimiter.$key;
		
		return $new_key;
	}
	
	protected function getVersionNumber($namespace)
	{
		// see if the namespace version exists in the index
		if(isset($this->index[$namespace])) {
			return $this->index[$namespace];	
		}

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

		return $this->index[$namespace];
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
}