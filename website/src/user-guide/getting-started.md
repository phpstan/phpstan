---
title: Getting Started
---

PHPStan requires PHP >= 7.4. You have to run it in environment with PHP 7.x but the actual code does not have to use PHP 7.x features. (Code written for PHP 5.6 and earlier can run on 7.x mostly unmodified.)

PHPStan works best with modern object-oriented code. The more strongly-typed your code is, the more information you give PHPStan to work with.

Properly annotated and typehinted code (class properties, function and method arguments, return types) helps not only static analysis tools but also other people that work with the code to understand it.

Installation
-------------

To start performing analysis on your code, require PHPStan in [Composer](https://getcomposer.org/):

```bash
composer require --dev phpstan/phpstan
```

Composer will install PHPStan's executable in its `bin-dir` which defaults to `vendor/bin`.

You can also download the [latest PHAR](https://github.com/phpstan/phpstan/releases) and just use that. But without Composer, you won't be able to install and use [PHPStan extensions](/user-guide/extension-library).

[Head here](/user-guide/docker) if you want to use PHPStan in Docker.

First run
-------------

To let PHPStan analyse your codebase, you have to use the `analyse` command and point it to the right directories.

So, for example if you have your classes in directories `src` and `tests`, you can run PHPStan like this:

```bash
vendor/bin/phpstan analyse src tests
```

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

You should only analyse files with the code you've written yourself. There's no need to analyse the `vendor` directory with 3rd party dependencies because it's not in your power to fix all the mistakes made by the developers you don't work with directly.

Yes, PHPStan needs to know about all the classes, interfaces, traits, and functions your code uses, but that's achieved through [discovering symbols](/user-guide/discovering-symbols), not by including the files in the analysis.
</div>

[Learn more about command line options »](/user-guide/command-line-usage)

PHPStan will probably find some errors, but don't worry, your code might be just fine. Errors found on the first run tend to be:

* Extra arguments passed to functions (e. g. function requires two arguments, the code passes three)
* Extra arguments passed to print/sprintf functions (e. g. format string contains one placeholder, the code passes two values to replace)
* Obvious errors in dead code
* Unknown symbols - like "class not found". See [Discovering Symbols](/user-guide/discovering-symbols) for more details.

**By default, PHPStan runs only the most basic checks. Head to [Rule Levels](/user-guide/rule-levels) to learn how to turn on stricter checks.**

**Learn about all the configuration options in the [Config Reference](/config-reference).**
