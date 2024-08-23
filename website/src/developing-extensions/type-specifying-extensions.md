---
title: Type-Specifying Extensions
---

These extensions allow you to specify types of expressions based on certain type-checking function and method calls, like `is_int()` or `self::assertNotNull()`.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

Most scenarios no longer require you to write an extension - only a PHPDoc to describe the behavior of your function.

Check out [narrowing types](/writing-php-code/narrowing-types#custom-type-checking-functions-and-methods) to learn more.

</div>

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

```php
if (is_int($variable)) {
    // here we can be sure that $variable is integer
}
```

```php
// using PHPUnit's asserts

self::assertNotNull($variable);
// here we can be sure that $variable is not null
```

This is the [the interface for the type-specifying extension](https://apiref.phpstan.org/1.12.x/PHPStan.Type.StaticMethodTypeSpecifyingExtension.html):

```php
namespace PHPStan\Type;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;

interface StaticMethodTypeSpecifyingExtension
{

	public function getClass(): string;

	public function isStaticMethodSupported(
		MethodReflection $staticMethodReflection,
		StaticCall $node,
		TypeSpecifierContext $context
	): bool;

	public function specifyTypes(
		MethodReflection $staticMethodReflection,
		StaticCall $node,
		Scope $scope,
		TypeSpecifierContext $context
	): SpecifiedTypes;

}
```

Type-specifying extension cannot have [`PHPStan\Analyser\TypeSpecifier`](https://apiref.phpstan.org/1.12.x/PHPStan.Analyser.TypeSpecifier.html) injected in the constructor due to circular reference issue, but the extensions can implement [`PHPStan\Analyser\TypeSpecifierAwareExtension`](https://apiref.phpstan.org/1.12.x/PHPStan.Analyser.TypeSpecifierAwareExtension.html) interface to obtain TypeSpecifier via a setter.

This is how you'd write the extension for the second example above:

```php
public function getClass(): string
{
	return \PHPUnit\Framework\Assert::class;
}

public function isStaticMethodSupported(
	MethodReflection $staticMethodReflection,
	StaticCall $node,
	TypeSpecifierContext $context
): bool
{
	// The $context argument tells us if we're in an if condition or not (as in this case).
	// Is assertNotNull called with at least 1 argument?
	return $staticMethodReflection->getName() === 'assertNotNull' && $context->null() && isset($node->getArgs()[0]);
}

public function specifyTypes(
	MethodReflection $staticMethodReflection,
	StaticCall $node,
	Scope $scope,
	TypeSpecifierContext $context
): SpecifiedTypes
{
	$expr = $node->getArgs()[0]->value;
	$typeBefore = $scope->getType($expr);
	$type = TypeCombinator::removeNull($typeBefore);

	// Assuming extension implements \PHPStan\Analyser\TypeSpecifierAwareExtension

	return $this->typeSpecifier->create($expr, $type, TypeSpecifierContext::createTruthy());
}
```

And finally, register the extension to PHPStan in the [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\AssertNotNullTypeSpecifyingExtension
		tags:
			- phpstan.typeSpecifier.staticMethodTypeSpecifyingExtension
```

There's also analogous functionality for:

* **instance methods** using [`MethodTypeSpecifyingExtension`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.MethodTypeSpecifyingExtension.html) interface and `phpstan.typeSpecifier.methodTypeSpecifyingExtension` service tag.
* **functions** using [`FunctionTypeSpecifyingExtension`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.FunctionTypeSpecifyingExtension.html) interface and `phpstan.typeSpecifier.functionTypeSpecifyingExtension` service tag.
