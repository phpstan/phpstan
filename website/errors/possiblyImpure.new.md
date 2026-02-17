---
title: "possiblyImpure.new"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Connection
{
	public function __construct(private string $dsn)
	{
	}
}

class Factory
{
	/**
	 * @phpstan-pure
	 */
	public function create(): Connection
	{
		return new Connection('sqlite::memory:'); // ERROR: Possibly impure instantiation of class Connection in pure method Factory::create().
	}
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` instantiates a class whose constructor's purity is unknown. The constructor might have side effects, which would make the calling function impure. PHPStan cannot guarantee that the instantiation is pure, so it reports it as possibly impure.

A pure function must not have any side effects and must always return the same result for the same inputs.

## How to fix it

Mark the constructor as `@phpstan-pure` so PHPStan knows the instantiation is safe:

```diff-php
 <?php declare(strict_types = 1);

 class Connection
 {
+	/** @phpstan-pure */
 	public function __construct(private string $dsn)
 	{
 	}
 }
```

Or remove the `@phpstan-pure` annotation from the calling method if it does not need to be pure:

```diff-php
 <?php declare(strict_types = 1);

 class Factory
 {
-	/**
-	 * @phpstan-pure
-	 */
 	public function create(): Connection
 	{
 		return new Connection('sqlite::memory:');
 	}
 }
```
