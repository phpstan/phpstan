<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'rar_comment_get',
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
	PHPStan\Type\StringType::__set_state(array())
);
