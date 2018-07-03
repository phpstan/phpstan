<?php

namespace DuplicateKeys;

class Foo
{

	private const EQ = '=';
	private const IS = '=';
	private const NEQ = '!=';

	public function doFoo()
	{
		$a = [
			null => true,
			NULL => false,
			1 => 'aaa',
			2 => 'bbb',
			3 => 'ccc',
			1 => 'aaa',
			1.0 => 'aaa',
			true => 'aaa',
			false => 'aaa',
			0 => 'aaa',
			PHPSTAN_DUPLICATE_KEY => 'aaa',
		];
	}

	public function doBar()
	{
		$array = [
			self::EQ => '= %s',
			self::IS => '= %s',
			self::NEQ => '!= %s',
		];
	}

}
