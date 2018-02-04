<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'dom_import_simplexml',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'node',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'SimpleXMLElement', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'DOMElement',     )),     1 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),   ), ))
);
