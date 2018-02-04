<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'fann_create_shortcut',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'num_layers',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'num_neurons1',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'num_neurons2',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'...',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			true
		),

	],
	true,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'reference', ))
);
