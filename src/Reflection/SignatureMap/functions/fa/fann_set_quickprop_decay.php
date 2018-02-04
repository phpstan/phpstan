<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'fann_set_quickprop_decay',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'ann',
			false,
			PHPStan\Type\ResourceType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'quickprop_decay',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
