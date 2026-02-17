---
title: "parameterByRef.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(string &$p): void
	{
		$p = 1;
	}
}
```

## Why is it reported?

A value assigned to a by-reference parameter does not match the parameter's declared type. In the example above, the parameter `$p` is declared as `string`, but an `int` value is assigned to it inside the method.

Since the parameter is passed by reference, the assigned value will be visible to the caller. Assigning a value of the wrong type can lead to type errors in the calling code that expects the variable to remain a `string`.

## How to fix it

Assign a value that matches the parameter's declared type:

```diff-php
 class Foo
 {
 	public function doFoo(string &$p): void
 	{
-		$p = 1;
+		$p = 'value';
 	}
 }
```

Or, if the parameter intentionally receives a different type, use a `@param-out` PHPDoc tag to declare the output type:

```diff-php
 class Foo
 {
+	/** @param-out int $p */
 	public function doFoo(string &$p): void
 	{
 		$p = 1;
 	}
 }
```
