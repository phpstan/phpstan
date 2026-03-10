---
title: "isset.property"
shortDescription: "Property in isset() always exists and is never null."
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
	if (isset($foo->bar)) {
		echo $foo->bar;
	}
}
```

## Why is it reported?

The `isset()` check on a property is unnecessary because PHPStan can determine the property always exists and is never `null` at the point of the check. The property `$bar` is declared with type `int` and has been assigned a value, so it is initialized and can never be `null`. Using `isset()` on it always returns `true`.

## How to fix it

Remove the unnecessary `isset()` check:

```diff-php
 function test(): void
 {
 	$foo = new Foo();
 	$foo->bar = 5;
-	if (isset($foo->bar)) {
-		echo $foo->bar;
-	}
+	echo $foo->bar;
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
