<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'array_rand',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'input',
			false,
			PHPStan\Type\ArrayType::__set_state(array(    'keyType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemTypeInferredFromLiteralArray' => false, )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'num_req',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'array<int,int>',     )),     1 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'array<int,string>',     )),     2 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     3 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), ))
);
