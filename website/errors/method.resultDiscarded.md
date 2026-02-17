---
title: "method.resultDiscarded"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Collection
{
	/** @var list<int> */
	private array $items;

	#[\NoDiscard]
	public function filter(callable $callback): self
	{
		$new = clone $this;
		$new->items = array_filter($this->items, $callback);
		return $new;
	}
}

$collection = new Collection();
$collection->filter(fn (int $v): bool => $v > 0);
```

## Why is it reported?

The method is called on a separate line and its return value is discarded. The method is marked with `#[\NoDiscard]`, indicating that its return value must be used. This typically applies to immutable or pure methods where calling the method without capturing the return value means the call has no meaningful effect. The original object remains unchanged.

## How to fix it

Capture the return value of the method call:

```diff-php
 <?php declare(strict_types = 1);

 $collection = new Collection();
-$collection->filter(fn (int $v): bool => $v > 0);
+$filtered = $collection->filter(fn (int $v): bool => $v > 0);
```

If the return value is intentionally not needed, use a `(void)` cast to signal the intent:

```diff-php
 <?php declare(strict_types = 1);

 $collection = new Collection();
-$collection->filter(fn (int $v): bool => $v > 0);
+(void) $collection->filter(fn (int $v): bool => $v > 0);
```
