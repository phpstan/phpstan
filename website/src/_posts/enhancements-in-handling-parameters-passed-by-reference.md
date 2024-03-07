---
title: "Enhancements in Handling Parameters Passed by Reference in PHPStan 1.10.60"
date: 2024-03-07
tags: releases
---

[PHPStan 1.10.60](https://github.com/phpstan/phpstan/releases/tag/1.10.59) introduces enhancements in handling parameters passed by reference. These parameters, besides typical returned values, also serve as a form of output that a called function can produce:

```php
function foo(&$p): void
{
	$p = true;
}

$v = false;
foo($v);
var_dump($v); // bool(true)
```

You might be surprised to learn that you can set a type for a parameter passed by reference, but it doesn't limit the output type:

```php
function foo(string &$p): void
{
	$p = 1;
}

$v = 'foo';
foo($v);
var_dump($v); // int(1)
```

Previously, PHPStan's type inference discarded the type of a variable passed into such a parameter:

```php
function foo(string &$p): void
{
	$p = 1;
}

$v = 'foo';
foo($v);
\PHPStan\dumpType($v); // mixed
```

When analysing code that calls a function, PHPStan relies on function's signature and PHPDoc to understand what's going to happen. It does not dive into the function implementation because it'd hurt the performance of the analyser. So changing the type of the variable to `mixed` was a sensible solution - we didn't know what's going on inside the function so we could not assume anything.

In the latest release, this behavior has changed.

All of the following changes are part of [Bleeding Edge](/blog/what-is-bleeding-edge) because we intend to keep our generous [backward compatibility promise](/user-guide/backward-compatibility-promise). You can get them in PHPStan 1.10.60 today if you include the Bleeding Edge config. Everybody else will get them as part of the next major release, PHPStan 2.0.

Checking the Type of Argument Passed into a Parameter by Reference
--------------------

The recent [pull request](https://github.com/phpstan/phpstan-src/pull/2941) by Lincoln Maskey initiated the development of these enhancements.

Previously, PHPStan skipped type checking for all parameters passed by reference. Consequently, code like the following did not trigger any warnings:

```php
function foo(string &$p): void
{
	// ...
}

$v = 1;
foo($v);
```

This was because some internal built-in PHP functions have parameters passed by reference in their signature, but the type enforcement didn't match that of userland functions.

However, PHPStan is now equipped to check the type in such cases, ensuring more comprehensive type validation moving forward.

Assume the "out" type is the same as the "in" type
--------------------

Instead of setting the type of the passed variable to `mixed`, we now assume that the user does not intend to change the type entirely:

```php
function foo(string &$p): void
{
	// ...
}

$v = 'foo';
foo($v);
\PHPStan\dumpType($v); // string
```

However, this creates a problem: what if we do want to change the type? You can still achieve this using [`@param-out`](/writing-php-code/phpdocs-basics#setting-parameter-type-passed-by-reference) PHPDoc tag:

```php
/**
 * @param-out int $p
 */
function foo(string &$p): void
{
	$p = 1;
}

$v = 'foo';
foo($v);
\PHPStan\dumpType($v); // int
```


Enforcing Assigned Type
--------------------

Psst, I'll let you in on a little secret. Not all extra PHPDoc features static analysers offer are actually enforced by their rules. `@param-out` was one example. You could have assigned anything to the variable and PHPStan would not complain.

That changes today. These assignments are now checked by new set of rules:

```php
function foo(string &$p): void
{
	// Parameter &$p by-ref type of function foo() expects string, int given.
	// Tip: You can change the parameter out type with @param-out PHPDoc tag.
	$p = 1;
}
```

Yes, PHPStan will even contextually hint to you that you can write a `@param-out` PHPDoc tag if the assignment to an integer is intentional. These tips can be seen next to a 💡 in the CLI output, and also as [a small grey text on the on-line playground](/r/846c85d9-2159-4a18-9118-04eca47e266d).

If `@param-out` is already involved, the message is a little bit different:

```php
/**
 * @param-out string $p
 */
function foo(string &$p): void
{
	// Parameter &$p @param-out type of function foo() expects string, int given.
	$p = 1;
}
```

And if the type of `@param-out` is different to the input type, but we don't reassign the variable, PHPStan is also able to notice it:

```php
/**
 * @param-out int $p
 */
function foo(string &$p): void // Parameter &$p @param-out type of function foo() expects int, string given.
{

}
```

Detecting Overly Broad `@param-out` Type
---------------------

Similar to how PHPStan handles return types, it now detects when a union type in the output parameter type includes unused parts:

```php
function foo(?string &$p): void
{
	// Function foo() never assigns null to &$p so it can be removed from the by-ref type.
	// Tip: You can narrow the parameter out type with @param-out PHPDoc tag.
	$p = 'foo';
}
```

If your function accepts `null` but the variable never leaves the function as `null` anymore, you should inform the caller with the `@param-out` tag as well:

```php
/**
 * @param-out string $p
 */
function foo(?string &$p): void
{
	// No errors
	$p = 'foo';
}
```

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan**](/sponsor). I’d really appreciate it!
