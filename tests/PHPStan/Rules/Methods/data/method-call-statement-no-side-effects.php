<?php

namespace MethodCallStatementNoSideEffects;

class Foo
{

	public function doFoo(\DateTime $dt)
	{
		$dt->modify('+1 month');
	}

	public function doBar(\DateTimeImmutable $dti)
	{
		$dti->modify('+1 month');
		$dti->createFromFormat('Y-m-d', '2019-07-24');
	}

}
