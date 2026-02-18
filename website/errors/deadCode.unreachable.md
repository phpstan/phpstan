---
title: "deadCode.unreachable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): int
{
	return 1;
	echo 'unreachable';
}
```

## Why is it reported?

The statement after `return` can never be executed. The `return` statement unconditionally transfers control out of the function, making any code following it in the same block dead code. This usually indicates a logic error or leftover code from refactoring.

The same applies to other control flow statements that always terminate, such as `throw`, `exit`, `continue`, or `break`.

## How to fix it

Remove the unreachable code:

```diff-php
 function doFoo(): int
 {
 	return 1;
-	echo 'unreachable';
 }
```

If the code should execute, restructure the logic so it runs before the return:

```diff-php
 function doFoo(): int
 {
+	echo 'this should run';
 	return 1;
-	echo 'unreachable';
 }
```
