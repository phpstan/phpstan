<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'debug_zval_dump',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'var',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			false,
			true
		),

	],
	true,
	PHPStan\Type\VoidType::__set_state(array())
);
