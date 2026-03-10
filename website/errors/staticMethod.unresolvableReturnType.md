---
title: "staticMethod.unresolvableReturnType"
shortDescription: "Return type of a static method call contains an unresolvable type after template substitution."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Loggable
{
	public function log(): void;
}

class Factory
{
	/**
	 * @template T of object
	 * @param T $value
	 * @return T&Loggable
	 */
	public static function makeLoggable(object $value): object
	{
		return $value;
	}
}

class PlainObject {}

function doFoo(): void
{
	$result = Factory::makeLoggable(new PlainObject());
}
```

## Why is it reported?

PHPStan reports this error when the return type of a static method call contains an unresolvable type after generic template substitution. This happens when the template type resolves to a value that makes the return type impossible.

In the example above, `makeLoggable` returns `T&Loggable`. When called with `new PlainObject()`, `T` resolves to `PlainObject`. Since `PlainObject` does not implement `Loggable`, the intersection `PlainObject&Loggable` is impossible and PHPStan cannot determine a valid return type.

## How to fix it

Pass an argument whose type satisfies all constraints in the return type:

```diff-php
+class LoggableObject implements Loggable
+{
+	public function log(): void {}
+}
+
 function doFoo(): void
 {
-	$result = Factory::makeLoggable(new PlainObject());
+	$result = Factory::makeLoggable(new LoggableObject());
 }
```

Or constrain the template type to require the interface upfront:

```diff-php
 /**
- * @template T of object
+ * @template T of Loggable
  * @param T $value
- * @return T&Loggable
+ * @return T
  */
 public static function makeLoggable(object $value): object
```
