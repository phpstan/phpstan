---
title: "possiblyImpure.propertyUnset"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public ?string $name = null;

	/** @phpstan-pure */
	public function reset(): void
	{
		unset($this->name);
	}
}
```

## Why is it reported?

The function or method is marked as `@phpstan-pure`, meaning it must not cause side effects. Unsetting a property modifies object state, which is a side effect. Since the object might be referenced elsewhere, this mutation is possibly impure.

## How to fix it

Remove the `@phpstan-pure` annotation if the method intentionally modifies state:

```diff-php
-/** @phpstan-pure */
 public function reset(): void
 {
 	unset($this->name);
 }
```

Or restructure the code to avoid mutating properties in a pure context:

```diff-php
 /** @phpstan-pure */
-public function reset(): void
-{
-	unset($this->name);
-}
+public function withoutName(): static
+{
+	$clone = clone $this;
+	$clone->name = null;
+	return $clone;
+}
```
