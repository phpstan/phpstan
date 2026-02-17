---
title: "function.inner"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function outer(): void
{
	function inner(): void
	{
		echo 'hello';
	}

	inner();
}
```

## Why is it reported?

PHP allows declaring named functions inside other functions, but PHPStan does not support analysing them. Inner named functions in PHP have unusual scoping behavior -- they are registered in the global scope when the outer function is called, not when the inner function is declared. This makes them difficult to analyse statically.

## How to fix it

Refactor the inner function to an anonymous function (closure):

```diff-php
 <?php declare(strict_types = 1);

 function outer(): void
 {
-	function inner(): void
-	{
-		echo 'hello';
-	}
+	$inner = function (): void {
+		echo 'hello';
+	};

-	inner();
+	$inner();
 }
```

Or move the function to the top level:

```diff-php
 <?php declare(strict_types = 1);

+function inner(): void
+{
+	echo 'hello';
+}
+
 function outer(): void
 {
-	function inner(): void
-	{
-		echo 'hello';
-	}
-
 	inner();
 }
```
