---
title: "The Three Pillars of Static Analysis in PHP"
date: 2016-12-11
tags: guides
---

My credo is that everything that can be automated should be automated. Computers are really good at repeating tedious tasks and they don’t usually make mistakes while us squishy humans are defined by making mistakes everywhere we go.

That’s why you should have a build process and continuous integration environment for every project you create. The mistakes we make can be caught there before they reach any users.

PHP lets you leave errors in the code and doesn’t complain until the code with the error is executed. That’s why everyone should invest into making their build process bulletproof by writing automated tests. But be honest with yourself — do you have tests for everything? Is it even economically feasible? There’s a lot you can do to make sure that your application works for free without writing tests for every single line of your code.

In the following article, I’d like to introduce three tools that will help you to find errors and inconsistencies in your codebase. If your build integrating these tools finishes successfully, you can be pretty sure your application is in a good shape.

## Syntax Check with Parallel Lint

The obvious first line of defense is checking that there aren’t any syntax errors in your code. You can achieve that by running php `-l` on a PHP file. But it’s a little bit more complicated to run it on all files in your codebase. Fortunately, Jakub Onderka solved this by creating [PHP-Parallel-Lint](https://github.com/JakubOnderka/PHP-Parallel-Lint). It’s a tiny efficient wrapper around `php -l` with several options for your convenience. Most importantly, it checks your files in parallel making use of your multiple-core machines.

You should let it check your codebase but also generated files, like Doctrine proxies or compiled templates from your favorite templating engine. It’s not very common but it’s possible to confuse the tools you rely on daily to generate some gibberish that’s not valid PHP. It happened to me several times.

## Coding Standard with CodeSniffer

After you made sure that the code is valid PHP, it’s time to find out if it looks like it should. [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) checks if it keeps to the configured coding standard. There are a lot of built-in rules available, even more 3rd party open-source standards and you can even write your own rules (called sniffs).

Parsing PHP code by using [`token_get_all`](https://secure.php.net/manual/en/function.token-get-all.php) enables CodeSniffer to check indentation, whitespace placement, unreachable code and a lot more useful stuff. Last year [we open-sourced our company’s coding standard](https://medium.com/@ondrejmirtes/slevomat-coding-standard-861267de576f) which contains advanced checks like finding unused uses on top of a file, unused private properties and methods, or Yoda conditions. So sniffs aren’t limited to enforcing formatting conventions, they can also find other issues.

But there’s one limitation CodeSniffer is not able to overcome — because it reads parser tokens, it can only check one file at a time. So for example when it’s checking a class, it does not have access to its reflection, thus not being to check its parent classes and other referenced types. So it’s not able to perform more advanced inspections you might come up with. But it’s a great tool that goes hand in hand with PHP-Parallel-Lint.

## Static Analysis with PHPStan

So now we have valid PHP code that looks like we want it to. But it can still contain plenty of bugs — because calling an undefined method is not simply a parse error! Because of dynamic nature of PHP, we cannot be sure about a type of a variable until the program actually runs. But modern practices and recent advances in PHP itself lead to code where we can be sure about types of a lot of data, converging with statically typed languages, although the dynamic nature is still present.

Which led me to the idea of a [static analysis tool for PHP](https://github.com/phpstan/phpstan) that would specialize in finding bugs. I’ve spent a lot of time working on it and I’ve been employing its various development versions checking our codebase for more than a year. I [announced PHPStan and wrote about it in more detail](/blog/find-bugs-in-your-code-without-writing-tests).

It currently checks for:

- Existence of classes used in instanceof, catch, typehints and other language constructs. PHP does not check this and just stays instead, rendering the surrounded code unused.
- Existence and accessibility of called methods and functions. It also checks the number of passed arguments.
- Whether a method returns the same type it declares to return.
- Existence and visibility of accessed properties. It will also point out if a different type from the declared one is assigned to the property.
- Correct number of parameters passed to sprintf/printf calls based on format strings.
- Existence of variables while respecting scopes of branches and loops.

PHPStan's main advantages are speed, extensibility (possibility to define “magic” behavior of PHP classes) and the ability to choose which checks you want to perform — either by selecting one of default rule levels or by creating completely custom rulesets.

Find out more about PHPStan in the [introductory article](/blog/find-bugs-in-your-code-without-writing-tests) or by visiting the [PHPStan’s official website](https://phpstan.org/) and checking out the documentation.

---

You can really benefit from employing all three tools in your build process:

1. [PHP-Parallel-Lint](https://github.com/JakubOnderka/PHP-Parallel-Lint) will make sure that you haven’t left any syntax errors in your code.

1. [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) will make sure that it’s properly formatted (plus some other goodies).

1. [PHPStan](https://github.com/phpstan/phpstan) will make some guesses about types in your code based on typehints and phpDoc annotations and will tell you about everything that doesn’t look right and is a potential bug.

Happy linting!
