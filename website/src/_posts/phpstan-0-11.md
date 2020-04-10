---
title: "PHPStan 0.11"
date: 2019-01-16
tags: releases
ogImage: /images/phpstan-0-11.jpg
---

PHPStan has had a wonderful year 2018. The adoption increased from 3,200 daily downloads to 9,800. I released [9 new versions](https://github.com/phpstan/phpstan/releases), [wrote a feature article](https://www.phparch.com/2018/04/testing-strategy-with-the-help-of-static-analysis/) for php[arch] magazine, and talked about PHPStan at Dutch PHP in Amsterdam.

But the moment I realized how big it has become happened at the [phpCE conference in Prague](https://2018.phpce.eu/en/) at the end of October. I’ve had a really awesome time there. First, I took advantage of many international speakers who contribute to PHPStan coming to town and organized “PHPStan dinner” at my favourite indian restaurant:

![PHPStan phpCE Dinner](/images/phpstan-0-11.jpg)

<p class="text-sm text-center text-gray-500">
PHPStan dinner with <a href="http://twitter.com/Ocramius" target="_blank" rel="noopener nofollow">@Ocramius</a> <a href="http://twitter.com/carusogabriel" target="_blank" rel="noopener nofollow">@carusogabriel</a> <a href="http://twitter.com/msvrtan" target="_blank" rel="noopener nofollow">@msvrtan</a> <a href="http://twitter.com/lookyman_" target="_blank" rel="noopener nofollow">@lookyman_</a> <a href="http://twitter.com/dantleech" target="_blank" rel="noopener nofollow">@dantleech</a> <a href="http://twitter.com/JanTvrdik" target="_blank" rel="noopener nofollow">@JanTvrdik</a> <a href="http://twitter.com/VasekPurchart" target="_blank" rel="noopener nofollow">@VasekPurchart</a> #phpce #phpce18 #phpce2018 If they poison our food, PHP is screwed 😂
</p>

We’ve had a really good time. Well, except Marco, who spent the next day on the toilet.

During the conference in the following two days, I’ve heard PHPStan mentioned many times. It was referenced in at least 5 talks, including Rasmus Lerdorf himself. I’m really proud that I contributed something useful to the PHP ecosystem.

I have no intention of stopping. Today, there’s a new version coming out. [**PHPStan 0.11 brings**](https://github.com/phpstan/phpstan/releases/tag/0.11) quite a few missing checks that increase confidence in your code being okay. It also contains 50+ bugfixes.

**PHPStan 0.11 is brought to you by [LOVOO](https://www.lovoo.com/):**

> PHPStan is an elementary part of our development workflow. We cannot live without it! Thanks for the great work!

Here’s a few highlights from the release:

## Ignore errors by path

By popular demand, here’s the ability to scope ignoreErrors regular expressions by path, allowing more granular settings. Until now, all ignores were applied globally.

```yaml
parameters:
	ignoreErrors:
		- '#Variable property access on PhpParser\\Node\.#'
		-
			message: '#Dynamic call to static method#'
			path: %rootDir%/src/Foo/Bar.php
```

## Improved understanding of non-empty arrays

PHPStan now understands situations where the foreach runs at least once. There’s many ways how you can check for an empty array — it gets them all 🙂

```php
if (count($array) === 0) {
	return;
}

foreach ($array as $value) {
	$item = $value;
}

echo $item; // no longer reported as an undefined variable!
```

## Checking unreachable branches

Previously, only projects with `checkAlwaysTrue` flags set to true (usually with installed [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules/)) found out about always true conditions. With this change, always-true conditions with else branches and ternary operators are reported on level 4 for everyone:

```php
if ($alwaysTrue) {
	// ...
} else {
	// dead branch - reported on level 4!
}
```

You will also find out about foreach loops that never run because an empty array is passed there.

## Enforcing Liskov substitution principle

If you’re not familiar with this [term](https://stackoverflow.com/questions/56860/what-is-an-example-of-the-liskov-substitution-principle), it means that a correct implementation of an interface (or a child class of a parent) must accept the same parameter types and return the same type as the interface or the parent. This is needed so that the places where the interface or the parent are typehinted always work the same, no matter the injected implementation.

PHP kind of enforces this with native typehints, but situation gets complicated if you’re types can be expressed only with phpDocs. In reality the type does not have to be exactly the same. Parameter types must be contravariant and return types covariant. Check out this [write-up on the PHPStan issue tracker](https://github.com/phpstan/phpstan/issues/532) talking about this problem in-depth.

There’s a new check on level 3 that will tell you if your classes adhere to this principle.

## Additions in phpstan-strict-rules

[This package](https://github.com/phpstan/phpstan-strict-rules/) contains additional rules that revolve around strictly and strongly typed code with no loose casting for those who want additional safety in extremely defensive programming, and it’s developed in parallel with PHPStan’s core.

In 0.11, it adds these new rules:

- Disallow usage of variable variables, like:

```php
$foo = 'bar';
echo $$foo;
echo $object->$$foo;
Foo::$foo();
Foo::${$bar} = 123;
```

- Disallow overwriting variables with foreach key and value:

```php
$foo = 123;
foreach ($array as $foo) {
	//...
}
```

- If you didn’t like that PHPStan 0.11 now understands that a variable set inside a foreach that’s always looped can now be used after the foreach, strict-rules come to the rescue:

```php
foreach ([1, 2, 3] as $value) {
	$item = $value;
}

echo $item; // reported in phpstan-strict-rules!
```

## Loaded extensions in PHP runtime no longer needed for functions

In previous versions the PHPStan environment had to match the environment of the application. It was because it needed access to PHP reflection. Since PHPStan embeds function signatures with more precise data anyway, I removed the requirement. So you will not encounter errors like "Function proc_open() not found" anymore.

There’s a better tool for checking whether you’re aware of all of the application dependencies — it’s called [ComposerRequireChecker](https://github.com/maglnet/ComposerRequireChecker).

Unfortunately, this requirement still applies to classes — it’s more work to embed all the data PHPStan needs about extension classes so I’ve skipped it for now.

---

## The Future

I always like to say that my todolist for PHPStan is almost infinite. Last time I checked, it contained more than 500 ideas. I can afford cherrypicking the ones that make most sense in regard to current state of the codebase, suggestions from the community, benefit-cost ratio, and scratching my own itches.

For PHPStan 0.12, I plan to explore these areas: performance improvements, "watch" command (continuously running and scanning modified files for errors), generics support, the ability to write more kinds of rules, not just those invoked for a specific AST node, and more.
