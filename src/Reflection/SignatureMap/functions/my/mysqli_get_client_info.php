<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqli_get_client_info',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'link',
			true,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'mysqli', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
