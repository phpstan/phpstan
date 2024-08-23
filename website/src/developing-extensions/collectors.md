---
title: Collectors
---

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.8.0</div>

If you want to write [custom rules](/developing-extensions/rules) that take a look at the whole codebase instead of a single AST node, you can take advantage of collectors.

PHPStan rules are executed in isolation across multiple processes so it's not possible to share information from all the executions.

In order to write a specific category of rules like unused code detection, we need to use collectors.

Collectors are executed the same way as rules, in separate processes, and they collect various information about the codebase. All of the data collected by collectors is gathered into a single object [`CollectedDataNode`](https://apiref.phpstan.org/1.12.x/PHPStan.Node.CollectedDataNode.html), and traditional rules registered for this node type are executed in the main PHPStan process with all of the gathered data.

Collectors are seamlessly integrated with the [result cache](/user-guide/result-cache) so even if you take advantage of them, PHPStan is going to be as fast as before.

The Collector interface
-------------------

Collectors are classes implementing the [`PHPStan\Collectors\Collector` interface](https://apiref.phpstan.org/1.12.x/PHPStan.Collectors.Collector.html). The interface has two methods:

* `public function getNodeType(): string`
* `public function processNode(PhpParser\Node $node, PHPStan\Analyser\Scope $scope): array`

The `getNodeType()` method returns the [AST](/developing-extensions/abstract-syntax-tree) node type. Every time the analyser encounters the node of this type, the second method `processNode()` is called with that AST node as the first argument, and the current [Scope](/developing-extensions/scope) as the second argument. The goal of the `processNode()` method is to return data later used by custom rules.

[Choosing the right AST node](/developing-extensions/rules#choosing-the-right-ast-node) and [implementing the collector logic](/developing-extensions/rules#implementing-the-rule-logic) is very similar to doing these things when implementing the `PHPStan\Rules\Rule` interface.

The class implementing the interface should use [generics](/blog/generics-in-php-using-phpdocs) to inform PHPStan about the collected data type:

```php
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * @implements Collector<Node\Stmt\Trait_, array{string, int}>
 */
class TraitDeclarationCollector implements Collector
{

	public function getNodeType(): string
	{
		return Node\Stmt\Trait_::class;
	}

	public function processNode(Node $node, Scope $scope)
	{
		if ($node->namespacedName === null) {
			return null;
		}

		// returns an array with trait name and line - array{string, int}
		return [$node->namespacedName->toString(), $node->getLine()];
	}

}
```

The returned data should be arrays and scalar values so that it's easy to send them "over the wire" between main and child processes, and also store them in the result cache.

Registering the collector in the configuration
---------------

The custom collector needs to be registered in the [configuration file](/config-reference):

```yaml
services:
	-
		class: App\MyCollector
		tags:
			- phpstan.collector
```

Using collected data in a custom rule
---------------

Custom rules need to be registered for the [`PHPStan\Node\CollectedDataNode` node](https://apiref.phpstan.org/1.12.x/PHPStan.Node.CollectedDataNode.html). This object contains all the gathered data from all the collectors, but it's only possible to get data from a single collector at a time:

```php
public function getNodeType(): string
{
	return CollectedDataNode::class;
}

public function processNode(Node $node, Scope $scope): array
{
	$traitDeclarationData = $node->get(TraitDeclarationCollector::class);
	// $traitDeclarationData is array<string, list<array{string, int}>>
	foreach ($traitDeclarationData as $file => $declarations) {
		foreach ($declarations as [$name, $line]) {
			// ...
		}
	}
	// ...
}
```

Because these custom CollectedDataNode rules are executed out of context, the reported errors should specify the file and line to report:

```php
$errors = [];
foreach ($declaredTraits as [$file, $name, $line]) {
	$errors[] = RuleErrorBuilder::message(sprintf(
		'Trait %s is used zero times and is not analysed.',
		$name,
	))->file($file)->line($line)->build();
}

return $errors;
```

Testing collectors
---------------

Collectors are always [tested in addition to the rule](/developing-extensions/testing#custom-rules) that's using them. Besides implementing the `getRule(): Rule` method that returns the tested rule instance, override the `getCollectors()` method to return the collectors needed for the rule to work:

```php
protected function getRule(): Rule
{
	return new NotAnalysedTraitRule();
}

protected function getCollectors(): array
{
	return [
		new TraitDeclarationCollector(),
		new TraitUseCollector(),
	];
}

public function testRule(): void
{
	$this->analyse([__DIR__ . '/data/not-analysed-trait.php'], [
		[
			'Trait NotAnalysedTrait\Bar is used zero times and is not analysed.',
			10,
		],
	]);
}
```
