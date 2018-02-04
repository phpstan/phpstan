<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'msg_receive',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'queue',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'desiredmsgtype',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'msgtype',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'maxsize',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'message',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'unserialize',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'flags',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'errorcode',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
