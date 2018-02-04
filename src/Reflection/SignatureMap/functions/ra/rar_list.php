<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'rar_list',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'rarfile',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'rararchive', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'RarArchive', ))
);
