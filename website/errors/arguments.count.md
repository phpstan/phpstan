---
title: "arguments.count"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function add(int $a, int $b): int
{
	return $a + $b;
}

add(1);
```

## Why is it reported?

The number of arguments passed to a function or method does not match its signature. Either too few required arguments were provided, or too many arguments were passed to a function that does not accept them. PHP will throw a fatal error at runtime in either case.

In the example above, `add()` requires 2 parameters but only 1 is provided.

## How to fix it

Pass the correct number of arguments:

```diff-php
-add(1);
+add(1, 2);
```

If the parameter should be optional, give it a default value:

```diff-php
-function add(int $a, int $b): int
+function add(int $a, int $b = 0): int
 {
 	return $a + $b;
 }
```
