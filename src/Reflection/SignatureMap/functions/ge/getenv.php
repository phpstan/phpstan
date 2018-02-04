<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'getenv',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'varname',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'local_only',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'array<string,string>',     )),     1 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),     2 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), ))
);
