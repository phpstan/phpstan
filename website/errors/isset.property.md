---
title: "isset.property"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $bar = 0;
}

function doFoo(Foo $foo): void
{
	if (isset($foo->bar)) {
		// ...
	}
}
```

## Why is it reported?

The `isset()` check on a property is unnecessary because the property always exists and is never `null`. The property `$bar` is declared with type `int` and has a default value, so it is always initialized and can never be `null`. Using `isset()` on it will always return `true`.

## How to fix it

Remove the unnecessary `isset()` check:

```diff-php
 function doFoo(Foo $foo): void
 {
-	if (isset($foo->bar)) {
-		// ...
-	}
+	// ...
 }
```

If the property can legitimately be nullable, declare it as such:

```diff-php
 class Foo
 {
-	public int $bar = 0;
+	public ?int $bar = 0;
 }
```
