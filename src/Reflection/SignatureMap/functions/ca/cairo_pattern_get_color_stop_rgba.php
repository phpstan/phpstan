<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_pattern_get_color_stop_rgba',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'pattern',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairogradientpattern', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'index',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ArrayType::__set_state(array(    'keyType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemTypeInferredFromLiteralArray' => false, ))
);
