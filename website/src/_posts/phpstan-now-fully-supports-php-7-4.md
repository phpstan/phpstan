---
title: "PHPStan now fully supports PHP 7.4!"
date: 2019-08-15
tags: releases
---

Once a year, new minor PHP version is released, and every few years, there’s a new major version. And as the language moves forward, the tooling that goes with it has to move forward too. That’s why I just released [new version of PHPStan](https://github.com/phpstan/phpstan/releases/tag/0.11.13) in the 0.11.x series (so everyone can upgrade) that fully supports the upcoming new version of PHP.

And today, I also want to talk about PHP 7.4 itself. You’ll see why.

## A little recapitulation

Some PHP releases are more substantial than others.

PHP 7.0 was the biggest release of this decade. Huge performance gains that brought PHP on par with HHVM. Scalar typehints. Return typehints. Strict types. Anonymous classes. These features allowed us to develop our apps in a completely new way.

PHP 7.1 filled the gaps that 7.0 missed. Nullable typehints, iterable typehint, void return typehint, class constants visibility modifiers, multi-exception catches.

PHP 7.2 and 7.3 didn’t bring anything memorable to the table in my opinion. But don’t get me wrong, I really appreciate small iterative improvements. Most importantly, the smallness of these releases is what sets off PHP 7.4.

## What’s new in PHP 7.4

This list isn’t meant to be a complete overview of the new version. There’s an [official upgrading document](https://github.com/php/php-src/blob/8a4171ac457c048e1b05b2d9fc9b74f4816272a0/UPGRADING) for that.

Instead, I’d like to go through the most interesting features from the point of view of a static analyser, and present what must be considered when implementing them.

### Typed properties

[Link to RFC](https://wiki.php.net/rfc/typed_properties_v2)

Properties with native typehints will have the biggest impact on the code developers write every day. We don’t have to use phpDocs anymore to typehint our properties:

```php
private string $name;

private ?Address $optionalAddress;
```

PHPStan will check if you typehint existing class names.

It will also allow you to combine phpDocs with native typehint in a meaningful way as it already does with `@param` / `@return` and parameter / return typehints.

If you want to specify what’s in an array, you can do it like this:

```php
/** @var Item[] */
private array $items;
```

PHPStan will interpret `array` and `Item[]` together to produce the right type. It does the same for `iterable`.

Also, if you write something that is impossible to combine, PHPStan will warn you about it:

```php
/** @var Bar */
private Foo $lorem;

> "PHPDoc tag @var for property Ipsum::$lorem with type Bar is incompatible with native type Foo."
```

### Arrow functions

[Link to RFC](https://wiki.php.net/rfc/arrow_functions_v2)

Concise alternative to anonymous functions first introduced in PHP 5.3.

```php
fn (int $i): int => $i + 1
```

The differences are:

- Variables from the outer scope are imported implicitly, no need for the `use`keyword
- Only a single expression can be executed and returned by the function

Interestingly, the arrow function still results in an object with the same `Closure` class.

PHPStan checks if you use existing class names in parameter and return typehints. It also checks if the correct type is returned from the function if you include return typehint in its signature.

### Null coalesce assign operator (??=)

[Link to RFC](https://wiki.php.net/rfc/null_coalesce_equal_operator)

The `??=` operator assigns the value on the right side to left side only if:

- The left side is not set
- Or the left side is null

```php
$foo ??= $bar;
```

PHPStan needs to consider each assign operator in two contexts:

1. What’s the resulting expression type?
1. How will the assigned (left side) change after the assignment?

We need to think about the resulting expression type because assign operators can be used in other expressions:

```php
someFunction($foo ??= $bar);
```

And after an assignment, the left side will change only if the specific conditions are met.

```php
// the variable will be established with value 'foo'
$nonexistentVariable ??= 'foo';

// type of possibly-undefined variable will be combined with 'foo'
$maybeUndefinedVariable ??= 'foo';

// the right side is never used, type of the variable stays the same
$alwaysDefinedNonNullable ??= 'foo';
```

### Array spread operator

[Link to RFC](https://wiki.php.net/rfc/spread_operator_for_array)

This operator (also known as splat operator, or unpack) brings to arrays what’s already possible in function and method calls:

```php
$array = [1, 2, ...[3, 4, 5], 6, 7];
// result is: [1, 2, 3, 4, 5, 6, 7);
```

If the unpacked expression is a literal array, PHPStan expands it in a straightforwad way. When the unpacked expression is a general array or an unknown iterable, PHPStan has to generalize the final array — we can no longer be sure about how many items it will contain. So a literal array becomes `array<int, int>` for instance.

```php
/** @var Iterator<int> */
$someIterator = makeIterator();
$array = [1, 2, ...$someIterator, 4, 5, 6]; // array<int, int>
```

---

PHP 7.4 will most likely [be released](https://wiki.php.net/todo/php74) at the end of November. You can try beta 2 today along with [PHPStan 0.11.13](https://github.com/phpstan/phpstan/releases/tag/0.11.13) and taste the future!
