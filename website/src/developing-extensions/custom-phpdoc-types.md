---
title: Custom PHPDoc Types
---

PHPStan lets you override how it converts [PHPDoc Type AST coming from its phpdoc-parser library](https://apiref.phpstan.org/__BRANCH__/PHPStan.PhpDocParser.Ast.Type.TypeNode.html) into [its type system representation](/developing-extensions/type-system). This can be used to introduce custom utility types - a popular feature known [from other languages like TypeScript](https://www.typescriptlang.org/docs/handbook/utility-types.html).

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) like [the type system](/developing-extensions/type-system) so check out that guide first and then continue here.

The conversion is done by a class called [`TypeNodeResolver`](https://apiref.phpstan.org/__BRANCH__/PHPStan.PhpDoc.TypeNodeResolver.html). That's why the interface to implement in this extension type is called [`TypeNodeResolverExtension`](https://apiref.phpstan.org/__BRANCH__/PHPStan.PhpDoc.TypeNodeResolverExtension.html):

```php
namespace PHPStan\PhpDoc;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;

interface TypeNodeResolverExtension
{

	public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type;

}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: MyApp\PHPStan\MyTypeNodeResolverExtension
		tags:
			- phpstan.phpDoc.typeNodeResolverExtension
```

TypeNodeResolverExtension cannot have [`TypeNodeResolver`](https://apiref.phpstan.org/__BRANCH__/PHPStan.PhpDoc.TypeNodeResolver.html) injected in the constructor due to circular reference issue, but the extensions can implement [`TypeNodeResolverAwareExtension`](https://apiref.phpstan.org/__BRANCH__/PHPStan.PhpDoc.TypeNodeResolverAwareExtension.html) interface to obtain `TypeNodeResolver` via a setter.

An example
----------------

Let's say we want to implement the [`Pick` utility type](https://www.typescriptlang.org/docs/handbook/utility-types.html#picktype-keys) from TypeScript. It will allow us to achieve the code in the following example:

```php
/**
 * @phpstan-type Address array{name: string, surname: string, street: string, city: string, country: Country}
 */
class Foo
{

    /**
     * @param Pick<Address, 'name' | 'surname'> $personalDetails
     */
    public function doFoo(array $personalDetails): void
    {
        \PHPStan\dumpType($personalDetails); // array{name: string, surname: string}
    }

}
```

This is how we'd be able to achieve that in our own `TypeNodeResolverExtension`:

```php
namespace MyApp\PHPStan;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class MyTypeNodeResolverExtension implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension
{

	private TypeNodeResolver $typeNodeResolver;

	public function setTypeNodeResolver(TypeNodeResolver $typeNodeResolver): void
	{
		$this->typeNodeResolver = $typeNodeResolver;
	}

	public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type
	{
		if (!$typeNode instanceof GenericTypeNode) {
			// returning null means this extension is not interested in this node
			return null;
		}

		$typeName = $typeNode->type;
		if ($typeName->name !== 'Pick') {
			return null;
		}

		$arguments = $typeNode->genericTypes;
		if (count($arguments) !== 2) {
			return null;
		}

		$arrayType = $this->typeNodeResolver->resolve($arguments[0], $nameScope);
		$keysType = $this->typeNodeResolver->resolve($arguments[1], $nameScope);

		$constantArrays = $arrayType->getConstantArrays();
		if (count($constantArrays) === 0) {
			return null;
		}

		$newTypes = [];
		foreach ($constantArrays as $constantArray) {
			$newTypeBuilder = ConstantArrayTypeBuilder::createEmpty();
			foreach ($constantArray->getKeyTypes() as $i => $keyType) {
				if (!$keysType->isSuperTypeOf($keyType)->yes()) {
					// eliminate keys that aren't in the Pick type
					continue;
				}

				$valueType = $constantArray->getValueTypes()[$i];
				$newTypeBuilder->setOffsetValueType(
					$keyType,
					$valueType,
					$constantArray->isOptionalKey($i),
				);
			}

			$newTypes[] = $newTypeBuilder->getArray();
		}

		return TypeCombinator::union(...$newTypes);
	}

}
```

One example of TypeNodeResolverExtension usage is in the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension. Before [intersection types](https://phpstan.org/blog/union-types-vs-intersection-types) picked up the pace and were largely unknown to PHP community, developers often written `Foo|MockObject` when they actually meant `Foo&MockObject`. So [the extension actually fixed it for them](https://github.com/phpstan/phpstan-phpunit/blob/1.1.x/src/PhpDoc/PHPUnit/MockObjectTypeNodeResolverExtension.php) and made PHPStan interpret `Foo|MockObject` as an intersection type.

Late-resolvable types
----------------

The `Pick` example above resolves its result eagerly - the moment the PHPDoc is parsed, it already knows the array shape and the keys, so it can compute the final type right away.

But that's not always possible. Sometimes the final type depends on [generics](/blog/generics-in-php-using-phpdocs) that are only known at each call site. Consider:

```php
/**
 * @template T of array
 * @template K of key-of<T>
 * @param T $array
 * @param K $key
 * @return T[K]
 */
function getOffset(array $array, int|string $key)
{
    return $array[$key];
}
```

When PHPStan statically interprets the `@return T[K]` PHPDoc, both `T` and `K` are still [template type](/developing-extensions/type-system) variables - there's no concrete array shape and no concrete key to look up yet. The return type can only be computed once the function is actually called and `T` and `K` get substituted with real types:

```php
$r = getOffset(['a' => 1, 'b' => 'foo'], 'a');
\PHPStan\dumpType($r); // int

$r = getOffset(['a' => 1, 'b' => 'foo'], 'b');
\PHPStan\dumpType($r); // string
```

This is what **late-resolvable types** are for. Instead of resolving immediately, the type holds onto its sub-types and resolves itself later, after the template types have been substituted at the call site.

A late-resolvable type implements the [`LateResolvableType`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.LateResolvableType.html) interface and uses the `LateResolvableTypeTrait`:

```php
namespace PHPStan\Type;

interface LateResolvableType
{

	public function resolve(): Type;

	public function isResolvable(): bool;

}
```

The trait implements the whole `Type` interface for you by delegating every method to `resolve()`. You only need to provide three things:

- `isResolvable()` - returns `false` while the type still contains unresolved template types, `true` once everything is concrete.
- `getResult()` - computes the actual type. The trait calls this from `resolve()` (and caches the result).
- the usual `Type` boilerplate that can't be delegated, like `equals()`, `describe()`, `traverse()` and `toPhpDocNode()`.

`OffsetAccessType` (the `T[K]` from the example above) is a good illustration of how little code this takes:

```php
final class OffsetAccessType implements CompoundType, LateResolvableType
{

	use LateResolvableTypeTrait;
	use NonGeneralizableTypeTrait;

	public function __construct(
		private Type $type,
		private Type $offset,
	)
	{
	}

	public function isResolvable(): bool
	{
		return !TypeUtils::containsTemplateType($this->type)
			&& !TypeUtils::containsTemplateType($this->offset);
	}

	protected function getResult(): Type
	{
		return $this->type->getOffsetValueType($this->offset);
	}

	// equals(), describe(), traverse(), toPhpDocNode() etc.

}
```

While `isResolvable()` is `false`, the type stays as `T[K]` and travels through the analysis untouched; once the call-site substitution makes it resolvable, `getResult()` turns it into the concrete value type at that offset.

PHPStan ships several built-in late-resolvable types you can study as references - they all live in `phpstan-src` and follow the same pattern:

- [`KeyOfType`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.KeyOfType.html) - `key-of<T>`
- [`ValueOfType`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.ValueOfType.html) - `value-of<T>`
- [`OffsetAccessType`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.OffsetAccessType.html) - `T[K]`
- [`ConditionalType`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.ConditionalType.html) and [`ConditionalTypeForParameter`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.ConditionalTypeForParameter.html) - `T extends X ? Y : Z`
- [`ClassConstantAccessType`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.ClassConstantAccessType.html) - `T::SOME_CONSTANT`
- [`NewObjectType`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Type.NewObjectType.html) - `new T`

So when your `TypeNodeResolverExtension` builds a custom type whose result depends on generics, return a late-resolvable type from `resolve()` instead of trying to compute the final type up front.
