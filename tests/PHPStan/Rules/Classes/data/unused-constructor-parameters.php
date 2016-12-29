<?php

namespace UnusedConstructorParameters;

class Foo
{

	private $foo;

	public function __construct(
		$usedParameter,
		$usedParameterInStringOne,
		$usedParameterInStringTwo,
		$usedParameterInStringThree,
		$usedParameterInCondition,
		$unusedParameter
	)
	{
		$this->foo = $usedParameter;
		var_dump("Hello $usedParameterInStringOne");
		var_dump("Hello {$usedParameterInStringTwo}");
		var_dump("Hello ${usedParameterInStringThree}");
		if (doFoo()) {
			$this->foo = $usedParameterInCondition;
		}
	}

}

interface Bar
{

	public function __construct($interfaceParameter);

}
