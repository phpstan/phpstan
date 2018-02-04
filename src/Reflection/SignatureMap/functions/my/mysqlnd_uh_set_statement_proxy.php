<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqlnd_uh_set_statement_proxy',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'statement_proxy',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'MysqlndUhStatement', )),
			true,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
