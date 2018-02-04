<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'class_uses',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'what',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectWithoutClassType::__set_state(array(     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'autoload',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'array<string,string>', ))
);
