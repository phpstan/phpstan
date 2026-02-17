---
title: "classConstant.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function foo(int $value): void
{
	echo $value::FOO;
}
```

## Why is it reported?

The code attempts to access a class constant using the `::` operator on a value that is not an object or a class string. Class constants can only be accessed on objects, class names, or class-string types.

In the example above, `$value` is an `int`, which does not support class constant access.

## How to fix it

Ensure the value used for class constant access is an object or a class-string:

```diff-php
 <?php declare(strict_types = 1);

-function foo(int $value): void
+function foo(Foo $value): void
 {
 	echo $value::FOO;
 }
```
