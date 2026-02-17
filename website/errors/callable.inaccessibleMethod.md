---
title: "callable.inaccessibleMethod"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private function secretMethod(): void
	{
	}
}

function doFoo(): void
{
	$callable = [new Foo(), 'secretMethod'];
	$callable();
}
```

## Why is it reported?

A callable references a class method that is not accessible from the current scope. The method is either `private` or `protected`, and the code attempting to call it is outside the allowed visibility. Invoking such a callable will result in a runtime error.

In the example above, `secretMethod()` is `private` in `Foo`, but the callable is created and invoked from the `doFoo()` function which is outside the class and has no access to the private method.

## How to fix it

Change the method visibility to `public` if it is intended to be called externally:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	private function secretMethod(): void
+	public function secretMethod(): void
 	{
 	}
 }

 function doFoo(): void
 {
 	$callable = [new Foo(), 'secretMethod'];
 	$callable();
 }
```

Or provide a public method that delegates to the private one:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private function secretMethod(): void
 	{
 	}

+	public function run(): void
+	{
+		$this->secretMethod();
+	}
 }

 function doFoo(): void
 {
-	$callable = [new Foo(), 'secretMethod'];
-	$callable();
+	(new Foo())->run();
 }
```
