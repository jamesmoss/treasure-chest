<?php

namespace TreasureChest;

class InstanceTest extends \PHPUnit_Framework_TestCase
{
	public function testCallingCacheMethods()
	{
		$cache    = $this->getMock('TreasureChest\CacheInterface');
		$instance = new Instance($cache);
		$cacheKey = 'testKey';

		$cache
			->expects($this->exactly(1))
			->method('exists')
			->with($cacheKey)
			->will($this->returnValue(false));

		$this->assertFalse($instance->exists($cacheKey));

		$cache
			->expects($this->exactly(1))
			->method('add')
			->with($cacheKey, 'my value', 5)
			->will($this->returnValue(true));

		$this->assertTrue($instance->add($cacheKey, 'my value', 5));

		$cache
			->expects($this->exactly(1))
			->method('store')
			->with($cacheKey, 'my value', 5)
			->will($this->returnValue(true));

		$this->assertTrue($instance->store($cacheKey, 'my value', 5));

		$cache
			->expects($this->exactly(1))
			->method('replace')
			->with($cacheKey, 'my value', 5)
			->will($this->returnValue(true));

		$this->assertTrue($instance->replace($cacheKey, 'my value', 5));

		$cache
			->expects($this->exactly(1))
			->method('fetch')
			->with($cacheKey)
			->will($this->returnValue('test'));

		$this->assertEquals('test', $instance->fetch($cacheKey));

		$cache
			->expects($this->exactly(1))
			->method('inc')
			->with($cacheKey)
			->will($this->returnValue(true));

		$this->assertTrue($instance->inc($cacheKey));

		$cache
			->expects($this->exactly(1))
			->method('dec')
			->with($cacheKey)
			->will($this->returnValue(true));

		$this->assertTrue($instance->dec($cacheKey));

		$cache
			->expects($this->exactly(1))
			->method('delete')
			->with($cacheKey)
			->will($this->returnValue(true));

		$this->assertTrue($instance->delete($cacheKey));

		$cache
			->expects($this->exactly(1))
			->method('clear')
			->will($this->returnValue(true));

		$this->assertTrue($instance->clear());
	}
}