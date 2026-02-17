---
title: "return.empty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): string
{
	return;
}
```

## Why is it reported?

A bare `return;` statement (without a value) is used in a function that declares a non-void return type. In the example above, the function is declared to return `string`, but the `return` statement does not provide a value.

## How to fix it

Return a value that matches the declared return type:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): string
 {
-	return;
+	return '';
 }
```

Or change the return type to `void` if the function is not meant to return a value.
