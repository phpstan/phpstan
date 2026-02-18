---
title: "nullsafe.neverNull"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $name = '';

	public function getName(): string
	{
		return $this->name;
	}
}

function doFoo(Foo $foo): void
{
	$foo?->getName();
	$foo?->name;
}
```

## Why is it reported?

The nullsafe operator (`?->`) is used on a value that can never be `null`. The `?->` operator is meant to short-circuit to `null` when the left side is `null`, but since the type is non-nullable, the null check is unnecessary. This suggests either the type is wrong, or `->` should be used instead.

## How to fix it

Replace the nullsafe operator with the regular object operator:

```diff-php
 function doFoo(Foo $foo): void
 {
-	$foo?->getName();
-	$foo?->name;
+	$foo->getName();
+	$foo->name;
 }
```

If the value can indeed be `null`, fix the type declaration:

```diff-php
-function doFoo(Foo $foo): void
+function doFoo(?Foo $foo): void
 {
 	$foo?->getName();
 	$foo?->name;
 }
```
