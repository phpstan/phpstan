---
title: "nullCoalesce.property"
shortDescription: "Property on the left side of ?? is not nullable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $bar = 0;
}

function test(): void
{
	$foo = new Foo();
	$foo->bar = 5;
	echo $foo->bar ?? 0;
}
```

## Why is it reported?

The null coalescing operator (`??`) is designed to provide a fallback value when the left-hand side is `null`. When the property being checked is typed as non-nullable (e.g., `int`), it can never be `null`, so the fallback value on the right side of `??` will never be used. This indicates either dead code or a missing nullable type on the property.

## How to fix it

If the property is intentionally non-nullable, remove the unnecessary null coalescing operator:

```diff-php
 function test(): void
 {
 	$foo = new Foo();
 	$foo->bar = 5;
-	echo $foo->bar ?? 0;
+	echo $foo->bar;
 }
```

If the property should be nullable, update its type declaration:

```diff-php
 class Foo
 {
-	public int $bar = 0;
+	public ?int $bar = null;
 }
```
