---
title: "possiblyImpure.propertyHookCall"
shortDescription: "Pure function reads or writes a property whose hook's purity is unknown."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Config
{
	private int $backing = 1;

	public int $counter {
		get {
			return $this->backing;
		}
	}
}

final class Service
{
	/** @phpstan-pure */
	public function read(Config $config): int
	{
		return $config->counter;
	}
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` accesses a property whose hook (PHP 8.4+) has an unknown purity. PHPStan cannot prove the hook is free of side effects, so it cannot guarantee that reading or writing the property is a pure operation.

A pure function must not have any side effects and must always return the same result for the same inputs. Related identifiers: [`impure.propertyHookCall`](/error-identifiers/impure.propertyHookCall) is reported when the hook is known to be impure, [`possiblyImpure.methodCall`](/error-identifiers/possiblyImpure.methodCall) covers the same situation for regular method calls.

## How to fix it

Mark the hook as `@phpstan-pure` so PHPStan knows it is safe to access from a pure context:

```diff-php
 public int $counter {
+	/** @phpstan-pure */
 	get {
 		return $this->backing;
 	}
 }
```

Or remove the `@phpstan-pure` annotation from the accessing method if it does not need to be pure:

```diff-php
-/** @phpstan-pure */
 public function read(Config $config): int
 {
 	return $config->counter;
 }
```
