<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_set_antialias',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'antialias',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'context',
			true,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairocontext', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, ))
);
