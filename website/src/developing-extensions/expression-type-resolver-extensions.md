---
title: Expression Type Resolver Extensions
---

If you want to override the inferred type of any expression, you can write an expression type resolver extension. This is a catch-all extension point — more general than [dynamic return type extensions](/developing-extensions/dynamic-return-type-extensions) which only work for specific method/function calls.

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

For overriding return types of specific method or function calls, [dynamic return type extensions](/developing-extensions/dynamic-return-type-extensions) are a better fit. Use expression type resolver extensions when you need to override types for arbitrary expressions like property fetches, array accesses, or other constructs that don't have a dedicated extension point.

</div>

This is [the interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.ExpressionTypeResolverExtension.html) your extension needs to implement:

```php
namespace PHPStan\Type;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

interface ExpressionTypeResolverExtension
{

	public function getType(Expr $expr, Scope $scope): ?Type;

}
```

The `getType()` method receives any expression node and the current scope. Return a `Type` to override the inferred type, or return `null` when your extension doesn't care about the given expression.

Here's an example extension that narrows the type of array dimension fetches on a config object — `$config['database']` always returns a `string`:

```php
namespace App\PHPStan;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class ConfigArrayAccessExtension implements ExpressionTypeResolverExtension
{

	public function getType(Expr $expr, Scope $scope): ?Type
	{
		if (!$expr instanceof ArrayDimFetch) {
			return null;
		}

		if ($expr->dim === null) {
			return null;
		}

		$varType = $scope->getType($expr->var);
		if (!(new ObjectType(\App\Config::class))->isSuperTypeOf($varType)->yes()) {
			return null;
		}

		$dimType = $scope->getType($expr->dim);
		if ($dimType instanceof ConstantStringType && $dimType->getValue() === 'database') {
			return new StringType();
		}

		return null;
	}

}
```

With this extension, PHPStan narrows the type of `$config['database']`:

```php
function foo(Config $config): void
{
	// Without extension: mixed
	// With extension: string
	$db = $config['database'];
}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\ConfigArrayAccessExtension
		tags:
			- phpstan.broker.expressionTypeResolverExtension
```
