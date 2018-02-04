<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'http_response_code',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'response_code',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\BooleanType::__set_state(array(     )),     1 =>      PHPStan\Type\IntegerType::__set_state(array(     )),   ), ))
);
