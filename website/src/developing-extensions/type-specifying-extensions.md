---
title: Type-Specifying Extensions
---

These extensions allow you to specify types of expressions based on certain type-checking function and method calls, like `is_int()` or `self::assertNotNull()`.

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

This is the the interface for the type-specifying extension:

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

Type-specifying extension cannot have `PHPStan\Analyser\TypeSpecifier` injected in the constructor due to circular reference issue, but the extensions can implement `PHPStan\Analyser\TypeSpecifierAwareExtension` interface to obtain TypeSpecifier via a setter.

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
	return $staticMethodReflection->getName() === 'assertNotNull' && $context->null() && isset($node->args[0]);
}

public function specifyTypes(
	MethodReflection $staticMethodReflection,
	StaticCall $node,
	Scope $scope,
	TypeSpecifierContext $context
): SpecifiedTypes
{
	$expr = $node->args[0]->value;
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

* **instance methods** using `MethodTypeSpecifyingExtension` interface and `phpstan.typeSpecifier.methodTypeSpecifyingExtension` service tag.
* **functions** using `FunctionTypeSpecifyingExtension` interface and `phpstan.typeSpecifier.functionTypeSpecifyingExtension` service tag.
