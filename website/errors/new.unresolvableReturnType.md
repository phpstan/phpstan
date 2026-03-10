---
title: "new.unresolvableReturnType"
shortDescription: "Return type of a constructor call contains an unresolvable type after template substitution."
ignorable: true
feasible: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface Loggable {}

/**
 * @template T of object
 */
class Wrapper
{
	/**
	 * @param T $value
	 * @phpstan-self-out self<T&Loggable>
	 */
	public function __construct(private object $value) {}
}

class PlainObject {}

function doFoo(): void
{
	new Wrapper(new PlainObject());
}
```

## Why is it reported?

PHPStan reports this error when the return type of a `new` expression contains an unresolvable type after generic template substitution. The return type of a constructor call is the class itself with its template parameters resolved. If the resolved template types make the class type contain an impossible intersection or an otherwise unresolvable type, PHPStan reports this error.

In the example above, if the constructor's `@phpstan-self-out` resolves to `self<PlainObject&Loggable>`, and `PlainObject` does not implement `Loggable`, the intersection is impossible and the return type becomes unresolvable.

## How to fix it

Pass an argument whose type satisfies all constraints:

```diff-php
+class LoggableObject implements Loggable
+{
+	public function log(): void {}
+}
+
 function doFoo(): void
 {
-	new Wrapper(new PlainObject());
+	$w = new Wrapper(new LoggableObject());
 }
```

Or constrain the template type so the intersection is always valid:

```diff-php
 /**
- * @template T of object
+ * @template T of Loggable
  */
 class Wrapper
 {
-	/**
-	 * @param T $value
-	 * @phpstan-self-out self<T&Loggable>
-	 */
-	public function __construct(private object $value) {}
+	/** @param T $value */
+	public function __construct(private object $value) {}
 }
```
