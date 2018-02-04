<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqlnd_uh_set_connection_proxy',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'connection_proxy',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'MysqlndUhConnection', )),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'mysqli_connection',
			true,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'mysqli', )),
			true,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
