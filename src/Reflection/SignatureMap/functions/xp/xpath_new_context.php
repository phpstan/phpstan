<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'xpath_new_context',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'dom_document',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DOMDocument', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'XPathContext', ))
);
