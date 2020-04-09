---
title: "PHPStan: Find Bugs In Your Code Without Writing Tests!"
date: 2016-12-04
tags: releases
---

I really like how much productivity a web developer gains by switching from compiled languages like Java or C# to an interpreted one like PHP. Aside from the dead simple execution model (start, handle one request, and die) and a much shorter feedback loop (no need to wait for the compiler), there’s a healthy ecosystem of open-source frameworks and libraries to help developers with their everyday tasks. Because of these reasons, PHP is [the most popular language for web development](https://w3techs.com/technologies/overview/programming_language/all) by far.

But there’s one downside.

## When do you find out about errors?

Compiled languages need to know about the type of every variable, return type of every method etc. before the program runs. This is why the compiler needs to make sure that the program is “correct” and will happily point out to you these kinds of mistakes in the source code, like calling an undefined method or passing a wrong number of arguments to a function. The compiler acts as a first line of defense before you are able to deploy the application into production.

On the other hand, PHP is nothing like that. If you make a mistake, the program will crash when the line of code with the mistake is executed. When testing a PHP application, whether manually or automatically, developers spend a lot of their time discovering mistakes that wouldn’t even compile in other languages, leaving less time for testing actual business logic.

I’d like to change that.

## Enter PHPStan

Keeping up with modern PHP practices leads to codebases where we can be sure about types of a lot of data, converging with statically typed languages, although the dynamic nature is still present. Modern PHP codebases are similar to the ones in languages people make much less fun of. Object-oriented code, dependency injection and usage of established design patterns are truly common nowadays.

Which led me to the idea of a [static analysis tool for PHP](https://github.com/phpstan/phpstan) that would substitute the role of the compiler from other languages. I’ve spent a lot of time working on it and I’ve been employing its various development versions checking our codebase for more than a year.

#### It’s called [PHPStan](https://phpstan.org/), it’s open-source and free to use.

---

## What it currently checks for?

- Existence of classes used in instanceof, catch, typehints and other language constructs. PHP does not check this and just stays instead, rendering the surrounded code unused.
- Existence and accessibility of called methods and functions. It also checks the number of passed arguments.
- Whether a method returns the same type it declares to return.
- Existence and visibility of accessed properties. It will also point out if a different type from the declared one is assigned to the property.
- Correct number of parameters passed to sprintf/printf calls based on format strings.
- Existence of variables while respecting scopes of branches and loops.
- Useless casting like (string) ‘foo’ and strict comparisons (=== and !==) with different types as operands which always result in false.

The list is growing with every release. But it’s not the only thing that makes PHPStan useful.

## PHPStan is fast…

It manages to check the whole codebase in a single pass. It doesn’t need to go through the code multiple times. And it only needs to go through the code you wish to analyze, e.g. the code you written. It doesn’t need to parse and analyze 3rd party dependencies. Instead, it uses reflection to find out useful information about somebody else’s code your codebase uses.

PHPStan is able to check our codebase (6000 files, 600k LOCs) in around a minute. And it checks itself under a second.

## …and extensible

Even with current static typing practices, a developer can sometimes justify using dynamic features of PHP like `__get`, `__set` and `__call` magic methods. They allow to define new properties and methods dynamically in runtime. Normally, static analysis would complain about accessing undefined properties and methods, but there’s a mechanism for telling the engine the rules how exactly the new properties and methods are created.

This is made possible thanks to a custom abstraction over native PHP reflection which allows the user to define extensions. For more details, check the [Class reflection extensions](https://github.com/phpstan/phpstan#class-reflection-extensions) section in the README.

Some methods’ return type depends on its arguments. It can depend on what class name you pass to it or it may return object of the same class as the object you passed. This is what [Dynamic return type extensions](https://github.com/phpstan/phpstan#dynamic-return-type-extensions) are for.

And last but not least, if you come up with a new check PHPStan could perform, you can [write and use it yourself](https://github.com/phpstan/phpstan#custom-rules). It’s possible to come up with framework-specific rules like checking if entities and fields referenced in a [DQL](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html) query exist or if all generated links in your MVC framework of choice lead to existing controllers.

## Choose your level of strictness

Other tools I tried out suffer from the initial experience of trying to integrate them into existing codebases. They spill thousands and thousands of errors which discourage further use.

Instead, I looked back to how I integrated PHPStan into our codebase during its initial development. Its first versions weren’t as capable as the current one, they didn’t find as many errors. But it was ideal from the integration perspective — When I had time, I wrote a new rule, I fixed what it found in our codebase and merged the new version with the fixes into master. We used the new version for a few weeks to find errors it was capable to find and the cycle repeated. This gradual increasing of strictness proved to be really beneficial, so I set out to simulate it even with current capabilities of PHPStan.

By default, PHPStan checks only code it’s sure about — constants, instantiation, methods called on \$this, statically called methods, functions and existing classes in various language constructs. By increasing the level (from the default 0 up to the current 4), you also increase the number of assumptions it makes about the code and the number of rules it checks.

You can also create your own rulesets if the built-in levels do not suit your needs.

---

## Write less unit tests! (but focus on the meaningful ones)

You don’t hear this advice often. Developers are forced to write unit tests even for trivial code because there is an equal chance to make a mistake in it, like write a simple typo or forget to assign a result to a variable. It’s not very productive to write unit tests for simple forwarding code that you can usually find in your controllers and [facades](https://en.wikipedia.org/wiki/Facade_pattern).

Unit tests come with a cost. They are code that has to be written and maintained like any other. Running PHPStan on every change, ideally on a continuous integration server, prevents these kinds of mistakes without the cost of unit tests. It’s really hard and not economically feasible to achieve 100 % code coverage, but you can statically analyze 100 % of your code.

As for your unit testing efforts, focus them on places where it’s easy to make a mistake that doesn’t look like one from the point of static analysis. That includes: complex filtering of data, loops, conditions, calculations with multiplication and division including rounding.

## On the shoulders of giants

Creating PHPStan wouldn’t be possible without the excellent [PHP Parser](https://github.com/nikic/PHP-Parser) created by [Nikita Popov](https://nikic.github.io/aboutMe.html).

---

PHP in 2016 has established and widely used tools for [package management](https://getcomposer.org/), [unit testing](https://phpunit.de/) and [coding standards](https://github.com/squizlabs/PHP_CodeSniffer). It’s a mystery to me there isn’t yet a widely used tool for finding bugs in code without running it. So I created one that is easy enough to use, fast, extensible, and doesn’t bother you with strict requirements on your codebase while still allowing you to benefit from various checks it performs. Check out the [GitHub repo](https://github.com/phpstan/phpstan) to find out how you can integrate it in your project today!

It’s already used by [Slevomat](https://www.slevomat.cz/), [Hele](https://www.hele.cz/) and [Bonami](https://www.bonami.cz/) — several of the most prominent Czech startups. I hope yours will be next!
