<h1 align="center">PHPStan - PHP Static Analysis Tool</h1>

<p align="center">
	<img src="https://i.imgur.com/MOt7taM.png" alt="PHPStan" width="300" height="300">
</p>

<p align="center">
	<a href="https://travis-ci.org/phpstan/phpstan"><img src="https://travis-ci.org/phpstan/phpstan.svg" alt="Build Status"></a>
	<a href="https://github.com/phpstan/phpstan/actions"><img src="https://github.com/phpstan/phpstan/workflows/Build/badge.svg" alt="Build Status"></a>
	<a href="https://packagist.org/packages/phpstan/phpstan"><img src="https://poser.pugx.org/phpstan/phpstan/v/stable" alt="Latest Stable Version"></a>
	<a href="https://packagist.org/packages/phpstan/phpstan/stats"><img src="https://poser.pugx.org/phpstan/phpstan/downloads" alt="Total Downloads"></a>
	<a href="https://choosealicense.com/licenses/mit/"><img src="https://poser.pugx.org/phpstan/phpstan/license" alt="License"></a>
	<a href="https://github.com/phpstan/phpstan"><img src="https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat" alt="PHPStan Enabled"></a>
</p>

------

PHPStan focuses on finding errors in your code without actually running it. It catches whole classes of bugs
even before you write tests for the code. It moves PHP closer to compiled languages in the sense that the correctness of each line of the code
can be checked before you run the actual line.

**[Read more about PHPStan on Medium.com »](https://medium.com/@ondrejmirtes/phpstan-2939cd0ad0e3)**

**[Try out PHPStan on the on-line playground! »](https://phpstan.org/)**

## Sponsors

<a href="https://mike-pretzlaw.de/"><img src="https://i.imgur.com/TW2US6H.png" alt="Mike Pretzlaw" width="247" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://coders.thecodingmachine.com/phpstan"><img src="https://i.imgur.com/kQhNOTP.png" alt="TheCodingMachine" width="247" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://packagist.com/?utm_source=phpstan&utm_medium=readme&utm_campaign=sponsorlogo"><img src="https://i.imgur.com/PmMC45f.png" alt="Private Packagist" width="326" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://stackoverflow.com/jobs/273089/backend-software-engineer-musement-spa"><img src="https://i.imgur.com/uw5rAlR.png" alt="Musement" width="247" height="49"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://blackfire.io/docs/introduction?utm_source=phpstan&utm_medium=github_readme&utm_campaign=logo"><img src="https://i.imgur.com/zR8rsqk.png" alt="Blackfire.io" width="254" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://www.intracto.com/"><img src="https://i.imgur.com/XRCDGZi.png" alt="Intracto" width="254" height="65"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://jobs.ticketswap.com/"><img src="https://i.imgur.com/lhzcutK.png" alt="TicketSwap" width="269" height="64"></a>
&nbsp;&nbsp;&nbsp;
<a href="https://www.startupjobs.cz/startup/shipmonk"><img src="https://i.imgur.com/bAC47za.jpg" alt="ShipMonk" width="290" height="64"></a>

[**You can now sponsor my open-source work on PHPStan through GitHub Sponsors.**](https://github.com/sponsors/ondrejmirtes)

Does GitHub already have your 💳? Do you use PHPStan to find 🐛 before they reach production? [Send a couple of 💸 a month my way too.](https://github.com/sponsors/ondrejmirtes) Thank you!

One-time donations [through PayPal](https://paypal.me/phpstan) are also accepted. To request an invoice, [contact me](mailto:ondrej@mirtes.cz) through e-mail.

BTC: bc1qd5s06wjtf8rzag08mk3s264aekn52jze9zeapt
<br>LTC: LSU5xLsWEfrVx1P9yJwmhziHAXikiE8xtC

## Prerequisites

PHPStan requires PHP >= 7.1. You have to run it in environment with PHP 7.x but the actual code does not have to use
PHP 7.x features. (Code written for PHP 5.6 and earlier can run on 7.x mostly unmodified.)

PHPStan works best with modern object-oriented code. The more strongly-typed your code is, the more information
you give PHPStan to work with.

Properly annotated and typehinted code (class properties, function and method arguments, return types) helps
not only static analysis tools but also other people that work with the code to understand it.

## Installation

To start performing analysis on your code, require PHPStan in [Composer](https://getcomposer.org/):

```
composer require --dev phpstan/phpstan
```

Composer will install PHPStan's executable in its `bin-dir` which defaults to `vendor/bin`.

<details>
  <summary>Use PHPStan via Docker</summary>

[![Docker Stars](https://img.shields.io/docker/stars/phpstan/phpstan.svg)](https://hub.docker.com/r/phpstan/phpstan/)
[![Docker Pulls](https://img.shields.io/docker/pulls/phpstan/phpstan.svg)](https://hub.docker.com/r/phpstan/phpstan/)

The image is based on [Alpine Linux](https://alpinelinux.org/) and built daily.

## Supported tags

- `0.12`, `latest`
- `0.11`
- `0.10`
- `nightly` (dev-master)

## How to use this image

### Install

Install the container:

```bash
docker pull phpstan/phpstan
```

Alternatively, pull a specific version:

```bash
docker pull phpstan/phpstan:0.11
```

### Usage

We are recommend to use the images as an shell alias to access via short-command.
To use simply *phpstan* everywhere on CLI add this line to your ~/.zshrc, ~/.bashrc or ~/.profile.

```bash
alias phpstan='docker run -v $PWD:/app --rm phpstan/phpstan'
```

If you don't have set the alias, use this command to run the container:

```bash
docker run --rm -v /path/to/app:/app phpstan/phpstan [some arguments for PHPStan]
```

For example:

```bash
docker run --rm -v /path/to/app:/app phpstan/phpstan analyse /app/src
```

### Customizing

#### Install PHPStan extensions

If you need an PHPStan extension, for example [phpstan/phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit), you can simply
extend an existing image and add the relevant extension via Composer.
In some cases you need also some additional PHP extensions like DOM. (see section below)

Here is an example Dockerfile for phpstan/phpstan-phpunit:

```dockerfile
FROM phpstan/phpstan:latest
RUN composer global require phpstan/phpstan-phpunit
```

You can update the `phpstan.neon` file in order to use the extension:

```neon
includes:
    - /composer/vendor/phpstan/phpstan-phpunit/extension.neon
```

#### Further PHP extension support

Sometimes your codebase requires some additional PHP extensions like "intl" or maybe "soap".

Therefore you need to know that our Docker image extends the [official php:cli-alpine Docker image](https://hub.docker.com/_/php).
So only [the default built-in extensions](#default-built-in-php-extensions) are available (see below).
Also because PHPStan needs no further extensions to run itself.

But to solve this issue you can extend our Docker image in an own Dockerfile like this, for example to add "soap" and "intl":

```dockerfile
FROM phpstan/phpstan:latest
RUN apk --update --progress --no-cache add icu-dev libxml2-dev \
    && docker-php-ext-install intl soap
```

#### Missing classes like "PHPUnit_Framework_TestCase"

Often you use PHAR files like PHPUnit in your projects. These PHAR files provide sometimes own classes
where your project classes extends from. But these cannot be found in
the vendor directory and so cannot be autoloaded. So you see error messages like this:
*"Fatal error: Class 'PHPUnit_Framework_TestCase' not found"*

To solve this issue you need an own configuration file, like "phpstan.neon".
This file can look like this:

```neon
parameters:
	autoload_files:
		- path/to/phpunit.phar
```

After creating this file in your project root you can run PHPStan for example via

```bash
docker run -v $PWD:/app --rm phpstan/phpstan -c phpstan.neon --level=4
```

and now the required classes are loaded. Please take also a look in the [relevant part](https://github.com/phpstan/phpstan#autoloading) at the PHPStan documentation.

---

#### Default built-in PHP extensions

You can use the following command to determine which php extensions are already installed on the base image:

```bash
docker run --rm php:cli-alpine -m
```

This should give you an output like this:

```ini
[PHP Modules]
Core
ctype
curl
date
dom
fileinfo
filter
ftp
hash
iconv
json
libxml
mbstring
mysqlnd
openssl
pcre
PDO
pdo_sqlite
Phar
posix
readline
Reflection
session
SimpleXML
sodium
SPL
sqlite3
standard
tokenizer
xml
xmlreader
xmlwriter
zlib

[Zend Modules]
```

</details>

## First run

To let PHPStan analyse your codebase, you have to use the `analyse` command and point it to the right directories.

So, for example if you have your classes in directories `src` and `tests`, you can run PHPStan like this:

```bash
vendor/bin/phpstan analyse src tests
```

PHPStan will probably find some errors, but don't worry, your code might be just fine. Errors found
on the first run tend to be:

* Extra arguments passed to functions (e. g. function requires two arguments, the code passes three)
* Extra arguments passed to print/sprintf functions (e. g. format string contains one placeholder, the code passes two values to replace)
* Obvious errors in dead code
* Magic behaviour that needs to be defined. See [Extensibility](#extensibility).

After fixing the obvious mistakes in the code, look to the following section
for all the configuration options that will bring the number of reported errors to zero
making PHPStan suitable to run as part of your continuous integration script.

## Rule levels

If you want to use PHPStan but your codebase isn't up to speed with strong typing
and PHPStan's strict checks, you can choose from currently 9 levels
(0 is the loosest and 8 is the strictest) by passing `--level` to `analyse` command. Default level is `0`.

This feature enables incremental adoption of PHPStan checks. You can start using PHPStan
with a lower rule level and increase it when you feel like it.

You can also use `--level max` as an alias for the highest level. This will ensure that you will always use the highest level when upgrading to new versions of PHPStan. Please note that this can create a significant obstacle when upgrading to a newer version because you might have to fix a lot of code to bring the number of errors down to zero.

## Extensibility

Unique feature of PHPStan is the ability to define and statically check "magic" behaviour of classes -
accessing properties that are not defined in the class but are created in `__get` and `__set`
and invoking methods using `__call`.

See [Class reflection extensions](#class-reflection-extensions), [Dynamic return type extensions](#dynamic-return-type-extensions) and [Type-specifying extensions](#type-specifying-extensions).

You can also install official framework-specific extensions:

* [Doctrine](https://github.com/phpstan/phpstan-doctrine)
* [PHPUnit](https://github.com/phpstan/phpstan-phpunit)
* [Nette Framework](https://github.com/phpstan/phpstan-nette)
* [Dibi - Database Abstraction Library](https://github.com/phpstan/phpstan-dibi)
* [PHP-Parser](https://github.com/phpstan/phpstan-php-parser)
* [beberlei/assert](https://github.com/phpstan/phpstan-beberlei-assert)
* [webmozart/assert](https://github.com/phpstan/phpstan-webmozart-assert)
* [Symfony Framework](https://github.com/phpstan/phpstan-symfony)
* [Mockery](https://github.com/phpstan/phpstan-mockery)

Unofficial extensions for other frameworks and libraries are also available:

* [Phony](https://github.com/eloquent/phpstan-phony)
* [Prophecy](https://github.com/Jan0707/phpstan-prophecy)
* [Laravel](https://github.com/nunomaduro/larastan)
* [marc-mabe/php-enum](https://github.com/marc-mabe/php-enum-phpstan)
* [myclabs/php-enum](https://github.com/timeweb/phpstan-enum)
* [Yii2](https://github.com/proget-hq/phpstan-yii2)
* [PhpSpec](https://github.com/proget-hq/phpstan-phpspec)
* [TYPO3](https://github.com/sascha-egerer/phpstan-typo3)
* [moneyphp/money](https://github.com/JohnstonCode/phpstan-moneyphp)
* [Drupal](https://github.com/mglaman/phpstan-drupal)
* [WordPress](https://github.com/szepeviktor/phpstan-wordpress)
* [Laminas](https://github.com/Slamdunk/phpstan-laminas-framework) (a.k.a. [Zend Framework](https://github.com/Slamdunk/phpstan-zend-framework))

Unofficial extensions with third-party rules:

* [thecodingmachine / phpstan-strict-rules](https://github.com/thecodingmachine/phpstan-strict-rules)
* [ergebnis / phpstan-rules](https://github.com/ergebnis/phpstan-rules)
* [pepakriz / phpstan-exception-rules](https://github.com/pepakriz/phpstan-exception-rules)
* [Slamdunk / phpstan-extensions](https://github.com/Slamdunk/phpstan-extensions)
* [ekino / phpstan-banned-code](https://github.com/ekino/phpstan-banned-code)

New extensions are becoming available on a regular basis!

## Configuration

A config file can be passed to the `phpstan` executable using the `-c` option:

```bash
vendor/bin/phpstan analyse -l 4 -c phpstan.neon src tests
```

When using a custom project config file, you have to pass the `--level` (`-l`)
option to `analyse` command (default value does not apply here).

If you do not provide config file explicitly, PHPStan will look for
files named `phpstan.neon` or `phpstan.neon.dist` in current directory.

The resolution priority is as such:
1. If config file is provided on command line, it is used.
2. If config file `phpstan.neon` exists in current directory, it will be used.
3. If config file `phpstan.neon.dist` exists in current directory, it will be used.
4. If none of the above is true, no config will be used.

[NEON file format](https://ne-on.org/) is very similar to YAML.
All the following options are part of the `parameters` section.

#### Configuration variables
 - `%rootDir%` - root directory where PHPStan resides (i.e. `vendor/phpstan/phpstan` in Composer installation)
 - `%currentWorkingDirectory%` - current working directory where PHPStan was executed

#### Configuration options

 - `tmpDir` - specifies the temporary directory used by PHPStan cache (defaults to `sys_get_temp_dir() . '/phpstan'`)
 - `level` - specifies analysis level - if specified, `-l` option is not required
 - `paths` - specifies analysed paths - if specified, paths are not required to be passed as arguments

Relative paths in the configuration are made absolute according to the directory where the configuration file resides.

Here is an example of a `phpstan.neon` file to run `vendor/bin/phpstan analyse` without any extra argument:

```neon
parameters:
	level: 5
	paths:
		- src
		- tests
```

### Autoloading

PHPStan uses Composer's autoloader by default.

Use the `autoload`/`autoload-dev` sections in composer.json to configure autoloading.

#### Specify paths to scan

If PHPStan complains about some non-existent classes and you're sure the classes
exist in the codebase AND you don't want to use Composer autoloader for some reason,
you can specify directories to scan and concrete files to include using
`autoload_directories` and `autoload_files` array parameters:

```
parameters:
	autoload_directories:
		- build
	autoload_files:
		- generated/routes/GeneratedRouteList.php
```

#### Autoloading for global installation

PHPStan supports global installation using [`composer global`](https://getcomposer.org/doc/03-cli.md#global) or via a [PHAR archive](#installation).
In this case, it's not part of the project autoloader, but it supports autodiscovery of the Composer autoloader
from current working directory residing in `vendor/`:

```bash
cd /path/to/project
phpstan analyse src tests # looks for autoloader at /path/to/project/vendor/autoload.php
```

If you have your dependencies installed at a different path
or you're running PHPStan from a different directory,
you can specify the path to the autoloader with the `--autoload-file|-a` option:

```bash
phpstan analyse --autoload-file=/path/to/autoload.php src tests
```

### Exclude files from analysis

If your codebase contains some files that are broken on purpose
(e. g. to test behaviour of your application on files with invalid PHP code),
you can exclude them using the `excludes_analyse` array parameter. String at each line
is used as a pattern for the [`fnmatch`](https://secure.php.net/manual/en/function.fnmatch.php) function.

```
parameters:
	excludes_analyse:
		- tests/*/data/*
```

### Include custom extensions

If your codebase contains php files with extensions other than the standard .php extension then you can add them
to the `fileExtensions` array parameter:

```
parameters:
	fileExtensions:
		- php
		- module
		- inc
```

### Universal object crates

Classes without predefined structure are common in PHP applications.
They are used as universal holders of data - any property can be set and read on them. Notable examples
include `stdClass`, `SimpleXMLElement` (these are enabled by default), objects with results of database queries etc.
Use `universalObjectCratesClasses` array parameter to let PHPStan know which classes
with these characteristics are used in your codebase:

```
parameters:
	universalObjectCratesClasses:
		- Dibi\Row
		- Ratchet\ConnectionInterface
```

### Add non-obviously assigned variables to scope

If you use some variables from a try block in your catch blocks, set `polluteCatchScopeWithTryAssignments` boolean parameter to `true`.

```php
try {
	$author = $this->getLoggedInUser();
	$post = $this->postRepository->getById($id);
} catch (PostNotFoundException $e) {
	// $author is probably defined here
	throw new ArticleByAuthorCannotBePublished($author);
}
```

If you are enumerating over all possible situations in if-elseif branches
and PHPStan complains about undefined variables after the conditions, you can write
an else branch with throwing an exception:

```php
if (somethingIsTrue()) {
	$foo = true;
} elseif (orSomethingElseIsTrue()) {
	$foo = false;
} else {
	throw new ShouldNotHappenException();
}

doFoo($foo);
```

I recommend leaving `polluteCatchScopeWithTryAssignments` set to `false` because it leads to a clearer and more maintainable code.

### Custom early terminating method calls

Previous example showed that if a condition branches end with throwing an exception, that branch does not have
to define a variable used after the condition branches end.

But exceptions are not the only way how to terminate execution of a method early. Some specific method calls
can be perceived by project developers also as early terminating - like a `redirect()` that stops execution
by throwing an internal exception.

```php
if (somethingIsTrue()) {
	$foo = true;
} elseif (orSomethingElseIsTrue()) {
	$foo = false;
} else {
	$this->redirect('homepage');
}

doFoo($foo);
```

These methods can be configured by specifying a class on whose instance they are called like this:

```
parameters:
	earlyTerminatingMethodCalls:
		Nette\Application\UI\Presenter:
			- redirect
			- redirectUrl
			- sendJson
			- sendResponse
```

### Custom early terminating function calls
In addition to the custom early terminating method calls, you can specify custom early terminating function calls. For example a global helper function called `redirect()`

These functions can be configured by adding them to the `earlyTerminatingFunctionCalls` list like this:

```
parameters:
	earlyTerminatingFunctionCalls:
			- redirect
```

### Ignore error messages with regular expressions

If some issue in your code base is not easy to fix or just simply want to deal with it later,
you can exclude error messages from the analysis result with regular expressions:

```
parameters:
	ignoreErrors:
		- '#Call to an undefined method [a-zA-Z0-9\\_]+::method\(\)#'
		- '#Call to an undefined method [a-zA-Z0-9\\_]+::expects\(\)#'
		- '#Access to an undefined property PHPUnit_Framework_MockObject_MockObject::\$[a-zA-Z0-9_]+#'
		- '#Call to an undefined method PHPUnit_Framework_MockObject_MockObject::[a-zA-Z0-9_]+\(\)#'
```

To exclude an error in a specific directory or file, specify a `path` or `paths` along with the `message`:

```
parameters:
	ignoreErrors:
		-
			message: '#Call to an undefined method [a-zA-Z0-9\\_]+::method\(\)#'
			path: some/dir/SomeFile.php
		-
			message: '#Call to an undefined method [a-zA-Z0-9\\_]+::method\(\)#'
			paths:
				- some/dir/*
				- other/dir/*
		- '#Other error to catch anywhere#'
```

If some of the patterns do not occur in the result anymore, PHPStan will let you know
and you will have to remove the pattern from the configuration. You can turn off
this behaviour by setting `reportUnmatchedIgnoredErrors` to `false` in PHPStan configuration.

### Bootstrap file

If you need to initialize something in PHP runtime before PHPStan runs (like your own autoloader),
you can provide your own bootstrap file:

```
parameters:
	bootstrap: phpstan-bootstrap.php
```

### Custom rules

PHPStan allows writing custom rules to check for specific situations in your own codebase. Your rule class
needs to implement the `PHPStan\Rules\Rule` interface and registered as a service in the configuration file:

```
services:
	-
		class: MyApp\PHPStan\Rules\DefaultValueTypesAssignedToPropertiesRule
		tags:
			- phpstan.rules.rule
```

For inspiration on how to implement a rule turn to [src/Rules](https://github.com/phpstan/phpstan-src/tree/master/src/Rules)
to see a lot of built-in rules.

Check out also [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) repository for extra strict and opinionated rules for PHPStan!

Check as well [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) for rules that detect usage of deprecated classes, methods, properties, constants and traits!

### Custom error formatters

PHPStan outputs errors via formatters. You can customize the output by implementing the `\PHPStan\Command\ErrorFormatter\ErrorFormatter` interface in a new class and add it to the configuration. For existing formatters, see next chapter.

```php

namespace PHPStan\Command\ErrorFormatter;

interface ErrorFormatter
{

	/**
	 * Formats the errors and outputs them to the console.
	 *
	 * @param \PHPStan\Command\AnalysisResult $analysisResult
	 * @param \Symfony\Component\Console\Style\OutputStyle $style
	 * @return int Error code.
	 */
	public function formatErrors(
		AnalysisResult $analysisResult,
		\PHPStan\Command\Output $output
	): int;

}
```

Register the formatter in your `phpstan.neon`:

```
services:
	errorFormatter.awesome:
		class: App\PHPStan\AwesomeErrorFormatter
```

Use the name part after `errorFormatter.` as the CLI option value:

```bash
vendor/bin/phpstan analyse -c phpstan.neon -l 4 --error-format awesome src tests
```

### Existing error formatters to be used

You can pass the following keywords to the `--error-format=X` parameter in order to affect the output:

- `table`: Default. Grouped errors by file, colorized. For human consumption.
- `raw`: Contains one error per line, with path to file, line number, and error description
- `checkstyle`: Creates a checkstyle.xml compatible output. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `json`: Creates minified .json output without whitespaces. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `junit`: Creates JUnit compatible output. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `prettyJson`: Creates human readable .json output with whitespaces and indentations. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `gitlab`: Creates format for use Code Quality widget on GitLab Merge Request.
- `baselineNeon`: Creates a .neon output for including in your config. This allows a baseline for existing errors. Note that you'd have to redirect output into a file in order to capture the results for later processing. [Detailed article about this feature.](https://medium.com/@ondrejmirtes/phpstans-baseline-feature-lets-you-hold-new-code-to-a-higher-standard-e77d815a5dff)

## Class reflection extensions

Classes in PHP can expose "magical" properties and methods decided in run-time using
class methods like `__get`, `__set` and `__call`. Because PHPStan is all about static analysis
(testing code for errors without running it), it has to know about those properties and methods beforehand.

When PHPStan stumbles upon a property or a method that is unknown to built-in class reflection, it iterates
over all registered class reflection extensions until it finds one that defines the property or method.

Class reflection extension cannot have `PHPStan\Broker\Broker` (service for obtaining class reflections) injected in the constructor due to circular reference issue, but the extensions can implement `PHPStan\Reflection\BrokerAwareExtension` interface to obtain Broker via a setter.

### Properties class reflection extensions

This extension type must implement the following interface:

```php
namespace PHPStan\Reflection;

interface PropertiesClassReflectionExtension
{

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool;

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection;

}
```

Most likely you will also have to implement a new `PropertyReflection` class:

```php
namespace PHPStan\Reflection;

interface PropertyReflection
{

	public function getType(): Type;

	public function getDeclaringClass(): ClassReflection;

	public function isStatic(): bool;

	public function isPrivate(): bool;

	public function isPublic(): bool;

}
```

This is how you register the extension in project's PHPStan config file:

```
services:
	-
		class: App\PHPStan\PropertiesFromAnnotationsClassReflectionExtension
		tags:
			- phpstan.broker.propertiesClassReflectionExtension
```

### Methods class reflection extensions

This extension type must implement the following interface:

```php
namespace PHPStan\Reflection;

interface MethodsClassReflectionExtension
{

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool;

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection;

}
```

Most likely you will also have to implement a new `MethodReflection` class:

```php
namespace PHPStan\Reflection;

interface MethodReflection
{

	public function getDeclaringClass(): ClassReflection;

	public function getPrototype(): self;

	public function isStatic(): bool;

	public function isPrivate(): bool;

	public function isPublic(): bool;

	public function getName(): string;

	/**
	 * @return \PHPStan\Reflection\ParameterReflection[]
	 */
	public function getParameters(): array;

	public function isVariadic(): bool;

	public function getReturnType(): Type;

}
```

This is how you register the extension in project's PHPStan config file:

```
services:
	-
		class: App\PHPStan\EnumMethodsClassReflectionExtension
		tags:
			- phpstan.broker.methodsClassReflectionExtension
```

## Dynamic return type extensions

If the return type of a method is not always the same, but depends on an argument passed to the method,
you can specify the return type by writing and registering an extension.

Because you have to write the code with the type-resolving logic, it can be as complex as you want.

After writing the sample extension, the variable `$mergedArticle` will have the correct type:

```php
$mergedArticle = $this->entityManager->merge($article);
// $mergedArticle will have the same type as $article
```

This is the interface for dynamic return type extension:

```php
namespace PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;

interface DynamicMethodReturnTypeExtension
{

	public function getClass(): string;

	public function isMethodSupported(MethodReflection $methodReflection): bool;

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type;

}
```

And this is how you'd write the extension that correctly resolves the EntityManager::merge() return type:

```php
public function getClass(): string
{
	return \Doctrine\ORM\EntityManager::class;
}

public function isMethodSupported(MethodReflection $methodReflection): bool
{
	return $methodReflection->getName() === 'merge';
}

public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
{
	if (count($methodCall->args) === 0) {
		return \PHPStan\Reflection\ParametersAcceptorSelector::selectFromArgs(
			$scope,
			$methodCall->args,
			$methodReflection->getVariants()
		)->getReturnType();
	}
	$arg = $methodCall->args[0]->value;

	return $scope->getType($arg);
}
```

And finally, register the extension to PHPStan in the project's config file:

```
services:
	-
		class: App\PHPStan\EntityManagerDynamicReturnTypeExtension
		tags:
			- phpstan.broker.dynamicMethodReturnTypeExtension
```

There's also an analogous functionality for:

* **static methods** using `DynamicStaticMethodReturnTypeExtension` interface
and `phpstan.broker.dynamicStaticMethodReturnTypeExtension` service tag.
* **functions** using `DynamicFunctionReturnTypeExtension` interface and `phpstan.broker.dynamicFunctionReturnTypeExtension` service tag.

## Type-specifying extensions

These extensions allow you to specify types of expressions based on certain pre-existing conditions. This is best illustrated with couple examples:

```php
if (is_int($variable)) {
    // here we can be sure that $variable is integer
}
```

```php
// using PHPUnit's asserts

self::assertNotNull($variable);
// here we can be sure that $variable is not null
```

Type-specifying extension cannot have `PHPStan\Analyser\TypeSpecifier` injected in the constructor due to circular reference issue, but the extensions can implement `PHPStan\Analyser\TypeSpecifierAwareExtension` interface to obtain TypeSpecifier via a setter.

This is the interface for type-specifying extension:

```php
namespace PHPStan\Type;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;

interface StaticMethodTypeSpecifyingExtension
{

	public function getClass(): string;

	public function isStaticMethodSupported(MethodReflection $staticMethodReflection, StaticCall $node, TypeSpecifierContext $context): bool;

	public function specifyTypes(MethodReflection $staticMethodReflection, StaticCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes;

}
```

And this is how you'd write the extension for the second example above:

```php
public function getClass(): string
{
	return \PHPUnit\Framework\Assert::class;
}

public function isStaticMethodSupported(MethodReflection $staticMethodReflection, StaticCall $node, TypeSpecifierContext $context): bool
{
	// The $context argument tells us if we're in an if condition or not (as in this case).
	// Is assertNotNull called with at least 1 argument?
	return $staticMethodReflection->getName() === 'assertNotNull' && $context->null() && isset($node->args[0]);
}

public function specifyTypes(MethodReflection $staticMethodReflection, StaticCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
{
	$expr = $node->args[0]->value;
	$typeBefore = $scope->getType($expr);
	$type = TypeCombinator::removeNull($typeBefore);

	// Assuming extension implements \PHPStan\Analyser\TypeSpecifierAwareExtension.

	return $this->typeSpecifier->create($expr, $type, TypeSpecifierContext::createTruthy());
}
```

And finally, register the extension to PHPStan in the project's config file:

```
services:
	-
		class: App\PHPStan\AssertNotNullTypeSpecifyingExtension
		tags:
			- phpstan.typeSpecifier.staticMethodTypeSpecifyingExtension
```

There's also an analogous functionality for:

* **dynamic methods** using `MethodTypeSpecifyingExtension` interface
and `phpstan.typeSpecifier.methodTypeSpecifyingExtension` service tag.
* **functions** using `FunctionTypeSpecifyingExtension` interface and `phpstan.typeSpecifier.functionTypeSpecifyingExtension` service tag.

## Known issues

* If `include` or `require` are used in the analysed code (instead of `include_once` or `require_once`),
PHPStan will throw `Cannot redeclare class` error. Use the `_once` variants to avoid this error.
* If PHPStan crashes without outputting any error, it's quite possible that it's
because of a low memory limit set on your system. **Run PHPStan again** to read a couple of hints
what you can do to prevent the crashes.

## Code of Conduct

This project adheres to a [Contributor Code of Conduct](https://github.com/phpstan/phpstan/blob/master/CODE_OF_CONDUCT.md). By participating in this project and its community, you are expected to uphold this code.

## Contributing

Any contributions are welcome. PHPStan's source code open to pull requests lives at [`phpstan/phpstan-src`](https://github.com/phpstan/phpstan-src).
