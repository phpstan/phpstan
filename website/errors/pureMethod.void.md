---
title: "pureMethod.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @phpstan-pure */
	public function doNothing(): void
	{
	}
}
```

## Why is it reported?

A method marked as `@phpstan-pure` must not have side effects and must return a meaningful value. A pure method that returns `void` serves no purpose -- since it has no side effects and produces no return value, calling it has no observable effect. This is almost certainly a mistake: either the method should not be marked as pure, or it should return a value.

The exception is constructors, which are allowed to be pure and return void because their purpose is to initialize an object.

## How to fix it

If the method performs side effects, remove the `@phpstan-pure` annotation:

```diff-php
 class Foo
 {
-	/** @phpstan-pure */
 	public function doSomething(): void
 	{
 		file_put_contents('/tmp/log.txt', 'done');
 	}
 }
```

If the method truly is pure, it should return a value:

```diff-php
 class Foo
 {
 	/** @phpstan-pure */
-	public function compute(): void
+	public function compute(): int
 	{
+		return 42;
 	}
 }
```
