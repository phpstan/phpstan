---
title: "PHPStan 0.9: A Huge Leap Forward"
date: 2017-11-29
tags: releases
---

It's been a year since [I unveiled PHPStan to the world](/blog/find-bugs-in-your-code-without-writing-tests). After 19 releases, more than 480,000 downloads (clocking around 3,600 daily during the workweek), 2,500 stars on GitHub and countless saved hours of developers that use it every day, PHPStan does so much more and is flourishing how I couldn't have ever imagine. It feels like [comparing the original iPhone to iPhone X](http://mashable.com/2017/11/06/how-many-original-iphone-screens-fit-apple-iphone-x-super-retina-display) just after a single year.

> In case you've never heard about PHPStan: It's a static analyser for PHP that focuses on finding bugs in your code. You can find out more about its basics in this [introductory article](/blog/find-bugs-in-your-code-without-writing-tests).

Today, I'd like to introduce the next version of PHPStan. It packs so many new features and improvements that it deserves its own article with more space to be able to explain them, but I published [comprehensive release notes](https://github.com/phpstan/phpstan/releases/tag/0.9) as well.

Before I dive into what's new in 0.9, I'd like to take a moment to thank [Roave](https://roave.com) for sponsoring this release:

> [Roave](https://roave.com) is a full-service web development firm, offering services such as consulting, training, software development, and more. Roave employs some of the most recognized and accomplished experts in the industry to ensure that organizations have access to the talent they need, when they need it.

If you look at their [Meet the Team](https://roave.com/team) page, you will definitely recognize some of the names from Twitter or from developer conferences. They are also responsible for many popular open-source projects. What a great bunch of folks!

I'm really lucky that I'm able to work on something I really love and that I can justify spending time on open-source instead of traditional paid work. And it's thanks to sponsors like Roave and also my [patrons on Patreon](https://www.patreon.com/phpstan).

## Intersection types

This is a largely unknown term to the PHP community. That's why I took time a few days ago to [describe it in-depth in a special article](/blog/union-types-vs-intersection-types).

Implementation of intersection types allows PHPStan to understand code much better. In previous versions, PHPStan would fail to analyse this code:

```php
function doSomething(Foo $foo)
{
    if ($foo instanceof Bar) {
        // you can still call methods from Foo
        // in case Bar is an interface!
    }
}
```

Having intersection types also solved more complex issues like [this](https://phpstan.org/r/2b417e6e863b6db40d42d2bd31eb28eb). Static analysis is hard. The hardest part for me is finding out of what type can each variable and expression be after a series of complex conditions and loops in a dynamic language like PHP. Under the umbrella of intersection types, we successfully set out to improve the engine. Type-inferring (someone might say type-guessing) in PHPStan now matches behaviour of PHP as close as possible.

## Next-gen phpDoc parser

Trying to make sense of phpDocs using regular expressions was no longer maintainable. Building intersection types support on top of the old solution wasn't possible. We decided to do not only that, but also make the new parser awesome. It's now based on AST instead of regexps which allows for even more complicated syntax.

It supports declaring intersection types (obviously) and also combining them with union types — the notation has to be unambiguous, parentheses help with that:

```php
/**
 * @param (Foo|Bar)&Baz $input
 */
function doSomething($input)
{

}
```

For the first time ever, developers can declare arrays and iterables that consist of complex types. For example, an array that consists of strings and integers can be written with: `(string | int)[]`

Until now, the only fallback that could be used for values like this was `mixed[]` which hampered the abilities of a static analyser. One could write `string[]|int[]` but that meant something different — having an array consisting only of strings, or an array consisting only of integers.

It's also possible to say what types of keys are in an array or an iterable, inspired by generics syntax from other languages: `array<KeyType, ValueType>`, `iterable<KeyType, ValueType>`.

I believe that these possibilities will allow to move the ecosystem of PHP applications forward. I'd like to position PHPStan as the driver of innovation — thanks to less baggage, shorter feedback loops and rapid releases, it's able to fullfil real world needs faster than the language itself and even than IDEs. Let's hope they will follow suit and the support for intersection types and complex array notation comes sooner rather than later.

## PHPUnit support

If you've already used PHPStan along with PHPUnit, you might be familar with these errors:

```
Parameter #3 $foo of class App\FooService constructor expects App\Foo, PHPUnit_Framework_MockObject_MockObject given.
```

Goods news is, you don't have to ignore them anymore! Thanks to intersection types, PHPStan is now able to make sense of mock objects and analyse them properly. Object instances returned by `createMock` method are now an intersection of the PHPUnit's MockObject interface (used for configuring the mock) and of the mocked class. So the mock can be passed as an argument to functions where the mocked class is required, and it's also possible to call methods from both MockObject and the mocked class on it.

PHPUnit support is [available as an extension](https://github.com/phpstan/phpstan-phpunit).

## Finding dead conditions

I love deleting dead code. Having less code means there's less to maintain and refactoring gets easier. Finding some types of dead code is easy — typically, nothing after a `return` or `throw` statement gets executed. Tools like PHP_CodeSniffer or PHPMD have been able to do that for years.

PHPStan is in a unique position of knowing the type of each variable so it can do advanced analysis of what code can be safely deleted. In 0.9, it's now able to detect:

- Always false/always true calls to type-checking functions like `is_int`, `is_bool`, `is_array`.
- Always false/always true occurences of `instanceof` with incompatible types and unknown classes on the right side.
- Always false comparison of different types on both sides of `===`and `!==`operators.
- Variables in `isset()` that can never be defined, or they're always defined and non-nullable. This is especially useful if you use it for checking whether an array contains a specific key. Call to`isset($foo['key'])`might hide the fact that `$foo`does not exist at all, but PHPStan can tell you about it.

These features mean that whole branches of never-executed code can be deleted and also that some code can be pulled out out of always-executed `if`branch.

## Optional strict rules

PHPStan core (`phpstan/phpstan`) has always been about finding objective bugs in code. I wouldn't want my opinionated views on how code should be written decrease the usefulness of the tool to users with different views.

But it's really easy to write advanced rules for people who want some additional safety and type-checking while they're writing their extremely defensive code.

The brand new package [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) is able to do the following:

- Require booleans in `if`, `elseif`, ternary operator, after `!`, and on both sides of `&&` and `||`.
- Functions `in_array` and `array_search` must be called with third parameter `$strict` set to `true` to search values with matching types only.
- Variables assigned in `while` loop condition and `for` loop initial assignment cannot be used after the loop.
- Types in `switch` condition and `case` value must match. PHP compares them loosely by default and that can lead to unexpected results.

Of course there's a never-ending backlog of ideas what else can be implemented so stay tuned for other rules that will surely follow!

## Full PHP 7.2 support

The next major version of PHP is going to be released tomorrow and I urge everyone to upgrade. PHPStan is ready for it and you should be too.

At the same time, [active support of PHP 7.0](http://php.net/supported-versions.php) ends in 3 days so PHPStan 0.9 is going to be the last version that supports it. I can't wait to get my hands on those nullable types!
