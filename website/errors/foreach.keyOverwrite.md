---
title: "foreach.keyOverwrite"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function process(array $items, string $key): void
{
	foreach ($items as $key => $value) {
		echo $key . ': ' . $value;
	}
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

The key variable of a `foreach` loop overwrites a variable that already exists in the current scope. The previous value of the variable is lost when the loop starts iterating, which is usually unintentional and can lead to bugs.

In the example above, the parameter `$key` is overwritten by the `foreach` loop key variable, discarding the value that was passed to the function.

## How to fix it

Use a different variable name for the loop key:

```diff-php
 <?php declare(strict_types = 1);

 function process(array $items, string $key): void
 {
-	foreach ($items as $key => $value) {
-		echo $key . ': ' . $value;
+	foreach ($items as $k => $value) {
+		echo $k . ': ' . $value;
 	}
 }
```

Or remove the conflicting variable from the outer scope if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

-function process(array $items, string $key): void
+function process(array $items): void
 {
 	foreach ($items as $key => $value) {
 		echo $key . ': ' . $value;
 	}
 }
```
