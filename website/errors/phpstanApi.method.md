---
title: "phpstanApi.method"
shortDescription: "Calling a PHPStan method not covered by the backward compatibility promise."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Php\ArrayKeyDynamicReturnTypeExtension;

class MyExtension
{
	public function doFoo(ArrayKeyDynamicReturnTypeExtension $ext, FunctionReflection $func): void
	{
		$ext->isFunctionSupported($func);
	}
}
```

## Why is it reported?

A method declared in a PHPStan class is being called, but the method is not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise). A method is covered only when its declaring class or interface, or the method itself, is marked with the `@api` PHPDoc tag. Methods not covered by this promise might change or be removed in minor PHPStan versions without notice, breaking the extension that calls them.

This applies to instance method calls (`$obj->method()`), static method calls (`Foo::method()`), and first-class callables (`$obj->method(...)`, `Foo::method(...)`). Constructors are not checked by this identifier — instantiating PHPStan classes is checked by [phpstanApi.constructor](/error-identifiers/phpstanApi.constructor).

If you think the method should be covered by the backward compatibility promise, you can [open a discussion](https://github.com/phpstan/phpstan/discussions).

## How to fix it

Use only methods that are part of PHPStan's public API — methods on `@api`-tagged classes and interfaces, or methods tagged with `@api` themselves. Instead of calling into another extension's internals, use the public reflection and type APIs to achieve the same goal:

```diff-php
 <?php declare(strict_types = 1);

 use PHPStan\Reflection\FunctionReflection;
-use PHPStan\Type\Php\ArrayKeyDynamicReturnTypeExtension;

 class MyExtension
 {
-	public function doFoo(ArrayKeyDynamicReturnTypeExtension $ext, FunctionReflection $func): void
+	public function doFoo(FunctionReflection $func): bool
 	{
-		$ext->isFunctionSupported($func);
+		return $func->getName() === 'key';
 	}
 }
```
