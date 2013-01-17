<?php

namespace TreasureChest\Cache;


class FilesystemTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		if(!is_dir($dir = __DIR__.'/tmp')) {
			mkdir($dir);
			chmod($dir, 0777);
		}

		$this->cache = new Filesystem(__DIR__.'/tmp');
	}

	public function tearDown()
	{
		array_map('unlink', glob(__DIR__.'/tmp/*'));
		rmdir(__DIR__.'/tmp');
	}

	/**
	 * @expectedException TreasureChest\Exception
	 */
	public function testUnwritableDirectory()
	{
		new Filesystem('/sdfsdf/324/af/334/sadas/pp/q/xc/j');
	}

	public function testTtlExpires()
	{
		$this->cache->store('surname', 'baggins', 2);

		$this->assertTrue($this->cache->exists('surname'));
		sleep(1);
		$this->assertTrue($this->cache->exists('surname'));
		sleep(1);
		$this->assertFalse($this->cache->exists('surname'));
	}

	public function testStoringObject()
	{
		$obj = new \stdClass;
		$obj->price = 25.00;
		$obj->title = 'Blue Shirt';
		$obj->published = false;

		$this->cache->store('product', $obj);

		$hydrated = $this->cache->fetch('product');

		$this->assertInstanceOf('\\stdClass', $hydrated);
	}

	public function testStoringArray()
	{
		$arr = array(1, 6, 'apple', 'pear', 'visible' => true);

		$this->cache->store('fruits', $arr);

		$hydrated = $this->cache->fetch('fruits');

		$this->assertInternalType('array', $hydrated);
	}
}
