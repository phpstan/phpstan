<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'eio_sync_file_range',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'fd',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => true, )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'offset',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'nbytes',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
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
			'pri',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'callback',
			true,
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
