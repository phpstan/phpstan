---
title: "paramOut.tooWideBool"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param-out bool $result
 */
function validate(mixed $input, bool &$result): void
{
	$result = true;
}
```

## Why is it reported?

The `@param-out` type declares `bool` for a by-reference parameter, but PHPStan has determined that the parameter is only ever assigned `true` (or only ever assigned `false`). The declared output type is wider than necessary because one of the boolean values is never produced.

This indicates that the `@param-out` annotation is more permissive than what the function actually assigns.

## How to fix it

Narrow the `@param-out` type to match what the function actually produces:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @param-out bool $result
+ * @param-out true $result
  */
 function validate(mixed $input, bool &$result): void
 {
 	$result = true;
 }
```

Or if the function should also produce `false` in some cases, add that code path:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @param-out bool $result
  */
 function validate(mixed $input, bool &$result): void
 {
-	$result = true;
+	$result = $input !== null;
 }
```
