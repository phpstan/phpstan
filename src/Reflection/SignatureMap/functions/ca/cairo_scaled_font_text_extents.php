<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_scaled_font_text_extents',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'scaledfont',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairoscaledfont', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'text',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ArrayType::__set_state(array(    'keyType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemTypeInferredFromLiteralArray' => false, ))
);
