---
title: "impure.propertyUnset"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public ?string $name = null;

	/**
	 * @phpstan-pure
	 */
	public function clearName(): void
	{
		unset($this->name); // ERROR: Impure property unset in pure method Foo::clearName().
	}
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Unsetting a property modifies the state of an object, which is a side effect. This makes the operation inherently impure.

## How to fix it

Remove the `@phpstan-pure` annotation if the method needs to unset properties:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public ?string $name = null;

-	/**
-	 * @phpstan-pure
-	 */
-	public function clearName(): void
+	public function clearName(): void
 	{
 		unset($this->name);
 	}
 }
```

Alternatively, refactor the pure method to return a new value without modifying the object:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public ?string $name = null;

 	/**
 	 * @phpstan-pure
 	 */
-	public function clearName(): void
+	public function withoutName(): self
 	{
-		unset($this->name);
+		$clone = clone $this;
+		$clone->name = null;
+		return $clone;
 	}
 }
```
