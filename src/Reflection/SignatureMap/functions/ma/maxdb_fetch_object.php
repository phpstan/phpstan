<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'maxdb_fetch_object',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'result',
			false,
			PHPStan\Type\ObjectWithoutClassType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectWithoutClassType::__set_state(array())
);
