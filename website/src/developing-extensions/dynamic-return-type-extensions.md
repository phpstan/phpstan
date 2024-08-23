---
title: Dynamic Return Type Extensions
---

If the return type of a method is not always the same, but depends on an argument passed to the method, you can specify the return type by writing and registering an extension.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

Most scenarios no longer require you to write an extension - only a PHPDoc to describe the behavior of your function.

Check out [generics](/blog/generics-in-php-using-phpdocs) and [conditional return types](/writing-php-code/phpdoc-types#conditional-return-types) to learn more.

</div>

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

<!-- TODO link generics -->
<!-- TODO different example - ParameterBag -->

Because you have to write the code with the type-resolving logic, it can be as complex as you want.

After writing the sample extension, the variable `$mergedArticle` will have the correct type:

```php
$mergedArticle = $this->entityManager->merge($article);
// $mergedArticle will have the same type as $article
```

This is [the interface for dynamic return type extension](https://apiref.phpstan.org/1.12.x/PHPStan.Type.DynamicMethodReturnTypeExtension.html):

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
	): ?Type;
}
```

And this is how you'd write the extension that correctly resolves the `EntityManager::merge()` return type:

```php
namespace App\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

class EntityManagerDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
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
	): ?Type
	{
		if (count($methodCall->getArgs()) === 0) {
			return null;
		}
		$arg = $methodCall->getArgs()[0]->value;

		return $scope->getType($arg);
	}
}
```

`ParametersAcceptorSelector::selectFromArgs(...)` is the default way to resolve the return type of a method call. Starting from PHPStan 1.5.0 the return type of `getTypeFromMethodCall()` is optional, so you can return `null` from it if you don't want to resolve to a specific `Type`.

Finally, register the extension in the [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\EntityManagerDynamicReturnTypeExtension
		tags:
			- phpstan.broker.dynamicMethodReturnTypeExtension
```

There's also analogous functionality for:

* **static methods** using [`DynamicStaticMethodReturnTypeExtension`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.DynamicStaticMethodReturnTypeExtension.html) interface and `phpstan.broker.dynamicStaticMethodReturnTypeExtension` service tag.
* **functions** using [`DynamicFunctionReturnTypeExtension`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.DynamicFunctionReturnTypeExtension.html) interface and `phpstan.broker.dynamicFunctionReturnTypeExtension` service tag.
