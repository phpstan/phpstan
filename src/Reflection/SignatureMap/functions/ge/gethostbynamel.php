<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'gethostbynamel',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'hostname',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ArrayType::__set_state(array(        'keyType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemTypeInferredFromLiteralArray' => false,     )),     1 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),   ), ))
);
