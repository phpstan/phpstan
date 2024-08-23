---
title: Custom PHPDoc Types
---

PHPStan lets you override how it converts [PHPDoc Type AST coming from its phpdoc-parser library](https://apiref.phpstan.org/1.12.x/PHPStan.PhpDocParser.Ast.Type.TypeNode.html) into [its type system representation](/developing-extensions/type-system). This can be used to introduce custom utility types - a popular feature known [from other languages like TypeScript](https://www.typescriptlang.org/docs/handbook/utility-types.html).

The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) like [the type system](/developing-extensions/type-system) so check out that guide first and then continue here.

The conversion is done by a class called [`TypeNodeResolver`](https://apiref.phpstan.org/1.12.x/PHPStan.PhpDoc.TypeNodeResolver.html). That's why the interface to implement in this extension type is called [`TypeNodeResolverExtension`](https://apiref.phpstan.org/1.12.x/PHPStan.PhpDoc.TypeNodeResolverExtension.html):

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

TypeNodeResolverExtension cannot have [`TypeNodeResolver`](https://apiref.phpstan.org/1.12.x/PHPStan.PhpDoc.TypeNodeResolver.html) injected in the constructor due to circular reference issue, but the extensions can implement [`TypeNodeResolverAwareExtension`](https://apiref.phpstan.org/1.12.x/PHPStan.PhpDoc.TypeNodeResolverAwareExtension.html) interface to obtain `TypeNodeResolver` via a setter.

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
