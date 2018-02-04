<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'similar_text',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'str1',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'str2',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'percent',
			true,
			PHPStan\Type\FloatType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
