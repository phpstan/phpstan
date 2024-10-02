---
title: Custom Rules
---

PHPStan allows writing custom rules to check for specific situations in your own codebase. The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

Motivation
-------------

We want to avoid dangerous and unwanted situations in our code.

```php
class Person
{

	/**
	 * !!! DO NOT INSTANTIATE Person DIRECTLY! Use PersonFactory instead !!!
	 */
	public function __construct(string $email)
	{

	}

}
```

If a piece of code is fine from the point of the programming language and thus not detected by core PHPStan rules, but does something that we want to avoid because of our experience with the project's codebase, we should write a custom PHPStan rule so that the situation is detected automatically and we don't have to rely on human code review - it's unreliable and we shouldn't keep our brains busy with something that computers do better.

In the example above we'd like to disallow `new Person()`.

The Rule interface
---------------

Custom rules are classes implementing the [`PHPStan\Rules\Rule` interface](https://apiref.phpstan.org/2.0.x/PHPStan.Rules.Rule.html). The interface has two methods:

* `public function getNodeType(): string`
* `public function processNode(PhpParser\Node $node, PHPStan\Analyser\Scope $scope): array`

The `getNodeType()` method returns the [AST](/developing-extensions/abstract-syntax-tree) node type. Every time the analyser encounters the node of this type, the second method `processNode()` is called with that AST node as the first argument, and the current [Scope](/developing-extensions/scope) as the second argument. The goal of the `processNode()` method is to return an array of errors to report to the user [using RuleErrorBuilder](/blog/using-rule-error-builder).

Choosing the right AST node
---------------

People unfamiliar with the [AST](/developing-extensions/abstract-syntax-tree) don't know which node type to return from the `getNodeType()` method to get started. Fortunately we can follow a simple guide to find the right type.

Write a small piece of code that contains the situation we want the rule to report:

```php
class Foo
{

	public function doFoo(): void
	{
		$p = new Person();
	}

}
```

Create the rule class and make sure the namespace, the class name, and the rule's location match your autoloading rules:

```php
namespace App;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

class MyRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		// todo
	}

	public function processNode(Node $node, Scope $scope): array
	{
		// todo
	}

}
```

Register it in your configuration file:

```yaml
rules:
	- App\MyRule
```

Return `\PhpParser\Node::class` from the `getNodeType()` method. This is the common interface to all AST nodes:

```php
public function getNodeType(): string
{
	return \PhpParser\Node::class;
}
```

And dump all the node types the rule is called with:

```php
public function processNode(Node $node, Scope $scope): array
{
	var_dump(get_class($node));
	return [];
}
```

When you run PHPStan with this rule registered on the small sample file we wrote above (note: we have to enable output from the rule by adding the `--debug` option):

```
vendor/bin/phpstan analyse -l 8 --debug test.php
```

You'll see PHP printing all the nodes the rule is called with:

```
string(21) "PHPStan\Node\FileNode"
string(26) "PhpParser\Node\Stmt\Class_"
string(24) "PHPStan\Node\InClassNode"
string(31) "PhpParser\Node\Stmt\ClassMethod"
string(25) "PhpParser\Node\Identifier"
string(30) "PHPStan\Node\InClassMethodNode"
string(30) "PhpParser\Node\Stmt\Expression"
string(26) "PhpParser\Node\Expr\Assign"
string(28) "PhpParser\Node\Expr\Variable"
string(24) "PhpParser\Node\Expr\New_"
string(29) "PHPStan\Node\ExecutionEndNode"
string(39) "PHPStan\Node\MethodReturnStatementsNode"
string(32) "PHPStan\Node\ClassPropertiesNode"
string(29) "PHPStan\Node\ClassMethodsNode"
string(31) "PHPStan\Node\ClassConstantsNode"
```

The method body (`$p = new Person();`) is represented by these four nodes:

```
string(30) "PhpParser\Node\Stmt\Expression"
string(26) "PhpParser\Node\Expr\Assign"
string(28) "PhpParser\Node\Expr\Variable"
string(24) "PhpParser\Node\Expr\New_"
```

The `new` keyword is represented by the `PhpParser\Node\Expr\New_` node, and that's the one we're interested in. Let's update our `getNodeType(): string` method:

```php
public function getNodeType(): string
{
	return Node\Expr\New_::class;
}
```

To have zero errors when analysing the rule itself with PHPStan, we also need to specify the `TNodeType` template type of the implemented `Rule` interface:

```php
/**
 * @implements \PHPStan\Rules\Rule<Node\Expr\New_>
 */
class MyRule implements \PHPStan\Rules\Rule
{
	...
```

The [New_ node class](https://apiref.phpstan.org/2.0.x/PhpParser.Node.Expr.New_.html) looks like this:

```php
class New_ extends Expr
{
	/** @var Node\Name|Expr|Node\Stmt\Class_ Class name */
	public $class;

	/** @var Node\Arg[] Arguments */
	public $args;

	...
}
```

The `$class` property can be three different types:

* A `Name` instance describing the usual `new Foo()`
* An `Expr` instance describing the more dynamic `new $foo()`
* A `Class_` instance describing an anonymous class: `new class () {}`

For the purpose of our rule we're interested only in the first case.

Implementing the rule logic
---------------

Our `processNode()` method is now called only for the `New_` nodes. We need filter out the cases instantiating other node types than `Name`, and also the cases that don't instantiate the `Person` class. We also want to allow `new Person()` in `PersonFactory` itself:

```php
public function processNode(Node $node, Scope $scope): array
{
	if (!$node->class instanceof Node\Name) {
		return [];
	}

	if ($node->class->toString() !== Person::class) {
		return [];
	}

	if (
		$scope->isInClass()
		&& $scope->getClassReflection()->getName() === PersonFactory::class
	) {
		return [];
	}

	...
}
```

What's left to do is to return an array of errors to report. The `processNode()` method can return either an array of strings, or an array of `RuleError` instances. These can't be instantiated directly, but only through [`PHPStan\Rules\RuleErrorBuilder`](https://apiref.phpstan.org/2.0.x/PHPStan.Rules.RuleErrorBuilder.html). It allows to attach more information to errors like a different error line, or making the error non-ignorable. The simplest way to use it is to create an instance using the static `message(string $message): RuleErrorBuilder` method and return `RuleError` instance using the `build(): RuleError` method. [Learn more about RuleErrorBuilder »](/blog/using-rule-error-builder)

```php
return [
	RuleErrorBuilder::message(
		'New Person instance can be created only in PersonFactory.'
	)->build(),
];
```

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

If you want to check out the surroundings of the node the rule was invoked for, like the parent node or the siblings (previous and next nodes), you have to write a custom node visitor that will preprocess the AST and gather the needed data for your custom rule. [Learn more »](/blog/preprocessing-ast-for-custom-rules)

</div>

Registering the rule in the configuration
---------------

PHPStan needs to know it's supposed to execute the rule. The simplest way is to add the rule class to the `rules` section in your [configuration file](/config-reference):

```yaml
rules:
	- App\MyRule
```

This only works if the rule class doesn't have a constructor, or all the constructor parameters can be [autowired](/developing-extensions/dependency-injection-configuration#autowiring). If we have non-autowirable parameters in the constructor, we need to resort the rule as a service in the `services` section of the configuration file, specify the arguments, and the `phpstan.rules.rule` tag:

```yaml
services:
	-
		class: App\MyRule
		arguments:
			reportMaybes: %reportMaybes%
		tags:
			- phpstan.rules.rule
```

[Learn more about dependency injection and configuration »](/developing-extensions/dependency-injection-configuration)

Testing the rule
---------------

PHPStan supports testing with the industry standard [PHPUnit](https://phpunit.de/) framework. Custom rules can be tested in a test case extended from `PHPStan\Testing\RuleTestCase`. A typical test can look like this:

```php
<?php declare(strict_types = 1);

namespace App;

use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MyRule>
 */
class MyRuleTest extends RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		// getRule() method needs to return an instance of the tested rule
		return new MyRule();
	}

	public function testRule(): void
	{
		// first argument: path to the example file that contains some errors that should be reported by MyRule
		// second argument: an array of expected errors,
		// each error consists of the asserted error message, and the asserted error file line
		$this->analyse([__DIR__ . '/data/my-rule.php'], [
			[
				'X should not be Y', // asserted error message
				15, // asserted error line
			],
		]);

		// the test fails, if the expected error does not occur,
		// or if there are other errors reported beside the expected one
	}

}
```

Virtual nodes
---------------

You might have noticed that some of the nodes in the [Choosing the right AST node](/developing-extensions/rules#choosing-the-right-ast-node) section do not come from the [PHP-Parser](https://github.com/nikic/php-parser) library but from PHPStan itself.

Some situations we want to report are hard to detect with the out-of-the-box [AST](/developing-extensions/abstract-syntax-tree). For example if we want to report that's something is missing in the AST (like a `return` statement), there's no node type to register our rule for.

That's why PHPStan introduces [custom virtual nodes](https://apiref.phpstan.org/2.0.x/namespace-PHPStan.Node.html) to use in rules. Some of the common use-cases are:

* We need to check something is at the beginning of each file, like `declare(strict_types = 1)` or a required namespace. We can use [`FileNode`](https://apiref.phpstan.org/2.0.x/PHPStan.Node.FileNode.html) for that.
* Rules are called before the scope is updated with the information of the registered node type. That means if we're registering our rule for the `PhpParser\Node\Stmt\Class_` node, the scope will say we're not in a class. But if we use the virtual [`InClassNode`](https://apiref.phpstan.org/2.0.x/PHPStan.Node.InClassNode.html), `$scope->getClassReflection()` will contain the class reflection. Same goes for [`InClassMethodNode`](https://apiref.phpstan.org/2.0.x/PHPStan.Node.InClassMethodNode.html) and [`InFunctionNode`](https://apiref.phpstan.org/2.0.x/PHPStan.Node.InFunctionNode.html).
* If we're interested in class properties regardless of whether they're traditional or [promoted](https://wiki.php.net/rfc/constructor_promotion), we can use the [`ClassPropertyNode`](https://apiref.phpstan.org/2.0.x/PHPStan.Node.ClassPropertyNode.html).

Collectors
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.8.0</div>

If you want to write custom rules that take a look at the whole codebase instead of a single AST node, you can take advantage of collectors.

PHPStan rules are executed in isolation across multiple processes so it's not possible to share information from all the executions.

In order to write a specific category of rules like unused code detection, we need to use collectors. [Learn more »](/developing-extensions/collectors)

More custom rules examples
---------------

<details>
	<summary class="text-blue-500 font-bold">Disallow comparing DateTime objects with === and !==</summary>

	Comparing `DateTime` and `DateTimeImmutable` objects with `===` and `!==` is probably a mistake made by the developer, because `===` only ever returns true if it compares the same object instance. But in case of `DateTime` instances, we probably want to compare the time values they contain. Operators `==` and `!=` should be used for that instead.

```php
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<BinaryOp>
 */
class CompareDateTimeRule implements Rule
{

	public function getNodeType(): string
	{
		return BinaryOp::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (
			!$node instanceof BinaryOp\Identical
			&& !$node instanceof BinaryOp\NotIdentical
		) {
			return [];
		}

		$leftType = $scope->getType($node->left);
		$rightType = $scope->getType($node->right);
		$dateTimeType = new ObjectType(\DateTimeInterface::class);

		if (
			!$dateTimeType->isSuperTypeOf($leftType)->yes()
			|| !$dateTimeType->isSuperTypeOf($rightType)->yes()
		) {
			return [];
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Cannot compare DateTime instances with %s.',
				$node->getOperatorSigil()
			))->build(),
		];
	}

}
```
</details>

<details>
	<summary class="text-blue-500 font-bold">Disallow assigning properties outside of constructor</summary>

```php
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\Expr\Assign>
 */
class ImmutableObjectRule implements Rule
{

	public function getNodeType(): string
	{
		// we're interested only in assignments
		return Node\Expr\Assign::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			// perhaps an error should be reported here too
			// if we want to disallow assigning properties outside of classes
			return [];
		}
		if (!$node->var instanceof Node\Expr\PropertyFetch) {
			// we're interested only in property on the left side of assignment
			// so variable assignments and similar are ignored by this rule
			return [];
		}
		if (!$node->var->name instanceof Node\Identifier) {
			// we're interested only in non-dynamic fetches like $this->foo
			return [];
		}
		$inMethod = $scope->getFunction();
		if (!$inMethod instanceof MethodReflection) {
			return [];
		}
		if ($inMethod->getName() === '__construct') {
			// nothing should be reported if we're in a constructor
			return [];
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Immutability violated - assigning $%s property outside constructor.',
				$node->var->name->toString()
			))->build(),
		];
	}

}
```
</details>
