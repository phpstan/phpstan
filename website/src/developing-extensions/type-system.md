---
title: Type System
---

PHPStan's type system is a collection of classes implementing the common [`PHPStan\Type\Type`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.Type.html) interface to inform the analyser about relationships between types, and their behaviour.

To retrieve the type of an [AST](/developing-extensions/abstract-syntax-tree) expression, you need to call the `getType()` method on the [Scope](/developing-extensions/scope) object.

Each type that we can encounter in PHP language and [in PHPDocs](/writing-php-code/phpdoc-types) has [an implementation counterpart](https://apiref.phpstan.org/1.12.x/namespace-PHPStan.Type.html) in PHPStan:

<details class="typesystem-types">
    <summary class="text-blue-500 font-bold">Show table of <code>PHPStan\Type\Type</code> implementations</summary>

| Type                          | PHPStan class                                        |
|-------------------------------|------------------------------------------------------|
| `mixed`                       | `PHPStan\Type\MixedType`                             |
| `Foo` (object of class `Foo`) | `PHPStan\Type\ObjectType`                            |
| `Foo<T>`                      | `PHPStan\Type\Generic\GenericObjectType`             |
| `object`                      | `PHPStan\Type\ObjectWithoutClassType`                |
| `array`                       | `PHPStan\Type\ArrayType`                             |
| `int`                         | `PHPStan\Type\IntegerType`                           |
| Integer interval              | `PHPStan\Type\IntegerRangeType`                      |
| `float`                       | `PHPStan\Type\FloatType`                             |
| `null`                        | `PHPStan\Type\NullType`                              |
| `string`                      | `PHPStan\Type\StringType`                            |
| `class-string`                | `PHPStan\Type\ClassStringType`                       |
| `class-string<T>`             | `PHPStan\Type\Generic\GenericClassStringType`        |
| `static`                      | `PHPStan\Type\StaticType`                            |
| `$this`                       | `PHPStan\Type\ThisType`                              |
| `void`                        | `PHPStan\Type\VoidType`                              |
| `callable`                    | `PHPStan\Type\CallableType`                          |
| `iterable`                    | `PHPStan\Type\IterableType`                          |
| `never`                       | `PHPStan\Type\NeverType`                             |
| Enum case (Foo::LOREM)        | `PHPStan\Type\Enum\EnumCaseObjectType`               |


In some cases PHPStan knows about the literal value of an expression. These classes implement the `PHPStan\Type\ConstantType` interface:

| Type                                                         | PHPStan class                               |
|--------------------------------------------------------------|---------------------------------------------|
| [Array shapes](/writing-php-code/phpdoc-types#array-shapes)  | `PHPStan\Type\Constant\ConstantArrayType`   |
| `true` and `false`                                           | `PHPStan\Type\Constant\ConstantBooleanType` |
| Integers                                                     | `PHPStan\Type\Constant\ConstantIntegerType` |
| Floats                                                       | `PHPStan\Type\Constant\ConstantFloatType`   |
| Strings                                                      | `PHPStan\Type\Constant\ConstantStringType`  |

Some types exist to only consist of other types (learn more about [union vs. intersection types](/blog/union-types-vs-intersection-types):

| Type                          | PHPStan class                         |
|-------------------------------|---------------------------------------|
| Union types                   | `PHPStan\Type\UnionType`              |
| Intersection types            | `PHPStan\Type\IntersectionType`       |

Some advanced types are implemented by combining different types in an intersection type:

| Type                          | First PHPStan class        | Second PHPStan class     |
|-------------------------------|----------------------------|--------------------------|
| `non-empty-string`            | `PHPStan\Type\StringType`  | `PHPStan\Type\Accessory\AccessoryNonEmptyStringType` |
| `numeric-string`              | `PHPStan\Type\StringType`  | `PHPStan\Type\Accessory\AccessoryNumericStringType`  |
| `callable-string`             | `PHPStan\Type\StringType`  | `PHPStan\Type\CallableType`                          |
| `literal-string`              | `PHPStan\Type\StringType`  | `PHPStan\Type\Accessory\AccessoryLiteralStringType`  |
| `non-empty-array`             | `PHPStan\Type\ArrayType`   | `PHPStan\Type\Accessory\NonEmptyArrayType`           |
| After asking about `method_exists()` | Any object type     | `PHPStan\Type\Accessory\HasMethodType`               |
| After asking about `property_exists()` | Any object type     | `PHPStan\Type\Accessory\HasPropertyType`           |
| After asking about `isset()` or `array_key_exists()` | `PHPStan\Type\ArrayType`    | `PHPStan\Type\Accessory\HasOffsetType`           |

[Generic template types](/blog/generics-in-php-using-phpdocs) are represented with classes that implement the `PHPStan\Type\Generic\TemplateType` interface. This table describes which implementation is used for different bounds (the X in `@template T of X`):

| Type                          | PHPStan class                                        |
|-------------------------------|------------------------------------------------------|
| `mixed` or no bound           | `PHPStan\Type\Generic\TemplateMixedType`               |
| `Foo` (object of class `Foo`) | `PHPStan\Type\Generic\TemplateObjectType`              |
| `Foo<T>`                      | `PHPStan\Type\Generic\TemplateGenericObjectType`       |
| `object`                      | `PHPStan\Type\Generic\TemplateObjectWithoutClassType`  |
| `int`                         | `PHPStan\Type\Generic\TemplateIntegerType`             |
| `string`                      | `PHPStan\Type\Generic\TemplateStringType`              |
| Union types                   | `PHPStan\Type\Generic\TemplateUnionType`               |

</details>

What can a type tell us?
-----------------

The [`PHPStan\Type\Type`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.Type.html) interface offers many methods to ask about the capabilities of values of this specific type. Following list is by no means complete, please see the interface code for more details.

The `describe()` method returns a string representation (description) of the type, which is useful for error messages. For example `StringType` returns `'string'`.

The `accepts()` method tells us whether the type accepts a different type. For example `IntegerType` accepts a different `IntegerType` or `ConstantIntegerType`. The `accepts()` method doesn't return a boolean, but a [`TrinaryLogic`](/developing-extensions/trinary-logic) object. This method shouldn't be used for [querying a specific type](#querying-a-specific-type) because the `accepts()` semantics are complicated. For example `FloatType` also accepts `IntegerType`.

There are methods that answer questions about properties, methods, and constants accessed on a type. The `get*` methods return [reflection](/developing-extensions/reflection) objects.

* `canAccessProperties(): TrinaryLogic`
* `hasProperty(string $propertyName): TrinaryLogic`
* `getProperty(string $propertyName, Scope $scope): PropertyReflection`
* `canCallMethods(): TrinaryLogic`
* `hasMethod(string $methodName): TrinaryLogic`
* `getMethod(string $methodName, Scope $scope): MethodReflection`
* `canAccessConstants(): TrinaryLogic`
* `hasConstant(string $constantName): TrinaryLogic`
* `getConstant(string $constantName): ConstantReflection`

It's safe to call a `get*` method only after making sure that call to `has*` method with the same argument returns `yes`.

Querying a specific type
-----------------

If we need to know whether we are working with a specific type, there are multiple ways to do it and it might seem they all work until we realize there's an edge case that's not covered by one of the naive approaches.

If we want to know that we're working with a `string`, the first thing that comes to mind is to do `$type instanceof StringType`. But that's not going to work if we have a `numeric-string` (an `IntersectionType` consisting of `StringType` and `AccessoryNumericStringType` as can be seen in the table above). Neither it's going to work if we have a union of literal strings, like `'foo'|'bar'`.

Asking about an array with `$type instanceof ArrayType` is also a wrong approach - it's not going to work for unions of `ConstantArrayType` and it's not going to work for `non-empty-array` - which is an `IntersectionType` with `NonEmptyArrayType`.

The best way to ask about a specific type is the `PHPStan\Type\Type::isSuperTypeOf(Type $type): TrinaryLogic` method. To understand how it works we need to imagine types as circles. The biggest circle containing all other types is `mixed` (also known as the [top type](https://en.wikipedia.org/wiki/Top_type)). The smallest circle that's empty (doesn't contain any type besides itself) is `never` (also known as the [bottom type](https://en.wikipedia.org/wiki/Bottom_type)).

This visual image of overlapping circles tells us how `isSuperTypeOf()` always responds. Let's say we draw a hierarchy of `Throwable` - `Exception` - `InvalidArgumentException`. It can look like this:

<img class="ml-auto mr-auto w-96 mb-8" src="/tmp/images/issupertypeof-1.png" />

Let's say we have three `Type` objects: `new ObjectType(\Throwable::class)` (T), `new ObjectType(\Exception::class)` (E), and `new ObjectType(\InvalidArgumentException)` (IAE). Asking both `T->isSuperTypeOf(E)` and `T->isSuperTypeOf(IAE)` will return `yes`. Because `T` is the largest circle and contains both `E` and `IAE`.

If we ask `E->isSuperTypeOf(T)`, it returns `maybe`. Because in runtime `T` might contain something that falls inside the `E` circle (like `InvalidArgumentException`) or something that falls outside of the `E` circle (like `TypeError`).

If we add `new ObjectType(\stdClass::class)` (S) to the mix, both `T->isSuperTypeOf(S)` and `S->isSuperTypeOf(T)` return `no` because the `S` type is a separate circle from the `T` type.

These relationships work between all types, as long as you imagine the circles correctly.

Circling back to our original examples, the correct way to ask whether `PHPStan\Type\Type` is a `string`, use this piece of code:

```php
use PHPStan\Type\StringType;

/// ...

$isString = (new StringType())->isSuperTypeOf($type);
if ($isString->yes()) {
    // we definitely have a string in $type,
    // such as StringType or a UnionType of ConstantStringType objects
} elseif ($isString->maybe()) {
    // we might have a string in $type
    // it might be string|null or even mixed
} else {
    // we definitely don't have a string in $type
    // so it's for example an int or something else
}
```

In PHPStan 1.10 and later there are also new and easy shortcut methods like `Type::isString(): TrinaryLogic` that will tell you the same thing.

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

Read an article on the same topic: [Why Is instanceof *Type Wrong and Getting Deprecated?](/blog/why-is-instanceof-type-wrong-and-getting-deprecated)

</div>

Type normalization
-----------------

Types in non-canonical form can and should be simplified. If we write `mixed|int` (a union type) into a PHPDoc, PHPStan normalizes the type to `mixed`, because `int` is already contained in `mixed`. Keeping the `int` in the union doesn't add any extra information. The supertype always wins.

If we write `mixed&int` (an intersection type), it's normalized to `int`. The subtype always wins.

Type normalization also prevents some invalid types to exist, like `string&int` - that's normalized to `never` and detected as an error by PHPStan rules.

When creating custom types like:

```php
$union = new UnionType([$a, $b, $c]);
$intersection = new IntersectionType([$a, $b, $c]);
```

Consider using `PHPStan\Type\TypeCombinator` instead to kick off the type normalization:

```php
$union = TypeCombinator::union($a, $b, $c);
$intersection = TypeCombinator::intersect($a, $b, $c);
```

Considerations for custom types
-----------------

You can also implement a custom type, usually representing a subtype of a more general type. This is typically done by extending an existing Type implementation, like [`StringType`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.StringType.html) or [`ObjectType`](https://apiref.phpstan.org/1.12.x/PHPStan.Type.ObjectType.html). The methods you'll always need to override in your implementation are:

* `public function describe(VerbosityLevel $level): string`
* `public function equals(Type $type): bool`
* `public function isSuperTypeOf(Type $type): TrinaryLogic`
* `public function accepts(Type $type, bool $strictTypes): TrinaryLogic`

It's important to correctly implement and test the `isSuperTypeOf()` method. It tells the relationships between types - which one is more general and which one is more specific (see [Querying a specific type](#querying-a-specific-type). The best way to test this method is through `TypeCombinator::union()` (a more general type should win) and `TypeCombinator::intersect()` (a more specific type should win).

For example if you're implementing your own `UuidStringType`, `TypeCombinator::union(new StringType(), new UuidStringType())` should result in `StringType`. On the other hand, `TypeCombinator::intersect(new StringType(), new UuidStringType())` should result in `UuidStringType`. PHPStan has excessive tests for the built-in types in [TypeCombinatorTest](https://github.com/phpstan/phpstan-src/blob/1.12.x/tests/PHPStan/Type/TypeCombinatorTest.php) which is a great starting point to learn from.

Once you have your own `PHPStan\Type\Type` implementation, you can add support for it in PHPDocs [through a custom TypeNodeResolverExtension](/developing-extensions/custom-phpdoc-types).
