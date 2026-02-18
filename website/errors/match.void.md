---
title: "match.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
	}

	public function doBar(int $i): void
	{
		$a = match ($i) {
			1 => $this->doFoo(),
			2 => $this->doFoo(),
			default => $this->doFoo(),
		};
	}
}
```

## Why is it reported?

The result of a `match` expression is being used (assigned to a variable, passed as an argument, etc.), but all arms of the `match` return `void`. A `void` return type means the method does not return a meaningful value, so the result of this `match` expression is always `void` and cannot be meaningfully used.

This typically indicates a logic error -- either the match arms should call methods that return values, or the match result should not be used.

## How to fix it

If the `match` is only used for side effects, use it as a standalone statement without assigning the result:

```diff-php
 public function doBar(int $i): void
 {
-	$a = match ($i) {
+	match ($i) {
 		1 => $this->doFoo(),
 		2 => $this->doFoo(),
 		default => $this->doFoo(),
 	};
 }
```

Alternatively, if a value is needed, call methods that return meaningful values:

```diff-php
-	public function doFoo(): void
+	public function doFoo(): string
 	{
+		return 'result';
 	}
```
