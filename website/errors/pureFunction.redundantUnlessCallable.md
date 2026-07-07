---
title: "pureFunction.redundantUnlessCallable"
shortDescription: "Function is marked @pure-unless-callable-is-impure for a parameter that is already a pure callable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param pure-callable(int): int $f
 * @param array<int> $arr
 * @return array<int>
 * @pure-unless-callable-is-impure $f
 */
function myMap(callable $f, array $arr): array
{
	$result = [];
	foreach ($arr as $i => $v) {
		$result[$i] = $f($v);
	}

	return $result;
}
```

## Why is it reported?

The `@pure-unless-callable-is-impure` tag marks a function as pure *except* when the named callable parameter is impure — its purity depends on the callable passed in at each call site.

Here the parameter `$f` is typed as `pure-callable`, which guarantees it is always pure. Because the only condition that could make the function impure can never happen, the tag has no effect: the function is unconditionally pure. Marking it `@pure-unless-callable-is-impure` is misleading and hides the fact that the function can simply be declared `@phpstan-pure`.

## How to fix it

Replace `@pure-unless-callable-is-impure` with `@phpstan-pure`:

```diff-php
 /**
  * @param pure-callable(int): int $f
  * @param array<int> $arr
  * @return array<int>
- * @pure-unless-callable-is-impure $f
+ * @phpstan-pure
  */
 function myMap(callable $f, array $arr): array
 {
 	$result = [];
 	foreach ($arr as $i => $v) {
 		$result[$i] = $f($v);
 	}

 	return $result;
 }
```

If the function is actually meant to accept impure callables, widen the parameter type from `pure-callable` to `callable` so that the `@pure-unless-callable-is-impure` tag becomes meaningful again:

```diff-php
 /**
- * @param pure-callable(int): int $f
+ * @param callable(int): int $f
  * @param array<int> $arr
  * @return array<int>
  * @pure-unless-callable-is-impure $f
  */
 function myMap(callable $f, array $arr): array
```
