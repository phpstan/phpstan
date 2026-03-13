---
title: Ignore Error Extensions
---

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.1.7</div>

Sometimes, you might want to [ignore errors](/user-guide/ignoring-errors) based on the Error, Node and Scope.

For example:

* Ignore `missingType.iterableValue` on controller methods (actions): when the method is public and it has `#[Route]` attribute or the class has `#[AsController]` attribute.
* Ignore `should return int but returns int|null` on `getId` for entities when the class has the `#[Entity]` attribute.
* Ignore `never returns null so it can be removed from the return type` when the method has `#[GraphQL\Field]` attribute.
* Enforce `missingCheckedExceptionInThrows` partially, only for specific classes.

You can create an extension that implements [IgnoreErrorExtension](https://apiref.phpstan.org/__BRANCH__/PHPStan.Analyser.IgnoreErrorExtension.html).

```php
use PhpParser\Node;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;

// This extension will ignore "missingType.iterableValue" errors for public Action methods inside Controller classes.
final class ControllerActionReturnTypeIgnoreExtension implements IgnoreErrorExtension
{
	public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
	{
		if ($error->getIdentifier() !== 'missingType.iterableValue') {
			return false;
		}

		if (!$scope->isInClass()) {
			return false;
		}
		$classReflection = $scope->getClassReflection();

		$methodReflection = $scope->getFunction();
		if ($methodReflection === null) {
			return false;
		}

		if (!str_ends_with($classReflection->getName(), 'Controller')) {
			return false;
		}

		if (!str_ends_with($methodReflection->getName(), 'Action')) {
			return false;
		}

		if (!$node->getMethodReflection()->isPublic()) {
			return false;
		}

		return true;
	}
}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: MyApp\PHPStan\ControllerActionReturnTypeIgnoreExtension
		tags:
			- phpstan.ignoreErrorExtension
```
