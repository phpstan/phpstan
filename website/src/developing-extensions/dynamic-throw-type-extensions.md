---
title: Dynamic Throw Type Extensions
---

To support [precise try-catch-finally analysis](/blog/precise-try-catch-finally-analysis), you can write a dynamic throw type extension to describe functions and methods that might throw an exception only when specific types of arguments are passed during a call.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

Because you have to write the code with the type-resolving logic, it can be as complex as you want.

This is [the interface](https://apiref.phpstan.org/1.12.x/PHPStan.Type.DynamicMethodThrowTypeExtension.html) for dynamic throw type extension:

```php
namespace PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;

interface DynamicMethodThrowTypeExtension
{

	public function isMethodSupported(MethodReflection $methodReflection): bool;

	public function getThrowTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): ?Type;

}
```

An example
----------------

Let's say you have a method with an implementation that looks like this:

```php
/** @throws ComponentNotFoundException */
public function getComponent(string $name, bool $throw): ?Component
{
	if (!array_key_exists($name, $this->components)) {
		if ($throw) {
			throw new ComponentNotFoundException($name);
		}

		return null;
	}

	return $this->components[$name];
}
```

This is how you'd write the extension that tells PHPStan the exception can be thrown only when `$throw` is `true`:

```php
public function isMethodSupported(MethodReflection $methodReflection): bool
{
	return $methodReflection->getDeclaringClass()->getName() === ComponentContainer::class
		&& $methodReflection->getName() === 'getComponent';
}

public function getThrowTypeFromMethodCall(
	MethodReflection $methodReflection,
	MethodCall $methodCall,
	Scope $scope
): ?Type
{
	if (count($methodCall->getArgs()) < 2) {
		return $methodReflection->getThrowType();
	}

	$argType = $scope->getType($methodCall->getArgs()[1]->value);
	if ((new ConstantBooleanType(true))->isSuperTypeOf($argType)->yes()) {
		return $methodReflection->getThrowType();
	}

	return null;
}
```

And finally, register the extension in the [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\GetComponentThrowTypeExtension
		tags:
			- phpstan.dynamicMethodThrowTypeExtension
```

There's also analogous functionality for:

* **static methods** using [`DynamicStaticMethodThrowTypeExtension`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.DynamicStaticMethodThrowTypeExtension.html) interface and `phpstan.dynamicStaticMethodThrowTypeExtension` service tag.
* **functions** using [`DynamicFunctionThrowTypeExtension`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.DynamicFunctionThrowTypeExtension.html) interface and `phpstan.dynamicFunctionThrowTypeExtension` service tag.
