---
title: "callable.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var callable(): void $callback */
$callback = static function (): void {};

$result = $callback();
```

## Why is it reported?

The result of calling a callable that returns `void` is being used. A `void` return type indicates that the function or callable does not return a meaningful value. Using the result of such a call is a logic error because the value is always `null` and the function author has explicitly declared no value should be expected.

In the example above, `$callback` is typed as `callable(): void`, so assigning `$callback()` to `$result` is using a void return value.

## How to fix it

Do not use the return value of a void callable. Call it as a standalone statement instead:

```diff-php
 <?php declare(strict_types = 1);

 /** @var callable(): void $callback */
 $callback = static function (): void {};

-$result = $callback();
+$callback();
```

If you need the callable to return a value, change its type signature to reflect the actual return type.
