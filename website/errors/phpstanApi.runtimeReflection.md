---
title: "phpstanApi.runtimeReflection"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PhpParser\Node\Expr\FuncCall;

class MyExtension implements DynamicFunctionReturnTypeExtension
{
	public function isFunctionSupported(\PHPStan\Reflection\FunctionReflection $functionReflection): bool
	{
		return true;
	}

	public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
	{
		$ref = new \ReflectionClass('SomeClass');
		$parents = class_parents('SomeClass');

		return null;
	}
}
```

## Why is it reported?

PHP runtime reflection functions and classes (`class_parents()`, `class_implements()`, `is_a()`, `is_subclass_of()`, `class_uses()`, `new ReflectionClass()`, `new ReflectionMethod()`, etc.) rely on classes being loaded at runtime. PHPStan uses a fully static reflection engine that analyses code without executing it, so these runtime reflection calls might not work correctly inside PHPStan extensions.

This error is reported when a class implementing a PHPStan interface (such as `DynamicFunctionReturnTypeExtension`, `Rule`, `Type`, etc.) uses runtime reflection. The classes being reflected may not be autoloadable during static analysis.

## How to fix it

Use PHPStan's `ReflectionProvider` service to obtain class reflection instead of PHP's runtime reflection:

```diff-php
 <?php declare(strict_types = 1);

 use PHPStan\Reflection\ReflectionProvider;

 class MyExtension implements DynamicFunctionReturnTypeExtension
 {
+	public function __construct(private ReflectionProvider $reflectionProvider)
+	{
+	}
+
 	public function getTypeFromFunctionCall(\PHPStan\Reflection\FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
 	{
-		$ref = new \ReflectionClass('SomeClass');
-		$parents = class_parents('SomeClass');
+		$classReflection = $this->reflectionProvider->getClass('SomeClass');
+		$parents = $classReflection->getParentClassesNames();

 		return null;
 	}
 }
```

The `ReflectionProvider` is available as a dependency injection service in PHPStan extensions and provides static reflection that works reliably during analysis.
