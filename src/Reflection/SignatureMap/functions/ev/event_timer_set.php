<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'event_timer_set',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'event',
			false,
			PHPStan\Type\ResourceType::__set_state(array()),
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
			'arg',
			true,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => true, )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
