---
title: "purePropertyHook.redundantUnlessCallable"
shortDescription: "Property hook is marked @pure-unless-callable-is-impure for a parameter that is already a pure callable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

final class Registry
{
	/** @var pure-callable(): int */
	private $stored;

	public mixed $handler {
		/**
		 * @param pure-callable(): int $value
		 * @pure-unless-callable-is-impure $value
		 */
		set {
			$this->stored = $value;
		}
	}
}
```

## Why is it reported?

The `@pure-unless-callable-is-impure` tag marks a property hook (PHP 8.4+) as pure *except* when the named callable parameter is impure — its purity depends on the callable passed in at each call site.

Here the parameter `$value` is typed as `pure-callable`, which guarantees it is always pure. Because the only condition that could make the hook impure can never happen, the tag has no effect: the hook is unconditionally pure. Marking it `@pure-unless-callable-is-impure` is misleading and hides the fact that the hook can simply be declared `@phpstan-pure`. This mirrors [`pureMethod.redundantUnlessCallable`](/error-identifiers/pureMethod.redundantUnlessCallable) for regular methods.

## How to fix it

Replace `@pure-unless-callable-is-impure` with `@phpstan-pure`:

```diff-php
 	/**
 	 * @param pure-callable(): int $value
-	 * @pure-unless-callable-is-impure $value
+	 * @phpstan-pure
 	 */
 	set {
```

If the hook is actually meant to accept impure callables, widen the parameter type from `pure-callable` to `callable` so that the `@pure-unless-callable-is-impure` tag becomes meaningful again:

```diff-php
 	/**
-	 * @param pure-callable(): int $value
+	 * @param callable(): int $value
 	 * @pure-unless-callable-is-impure $value
 	 */
 	set {
```
