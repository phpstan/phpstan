<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'rar_entry_get',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'entryname',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'rarfile',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'rararchive', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'RarEntry', ))
);
