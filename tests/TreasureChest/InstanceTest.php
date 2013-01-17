<?php

namespace TreasureChest;

class InstanceTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException TreasureChest\Exception
	 */
	public function testNonExistantMethod()
	{
		$cache = new Instance(new Cache\Filesystem('/tmp'));

		$cache->flubble('user123:username');
	}
}