<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqli_num_fields',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'link',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'mysqli_result', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
