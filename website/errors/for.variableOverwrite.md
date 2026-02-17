---
title: "for.variableOverwrite"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function process(int $i): void
{
	for ($i = 0; $i < 10; $i++) {
		echo $i;
	}
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

The initial assignment in a `for` loop overwrites a variable that already exists in the current scope. This means the previous value of the variable is lost, which is usually unintentional and can lead to bugs.

In the example above, the parameter `$i` is overwritten by the `for` loop initialization `$i = 0`, discarding the value that was passed to the function.

## How to fix it

Use a different variable name for the loop counter:

```diff-php
 <?php declare(strict_types = 1);

 function process(int $i): void
 {
-	for ($i = 0; $i < 10; $i++) {
-		echo $i;
+	for ($j = 0; $j < 10; $j++) {
+		echo $j;
 	}
 }
```

Or remove the conflicting variable from the outer scope if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

-function process(int $i): void
+function process(): void
 {
 	for ($i = 0; $i < 10; $i++) {
 		echo $i;
 	}
 }
```
