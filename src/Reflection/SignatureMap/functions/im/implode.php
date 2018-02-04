<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'implode',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'glue',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ArrayType::__set_state(array(        'keyType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemTypeInferredFromLiteralArray' => false,     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'pieces',
			true,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ArrayType::__set_state(array(        'keyType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemTypeInferredFromLiteralArray' => false,     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
