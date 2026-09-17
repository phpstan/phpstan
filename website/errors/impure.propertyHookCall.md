---
title: "impure.propertyHookCall"
shortDescription: "Pure function reads or writes a property whose hook has side effects."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Config
{
	private int $backing = 1;

	public int $counter {
		/** @phpstan-impure */
		get {
			echo 'accessed';
			return $this->backing;
		}
	}
}

final class Service
{
	/** @phpstan-pure */
	public function read(Config $config): int
	{
		return $config->counter; // ERROR: Impure call to get hook of property Config::$counter in pure method Service::read().
	}
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not cause any side effects. Property hooks (PHP 8.4+) run code when a property is read or written, so accessing a property that has an impure hook effectively calls impure code.

In the example, reading `$config->counter` invokes the `get` hook, which is marked `@phpstan-impure` and performs I/O. Because the hook has side effects, reading the property is not a pure operation, and doing so from a pure method violates the purity contract.

## How to fix it

Remove the property access with the impure hook from the pure method:

```diff-php
 /** @phpstan-pure */
 public function read(Config $config): int
 {
-	return $config->counter;
+	return 0;
 }
```

Or, if the side effect is intentional, remove the `@phpstan-pure` annotation:

```diff-php
-/** @phpstan-pure */
 public function read(Config $config): int
 {
 	return $config->counter;
 }
```

If the hook does not actually have side effects, remove its `@phpstan-impure` annotation so PHPStan can treat the property access as pure:

```diff-php
 public int $counter {
-	/** @phpstan-impure */
 	get => $this->backing;
 }
```
