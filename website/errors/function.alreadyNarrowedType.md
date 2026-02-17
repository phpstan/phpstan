---
title: "function.alreadyNarrowedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function process(int $value): void
{
	if (is_int($value)) {
		echo $value;
	}
}
```

## Why is it reported?

A type-checking function call will always evaluate to `true` because the type of the argument has already been narrowed to match the check. This means the condition is redundant -- the type is already guaranteed by a previous check, a type declaration, or the control flow.

In the example above, `$value` is declared as `int`, so `is_int($value)` is always `true`.

## How to fix it

Remove the unnecessary type check when the type is already guaranteed by the parameter type:

```diff-php
 <?php declare(strict_types = 1);

 function process(int $value): void
 {
-	if (is_int($value)) {
-		echo $value;
-	}
+	echo $value;
 }
```

If the function accepts a union type, the check may become meaningful:

```diff-php
 <?php declare(strict_types = 1);

-function process(int $value): void
+function process(int|string $value): void
 {
 	if (is_int($value)) {
 		echo $value;
 	}
 }
```
