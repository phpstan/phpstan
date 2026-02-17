---
title: "paramLaterInvokedCallable.nonCallable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class EventEmitter
{
	/**
	 * @param-later-invoked-callable $listener
	 */
	public function on(string $event, string $listener): void
	{
	}
}
```

## Why is it reported?

The `@param-later-invoked-callable` PHPDoc tag is applied to a parameter whose native type is not callable. This tag is used to tell PHPStan that the callable parameter will be invoked later (not during the current function call), which affects how PHPStan narrows types inside the callable.

In the example above, the parameter `$listener` has a native type of `string`, which is not a callable type. The `@param-later-invoked-callable` tag only makes sense on parameters that accept callable values such as `callable`, `Closure`, or a callable-typed parameter.

## How to fix it

Change the parameter type to a callable type:

```diff-php
 <?php declare(strict_types = 1);

 class EventEmitter
 {
 	/**
 	 * @param-later-invoked-callable $listener
 	 */
-	public function on(string $event, string $listener): void
+	public function on(string $event, callable $listener): void
 	{
 	}
 }
```

Or remove the `@param-later-invoked-callable` tag if the parameter is not intended to be callable:

```diff-php
 <?php declare(strict_types = 1);

 class EventEmitter
 {
-	/**
-	 * @param-later-invoked-callable $listener
-	 */
 	public function on(string $event, string $listener): void
 	{
 	}
 }
```
