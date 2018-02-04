<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqli_stmt_get_result',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'stmt',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'mysqli_stmt', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),     1 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'mysqli_result',     )),   ), ))
);
