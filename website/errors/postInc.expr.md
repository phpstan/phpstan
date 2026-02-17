---
title: "postInc.expr"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function increment(): void
{
	getCounter()++;
}
```

## Why is it reported?

The `++` operator can only be used on variables, array offsets, and property accesses. Using it on the result of a function call or any other non-variable expression is not valid because the increment operator needs a storage location to write the new value back to.

## How to fix it

Assign the value to a variable first, then increment it:

```diff-php
 <?php declare(strict_types = 1);

 function increment(): void
 {
-	getCounter()++;
+	$counter = getCounter();
+	$counter++;
 }
```
