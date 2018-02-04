<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'xmlrpc_set_type',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'value',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'DateTime',     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'type',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
