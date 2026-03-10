---
title: "method.unresolvableReturnType"
shortDescription: "Return type of a method call contains an unresolvable type after template substitution."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Loggable
{
	public function log(): void;
}

class Service
{
	/**
	 * @template T of object
	 * @param T $value
	 * @return T&Loggable
	 */
	public function makeLoggable(object $value): object
	{
		return $value;
	}
}

class PlainObject {}

function doFoo(Service $service): void
{
	$service->makeLoggable(new PlainObject());
}
```

## Why is it reported?

PHPStan reports this error when the return type of a method call contains an unresolvable type after generic template substitution. This happens when the template type resolves to a value that makes the return type impossible.

In the example above, `makeLoggable` returns `T&Loggable`. When called with `new PlainObject()`, `T` resolves to `PlainObject`. Since `PlainObject` does not implement `Loggable`, the intersection `PlainObject&Loggable` is impossible and PHPStan cannot determine a valid return type.

## How to fix it

Pass an argument whose type satisfies all constraints in the return type:

```diff-php
+class LoggableObject implements Loggable
+{
+	public function log(): void {}
+}
+
 function doFoo(Service $service): void
 {
-	$service->makeLoggable(new PlainObject());
+	$result = $service->makeLoggable(new LoggableObject());
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
 public function makeLoggable(object $value): object
```
