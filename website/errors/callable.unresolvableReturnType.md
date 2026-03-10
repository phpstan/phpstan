---
title: "callable.unresolvableReturnType"
shortDescription: "Return type of a callable call contains an unresolvable type after template substitution."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Loggable
{
	public function log(): void;
}

/**
 * @template T of object
 * @param T $value
 * @return T&Loggable
 */
function makeLoggable(object $value): object
{
	return $value;
}

class PlainObject {}

function doFoo(): void
{
	$fn = makeLoggable(...);
	$fn(new PlainObject());
}
```

## Why is it reported?

PHPStan reports this error when the return type of a callable or first-class callable call contains an unresolvable type after generic template substitution. This happens when the template type resolves to a value that makes the return type impossible.

In the example above, `makeLoggable` returns `T&Loggable`. When the first-class callable `$fn` is called with `new PlainObject()`, `T` resolves to `PlainObject`. Since `PlainObject` does not implement `Loggable`, the intersection `PlainObject&Loggable` is impossible and becomes unresolvable.

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
 	$fn = makeLoggable(...);
-	$fn(new PlainObject());
+	$fn(new LoggableObject());
 }
```

Or simplify the return type to avoid intersection types:

```diff-php
 /**
- * @template T of object
- * @param T $value
- * @return T&Loggable
+ * @param object $value
+ * @return Loggable
  */
-function makeLoggable(object $value): object
+function makeLoggable(object $value): Loggable
```
