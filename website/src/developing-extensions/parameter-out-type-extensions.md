---
title: Parameter Out Type Extensions
---

When a function or method modifies a by-reference parameter, PHPStan uses the [`@param-out` PHPDoc tag](/writing-php-code/phpdocs-basics#setting-parameter-type-passed-by-reference) to know the type of the variable after the call. But when the output type depends on the arguments passed to the call, a static PHPDoc is not sufficient.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

This is [the interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.MethodParameterOutTypeExtension.html) your extension needs to implement for non-static methods:

```php
namespace PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;

interface MethodParameterOutTypeExtension
{

	public function isMethodSupported(
		MethodReflection $methodReflection,
		ParameterReflection $parameter,
	): bool;

	public function getParameterOutTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		ParameterReflection $parameter,
		Scope $scope,
	): ?Type;

}
```

The extension has two methods:

* `isMethodSupported()` — return `true` when your extension wants to handle the given method and parameter combination.
* `getParameterOutTypeFromMethodCall()` — return the output type of the by-reference parameter, or `null` to fall back to the default behavior.

Here's an example for a method that populates a by-reference parameter with a type determined by the first argument:

```php
namespace App\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MethodParameterOutTypeExtension;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class CastMethodParameterOutExtension implements MethodParameterOutTypeExtension
{

	public function isMethodSupported(
		MethodReflection $methodReflection,
		ParameterReflection $parameter,
	): bool
	{
		return $methodReflection->getDeclaringClass()->getName() === \App\Caster::class
			&& $methodReflection->getName() === 'cast'
			&& $parameter->getName() === 'value';
	}

	public function getParameterOutTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		ParameterReflection $parameter,
		Scope $scope,
	): ?Type
	{
		$args = $methodCall->getArgs();
		if (count($args) < 1) {
			return null;
		}

		$typeArg = $scope->getType($args[0]->value);
		if ($typeArg instanceof ConstantStringType && $typeArg->getValue() === 'int') {
			return new IntegerType();
		}

		return new StringType();
	}

}
```

With this extension, PHPStan knows the type of the by-reference parameter after the call:

```php
$caster = new Caster();

$caster->cast('int', $value);
\PHPStan\dumpType($value); // int

$caster->cast('string', $value);
\PHPStan\dumpType($value); // string
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\CastMethodParameterOutExtension
		tags:
			- phpstan.methodParameterOutTypeExtension
```

There's also analogous functionality for:

* **static methods** using [`StaticMethodParameterOutTypeExtension`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.StaticMethodParameterOutTypeExtension.html) interface and `phpstan.staticMethodParameterOutTypeExtension` service tag.
* **functions** using [`FunctionParameterOutTypeExtension`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.FunctionParameterOutTypeExtension.html) interface and `phpstan.functionParameterOutTypeExtension` service tag.
