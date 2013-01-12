<?php

namespace TreasureChest;

class KeyMapperTest extends \PHPUnit_Framework_TestCase
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