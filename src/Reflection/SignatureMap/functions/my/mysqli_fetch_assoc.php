<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqli_fetch_assoc',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'result',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'mysqli_result', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'array<string,string>',     )),     1 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), ))
);
