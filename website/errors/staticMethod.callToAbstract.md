---
title: "staticMethod.callToAbstract"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Processor
{
	public static function process(): void;
}

function doFoo(): void
{
	Processor::process();
}
```

## Why is it reported?

An abstract static method is being called directly on an interface or abstract class. Abstract methods have no implementation, so calling them will result in a fatal error at runtime. Static calls to abstract methods cannot be dispatched to a concrete implementation because there is no instance to determine which class should handle the call.

This commonly happens when calling a static method on an interface type rather than on a concrete class that implements it.

## How to fix it

Call the method on a concrete class that implements the interface:

```diff-php
+class MyProcessor implements Processor
+{
+	public static function process(): void
+	{
+		// implementation
+	}
+}
+
 function doFoo(): void
 {
-	Processor::process();
+	MyProcessor::process();
 }
```

Or pass a concrete instance and call the method on it:

```diff-php
-function doFoo(): void
+function doFoo(Processor $processor): void
 {
-	Processor::process();
+	$processor::process();
 }
```
