---
title: "argument.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 * @param T[] $items
 * @return T|null
 */
function firstOrNull(array $items): mixed
{
	return count($items) > 0 ? $items[0] : null;
}

function doFoo(mixed $list): void
{
	$first = firstOrNull($list);
}
```

## Why is it reported?

PHPStan reports this error when calling a [generic](/blog/generics-in-php-using-phpdocs) function or method and it cannot resolve the template type from the provided arguments. Generic functions declare template types via `@template` PHPDoc tags, and the return type depends on what gets passed as arguments.

In the example above, `firstOrNull` expects `T[]` so that it can resolve `T`. When `mixed` is passed instead of a specific array type, PHPStan cannot determine what `T` is. To prevent `mixed` from being propagated further, PHPStan reports this error and asks for a more specific argument.

Learn more: [Solving PHPStan error "Unable to resolve the template type"](/blog/solving-phpstan-error-unable-to-resolve-template-type)

## How to fix it

Pass a more specific type so that PHPStan can resolve the template type from the argument:

```diff-php
-function doFoo(mixed $list): void
+/** @param int[] $list */
+function doFoo(array $list): void
 {
 	$first = firstOrNull($list);
 }
```
