---
title: "assign.invalidExpr"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(\stdClass $s): void
	{
		$s->foo() = 'test';
	}
}
```

## Why is it reported?

The expression on the left side of the assignment is not something that can be assigned to. In PHP, only variables, properties, array offsets, and static properties can appear on the left side of an assignment. Expressions like method calls, function calls, or other non-lvalue expressions cannot be assigned to and will cause a compile-time error.

In the example above, `$s->foo()` is a method call, not a property access or variable, so it cannot be used as the target of an assignment.

## How to fix it

Assign to a valid target such as a variable or a property:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(\stdClass $s): void
 	{
-		$s->foo() = 'test';
+		$s->foo = 'test';
 	}
 }
```
