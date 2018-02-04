<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'forward_static_call',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'function',
			false,
			PHPStan\Type\CallableType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'parameters',
			true,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			false,
			true
		),

	],
	true,
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, ))
);
