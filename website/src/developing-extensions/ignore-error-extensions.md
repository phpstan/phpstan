---
title: Ignore Error Extensions
---

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.1.7</div>

Sometimes, you might want to ignore errors based on the Node and Scope.

For example:

* Ignore `missingType.iterableValue` on controller methods (actions):
  Rule: when the method is public and it has `#[Route]` attribute or the class has `#[AsController]` attribute.
* Ignore `should return int but returns int|null` on `getId` for entities.
  Rule: class needs to have `#[Entity]` attribute.
* Ignore `never returns null so it can be removed from the return type`
  Rule: method needs to have `#[GraphQL\Field]` attribute.
* Enforce `missingCheckedExceptionInThrows` partially, only for specific classes.

You can create an extension that implements [IgnoreErrorExtension](https://apiref.phpstan.org/2.1.x/PHPStan.Analyser.IgnoreErrorExtension.html).

```php
use PhpParser\Node;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;

// This extension will ignore "missingType.iterableValue" errors for public Action methods inside Controller classes.
final class ControllerActionReturnTypeIgnoreExtension implements IgnoreErrorExtension
{
	public function shouldIgnore(Error $error, Node $node, Scope $scope) : bool
	{
		if ($error->getIdentifier() !== 'missingType.iterableValue') {
			return false;
		}

		// @phpstan-ignore phpstanApi.instanceofAssumption
		if (! $node instanceof InClassMethodNode) {
			return false;
		}

		if (! str_ends_with($node->getClassReflection()->getName(), 'Controller')) {
			return false;
		}

		if (! str_ends_with($node->getMethodReflection()->getName(), 'Action')) {
			return false;
		}

		if (! $node->getMethodReflection()->isPublic()) {
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
