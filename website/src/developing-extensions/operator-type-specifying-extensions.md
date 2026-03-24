---
title: Operator Type Specifying Extensions
---

If you're working with PHP extensions that overload arithmetic operators (like GMP or BCMath), you can write an operator type specifying extension to describe how operators like `+`, `-`, `*`, `/`, `**`, `%`, `^` should infer types when applied to your custom objects.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

This is [the interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.OperatorTypeSpecifyingExtension.html) your extension needs to implement:

```php
namespace PHPStan\Type;

interface OperatorTypeSpecifyingExtension
{

	public function isOperatorSupported(string $operatorSigil, Type $leftSide, Type $rightSide): bool;

	public function specifyType(string $operatorSigil, Type $leftSide, Type $rightSide): Type;

}
```

The extension has two methods:

* `isOperatorSupported()` — receives the operator sigil (e.g. `+`, `-`, `*`) and the types of both operands. Return `true` when your extension wants to handle this combination.
* `specifyType()` — called when `isOperatorSupported()` returns `true`. Return the resulting type of the operation.

Here's an example for a `Decimal` class that supports arithmetic:

```php
namespace App\PHPStan;

use PHPStan\Type\ObjectType;
use PHPStan\Type\OperatorTypeSpecifyingExtension;
use PHPStan\Type\Type;

class DecimalOperatorExtension implements OperatorTypeSpecifyingExtension
{

	public function isOperatorSupported(string $operatorSigil, Type $leftSide, Type $rightSide): bool
	{
		$decimalType = new ObjectType(\App\Decimal::class);

		if (in_array($operatorSigil, ['+', '-', '*', '/'], true)) {
			return $decimalType->isSuperTypeOf($leftSide)->yes()
				&& $decimalType->isSuperTypeOf($rightSide)->yes();
		}

		return false;
	}

	public function specifyType(string $operatorSigil, Type $leftSide, Type $rightSide): Type
	{
		return new ObjectType(\App\Decimal::class);
	}

}
```

With this extension, PHPStan knows that arithmetic on `Decimal` objects produces a `Decimal`:

```php
function calculate(Decimal $a, Decimal $b): void
{
	$c = $a + $b;
	// Without extension: mixed (PHPStan doesn't know about operator overloading)
	// With extension: Decimal
	\PHPStan\dumpType($c); // App\Decimal
}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\DecimalOperatorExtension
		tags:
			- phpstan.broker.operatorTypeSpecifyingExtension
```

Unary Operator Type Specifying Extensions
---

For unary operators (`-`, `+`, `~`), there's a separate [interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.UnaryOperatorTypeSpecifyingExtension.html):

```php
namespace PHPStan\Type;

interface UnaryOperatorTypeSpecifyingExtension
{

	public function isOperatorSupported(string $operatorSigil, Type $operand): bool;

	public function specifyType(string $operatorSigil, Type $operand): Type;

}
```

It works the same way as the binary variant but takes a single operand instead of two. Register it with the `phpstan.broker.unaryOperatorTypeSpecifyingExtension` service tag:

```yaml
services:
	-
		class: App\PHPStan\DecimalUnaryOperatorExtension
		tags:
			- phpstan.broker.unaryOperatorTypeSpecifyingExtension
```
