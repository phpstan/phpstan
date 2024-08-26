---
title: "PHPStan 1.12: Road to PHPStan 2.0"
date: 2024-08-27
tags: releases
---

After three years since the initial [PHPStan 1.0 release](/blog/phpstan-1-0-released), we're getting closer to PHPStan 2.0. After sifting through my list of ideas for the new major version, I realized I can move some of them forward and release them in 1.x series and hide them behind the [Bleeding Edge config toggle](/blog/what-is-bleeding-edge), so they can be enjoyed by PHPStan users sooner.

This isn't true just for PHPStan 1.12 but ever since 1.0. If you enable Bleeding Edge, you basically live in the future. You get new rules and behavior changes that will be enabled for everyone in the next major version. That's your reward as an early adopter.

Here's an equation:

PHPStan 2.0 = PHPStan 1.12 + Bleeding Edge + BC breaks {.text-center .font-bold .text-lg}

When you upgrade to PHPStan 1.12 and enable Bleeding Edge, you can get mostly ready for PHPStan 2.0 today.

But enough about the future. Here's what's new in [today's 1.12 release](https://github.com/phpstan/phpstan/releases/tag/1.12.0).

General availability of precise type inference for regular expressions
-----------------------------

This is a rare example of a feature that began its life in Bleeding Edge but got out of it sooner than the next major version. Because of its complexity we needed a staged rollout to weed out the bugs.

First [introduced in 1.11.6](https://github.com/phpstan/phpstan/releases/tag/1.11.6), and improved by dozens of pull requests since then, this feature is about figuring out the precise type for `$matches` by-ref argument coming from `preg_match()` and other related functions:

```php
if (preg_match('/Price: (?<currency>£|€)\d+/', $s, $matches, PREG_UNMATCHED_AS_NULL)) {
	// array{0: string, currency: non-empty-string, 1: non-empty-string}
	\PHPStan\dumpType($matches);
}
```

Markus Staab and Jordi Boggiano of Composer fame [worked really hard on this](https://staabm.github.io/2024/07/05/array-shapes-for-preg-match-matches.html). It brings a whole new level of precision to PHPStan's type inference.

From what I gathered, this ability is pretty unique and not many programming languages offer it. And now we have it in PHP!


Fix blind spots in PHPDoc tags type checks
-----------------------------

PHPStan performs four categories of sanity checks on [types in PHPDocs](/writing-php-code/phpdoc-types):

* Missing types: performed on level 6 and up, it enforces specifying array value types, generic types, and [optionally also callable signatures](/config-reference#vague-typehints).
* Existing classes: looks for nonexistent classes and also trait references
* Unresolvable types: looks for `never` bottom type that resulted from impossible intersections like `string&int`, referencing undefined constants like `Foo::BAR`, etc.
* Generics type checks: checks sanity of generic types. Compares the number of type variables in a type against the number of `@template` above the class declaration, checks the subtyping of bounds, etc.

These type checks weren't performed consistently for all [supported PHPDocs tags](/writing-php-code/phpdocs-basics). We were missing most or all of these checks for these tags:

* `@param-out`
* `@param-closure-this`
* `@param-immediately-invoked-callable` and `@param-later-invoked-callable`
* `@phpstan-self-out`
* `@mixin`
* `@property`
* `@method`
* `@extends`, `@implements`, `@use`

PHPStan 1.12 fixes that. Wrong and corrupted types are checked consistently across the board. Because these are essentially new rules which would cause new errors reported for most codebases, they were added only to Bleeding Edge, and will be enabled for everyone in PHPStan 2.0.

Too wide private property type
-----------------------------

Another addition to Bleeding Edge checks if a type could be removed from a private property union type, without affecting anything.

```php
// if string is never assigned to `$this->a`, we can remove it from the type
private int|string $a;
```

PHPStan already had this rule for function and method return types, and now we can enjoy it for properties too.

PHP 8.4 runtime support
-----------------------------

Implementing support for new PHP version in PHPStan comes in four phases:

* Make sure PHPStan runs on the new PHP version and all tests pass.
* Understand added functions and changed signatures of existing functions in the new PHP version.
* Understand new syntax and features. This removes false errors when analysing code written against the new PHP version.
* Implement new rules specific to new syntax and features. Discover which parts of the new PHP features are tricky and need to be checked with new static analysis rules.

PHPStan 1.12 implements the first two phases. It runs on PHP 8.4 without any deprecation notices, and new functions like [`array_find()`](/r/15c8954d-d090-4c01-8c24-46ef5fd93541) are already known.

Because of new syntax, the rest is dependent on upgrading to [nikic/PHP-Parser](https://github.com/nikic/PHP-Parser) v5 which has to be done in a major version.

My plan for the upcoming months is straightforward: finish and release PHPStan 2.0, and then work on PHP 8.4-specific features.

---

Me and PHPStan contributors put a lot of hard work into this release. I hope that you'll really enjoy it and take advantage of these new features! We're going to be [waiting for everyone's feedback on GitHub](https://github.com/phpstan/phpstan/discussions).

---

Do you like PHPStan and use it every day? [**Consider sponsoring** further development of PHPStan on GitHub Sponsors and also **subscribe to PHPStan Pro**](/sponsor)! I’d really appreciate it!
