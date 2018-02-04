<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'sqlsrv_errors',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'errorsOrWarnings',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => true, ))
);
