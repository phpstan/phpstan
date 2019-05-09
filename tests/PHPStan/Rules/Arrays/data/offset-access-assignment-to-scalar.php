<?php

namespace OffsetAccessAssignment;

class Test1
{

	public function test()
	{
		$value = [];
		$value['foo'] = null;

		$value = 'Foo';
		$value['foo'] = null;

		$value = 'Foo';
		$value[] = 'test';

		$value = 'Foo';
		$value[12.34] = 'test';

		/** @var string|array $maybeString */
		$maybeString = [];
		$maybeString[0] = 'foo';

		/** @var string|array $maybeString */
		$maybeString = [];
		$maybeString['foo'] = 'foo';

		/** @var int|object $maybeInt */
		$maybeInt = null;

		/** @var string|array $maybeString */
		$maybeString = [];
		$maybeString[$maybeInt] = 'foo';

		$value = 'Foo';
		$value[$maybeInt] = 'foo';

		$value = new \stdClass();
		$value['foo'] = null;

		$value = true;
		$value['foo'] = null;

		$value = false;
		$value['foo'] = null;

		/** @var resource $value */
		$value = null;
		$value['foo'] = null;

		$value = 42;
		$value['foo'] = null;

		$value = 4.141;
		$value['foo'] = null;

		/** @var array|int $value */
		$value = [];
		$value['foo'] = null;

		$string1 = 'Foo';
		$string1[999] = 'B';
		$string2 = 'Foo';
		$string2[false] = 'C';
		$string3 = 'Foo';
		$string3[new \stdClass()] = 'E';

		$key = [1, 2, 3];
		$storage = new \SplObjectStorage();
		$storage[$key] = 'test';

		$obj1 = new ObjectWithOffsetAccess();
		$obj1[false] = 'invalid key, valid value';

		$obj2 = new ObjectWithOffsetAccess();
		$obj2['valid key'] = ['invalid value'];

		$obj3 = new ObjectWithOffsetAccess();
		$obj3[] = 'null key';
	}

}

class ObjectWithOffsetAccess implements \ArrayAccess
{

	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return true;
	}

	/**
	 * @param string $offset
	 * @return int
	 */
	public function offsetGet($offset)
	{
		return 0;
	}

	/**
	 * @param string $offset
	 * @param int $value
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
	}

	/**
	 * @param string $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
	}

}
