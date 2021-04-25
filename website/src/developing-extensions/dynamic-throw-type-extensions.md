---
title: Dynamic Throw Type Extensions
---

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 0.12.85</div>

To support [precise try-catch-finally analysis](/blog/precise-try-catch-finally-analysis), you can write a dynamic throw type extension to describe functions and methods that might throw an exception only when specific types of arguments are passed during a call.

Because you have to write the code with the type-resolving logic, it can be as complex as you want.

This is the interface for dynamic throw type extension:

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
	if (count($methodCall->args) < 2) {
		return $methodReflection->getThrowType();
	}

	$argType = $scope->getType($methodCall->args[1]->value);
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

* **static methods** using `DynamicStaticMethodThrowTypeExtension` interface and `phpstan.dynamicStaticMethodThrowTypeExtension` service tag.
* **functions** using `DynamicFunctionThrowTypeExtension` interface and `phpstan.dynamicFunctionThrowTypeExtension` service tag.
