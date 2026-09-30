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

That's a big hammer. If your rules and extensions know what exactly they read, they can declare it instead, and only the files that depend on what changed are analysed again. Read on.

Declaring dependencies on files
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.0</div>

A [custom rule](/developing-extensions/rules) or a [dynamic return type extension](/developing-extensions/dynamic-return-type-extensions) sometimes reads a file PHPStan doesn't know about - a configuration file, a template, a JSON schema. When the file changes, the result cache doesn't know the analysis of the file with the rule's node, or with the call, should run again.

Declare the file by calling `$scope->fileDependency()`. For that, typehint the `$scope` parameter as `Scope&DependencyEmitter` in the PHPDoc:

```php
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\DependencyEmitter;
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
	 * @param Scope&DependencyEmitter $scope
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		// ... check that $node is a feature() call and get $flagName ...

		$flagsFile = __DIR__ . '/../config/feature-flags.json';
		$scope->fileDependency($flagsFile);

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

The analysed file is analysed again whenever the declared file is created, changed in any way, or deleted. Declare the dependency whether the rule reports an error or not - a change of the file can make the error appear as well as disappear. The file doesn't have to exist.

The intersection type belongs in the PHPDoc only. Keep the native parameter type `Scope`, as the interfaces declare it once PHPStan is downgraded for older PHP versions.

Declaring dependencies on values
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

A rule or an extension then declares what it asks about with `$scope->valueDependency()`, with the same `Scope&DependencyEmitter` typehint as above:

```php
$scope->valueDependency(ServiceValueExtension::class, $serviceId);
$service = $this->serviceMap->getService($serviceId);
```

The value is saved in the result cache. On the next run, PHPStan asks the extension for the value again, and the files that declared it are analysed again if it's different. The same value declared by more rules and extensions in the same file is saved once.

The value can be a hash if what it describes is big. Return a value for a key that doesn't exist too, so that the result cache notices when it starts to exist.

The keys are saved in the result cache as `keyToResultCache()` returns them, and read back through `keyFromResultCache()`. That's useful for keys that are absolute file paths. You can make them relative so that the result cache survives moving the project to a different directory, for example between CI runs.

`$scope->fileDependency()` from the previous section is a shortcut for a value dependency on the hash of the file.

Which file is analysed again
---------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 2.3.0</div>

Usually it's the file being analysed when the rule or the extension declared the dependency. When a dynamic return type extension declares a dependency for a call of a function or a method, it's the file with the call, not the file where the function or the method is declared.

There's one exception. PHPStan sometimes infers what a file declares, like the type of a private property without a native type from the assignments in the constructor (with [`inferPrivatePropertyTypeFromConstructor`](/config-reference#inferprivatepropertytypefromconstructor)), and remembers it for the files analysed later. When a dependency is declared during that inference, the file with the constructor and all files depending on it are analysed again.
