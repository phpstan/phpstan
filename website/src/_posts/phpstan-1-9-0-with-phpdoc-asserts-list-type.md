---
title: "PHPStan 1.9.0 With PHPDoc Asserts, List Type, and More!"
date: 2022-11-03
tags: releases
---

[PHPStan 1.9.0](https://github.com/phpstan/phpstan/releases/tag/1.9.0) has been a real community effort. You'll notice that every headlining feature of this release has been contributed by someone other than me, the maintainer. It's not that I don't like writing code anymore, but others get around to implementing new features faster while I'm down in the weeds hunting mysterious bugs.

I'm now pressing the green "merge" button multiple times a day. My role has shifted from the main code contributor to quality assurance, project vision [^sayno], and taking care of the continuous integration pipeline. I acknowledged this in [a recent letter to contributors](https://github.com/phpstan/phpstan/discussions/8228) which is well worth your read even if you don't contribute code yourself.

[^sayno]: [A thousand No's for every Yes](https://www.youtube.com/watch?v=XAEPqUtra6E).

<blockquote class="twitter-tweet tw-align-center" data-lang="en" data-dnt="true"><p lang="en" dir="ltr">At the current rate of quality pull requests from <a href="https://twitter.com/rvanvelzen1?ref_src=twsrc%5Etfw">@rvanvelzen1</a> <a href="https://twitter.com/markusstaab?ref_src=twsrc%5Etfw">@markusstaab</a> and <a href="https://twitter.com/herndlm?ref_src=twsrc%5Etfw">@herndlm</a> I might just rename my job title to &quot;green button pusher&quot;.</p>&mdash; Ondřej Mirtes (@OndrejMirtes) <a href="https://twitter.com/OndrejMirtes/status/1583364120362987520?ref_src=twsrc%5Etfw">October 21, 2022</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

PHPDoc asserts
--------------------

This feature was developed by [Richard van Velzen](https://github.com/rvanvelzen). {.text-sm}

After [generics](/blog/generics-in-php-using-phpdocs) and [conditional return types](/writing-php-code/phpdoc-types#conditional-return-types) PHPStan continues to democratize its advanced features.

Let's consider a custom type-checking function like this:

```php
public function foo(object $object): void
{
    $this->checkType($object);
    $object->doSomething(); // Call to an undefined method object::doSomething().
}

public function checkType(object $object): void
{
    if (!$object instanceof BarService) {
        throw new WrongObjectTypeException();
    }
}
```

During the analysis of the `foo()` method, PHPStan doesn't understand that the type of `$object` was narrowed to `BarService` because it doesn't descend to called functions and symbols, it just reads their typehints and PHPDocs.

It's always been possible to describe these scenarios scenario by writing custom [type-specifying extensions](/developing-extensions/type-specifying-extensions) [^phpunit], but that comes with the need to understand the [core concepts](/developing-extensions/core-concepts) PHPStan is built on, like the abstract syntax tree and the type system.

[^phpunit]: Otherwise analysis of PHPUnit test cases wouldn't really work.

PHPStan 1.9.0 makes this easier for everyone and makes it possible to describe what's going on inside the called function and how types are narrowed with custom PHPDoc tags `@phpstan-assert`, `@phpstan-assert-if-true`, `@phpstan-assert-if-false`. Besides arguments, it also supports narrowing types of properties and returned values from other methods on the same object.

```php
public function foo(object $object): void
{
    $this->checkType($object);
    $object->doSomething(); // No error
    \PHPStan\dumpType($object); // BarService
}

/** @phpstan-assert BarService $object */
public function checkType(object $object): void
{
    if (!$object instanceof BarService) {
        throw new WrongObjectTypeException();
    }
}
```

Learn everything about this new feature [in the documentation »](/writing-php-code/narrowing-types#custom-type-checking-functions-and-methods).


List type
--------------------

This feature was developed by [Richard van Velzen](https://github.com/rvanvelzen). {.text-sm}

PHP arrays are really powerful, but they represent several computer science concepts in a single data structure, and sometimes it's difficult to work with that. That's why it's useful to narrow it down when we're sure we only want a single concept like a list.

[List](https://en.wikipedia.org/wiki/List_(abstract_data_type)) in PHPStan is an array with sequential integer keys starting at 0 and with no gaps. It joins [many other advanced types expressible in PHPDocs](/writing-php-code/phpdoc-types):

```php
/** @param list<int> $listOfIntegers */
public function doFoo(array $listOfIntegers): void
{
}
```

What was challenging about this feature is that there's a lot of ways to manipulate arrays in PHP. We had to go through all of those and decide:

* Does this create a list out of an array that wasn't a list before? `array_values` function does that.
* Does this preserve a list when it was already a list? `array_map` does that.
* Does this make list stop being a list? `array_filter` does that.

That's why this feature is introduced as experimental and is available only through [bleeding edge](/blog/what-is-bleeding-edge). It'd be quite disruptive to push it to all users now. We'll work out the kinks with early adopters and it's gonna be ready for prime time in PHPStan 2.0.

If you want to try it now, include `bleedingEdge.neon` in your [configuration file](/config-reference):

```neon
includes:
	- phar://phpstan.phar/conf/bleedingEdge.neon
```

Parameter type assigned by reference
--------------------

This feature was developed by [Markus Staab](https://github.com/staabm). {.text-sm}

As I've already written once in this article, PHPStan doesn't know what's going on inside the called functions and methods. So if a parameter is assigned by reference, the type after the function call is always `mixed`:

```php
function foo(mixed &$i): void
{
    $i = 5;
}

foo($a);
\PHPStan\dumpType($a); // mixed
```

With this new feature, PHPStan allows to describe the outgoing type with `@param-out` PHPDoc tag:

```php
/**
 * @param-out int $i
 */
function foo(mixed &$i): void
{
    $i = 5;
}

foo($a);
\PHPStan\dumpType($a); // int
```

Aside from allowing developers marking their functions with this PHPDoc tag, we also annotated more than 30 built-in PHP functions, so type inference performed by PHPStan is again a fair bit smarter.


Describe type of current object after calling a method
--------------------

This feature was developed by [Richard van Velzen](https://github.com/rvanvelzen). {.text-sm}

The type of a mutable object can change after calling a mutating method. Let's say you have a generic collection with just integers, and the `add` method allows values of different type:

```php
/**
 * @template TValue
 */
class Collection
{

	// ...

	/**
	 * @template TItemValue
	 * @param TItemValue $item
	 * @phpstan-self-out self<TValue|TItemValue>
	 */
	public function add($item): void
	{
		// ...
	}

}
```

The type of the collection is going to change after the call and that's what PHPDoc tag `@phpstan-self-out` (or alternatively `@phpstan-this-out`) describes.

```php
/** @param Collection<int> $c */
function foo(Collection $c, string $s): void
{
	$c->add($s);
	\PHPStan\dumpType($c); // Collection<int|string>
}
```

New extension for describing allowed subtypes
--------------------

This feature was developed by [Jiří Pudil](https://github.com/jiripudil). {.text-sm}

PHPStan is not just an end user tool for finding bugs in your code, it's also a framework that covers various static analysis needs - you can write custom extensions to tell PHPStan how exactly the magic in your code works, you can tell it [the result type of your database query](https://github.com/staabm/phpstan-dba), you can write [custom rules](/developing-extensions/rules) to check specific tricky situations in your codebase.

The [list of extension types](/developing-extensions/extension-types) you can implement is already pretty hefty, and gets a new one today.

PHP language doesn't have a concept of sealed classes - a way to restrict class hierarchies and provide more control over inheritance. So any interface or non-final class can have an infinite number of child classes. By implementing [AllowedSubTypesClassReflectionExtension](/developing-extensions/allowed-subtypes) interface you'll tell PHPStan the complete list of allowed child classes for a single parent class.

This extension type can be used simply to hardcode a set of classes. But it can also be used [to read custom PHPDocs](/developing-extensions/reflection#retrieving-custom-phpdocs) or class attributes.

I'm sure that Jiří Pudil himself will take advantage of it in [his custom package](https://github.com/jiripudil/phpstan-sealed-classes) that adds a `#[Sealed]` attribute! *UPDATE: And he already did in 1.0.0!*

Type refactoring has begun!
--------------------

If I could start over and do one thing in PHPStan internals differently, I'd absolutely avoid inheritance. Every developer during their career must have stumbled upon similar structure:

```php
class User {}
class Admin extends User {}
class Editor extends User {}
class Customer extends User {}
```

It's all fine and dandy. Until one day when someone requests to take on multiple roles. They'll either have to juggle multiple accounts, or you'll have to refactor your inheritance hierarchy to use composition instead.

And that's where we are with the [type system](https://apiref.phpstan.org/1.12.x/PHPStan.Type.Type.html).

PHP is a complex language. A type often stands in for a different type. A string can be a callable. A callable can be an array. Asking `$type instanceof StringType` doesn't cover all possible situations, because a lot of other Type implementations can be a string too.

So we changed the preferred way to ask "is this an array?" to `Type::isArray(): TrinaryLogic`. And "is this a string?" to `Type::isString(): TrinaryLogic`. Every step like that helps us to get rid of a lot of bugs.

When we replace all instances of `$type instanceof *Type`, the [`Type` interface](https://apiref.phpstan.org/1.12.x/PHPStan.Type.Type.html) is going to have hundreds of methods. And I'm persuaded it's the correct solution to this problem 🤣

And once we deprecate and eradicate `$type instanceof *Type` from all 3rd party PHPStan extensions as well, we'll finally be able to decouple types from each other, which will allow us to do cool stuff, e.g. support all template type bounds automatically [^templateTypes], or make all types subtractable automatically [^subtractable] as well.

[^templateTypes]: Right now each bound (like `@template T of int`) requires to have a custom class like `TemplateIntegerType`, so that both `$type instanceof IntegerType` and `$type instanceof TemplateType` pass.

[^subtractable]: Only few selected types are currently subtractable, like `mixed` or `object`. [Playground example](https://phpstan.org/r/6a8c7fbb-9d55-41dd-8913-60ff3aa37f1f)

[Martin Herndl](https://github.com/herndlm/) started this refactoring in PHPStan 1.9.0 by tackling various use-cases for array types, it's the main reason why the **Internals** section of the [release notes](https://github.com/phpstan/phpstan/releases/tag/1.9.0) is bigger than usual. I hope that others will join this effort as well, because the end result will be worth it.

...and more!
--------------------

Check out [the complete release notes](https://github.com/phpstan/phpstan/releases/tag/1.9.0) listing more than 100 changes bringing other improvements and bugfixes.
