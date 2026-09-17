---
title: "impurePropertyHook.pure"
shortDescription: "Property hook marked as @phpstan-impure has no actual side effects."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Foo
{
	private int $backing = 1;

	public int $value {
		/** @phpstan-impure */
		get => $this->backing; // ERROR: Get hook for property Foo::$value is marked as impure but does not have any side effects.
	}
}
```

## Why is it reported?

A property hook (PHP 8.4+) is marked as `@phpstan-impure`, but PHPStan's analysis found no actual side effects in its body. The hook does not perform I/O, modify external state, or call other impure code. Marking a side-effect-free hook as impure is misleading — callers cannot benefit from purity optimizations, and the annotation does not match the actual behavior.

This is only reported when the hook cannot be overridden — either the declaring class is `final`, or the hook itself is declared `final`. For hooks that a subclass could override with a side-effecting implementation, PHPStan does not report this.

## How to fix it

Remove the `@phpstan-impure` annotation since the hook has no side effects:

```diff-php
 public int $value {
-	/** @phpstan-impure */
 	get => $this->backing;
 }
```

If the hook is supposed to have side effects, add them (and keep the annotation). If it is genuinely pure, you may mark it accordingly:

```diff-php
 public int $value {
-	/** @phpstan-impure */
+	/** @phpstan-pure */
 	get => $this->backing;
 }
```
