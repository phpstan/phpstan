<?php

namespace TypesNamespaceDeductedTypes;

use TypesNamespaceFunctions;

class Foo
{

	public function doFoo()
	{
		$integerLiteral = 1;
		$booleanLiteral = true;
		$anotherBooleanLiteral = false;
		$stringLiteral = 'foo';
		$floatLiteral = 1.0;
		$nullLiteral = null;
		$loremObjectLiteral = new Lorem();
		$mixedObjectLiteral = new $class();
		$anotherMixedObjectLiteral = new static();
		$arrayLiteral = [];
		$stringFromFunction = TypesNamespaceFunctions\stringFunction();
		$fooObjectFromFunction = TypesNamespaceFunctions\objectFunction();
		$mixedFromFunction = TypesNamespaceFunctions\unknownTypeFunction();
		die;
	}

}
