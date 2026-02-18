---
title: "ternary.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $a, string $b, string $s): void
{
	$a ? $b : $s;
}
```

## Why is it reported?

A ternary expression is used as a standalone statement, but its result is not assigned to a variable, returned, or passed as an argument. The expression evaluates to a value that is immediately discarded, which means it has no effect on the program.

This usually indicates one of the following:
- The result was meant to be assigned to a variable or returned.
- The intent was to use an `if`/`else` statement for side effects instead.

## How to fix it

Assign the result to a variable or return it:

```diff-php
-$a ? $b : $s;
+$result = $a ? $b : $s;
```

Or use an `if`/`else` statement if the branches have side effects:

```diff-php
-$a ? doSomething() : doSomethingElse();
+if ($a) {
+	doSomething();
+} else {
+	doSomethingElse();
+}
```
