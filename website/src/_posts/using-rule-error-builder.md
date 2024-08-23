---
title: "Using RuleErrorBuilder to enrich reported errors in custom rules"
date: 2023-07-12
tags: guides
---

When writing [custom rules](/developing-extensions/rules) for PHPStan, developers can either return plain strings or RuleError instances.

```php
/**
 * @phpstan-param TNodeType $node
 * @return (string|RuleError)[] errors
 */
public function processNode(Node $node, Scope $scope): array;
```

The latter is a way to attach additional information to the reported errors.

RuleError [is just an interface](https://github.com/phpstan/phpstan-src/blob/1.12.x/src/Rules/RuleError.php). The way you create an instance is through [RuleErrorBuilder](https://apiref.phpstan.org/1.12.x/PHPStan.Rules.RuleErrorBuilder.html):

```php
return [
	RuleErrorBuilder::message('This is an error message.')
		->build(),
];
```

Besides setting an error message RuleErrorBuilder offers the following capabilities:

* `->line(int $line)`: Set a different file line. Useful when you want to report a different line than the node which the rule was called for
* `->file(string $file)`: Set a different file path. Useful for [collector rules](/developing-extensions/collectors).
* `->tip(string $tip)`: Shows an additional text next to a 💡 emoji on the command line. You can tell the user why the error is reported or how to solve it.
* `->nonIgnorable()`: Makes the error non-ignorable by any means.
* `->metadata(array<mixed> $metadata)`: Attach additional metadata to the error that can later be read in the [error formatter](/developing-extensions/error-formatters).
* `->identifier(string $identifier)`: Sets an error identifier. More about this below.


Error identifiers
----------------

The flagship feature of the upcoming PHPStan 1.11 release are error identifiers. You will be able to use them to ignore specific errors:

```php
function () {
	// @phpstan-ignore argument.type
	$this->foo->doSomethingWithString(1);

	$this->foo->doSomethingWithString(2); // @phpstan-ignore argument.type
};
```

There's a [generated catalogue](/error-identifiers) of all the identifiers in PHPStan itself and 1<sup>st</sup> party extensions. Each identifier links to source code where it's reported so this serves as a great educational resource about PHPStan internals.

To ensure great experience for all users, custom rules will also be required to provide their own identifiers. At first it will only be soft-enforced in [Bleeding Edge](/blog/what-is-bleeding-edge), and in PHPStan 2.0 it will be hard-enforced with a native return type.

This means that the ability to return plain strings from custom rules **will eventually go away**. I recommend you to switch to RuleErrorBuilder sooner rather than later.

PHPStan 1.11 with Bleeding Edge enabled will require custom rules to return errors with identifiers. As a first step, remove any PHPDoc above your `processNode` method. If your rule does not yet have `@implements Rule<...>` generic PHPDoc tag, add it:

```diff-php
+/**
+ * @implements Rule<StaticCall>
+ */
 class MyRule implements Rule
 {
 	public function getNodeType(): string
 	{
 		return StaticCall::class;
 	}

-	/**
-	 * @param StaticCall $node
-	 * @param Scope $scope
-	 * @return string[]
-	 */
 	public function processNode(Node $node, Scope $scope): array
 	{
 		// ...
 	}
```

Even without the PHPDoc both PHPStan and PhpStorm will understand that the parameter `$node` coming into the `processNode` method is a `StaticCall`.

After that migrate the plain strings to RuleErrorBuilder, and add error identifiers for each of them:

```diff-php
 	public function processNode(Node $node, Scope $scope): array
 	{
 		return [
-			'This is an error message.',
+			RuleErrorBuilder::message('This is an error message.')
+				->identifier('some.problem')
+				->build(),
 		];
 	}
```

For an inspiration how the identifiers should look like check out [the catalogue](/error-identifiers).

The identifier must consist of lowercase and uppercase ASCII letters, and optionally can have one or more dots in the middle. [See the tests](https://github.com/phpstan/phpstan-src/blob/1.12.x/tests/PHPStan/Analyser/ErrorTest.php) for examples of valid and invalid identifiers.

------------

PHPStan 1.11 will be released at some time in the coming months. Don't worry, your old rules will not break with that release, but it would be great to modernize them to take advantage (and let your users take advantage) of the latest PHPStan features. Thanks!
