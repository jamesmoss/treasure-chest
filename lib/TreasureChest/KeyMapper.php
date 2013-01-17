<?php

namespace TreasureChest;

/**
* KeyMapper Class
*
* Translates a key name with namespaces into one usable in the low level cache.
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
	 * This string is prefixed to all namespace version keys to prevent clashes 
	 * with other keys in the datastore.
	 *
	 * @var string 
	 */
	protected $prefix = 'ns';

	protected $delimiter;


	public function __construct(CacheInterface $cache, $delimiter = ':')
	{
		$this->cache = $cache;
		$this->setDelimiter($delimiter);
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

	public function setDelimiter($delimiter)
	{
		if(strlen($delimiter) !== 1) {
			throw new Exception('Cache delimiter must be exactly one character.');
		}

		$this->delimiter = $delimiter;
	}

	/**
	 * generates the final cache identifier for the provided namespace and key
	 *
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
			$component.= $this->delimiter.$namespace;
			$parts[] = $this->getVersionNumber(substr($component, 1));
		}
		
		// generate the full key which will be passed to the low level cache functions
		$newKey = implode($this->delimiter, $parts).$this->delimiter;
		$newKey.= substr($component, 1).$this->delimiter;
		$newKey.= $key;
		
		return $newKey;
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
	 * @param string $namespace 
	 * @return string 
	 */
	protected function getVersionKey($namespace)
	{
		// an example version key might be ns:version:user:james:age
		return $this->prefix.$this->delimiter.'version'.$this->delimiter.$namespace;
	}

	public function invalidate($namespace)
	{
		if(!$namespace) {
			return false;
		}

		$versionKey = $this->getVersionKey($namespace);

		if($this->cache->exists($versionKey)) {
			$this->index[$namespace] = $this->cache->inc($versionKey);
		} else {
			$this->cache->store($versionKey, 1);
		}

		// Reset the index
		$this->index = array();

		return true;
	}
}