---
title: "argument.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function greet(string $name): void
{
}

greet(123);
```

## Why is it reported?

The type of an argument passed to a function or method does not match the expected parameter type. PHP would either throw a `TypeError` at runtime or perform an implicit type coercion, depending on strict types mode.

In the example above, an `int` is passed where a `string` is expected.

## How to fix it

Pass a value of the correct type:

```diff-php
 <?php declare(strict_types = 1);

 function greet(string $name): void
 {
 }

-greet(123);
+greet('Alice');
```

Or convert the value to the expected type before passing it:

```diff-php
-greet(123);
+greet((string) 123);
```

If the function should accept multiple types, update its parameter type declaration:

```diff-php
-function greet(string $name): void
+function greet(string|int $name): void
 {
 }
```
