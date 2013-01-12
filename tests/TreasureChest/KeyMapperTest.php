<?php

namespace TreasureChest;

class KeyMapperTest extends \PHPUnit_Framework_TestCase
{
	public function testNonExistantMethod()
	{
		$cache = new Cache\Filesystem('/tmp');

		$mapper = new KeyMapper($cache);

		$key = 'test:users:james:age';

		var_dump($mapper->parse($key));
	}
}