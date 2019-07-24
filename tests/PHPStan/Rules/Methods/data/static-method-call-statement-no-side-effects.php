<?php

namespace StaticMethodCallStatementNoSideEffects;

use DateTimeImmutable;

class Foo
{

	public function doFoo(\DateTimeImmutable $dt)
	{
		DateTimeImmutable::createFromFormat('Y-m-d', '2019-07-24');
		$dt::createFromFormat('Y-m-d', '2019-07-24');
	}

}

class MyOwnDateTime extends \DateTime
{

	public function doFoo()
	{
		parent::format('j. n. Y');
		parent::modify('-1 day');
	}

}
