<h1 align="center">PHPStan - PHP Static Analysis Tool</h1>

<p align="center">
	<img src="https://i.imgur.com/MOt7taM.png" alt="PHPStan" width="300" height="300">
</p>

<p align="center">
	<a href="https://travis-ci.com/phpstan/phpstan"><img src="https://travis-ci.com/phpstan/phpstan.svg?branch=master" alt="Build Status"></a>
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
&nbsp;&nbsp;&nbsp;
<a href="https://togetter.com/"><img src="https://i.imgur.com/x9n5cj3.png" alt="Togetter" width="283" height="64"></a>

[**You can now sponsor my open-source work on PHPStan through GitHub Sponsors.**](https://github.com/sponsors/ondrejmirtes)

Does GitHub already have your 💳? Do you use PHPStan to find 🐛 before they reach production? [Send a couple of 💸 a month my way too.](https://github.com/sponsors/ondrejmirtes) Thank you!

One-time donations [through PayPal](https://paypal.me/phpstan) are also accepted. To request an invoice, [contact me](mailto:ondrej@mirtes.cz) through e-mail.

BTC: bc1qd5s06wjtf8rzag08mk3s264aekn52jze9zeapt
<br>LTC: LSU5xLsWEfrVx1P9yJwmhziHAXikiE8xtC

## Documentation

* [Getting Started & User Guide](https://phpstan.org/user-guide/getting-started)
* [Config Reference](https://phpstan.org/config-reference)

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
* [taptima / phpstan-custom](https://github.com/taptima/phpstan-custom)

New extensions are becoming available on a regular basis!

## Custom rules

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

## Custom error formatters

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

## Existing error formatters to be used

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

## Code of Conduct

This project adheres to a [Contributor Code of Conduct](https://github.com/phpstan/phpstan/blob/master/CODE_OF_CONDUCT.md). By participating in this project and its community, you are expected to uphold this code.

## Contributing

Any contributions are welcome. PHPStan's source code open to pull requests lives at [`phpstan/phpstan-src`](https://github.com/phpstan/phpstan-src).
