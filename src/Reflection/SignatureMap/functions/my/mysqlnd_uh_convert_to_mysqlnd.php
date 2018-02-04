<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqlnd_uh_convert_to_mysqlnd',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'mysql_connection',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'mysqli', )),
			true,
			false
		),

	],
	false,
	PHPStan\Type\ResourceType::__set_state(array())
);
