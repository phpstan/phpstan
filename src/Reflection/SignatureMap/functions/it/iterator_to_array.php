<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'iterator_to_array',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'it',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'Traversable', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'use_keys',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ArrayType::__set_state(array(    'keyType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemTypeInferredFromLiteralArray' => false, ))
);
