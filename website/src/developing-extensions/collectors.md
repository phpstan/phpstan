---
title: Collectors
---

If you want to write [custom rules](/developing-extensions/rules) that take a look at the whole codebase instead of a single AST node, you can take advantage of collectors.

PHPStan rules are executed in isolation across multiple processes so it's not possible to share information from all the executions.

In order to write a specific category of rules like unused code detection, we need to use collectors.

Collectors are executed the same way as rules, in separate processes, and they collect various information about the codebase. All of the data collected by collectors is gathered into a single object [`CollectedDataNode`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Node.CollectedDataNode.html), and traditional rules registered for this node type are executed in the main PHPStan process with all of the gathered data.

Collectors are seamlessly integrated with the [result cache](/user-guide/result-cache) so even if you take advantage of them, PHPStan is going to be as fast as before.

The Collector interface
-------------------

Collectors are classes implementing the [`PHPStan\Collectors\Collector` interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Collectors.Collector.html). The interface has two methods:

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

Custom rules need to be registered for the [`PHPStan\Node\CollectedDataNode` node](https://apiref.phpstan.org/__BRANCH__/PHPStan.Node.CollectedDataNode.html). This object contains all the gathered data from all the collectors, but it's only possible to get data from a single collector at a time:

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

Emitting collected data from rules
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.2.0</div>

Rules can emit collected data directly, without having to write a separate Collector class. The emitted data is aggregated the same way as data from Collectors and can be consumed by rules registered for [`CollectedDataNode`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Node.CollectedDataNode.html).

You can achieve this by typehinting the second parameter of `processNode()` as `Scope&CollectedDataEmitter`:

```php
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\CollectedDataEmitter;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<MethodCall>
 */
class MyRule implements Rule
{

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	/**
	 * @param Scope&CollectedDataEmitter $scope
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		// Emit data as if it came from MyCollector
		$scope->emitCollectedData(MyCollector::class, $node->name->toString());

		return [];
	}

}
```

The referenced Collector class (`MyCollector` in the example above) must exist and implement the `Collector` interface. It's used to verify the data type statically and to identify the collected data. However, it does **not** need to be registered as a collector in the configuration, unless you also want it to collect data on its own.

The emitted data can then be consumed by a rule registered for `CollectedDataNode` the same way as data from regular collectors:

```php
$collectedData = $node->get(MyCollector::class);
```

Tracking dependencies for the result cache
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.0</div>

If the collector reads something PHPStan doesn't know about, like a configuration file, typehint the second parameter of `processNode()` as `Scope&DependencyTracker` in the PHPDoc and track it with `$scope->trackFileDependency()` or one of the other `DependencyTracker` methods. When it changes, the [result cache](/user-guide/result-cache) analyses the file again and its data is collected again. [Learn more »](/developing-extensions/result-cache-meta-extensions#tracking-dependencies-on-files)

Which files the reported errors depend on
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.6</div>

A rule on `CollectedDataNode` often reports an error in one file based on what it collected in another one. Take a rule reporting calls of functions marked as legacy: the collector of the calls runs in the file with the call, but the rule knows a function is legacy only when the file declaring it is analysed too.

When only a part of the project is analysed - some directories or files passed on the command line instead of all the configured [`paths`](/config-reference#analysed-files) - PHPStan reports [`ignoreErrors`](/user-guide/ignoring-errors) entries that matched nothing only for the files whose errors it knows, and `--fix` removes only those. Without knowing about the other file, PHPStan would report the entry ignoring the legacy call as unmatched, because the declaration wasn't analysed and the error wasn't reported.

Tell PHPStan about the other file by calling `$scope->trackAnalysedFileDependency()` in the collector. Typehint the `$scope` parameter as `Scope&DependencyTracker` in the PHPDoc:

```php
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ReflectionProvider;

/**
 * @implements Collector<FuncCall, array{string, int}>
 */
class LegacyCallCollector implements Collector
{

	public function __construct(private ReflectionProvider $reflectionProvider)
	{
	}

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	/**
	 * @param Scope&DependencyTracker $scope
	 */
	public function processNode(Node $node, Scope $scope)
	{
		if (!$node->name instanceof Node\Name || !$this->reflectionProvider->hasFunction($node->name, $scope)) {
			return null;
		}

		$function = $this->reflectionProvider->getFunction($node->name, $scope);

		// the rule reports the call only when the declaration is analysed too
		$fileName = $function->getFileName();
		if ($fileName !== null) {
			$scope->trackAnalysedFileDependency($fileName);
		}

		return [$function->getName(), $node->getStartLine()];
	}

}
```

When the tracked file isn't analysed, the `ignoreErrors` entries of the file with the call aren't reported as unmatched. The same goes for the files the tracked file depends on, so a chain of dependencies across several files is followed too. A file that isn't analysed even when the whole project is - one in `vendor/`, outside the configured `paths`, or in [`excludePaths`](/user-guide/ignoring-errors#excluding-whole-files) - doesn't count, because it doesn't report errors either way. Track the file whether the rule is going to report an error or not.

When what the rule reports in a file can depend on any file in the project, like on all subclasses of a class, call `$scope->trackAnalysedProjectDependency()` instead. The `ignoreErrors` entries of the file are then reported as unmatched only when the whole project is analysed.

The tracked dependencies are saved in the [result cache](/user-guide/result-cache) along with the collected data. They only decide which unmatched entries are reported. They don't make the result cache analyse the file again when the tracked file changes - for that, see [tracking dependencies for the result cache](#tracking-dependencies-for-the-result-cache).

Track the dependencies in the collector, or in a rule [emitting collected data](#emitting-collected-data-from-rules) - typehint its `$scope` as `Scope&CollectedDataEmitter&DependencyTracker` then. Calling them in the rule on `CollectedDataNode` itself does nothing, because it doesn't analyse any file.

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
