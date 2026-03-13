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
