<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'eio_open',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'path',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'flags',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'mode',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'pri',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'callback',
			false,
			PHPStan\Type\CallableType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'data',
			true,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => true, )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ResourceType::__set_state(array())
);
