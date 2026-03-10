---
title: "isset.expr"
shortDescription: "Expression in isset() is never null, making the check redundant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function getValue(): int
	{
		return 5;
	}
}

function doFoo(Foo $foo): void
{
	if (isset($foo->getValue())) {
		echo $foo->getValue();
	}
}
```

## Why is it reported?

The expression inside `isset()` is never `null` based on the types PHPStan has inferred, so the `isset()` check is unnecessary -- it will always evaluate to `true`. In the example above, `getValue()` returns `int`, which cannot be `null`, so `isset($foo->getValue())` serves no purpose.

## How to fix it

Remove the unnecessary `isset()` check if the expression is always non-null:

```diff-php
 function doFoo(Foo $foo): void
 {
-	if (isset($foo->getValue())) {
-		echo $foo->getValue();
-	}
+	echo $foo->getValue();
 }
```

If the return value should be nullable, update the return type:

```diff-php
-	public function getValue(): int
+	public function getValue(): ?int
 	{
 		return 5;
 	}
```
