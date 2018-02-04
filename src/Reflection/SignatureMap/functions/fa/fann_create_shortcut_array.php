<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'fann_create_shortcut_array',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'num_layers',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'layers',
			false,
			PHPStan\Type\ArrayType::__set_state(array(    'keyType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemTypeInferredFromLiteralArray' => false, )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ResourceType::__set_state(array())
);
