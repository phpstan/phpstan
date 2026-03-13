---
title: "PHPStan fully supports PHP 8.5!"
date: 2026-02-13
tags: releases
---

PHP 8.5 has been out for a while now, and so has PHPStan's support for it. [PHPStan 2.1.32](https://github.com/phpstan/phpstan/releases/tag/2.1.32), released back in November 2025, shipped with over 17 PHP 8.5-related changes. It was a massive community effort with many contributors. But I haven't had the chance to sit down and write about all the interesting implementation details until now. It's bigger than how it might seem on the first sight.

`#[NoDiscard]` attribute
--------------------

This is the feature I'm most excited about. It feels like it was made for static analysis, even though it's really a runtime feature.

[`#[NoDiscard]`](https://wiki.php.net/rfc/marking_return_value_as_important) lets you mark functions where ignoring the return value is a likely bug. Call such a function without doing anything with the result and PHP gives you a warning.

```php
#[\NoDiscard]
function createDatabaseBackup(): string
{
    // ...
    return $backupPath;
}

// You probably wanted to do something with the path!
createDatabaseBackup();
```

PHPStan reports: `Call to function createDatabaseBackup() on a separate line discards return value.`

This is great for API design. Think about immutable objects where methods like `withHeader()` or `withStatus()` return a new instance — calling them without using the result is always a mistake. I've seen this bug in real codebases way more times than I'd like to admit.

The `#[NoDiscard]` errors are **non-ignorable** in PHPStan. The attribute is something you put in your code on purpose, so it doesn't really make sense to then ignore the errors it produces. If you don't want the errors, just remove the attribute.

### The `(void)` cast

PHP 8.5 also introduces a `(void)` cast to explicitly say "I know this returns something, and I don't care":

```php
(void) createDatabaseBackup(); // No warning
```

But here's a gotcha: you can't use `(void)` inside other expressions:

```php
$a = (void) someFunction(); // Error!
var_dump((void) someFunction()); // Error!
```

PHPStan reports: `The (void) cast cannot be used within an expression.`

### PHPStan goes a step further

PHPStan doesn't just warn about discarding `#[NoDiscard]` return values — it also reports **unnecessary** `(void)` casts:

```php
function canDiscard(): int
{
    return 1;
}

(void) canDiscard(); // PHPStan reports this!
```

PHPStan reports: `Call to function canDiscard() in (void) cast but function allows discarding return value.`

I always try to clamp new language features from both sides — if you forget to use the return value, that's an error. But if you use `(void)` where there's nothing to suppress, that's also an error. This way you can't go wrong either way.

### Interaction with the pipe operator

The `NoDiscard` rules also handle pipe operator expressions. When they encounter a `Node\Expr\BinaryOp\Pipe`, they unwrap the right-hand side to get at the actual function being called:

```php
5 |> withSideEffects(...); // Error: return value discarded
```

Big thanks to [Daniel Scherzer](https://github.com/DanielEScherzer) for the [initial `#[NoDiscard]` implementation](https://github.com/phpstan/phpstan-src/pull/4253)!

Pipe operator
--------------------

The pipe operator `|>` passes the left-hand side as the sole argument to a callable on the right-hand side:

```php
$result = "Hello World"
    |> trim(...)
    |> strtolower(...)
    |> strlen(...);

// Instead of: strlen(strtolower(trim("Hello World")))
```

What's interesting is that most of PHPStan doesn't actually see the pipe operator at all. The AST gets rewritten on-the-fly to traditional function calls. This is pretty neat because it means all existing rules — type checking, argument validation, return type inference — just work with pipe expressions for free. No need to go and teach every single rule about a new syntax. The transformation handles `FuncCall`, `MethodCall`, and `StaticCall` with first-class callables, so `$x |> $obj->method(...)` simply becomes `$obj->method($x)`.

The only rule that actually operates on the `Pipe` node directly is [`PipeOperatorRule`](https://github.com/phpstan/phpstan-src/blob/__BRANCH__/src/Rules/Operators/PipeOperatorRule.php), which checks that the callable on the right side doesn't accept its parameter by reference:

```php
// PHPStan reports:
// Parameter #1 $value of callable on the right side of pipe operator
// is passed by reference.
$x |> sort(...);
```

One syntax gotcha: **arrow functions need parentheses** when used in a pipe chain.

```php
// This doesn't work:
5 |> fn ($x) => $x * 2;

// You need parentheses:
5 |> (fn ($x) => $x * 2);
```

Clone with
--------------------

We've all been writing `withFoo()` methods on immutable value objects for years. The new `clone()` with property overrides makes this pattern way cleaner:

```php
readonly class Response
{
    public function __construct(
        public int $statusCode,
        public string $body,
    ) {}
}

$response = new Response(200, 'OK');
$error = clone($response, [
    'statusCode' => 500,
    'body' => 'Internal Server Error',
]);
```

PHPStan enforces the same rules for property assignments during cloning as it does for regular assignments — you can't set `private` properties from outside the class, you can't assign `'wrong'` to an `int` property, and readonly properties can only be set from the appropriate scope.

It also infers types from clone expressions. If you clone a generic `object` and pass property overrides, PHPStan knows the result has those properties:

```php
function doBar(object $object): void
{
    $cloned = clone($object, [
        'foo' => 1,
        'bar' => 2,
    ]);
    // PHPStan knows: object&hasProperty(bar)&hasProperty(foo)
}
```

New functions: `array_first()` and `array_last()`
--------------------

Finally, PHP gets proper functions for getting the first and last element of an array. Unlike `reset()` and `end()`, these don't mess with the array's internal pointer, so they're truly pure functions.

PHPStan's [`ArrayFirstLastDynamicReturnTypeExtension`](https://github.com/phpstan/phpstan-src/blob/__BRANCH__/src/Type/Php/ArrayFirstLastDynamicReturnTypeExtension.php) ([#4499](https://github.com/phpstan/phpstan-src/pull/4499), thanks [@canvural](https://github.com/canvural)!) understands them precisely:

```php
/** @param non-empty-array<int, string> $nonEmpty */
function doFoo(array $strings, array $nonEmpty): void
{
    array_first($strings);  // string|null
    array_first($nonEmpty);  // string (no null - guaranteed non-empty!)

    array_first([1 => 'a', 0 => 'b', 2 => 'c']); // 'a'|'b'|'c'
}
```

Notice how PHPStan narrows the return type based on whether the array can be empty — pass a `non-empty-array` and you'll never get `null` back. We liked these functions so much we started using `array_last()` inside PHPStan itself right away ([#4504](https://github.com/phpstan/phpstan-src/pull/4504), thanks [@staabm](https://github.com/staabm)!).

Deprecated casts
--------------------

PHP 8.5 deprecates the non-canonical cast names:

```php
(integer) $m; // Deprecated! Use (int)
(boolean) $m; // Deprecated! Use (bool)
(double) $m;  // Deprecated! Use (float)
(binary) $m;  // Deprecated! Use (string)
```

The [`DeprecatedCastRule`](https://github.com/phpstan/phpstan-src/blob/__BRANCH__/src/Rules/Cast/DeprecatedCastRule.php) reports these so you can clean them up before upgrading.

Deprecated backtick operator
--------------------

The backtick operator for `shell_exec()` is deprecated in PHP 8.5:

```php
$output = `ls -la`; // Deprecated!
```

Pssst, I have a secret for you. If you still have backticks in your source code, and PHPStan reports them as deprecated, try re-running your analysis but add `--fix` to the CLI options. You'll be surprised what happens. I'll have more to say about this later. 

More PHP 8.5 goodies
--------------------

Here are some other notable changes PHPStan now supports:

* **`#[Override]` on properties** — You can now use the `#[Override]` attribute on properties to assert that the property overrides a parent class property. There's a new configuration option `checkMissingOverridePropertyAttribute: true` which can enforce them, similar to [the one for methods](/config-reference#checkmissingoverridemethodattribute).
* **Asymmetric visibility for static properties** — Previously only instance properties supported different read/write visibility. Now static properties can too.
* **`Closure::getCurrent()`** — Returns the current closure, enabling recursion without assigning to a variable first. PHPStan will know the correct signature of the closure if you fetch it with this method.
* **Closures and first-class callables in constant expressions** — Static closures can now be used in constant expressions like property defaults, constants, and attribute parameters. They must be static and can't capture variables.
* **Casts in constant expressions** — You can now use type casts in constant initializers.
* **Support for deprecated traits** — The `#[Deprecated]` attribute now works on traits.
* **`FILTER_THROW_ON_FAILURE`** — New flag for `filter_var()` ([#4495](https://github.com/phpstan/phpstan-src/pull/4495), thanks [@canvural](https://github.com/canvural)!).
* **Global constants support attributes** — Constants can now have attributes.
* **`PHP_BUILD_DATE` type** ([#4468](https://github.com/phpstan/phpstan-src/pull/4468), thanks [@staabm](https://github.com/staabm)!).

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan**](/sponsor). I'd really appreciate it!
