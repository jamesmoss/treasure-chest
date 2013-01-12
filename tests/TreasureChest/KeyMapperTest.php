<?php

namespace TreasureChest;

class KeyMapperTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		require_once __DIR__.'/FauxCache.php';
		$this->cache  = new FauxCache();
		$this->mapper = new KeyMapper($this->cache);
	}

	public function testMapperWithoutNamespace()
	{
		$this->assertEquals('surname', $this->mapper->parse('surname'));
	}

	public function testMultipleNamespaces()
	{
		$key = 'test:users:james:age';
		$this->assertEquals('0:0:0:test_users_james:age', $this->mapper->parse($key));
	}

	public function testSingleNamespace()
	{
		$key = 'blog:date';
		$this->assertEquals('0:blog:date', $this->mapper->parse($key));
	}
}