---
title: "impure.methodCall"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
	/** @phpstan-impure */
	public function log(string $message): void
	{
		echo $message;
	}
}

class Calculator
{
	private Logger $logger;

	/** @phpstan-pure */
	public function add(int $a, int $b): int
	{
		$this->logger->log('adding');
		return $a + $b;
	}
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not cause any side effects. Calling an impure method (one that performs I/O, modifies external state, or is marked `@phpstan-impure`) inside a pure function violates purity guarantees. Pure functions must only depend on their arguments and return a value without observable side effects.

## How to fix it

Remove the impure method call from the pure function:

```diff-php
 /** @phpstan-pure */
 public function add(int $a, int $b): int
 {
-	$this->logger->log('adding');
 	return $a + $b;
 }
```

Or, if the side effect is intentional, remove the `@phpstan-pure` annotation:

```diff-php
-/** @phpstan-pure */
 public function add(int $a, int $b): int
 {
 	$this->logger->log('adding');
 	return $a + $b;
 }
```
