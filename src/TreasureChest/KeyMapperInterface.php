<?php

namespace TreasureChest;

/**
* KeyMapper Interface
*/
interface KeyMapperInterface
{
	public function __construct(CacheInterface $cache, $delimiter = ':');
	public function parse($key);
	public function setDelimiter($delimiter);
	public function invalidate($namespace);
}