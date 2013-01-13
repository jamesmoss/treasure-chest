<?php

namespace TreasureChest;

interface CacheInterface
{
	public function add($key, $var = null, $ttl = 0);
	public function store($key, $var = null, $ttl = 0);
	public function replace($key, $var = null, $ttl = 0);
	public function exists($key);
	public function fetch($key, &$success = false);
	public function inc($key, $step = 1, &$success = null);
	public function dec($key, $step = 1, &$success = null);
	public function delete($key);
	public function clear();
}