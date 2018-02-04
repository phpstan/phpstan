<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'timezone_transitions_get',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'object',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DateTimeZone', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'timestamp_begin',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'timestamp_end',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ArrayType::__set_state(array(        'keyType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemTypeInferredFromLiteralArray' => false,     )),     1 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),   ), ))
);
