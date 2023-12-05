---
title: "Why Is instanceof *Type Wrong and Getting Deprecated?"
date: 2023-02-08
tags: guides
---

This one is for developers of [custom rules](/developing-extensions/rules) and [other extension types](/developing-extensions/extension-types). PHPStan can be extended by writing your own code. Many users take advantage of that and write custom rules for their proprietary code. Some of the [most notable public extensions](/user-guide/extension-library) are [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) and [Larastan](https://github.com/larastan/larastan). PHPStan also serves as the foundation for tools like [Rector](https://github.com/rectorphp/rector). The points and changes described below are important for developers of all those packages.

The upcoming release of PHPStan 1.10 is going to deprecate using `instanceof *Type` for many [Type](/developing-extensions/type-system) interface implementations. We have many good reasons to do so. If you rewrite your code based on the guidelines below, you're going to have edge cases solved for free, and therefore have less bugs. By doing the same thing in PHPStan itself we've also fixed many bug reports.


StringType
-------------------

Let's say you're writing a custom rule and you want to check something about strings. If you follow dump-driven development [^ddd], you would write a piece of code to test your rule against, and in that rule you would `var_dump()` the type representing an expression to figure out how to implement the rule:

[^ddd]: If you've just started working with the AST and with PHPStan's typesystem, you don't have much choice than to start orientating yourself by `var_dump`-ing what's going on.

```php
public function doFoo(string $input): void {
    // yeah, the type of expr in PhpParser\Node\Stmt\Echo_ is PHPStan\Type\StringType
    echo $input;
}
```

In the rule we will not continue unless the type is StringType:

```php
if (!$type instanceof StringType) {
  return [];
}

// ...
```

Looks reasonable, right? But PHPStan 1.10 with [bleeding edge](/blog/what-is-bleeding-edge) enabled or with [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) installed will report the following error [thanks to a new rule](https://github.com/phpstan/phpstan-src/blob/1.11.x/src/Rules/Api/ApiInstanceofTypeRule.php):

> Doing instanceof PHPStan\Type\StringType is error-prone and deprecated. Use Type::isString() instead.

Why is it error-prone? Because not all strings are represented with just a StringType instance. Many strings, like `non-empty-string`, are actually an IntersectionType of StringType and some other accessory type. So in case of some strings, your straightforward check with `instanceof StringType` wouldn't work as expected.

For a reliable check there's a new method `PHPStan\Type\Type::isString(): TrinaryLogic`. Why does it return [TrinaryLogic](/developing-extensions/trinary-logic)? Because it's useful to know that:

* This can't be a string (e.g. an integer, an object)
* This might be a string (e.g. `mixed`, `int|string`)
* This is a string (e.g. `string`, `non-empty-string`, `'a'`)

In most situations you would be interested in the last case with `$type->isString()->yes()` but it's useful to be aware of all possibilities.


ConstantStringType
------------------

Another example of a deprecated check is `instanceof ConstantStringType` which represents a literal string like `'lorem ipsum'`. The common pattern for working with it is:

```php
if (!$type instanceof ConstantStringType) {
  return [];
}

// do something with $type->getValue() (which is 'lorem ipsum')
```

PHPStan 1.10 wants you to use `Type::getConstantStrings(): list<ConstantStringType>`. It's plural because it wants you to handle unions of different strings. So after the refactoring your code is going to be able to handle `'lorem ipsum'|'dolor sit amet'` as well:

```php
if (count($type->getConstantStrings()) === 0) {
    return []; // no constant strings
}

foreach ($type->getConstantStrings() as $constantString) {
    // do something with each value
}
```


ArrayType
------------------

Doing `instanceof ArrayType` comes with the same gotchas as `StringType` described above. When you have a `non-empty-array` type or a `list`, they're actually an `IntersectionType`.

You can replace `instanceof ArrayType` with `Type::isArray(): TrinaryLogic`.


ObjectType
------------------

Doing `instanceof ObjectType` breaks in many situations. Objects can be part of intersection types, objects usually come in unions, object can be represented by a completely different class when it's a `static` type or `$this`.

Use `Type::isObject(): TrinaryLogic` to find out if the type is an object, or `Type::getObjectClassNames(): list<string>` to get a list of class names for a given object type.

You can also use `Type::isSuperTypeOf(): TrinaryLogic` to find out whether an object is of a specific class or its subclass:

```php
// is YES for DateTimeInterface, DateTime, DateTimeImmutable
$isDateTime = (new ObjectType(DateTimeInterface::class))->isSuperTypeOf($type);
```

[Learn more about `isSuperTypeOf()` in the documentation »](/developing-extensions/type-system#querying-a-specific-type)


CallableType
--------------------

Many things in PHP can be a callable, not just the `callable` typehint. Doing `instanceof CallableType` will miss out on many of these types:

* A string with a function name like `'date'` is a callable
* An array with an object and a method like `[$this, 'method']` is a callable
* An array with a class name and a static method like `[Foo::class, 'method']` is a callable
* A `Closure` is a callable
* An object of a class with an `__invoke()` method is a callable

If you ask `Type::isCallable(): TrinaryLogic` it will account for all of these types. And you can get the actual callable signatures with `Type::getCallableParametersAcceptors(): ParametersAcceptor[]` as well.

ConstantBooleanType
--------------------

Surely `true` or `false` doesn't get any more complicated and asking `instanceof ConstantBooleanType` always works, right!?

Let me break that for you:

```php
/**
 * @template T
 * @param T $b
 * @return T
 */
public function sayHello($b)
{
    if ($b === true) {
        // T&true = IntersectionType
        \PHPStan\dumpType($b);
    }
}
```

It's an `IntersectionType` because we need to carry that `$b` is still the template type `T` while at the same time we narrowed it down to `true`.

This is why you should use `Type::isTrue(): TrinaryLogic` and `Type::isFalse(): TrinaryLogic`.

-----------------------------

This isn't an exhaustive list of all types where doing `instanceof *Type` is deprecated in PHPStan 1.10, just the most notable examples. For a complete list please refer to [the actual rule source code](https://github.com/phpstan/phpstan-src/blob/1.11.x/src/Rules/Api/ApiInstanceofTypeRule.php). This list is going to grow bigger in future PHPStan releases.

Don't worry, this is currently just being deprecated. If you have `instanceof *Type` in your code, it's still going to work for the scenarios where it has already worked. But as you can see, switching to the new methods can be very beneficial.

We're deprecating it now so that we have free hands to do more changes in future major PHPStan releases. Not relying on `instanceof` to work will allow us to stop using inheritance in the typesystem. So for example `ConstantStringType` might no longer extend `StringType` in PHPStan 2.0.

Once we deprecate and eradicate `$type instanceof *Type` from PHPStan itself and all 3rd party PHPStan extensions as well, we'll finally be able to decouple types from each other, which will allow us to do cool stuff, e.g. support all template type bounds automatically [^templateTypes], or make all types subtractable automatically [^subtractable] as well.

[^templateTypes]: Right now each bound (like `@template T of int`) requires to have a custom class like `TemplateIntegerType`, so that both `$type instanceof IntegerType` and `$type instanceof TemplateType` pass.

[^subtractable]: Only few selected types are currently subtractable, like `mixed` or `object`. [Playground example](https://phpstan.org/r/6a8c7fbb-9d55-41dd-8913-60ff3aa37f1f)
