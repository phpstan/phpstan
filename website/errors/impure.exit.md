---
title: "impure.exit"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @phpstan-pure */
	public function doFoo(): string
	{
		exit('fatal error');
	}
}
```

## Why is it reported?

The `exit` language construct is used inside a function or method marked as `@phpstan-pure`. Pure functions must not have side effects -- they should only compute and return a value based on their inputs. Calling `exit` terminates the entire PHP process, which is a significant side effect.

## How to fix it

Remove `exit` from the pure function by throwing an exception instead, or remove the `@phpstan-pure` annotation if the function genuinely needs to terminate the process:

```diff-php
 class Foo
 {
 	/** @phpstan-pure */
 	public function doFoo(): string
 	{
-		exit('fatal error');
+		throw new \RuntimeException('fatal error');
 	}
 }
```

Or remove the purity annotation:

```diff-php
 class Foo
 {
-	/** @phpstan-pure */
 	public function doFoo(): string
 	{
 		exit('fatal error');
 	}
 }
```
