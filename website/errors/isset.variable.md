---
title: "isset.variable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	if (isset($undefined)) { // ERROR: Variable $undefined in isset() is never defined.
		echo $undefined;
	}
}
```

## Why is it reported?

The variable checked inside `isset()` is never defined in the current scope. The `isset()` language construct checks whether a variable exists and is not `null`. When used on a variable that is never defined, the result is always `false`. This usually indicates a typo in the variable name or a missing assignment.

## How to fix it

Define the variable before checking it, or fix the variable name if it is a typo:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	if (isset($undefined)) {
-		echo $undefined;
+	$value = getValue();
+	if (isset($value)) {
+		echo $value;
 	}
 }
```

If the variable comes from a dynamic source (e.g., extracted variables), make the data flow explicit:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): void
+function doFoo(?string $name = null): void
 {
-	if (isset($undefined)) {
-		echo $undefined;
+	if (isset($name)) {
+		echo $name;
 	}
 }
```
