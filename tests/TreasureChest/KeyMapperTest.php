<?php

namespace TreasureChest;

class KeyMapperTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->cache  = new Cache\Faux();
		$this->mapper = new KeyMapper($this->cache);
	}

	public function testMapperWithoutNamespace()
	{
		$this->assertEquals('surname', $this->mapper->parse('surname'));
	}

	public function testSingleNamespace()
	{
		$key = 'blog:date';
		$this->assertEquals('0:blog:date', $this->mapper->parse($key));
		// Assert the same again - the second time it will be got from the index
		$this->assertEquals('0:blog:date', $this->mapper->parse($key));
	}

	public function testMultipleNamespaces()
	{
		$key = 'test:users:james:age';
		$this->assertEquals('0:0:0:test:users:james:age', $this->mapper->parse($key));
	}

	public function testInvalidatingMultipleNamespaces()
	{
		$key = 'catalogue:shirts:product:title';
		$this->assertEquals('0:0:0:catalogue:shirts:product:title', $this->mapper->parse($key));

		$this->mapper->invalidate('catalogue');
		$this->assertEquals('1:0:0:catalogue:shirts:product:title', $this->mapper->parse($key));

		$this->mapper->invalidate('catalogue:shirts');
		$this->assertEquals('1:1:0:catalogue:shirts:product:title', $this->mapper->parse($key));

		$this->mapper->invalidate('catalogue:shirts:product');
		$this->assertEquals('1:1:1:catalogue:shirts:product:title', $this->mapper->parse($key));

		$this->mapper->invalidate('catalogue:shirts');
		$this->assertEquals('1:2:1:catalogue:shirts:product:title', $this->mapper->parse($key));

		$this->mapper->invalidate('catalogue:shirts');
		$this->assertEquals('1:3:1:catalogue:shirts:product:title', $this->mapper->parse($key));
	}

	public function testInvalidatingSingleNamespace()
	{
		$key = 'product:title';
		$this->mapper->invalidate('product');
		$this->assertEquals('1:product:title', $this->mapper->parse($key));

		$this->mapper->invalidate('product');
		$this->assertEquals('2:product:title', $this->mapper->parse($key));
	}

	public function testInvalidatingEmpty()
	{
		$this->assertFalse($this->mapper->invalidate(''));
	}

	public function testSettingDelimiter()
	{
		$this->mapper->setDelimiter('.');
	}

	/**
	 * @expectedException        TreasureChest\Exception
	 * @expectedExceptionMessage delimiter must be exactly one character
	 */
	public function testSettingLongDelimiter()
	{
		$this->mapper->setDelimiter('--');
	}
}