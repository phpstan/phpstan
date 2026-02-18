---
title: "foreach.valueOverwrite"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function process(array $items, string $value): void
{
	foreach ($items as $value) {
		echo $value;
	}
}
```

## Why is it reported?

This error is reported by [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

The value variable of a `foreach` loop overwrites a variable that already exists in the current scope. The previous value of the variable is lost when the loop starts iterating, which is usually unintentional and can lead to bugs.

In the example above, the parameter `$value` is overwritten by the `foreach` loop value variable, discarding the value that was passed to the function.

## How to fix it

Use a different variable name for the loop value:

```diff-php
 function process(array $items, string $value): void
 {
-	foreach ($items as $value) {
-		echo $value;
+	foreach ($items as $item) {
+		echo $item;
 	}
 }
```

Or remove the conflicting variable from the outer scope if it is not needed:

```diff-php
-function process(array $items, string $value): void
+function process(array $items): void
 {
 	foreach ($items as $value) {
 		echo $value;
 	}
 }
```
