---
title: Type System
---

PHPStan's type system is a collection of classes implementing the common [`PHPStan\Type\Type`](https://github.com/phpstan/phpstan-src/blob/master/src/Type/Type.php) interface to inform the analyser about relationships between types, and their behaviour.

To retrieve the type of an [AST](/developing-extensions/abstract-syntax-tree) expression, you need to call the `getType()` method on the [Scope](/developing-extensions/scope) object.

Each type that we can encounter in PHP language and [in PHPDocs](/writing-php-code/phpdoc-types) has an implementation counterpart in PHPStan:

<details>
    <summary>Show table of <code>PHPStan\Type\Type</code> implementations</summary>

| Type                          | PHPStan class                                        |
|-------------------------------|------------------------------------------------------|
| `mixed`                       | `PHPStan\Type\MixedType`                             |
| `Foo` (object of class `Foo`) | `PHPStan\Type\ObjectType`                            |
| `Foo<T>`                      | `PHPStan\Type\Generic\GenericObjectType`             |
| `object`                      | `PHPStan\Type\ObjectWithoutClassType`                |
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

The [`PHPStan\Type\Type`](https://github.com/phpstan/phpstan-src/blob/master/src/Type/Type.php) interface offers many methods to ask about the capabilities of values of this specific type.

The `describe()` method returns a string representation (description) of the type, which is useful for error messages. For example `StringType` returns `string`.

The `accepts()` method tells us whether the type accepts a different type. For example `IntegerType` accepts a different `IntegerType` or `ConstantIntegerType`. The `accepts()` method doesn't return a boolean, but a [`TrinaryLogic`](/developing-extensions/trinary-logic) object.
