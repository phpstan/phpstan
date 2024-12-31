---
title: "PHPStan 2.1: Support For PHP 8.4's Property Hooks, and More!"
date: 2024-12-31
tags: releases
---

This release is a culmination of the last four months of my work. Beginning in September, I branched 2.0.x off of 1.12.x. In order to support PHP 8.4 syntax, I had to upgrade to [PHP-Parser v5](https://github.com/nikic/PHP-Parser/releases/tag/v5.0.0). In order to upgrade to PHP-Parser v5, I had to release PHPStan 2.0. [Which I did on November 11th](/blog/phpstan-2-0-released-level-10-elephpants), alongside the cute and also long-awaited PHPStan elephpant. PHPStan's fans ordered 750 of those! They will be manufactured in China and sent on a ship to Europe which is going to take some time. I hope they're going to find themselves in the hands of their happy owners in May-June 2025, as promised in the order confirmation emails.

Today's [PHPStan 2.1](https://github.com/phpstan/phpstan/releases/tag/2.1.0) brings full understanding of flagship PHP 8.4 features like [property hooks](https://wiki.php.net/rfc/property-hooks), [asymmetric visibility](https://wiki.php.net/rfc/asymmetric-visibility-v2), and the [`[#Deprecated]` attribute](https://wiki.php.net/rfc/deprecated_attribute). As usual I'm going to describe what considerations went into supporting these language features in PHPStan.

Property hooks
--------------------

This is a massive and complex new language feature which is obvious just by looking at the length of [the RFC](https://wiki.php.net/rfc/property-hooks). After reading it I wrote down about 70-80 todos about what should be done in PHPStan's codebase in order to properly support it 😅 Out of those there are still [around 32 todos left](https://github.com/phpstan/phpstan/issues/12336). They are not necessary enough to be included in this initial release, but it'd be nice to have them.

In short, property hooks let you intercept reading and writing properties with your own code. They also allow properties to be declared on interfaces for the first time in PHP. You can have virtual properties without a backing value stored in the object. And last but not least, hook bodies can be written with either long or short syntax.

Property hooks are very similar to class methods, but they are also their own thing. Which means I had to adjust or replicate a lot of existing rules around methods:

* [Missing return](/r/041c903d-87c4-4de1-97aa-12e5c80975e5)
* [Returned type](/r/1d82e9b9-0e10-4b3b-a444-f5ad65523b48)
* [Existence of parameter types](/r/e5d7d8c8-4133-4f3a-b003-7c877931d152)
* [Compatibility of PHPDocs with parameter types](/r/0f0103a7-6226-4dbb-894b-3462b831ab8c)
* [Syntax errors in PHPDocs](/r/611182a8-ac7f-4155-ab1a-d7553511282e)
* [Unknown `@phpstan-` tags in PHPDocs](/r/02f64b10-6579-4cbc-b1b8-939a32d89f13)
* [Checked exceptions thrown in the body must either be handled or documented with `@throws`](/blog/bring-your-exceptions-under-control)
* [Rule for unused private properties must ignore property access inside hooks](/r/b721ee1f-d2df-4d28-b84c-fb072f19b97a)
* [Attributes above property hooks](/r/7083a3bb-fc8b-4e2b-aa68-722a87e3a6e6)

Additionally, there are new special rules for property hooks:

* Backing value of non-virtual property [must be read](/r/386f6fa6-2c6e-4e22-818f-47d35fa1ee28) in get hook
* Backing value of non-virtual property [must be always assigned](/r/ce9480ce-fdf1-4ff7-9377-105ca7fa4313) in set hook

Set hook parameter type can be wider than the actual property type so PHPStan also has to be aware of the types it [allows to assign to properties in and out of hooks](/r/5b12238d-8206-4194-a30c-19ad4071bbf9):

```php
class Foo
{
	public int $i {
		get {
			return $this->i - 10;
		}
		set (int|string $value) {
			$this->i = $value; // string not allowed
		}
	}
}

$f = new Foo();
$f->i = rand(0,1) ? '10' : 'foo'; // string allowed
```

Property hooks also bring properties that [can only be read, or can only be written](/r/2494f530-17c0-4da7-8d91-263d171766a9), to the language:

```php
interface Foo
{
	public int $i { get; }
	public int $j { set; }
}

function (Foo $f): void {
	$f->i = 42; // invalid
	echo $f->j; // invalid	
};
```

And my final remark is that access to properties can now be [expected to throw any exception](/r/39b8d6a8-99db-4bfc-a99b-9af4b214abee):

```php
interface Foo
{
	public int $i { get; }
}

function (Foo $f): void {
	try {
		echo $f->i;
	} catch (MyCustomException) { // dead catch on PHP 8.3-, but not on 8.4+
		
	}
};
```

Even non-hooked properties are subject to this, because they can be overwritten in a subclass with a hook. If you want to persuade PHPStan that access to your properties cannot throw exceptions, make them `private` or `final`.

Hooks can be documented with `@throws` PHPDoc tag which [improves analysis of try-catch blocks](/blog/precise-try-catch-finally-analysis), same as with functions and methods.

I also posted a [community update](https://github.com/phpstan/phpstan/discussions/12337) which goes into depth what you should do if you want to support property hooks in your custom rules.

A huge thanks to [Jarda Hanslík](https://github.com/kukulich) for [initial property hooks support](https://github.com/Roave/BetterReflection/pull/1462) in BetterReflection, and for [many](https://github.com/Roave/BetterReflection/pull/1465) [many](https://github.com/Roave/BetterReflection/pull/1466) [subsequent](https://github.com/Roave/BetterReflection/pull/1470) [fixes](https://github.com/Roave/BetterReflection/pull/1471) for bugs I found 😅

And also a huge thanks to [Nikita Popov](https://github.com/nikic), not just for his work on PHP-Parser in general, but also for merging a [couple](https://github.com/nikic/PHP-Parser/pull/1049) of [fixes](https://github.com/nikic/PHP-Parser/pull/1051) I made, and also for fixing a [couple](https://github.com/nikic/PHP-Parser/issues/1053) of other [issues](https://github.com/nikic/PHP-Parser/issues/1050) I reported.

Asymmetric visibility
--------------------

PHP now allows for different properties visibility when reading then and when assigning them. So property can for example be publicly readable, but only privately writable.

This feature is much [easier to grasp](https://wiki.php.net/rfc/asymmetric-visibility-v2) than property hooks, but also comes with a few gotchas.

* Property that's public-readable or protected-readable, but only privately writable, is [implicitly final and cannot be overridden](/r/eb32ab7b-1f3d-4abf-898a-b22523e88e8c).
* Acquiring reference to a property follows write visibility, not read visibility, so I added [a new rule for that](/r/add82dc4-8cd2-4d5a-bed4-05f8d51e8cfa).

Similarly to property hooks, I also have a few nice-to-have todos [left for later](https://github.com/phpstan/phpstan/issues/12347).

`#[\Deprecated]` attribute
---------------------

[This attribute](https://wiki.php.net/rfc/deprecated_attribute) allows the PHP engine [trigger deprecated warnings](https://3v4l.org/MEJTq).

I suspect it's not that useful in practice because it's allowed only above functions, classes, and class constants. Most notably, it can't be used to mark entire classes deprecated. It also does not work for properties. This is something that PHP could address in the future.

Nevertheless, you can use it in PHPStan 2.1 in tandem with [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) to mark deprecated code and have it reported when used.

---

Do you like PHPStan and use it every day? [**Consider sponsoring** further development of PHPStan on GitHub Sponsors and also **subscribe to PHPStan Pro**](/sponsor)! I’d really appreciate it!
