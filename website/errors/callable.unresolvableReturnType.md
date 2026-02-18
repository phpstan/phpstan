---
title: "callable.unresolvableReturnType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 * @param T $value
 * @return T
 */
function identity(mixed $value): mixed
{
	return $value;
}

$fn = identity(...);
$result = $fn('hello');
```

## Why is it reported?

PHPStan detected that the return type of a callable, closure, or first-class callable contains an unresolvable template (generic) type. This typically happens when creating a first-class callable reference to a generic function, where the template type cannot be resolved from the calling context.

## How to fix it

Call the function directly instead of via a first-class callable so that PHPStan can resolve the template types from the arguments:

```diff-php
 <?php declare(strict_types = 1);

-$fn = identity(...);
-$result = $fn('hello');
+$result = identity('hello');
```

If you need a callable reference, add a PHPDoc type to the variable to help PHPStan understand the resolved type:

```diff-php
+/** @var \Closure(string): string $fn */
 $fn = identity(...);
 $result = $fn('hello');
```
