---
title: Dynamic Return Type Extensions
---

If the return type of a method is not always the same, but depends on an argument passed to the method, you can specify the return type by writing and registering an extension.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

<!-- TODO link generics -->
<!-- TODO different example - ParameterBag -->

Because you have to write the code with the type-resolving logic, it can be as complex as you want.

After writing the sample extension, the variable `$mergedArticle` will have the correct type:

```php
$mergedArticle = $this->entityManager->merge($article);
// $mergedArticle will have the same type as $article
```

This is the interface for dynamic return type extension:

```php
namespace PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;

interface DynamicMethodReturnTypeExtension
{

	public function getClass(): string;

	public function isMethodSupported(MethodReflection $methodReflection): bool;

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type;

}
```

And this is how you'd write the extension that correctly resolves the `EntityManager::merge()` return type:

```php
public function getClass(): string
{
	return \Doctrine\ORM\EntityManager::class;
}

public function isMethodSupported(MethodReflection $methodReflection): bool
{
	return $methodReflection->getName() === 'merge';
}

public function getTypeFromMethodCall(
	MethodReflection $methodReflection,
	MethodCall $methodCall,
	Scope $scope
): Type
{
	if (count($methodCall->getArgs()) === 0) {
		return \PHPStan\Reflection\ParametersAcceptorSelector::selectFromArgs(
			$scope,
			$methodCall->getArgs(),
			$methodReflection->getVariants()
		)->getReturnType();
	}
	$arg = $methodCall->getArgs()[0]->value;

	return $scope->getType($arg);
}
```

And finally, register the extension in the [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\EntityManagerDynamicReturnTypeExtension
		tags:
			- phpstan.broker.dynamicMethodReturnTypeExtension
```

There's also analogous functionality for:

* **static methods** using [`DynamicStaticMethodReturnTypeExtension`](https://github.com/phpstan/phpstan-src/blob/master/src/Type/DynamicStaticMethodReturnTypeExtension.php) interface and `phpstan.broker.dynamicStaticMethodReturnTypeExtension` service tag.
* **functions** using [`DynamicFunctionReturnTypeExtension`](https://github.com/phpstan/phpstan-src/blob/master/src/Type/DynamicFunctionReturnTypeExtension.php) interface and `phpstan.broker.dynamicFunctionReturnTypeExtension` service tag.
