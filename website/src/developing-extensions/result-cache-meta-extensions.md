---
title: Result Cache Meta Extensions
---

PHPStan invalidates the [result cache](/user-guide/result-cache) based on changes in analysed files.

But sometimes the project setup or custom extensions are so complex, the result cache invalidation mechanism cannot invalidate the cache properly and it becomes stale.

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.1.2</div>

You can implement [ResultCacheMetaExtension interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Analyser.ResultCache.ResultCacheMetaExtension.html) which returns a hash:

```php
interface ResultCacheMetaExtension
{
	/**
	 * Returns unique key for this result cache meta entry. This describes the source of the metadata.
	 */
	public function getKey(): string;
	/**
	 * Returns hash of the result cache meta entry. This represents the current state of the additional meta source.
	 */
	public function getHash(): string;
}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: MyApp\PHPStan\ResultCacheMetaExtension
		tags:
			- phpstan.resultCacheMetaExtension
```


If the returned hash changes between runs, the result cache is completely invalidated and the project is analysed fully from scratch.

That's a big hammer. If your rules and extensions know what exactly they read, they can track it instead, and only the files that depend on what changed are analysed again. Read on.

Tracking dependencies on files
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.0</div>

A [custom rule](/developing-extensions/rules), a [collector](/developing-extensions/collectors), a [dynamic return type extension](/developing-extensions/dynamic-return-type-extensions), a [dynamic throw type extension](/developing-extensions/dynamic-throw-type-extensions), an [expression type resolver extension](/developing-extensions/expression-type-resolver-extensions), a [parameter out type extension](/developing-extensions/parameter-out-type-extensions), a [closure extension](/developing-extensions/closure-extensions) or a [type-specifying extension](/developing-extensions/type-specifying-extensions) sometimes reads a file PHPStan doesn't know about - a configuration file, a template, a JSON schema. When the file changes, the result cache doesn't know the analysis of the file with the rule's node, or with the call, should run again.

Track the file by calling `$scope->trackFileDependency()`. For that, typehint the `$scope` parameter as `Scope&DependencyTracker` in the PHPDoc:

```php
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<FuncCall>
 */
class FeatureFlagRule implements Rule
{

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	/**
	 * @param Scope&DependencyTracker $scope
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		// ... check that $node is a feature() call and get $flagName ...

		$flagsFile = __DIR__ . '/../config/feature-flags.json';
		$scope->trackFileDependency($flagsFile);

		$flags = json_decode(file_get_contents($flagsFile), true);
		if (isset($flags[$flagName])) {
			return [];
		}

		return [
			RuleErrorBuilder::message(sprintf('Unknown feature flag %s.', $flagName))
				->identifier('featureFlag.unknown')
				->build(),
		];
	}

}
```

The analysed file is analysed again whenever the tracked file is created, changed in any way, or deleted. Track the dependency whether the rule reports an error or not - a change of the file can make the error appear as well as disappear. The file doesn't have to exist.

The intersection type belongs in the PHPDoc only. Keep the native parameter type `Scope`, as the interfaces declare it once PHPStan is downgraded for older PHP versions.

Tracking dependencies on values
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.0</div>

What the analysis depends on doesn't have to be a whole file. A rule asking about one service in a dependency injection container depends on the definition of that service, not on the rest of the container.

Implement the [ResultCacheValueExtension interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Analyser.ResultCache.ResultCacheValueExtension.html) that returns the current value for a key:

```php
use PHPStan\Analyser\ResultCache\ResultCacheValueExtension;

class ServiceValueExtension implements ResultCacheValueExtension
{

	public function __construct(private ServiceMap $serviceMap)
	{
	}

	public function getValue(string $key): string
	{
		$service = $this->serviceMap->getService($key);
		if ($service === null) {
			return 'missing';
		}

		return $service->getClass() . ($service->isPublic() ? ' public' : ' private');
	}

	public function keyToResultCache(string $key): string
	{
		return $key;
	}

	public function keyFromResultCache(string $storedKey): string
	{
		return $storedKey;
	}

}
```

And register it in your [configuration file](/config-reference):

```yaml
services:
	-
		class: MyApp\PHPStan\ServiceValueExtension
		tags:
			- phpstan.resultCacheValueExtension
```

A rule or an extension then tracks what it asks about with `$scope->trackValueDependency()`, with the same `Scope&DependencyTracker` typehint as above:

```php
$scope->trackValueDependency(ServiceValueExtension::class, $serviceId);
$service = $this->serviceMap->getService($serviceId);
```

The value is saved in the result cache. On the next run, PHPStan asks the extension for the value again, and the files that tracked it are analysed again if it's different. The same value tracked by more rules and extensions in the same file is saved once.

The value can be a hash if what it describes is big. Return a value for a key that doesn't exist too, so that the result cache notices when it starts to exist.

The keys are saved in the result cache as `keyToResultCache()` returns them, and read back through `keyFromResultCache()`. That's useful for keys that are absolute file paths. You can make them relative so that the result cache survives moving the project to a different directory, for example between CI runs.

`$scope->trackFileDependency()` from the previous section is a shortcut for a value dependency on the hash of the file.

Tracking dependencies on directories
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.0</div>

Sometimes the analysis depends on the files in a directory and you don't know in advance which ones exist. A rule checking that a view exists looks for a template file:

```php
/**
 * @param Scope&DependencyTracker $scope
 */
public function processNode(Node $node, Scope $scope): array
{
	// ... check that $node is a view() call and get $viewName ...

	$viewsDirectory = __DIR__ . '/../resources/views';
	$scope->trackDirectoryDependency($viewsDirectory, '*.blade.php');

	if (is_file($viewsDirectory . '/' . $viewName . '.blade.php')) {
		return [];
	}

	return [
		RuleErrorBuilder::message(sprintf('View %s does not exist.', $viewName))
			->identifier('view.notFound')
			->build(),
	];
}
```

The analysed file is analysed again whenever a file matching the pattern is created, changed in any way, deleted or renamed anywhere in the directory or its subdirectories, or when the directory itself is created or deleted. The pattern is matched against the file name with [`fnmatch()`](https://www.php.net/manual/en/function.fnmatch.php) syntax, like `*.php` or `Pest.php`. Leave it out to match every file.

Tracking dependencies on classes
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.0</div>

PHPStan knows about the classes the analysed code refers to. When one of them changes, the files that use it are analysed again. But a rule or an extension can also look up a class by a name from somewhere else - a string, a PHPDoc tag PHPStan doesn't resolve, a configuration file:

```php
/**
 * @param Scope&DependencyTracker $scope
 */
public function processNode(Node $node, Scope $scope): array
{
	// ... get $coveredClass from a @covers tag ...

	$scope->trackClassDependency($coveredClass);

	if ($this->reflectionProvider->hasClass($coveredClass)) {
		return [];
	}

	return [
		RuleErrorBuilder::message(sprintf('Class %s in @covers does not exist.', $coveredClass))
			->identifier('covers.classNotFound')
			->build(),
	];
}
```

The analysed file is then analysed again when the class or one of its parents, interfaces or traits changes what it declares, and when the class is created, deleted or moved to another file. Here, "what it declares" means signatures and PHPDocs, not method bodies. The class doesn't have to exist.

Extensions describing a class
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.0</div>

[Class reflection extensions](/developing-extensions/class-reflection-extensions) don't analyse code and don't get a `Scope`. They describe a class, and PHPStan remembers what they said about it for every file analysed after that. So it's the class that depends on what the extension reads, not the file being analysed.

Inject [`DeclarationDependencyTracker`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Analyser.DeclarationDependencyTracker.html) into the extension's constructor. It has the same methods as `DependencyTracker`, but each of them takes the class reflection as the first argument:

```php
use PHPStan\Analyser\DeclarationDependencyTracker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;

class ModelColumnMethodsExtension implements MethodsClassReflectionExtension
{

	public function __construct(
		private DeclarationDependencyTracker $dependencyTracker,
		private ModelSchema $modelSchema,
	)
	{
	}

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		if (!$classReflection->is(Model::class)) {
			return false;
		}

		$schemaFile = $this->modelSchema->getSchemaFile($classReflection->getName());
		$this->dependencyTracker->trackFileDependency($classReflection, $schemaFile);

		return $this->modelSchema->hasColumnForMethod($schemaFile, $methodName);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		// ...
	}

}
```

When the schema file changes, every file that depends on the class is analysed again: files that refer to it, call its methods or read its properties, and also files that depend on a class extending it. Track the dependency also when the extension says the class doesn't have the method or the property, because a change can make it appear.

Which file is analysed again
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.0</div>

Usually it's the file being analysed when the rule or the extension tracked the dependency. When an extension tracks a dependency for a call of a function or a method - a dynamic return type extension, a dynamic throw type extension, a parameter out type extension, a closure extension or a type-specifying extension - it's the file with the call, not the file where the function or the method is declared.

With `DeclarationDependencyTracker`, it's every file depending on the class, as [described above](#extensions-describing-a-class).

There's one exception. PHPStan sometimes infers what a file declares, like the type of a private property without a native type from the assignments in the constructor (with [`inferPrivatePropertyTypeFromConstructor`](/config-reference#inferprivatepropertytypefromconstructor)), and remembers it for the files analysed later. When a dependency is tracked during that inference, the file with the constructor and all files depending on it are analysed again.
