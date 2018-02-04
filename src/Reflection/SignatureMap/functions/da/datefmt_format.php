<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'datefmt_format',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'fmt',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'IntlDateFormatter', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'value',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ArrayType::__set_state(array(        'keyType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemType' =>        PHPStan\Type\MixedType::__set_state(array(          'isExplicitMixed' => false,       )),        'itemTypeInferredFromLiteralArray' => false,     )),     1 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'DateTime',     )),     2 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     3 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'IntlCalendar',     )),   ), )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
