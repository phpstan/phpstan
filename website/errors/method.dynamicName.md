---
title: "method.dynamicName"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(object $obj): void
{
	$method = 'doSomething';
	$obj->$method(); // ERROR: Variable method call on object.
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

A method is being called using a variable or expression as the method name instead of a static identifier. Variable method calls make the code harder to analyse statically, obscure which method is actually being called, and bypass IDE refactoring tools. PHPStan cannot verify that the method exists, has the correct signature, or is callable in this context.

## How to fix it

Replace the variable method call with a direct method call:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(object $obj): void
 {
-	$method = 'doSomething';
-	$obj->$method();
+	$obj->doSomething();
 }
```

If the method name must be dynamic, consider using a match expression or a map of known method names to callables:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(object $obj, string $action): void
 {
-	$obj->$action();
+	match ($action) {
+		'start' => $obj->start(),
+		'stop' => $obj->stop(),
+		default => throw new \InvalidArgumentException('Unknown action'),
+	};
 }
```
