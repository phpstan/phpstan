<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'apc_delete',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'key',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'APCIterator',     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),     2 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'string[]',     )),   ), )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
