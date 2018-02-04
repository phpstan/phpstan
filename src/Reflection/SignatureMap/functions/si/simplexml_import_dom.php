<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'simplexml_import_dom',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'node',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DOMNode', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'class_name',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),     1 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'SimpleXMLElement',     )),   ), ))
);
