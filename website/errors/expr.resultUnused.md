---
title: "expr.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(stdClass $obj): void
{
	$obj->name;
}
```

## Why is it reported?

An expression is written on a separate line but its result is never used. The expression is evaluated and immediately discarded, which means the statement has no effect. This typically indicates a bug -- a missing assignment, a forgotten function call, or dead code.

## How to fix it

If the expression was meant to be assigned, add the assignment:

```diff-php
 function doFoo(stdClass $obj): void
 {
-	$obj->name;
+	$name = $obj->name;
 }
```

If a method call was intended, add the missing parentheses or call:

```diff-php
 function doFoo(stdClass $obj): void
 {
-	$obj->name;
+	$obj->getName();
 }
```

If the expression is truly unnecessary, remove it:

```diff-php
 function doFoo(stdClass $obj): void
 {
-	$obj->name;
 }
```
