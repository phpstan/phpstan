<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'dom_xpath_query',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'expr',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'context',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DOMNode', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'registernodens',
			false,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DOMNodeList', ))
);
