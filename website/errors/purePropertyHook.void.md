---
title: "purePropertyHook.void"
shortDescription: "Pure property hook returns void, making it useless without side effects."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Foo
{
	private int $backing = 1;

	public int $value {
		/** @phpstan-pure */
		set {
			$this->backing = $value; // ERROR: Set hook for property Foo::$value is marked as pure but returns void.
		}
	}
}
```

## Why is it reported?

A property hook (PHP 8.4+) marked as `@phpstan-pure` must not have side effects and must produce a meaningful value. A `set` hook always returns `void`, so a pure `set` hook can neither return a value nor legitimately do anything: assigning to the backing value is itself a side effect. Marking a `set` hook as pure is therefore contradictory.

The same applies to any hook that returns `void`: without side effects and without a return value, invoking it has no observable effect, which is almost certainly a mistake.

## How to fix it

A `set` hook exists to mutate state, so it is inherently impure. Remove the `@phpstan-pure` annotation:

```diff-php
 public int $value {
-	/** @phpstan-pure */
 	set {
 		$this->backing = $value;
 	}
 }
```

If you meant to mark the read side of the property, put `@phpstan-pure` on the `get` hook, which returns a value:

```diff-php
 public int $value {
+	/** @phpstan-pure */
 	get => $this->backing;
 }
```
