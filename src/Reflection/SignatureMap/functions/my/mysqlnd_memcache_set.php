<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqlnd_memcache_set',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'mysql_connection',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => true, )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'memcache_connection',
			true,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'Memcached', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'pattern',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'callback',
			true,
			PHPStan\Type\CallableType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
